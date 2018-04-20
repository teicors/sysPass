<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      http://syspass.org
 * @copyright 2012-2017, Rubén Domínguez nuxsmin@$syspass.org
 *
 * This file is part of sysPass.
 *
 * sysPass is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sysPass is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 *  along with sysPass.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace SP\Modules\Web;

use Defuse\Crypto\Exception\CryptoException;
use DI\Container;
use SP\Bootstrap;
use SP\Core\Context\ContextException;
use SP\Core\Context\ContextInterface;
use SP\Core\Context\SessionContext;
use SP\Core\Crypt\CryptSessionHandler;
use SP\Core\Crypt\Session as CryptSession;
use SP\Core\Language;
use SP\Core\ModuleBase;
use SP\Core\UI\Theme;
use SP\Http\Request;
use SP\Services\Crypt\SecureSessionService;
use SP\Services\Upgrade\UpgradeAppService;
use SP\Services\Upgrade\UpgradeDatabaseService;
use SP\Services\Upgrade\UpgradeUtil;
use SP\Services\UserProfile\UserProfileService;
use SP\Storage\Database;
use SP\Storage\DBUtil;
use SP\Util\HttpUtil;

/**
 * Class Init
 *
 * @package SP\Modules\Web
 */
class Init extends ModuleBase
{
    /**
     * List of controllers that don't need to perform fully initialization
     * like: install/database checks, session/event handlers initialization
     */
    const PARTIAL_INIT = ['resource', 'install', 'bootstrap', 'status', 'upgrade', 'error'];

    /**
     * @var SessionContext
     */
    protected $context;
    /**
     * @var Theme
     */
    protected $theme;
    /**
     * @var Language
     */
    protected $language;
    /**
     * @var SecureSessionService
     */
    protected $secureSessionService;

    /**
     * Init constructor.
     *
     * @param Container $container
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->context = $container->get(ContextInterface::class);
        $this->theme = $container->get(Theme::class);
        $this->language = $container->get(Language::class);
        $this->secureSessionService = $container->get(SecureSessionService::class);
    }

    /**
     * Initialize Web App
     *
     * @param string $controller
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \SP\Core\Exceptions\SPException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function initialize($controller)
    {
        debugLog(__METHOD__);

        // Iniciar la sesión de PHP
        $this->initSession($this->configData->isEncryptSession());

        // Volver a cargar la configuración si se recarga la página
        if (Request::checkReload($this->router) === false) {
            // Cargar la configuración
            $this->config->loadConfig($this->context);

            // Cargar el lenguaje
            $this->language->setLanguage();

            // Initialize theme
            $this->theme->initialize();
        } else {
            debugLog('Browser reload');

            $this->context->setAppStatus(SessionContext::APP_STATUS_RELOADED);

            // Cargar la configuración
            $this->config->loadConfig($this->context, true);

            // Restablecer el idioma
            $this->language->setLanguage(true);

            // Re-Initialize theme
            $this->theme->initialize(true);
        }

        // Comprobar si es necesario cambiar a HTTPS
        HttpUtil::checkHttps($this->configData);

        if (in_array($controller, self::PARTIAL_INIT, true) === false) {
            // Checks if sysPass is installed
            if ($this->checkInstalled() === false) {
                $this->router->response()
                    ->redirect('index.php?r=install/index')
                    ->send();
            }

            // Checks if maintenance mode is turned on
            if ($this->checkMaintenanceMode($this->context)) {
                $this->router->response()
                    ->redirect('index.php?r=error/maintenanceError')
                    ->send();
            }

            // Checks if upgrade is needed
            if ($this->checkUpgrade()) {
                $this->config->generateUpgradeKey();

                $this->router->response()
                    ->redirect('index.php?r=upgrade/index')
                    ->send();
            }

            // Checks if the database is set up
            if (!DBUtil::checkDatabaseExist($this->container->get(Database::class)->getDbHandler(), $this->configData->getDbName())) {
                $this->router->response()
                    ->redirect('index.php?r=error/databaseError')
                    ->send();
            }

            // Initialize event handlers
            $this->initEventHandlers();

            // Initialize user session context
            $this->initUserSession();

            // Load plugins
//            PluginUtil::loadPlugins();

            // Comprobar acciones en URL
//        $this->checkPreLoginActions();

            if ($this->context->isLoggedIn() && $this->context->getAppStatus() === SessionContext::APP_STATUS_RELOADED) {
                debugLog('Reload user profile');
                // Recargar los permisos del perfil de usuario
                $this->context->setUserProfile($this->container->get(UserProfileService::class)->getById($this->context->getUserData()->getUserProfileId())->getProfile());
            }

            return;
        }

        // Do not keep the PHP's session opened
        SessionContext::close();
    }

    /**
     * Iniciar la sesión PHP
     *
     * @param bool $encrypt Encriptar la sesión de PHP
     * @throws ContextException
     */
    private function initSession($encrypt = false)
    {
        if ($encrypt === true
            && Bootstrap::$checkPhpVersion
            && ($key = $this->secureSessionService->getKey()) !== false) {
            session_set_save_handler(new CryptSessionHandler($key), true);
        }


        try {
            $this->context->initialize();
        } catch (ContextException $e) {
            $this->router->response()->header('HTTP/1.1', '500 Internal Server Error');

            throw $e;
        }
    }

    /**
     * Comprueba que la aplicación esté instalada
     * Esta función comprueba si la aplicación está instalada. Si no lo está, redirige al instalador.
     */
    private function checkInstalled()
    {
        return $this->configData->isInstalled()
            && $this->router->request()->param('r') !== 'install/index';
    }

    /**
     * Comprobar si es necesario actualizar componentes
     */
    private function checkUpgrade()
    {
        return $this->configData->getUpgradeKey()
            || (UpgradeDatabaseService::needsUpgrade($this->configData->getDatabaseVersion()) ||
                UpgradeAppService::needsUpgrade(UpgradeUtil::fixVersionNumber($this->configData->getConfigVersion())));
    }

    /**
     * Inicializar la sesión de usuario
     *
     */
    private function initUserSession()
    {
        $lastActivity = $this->context->getLastActivity();
        $inMaintenance = $this->configData->isMaintenance();

        // Timeout de sesión
        if ($lastActivity > 0
            && !$inMaintenance
            && time() > ($lastActivity + $this->getSessionLifeTime())
        ) {
            if ($this->router->request()->cookies()->get(session_name()) !== null) {
                $this->router->response()->cookie(session_name(), '', time() - 42000);
            }

            SessionContext::restart();
        } else {

            $sidStartTime = $this->context->getSidStartTime();

            // Regenerar el Id de sesión periódicamente para evitar fijación
            if ($sidStartTime === 0) {
                // Intentar establecer el tiempo de vida de la sesión en PHP
                @ini_set('session.gc_maxlifetime', $this->getSessionLifeTime());

                $this->context->setSidStartTime(time());
                $this->context->setStartActivity(time());
            } else if (!$inMaintenance
                && time() > ($sidStartTime + 120)
                && $this->context->isLoggedIn()
            ) {
                try {
                    CryptSession::reKey($this->context);

                    // Recargar los permisos del perfil de usuario
//                $this->session->setUserProfile(Profile::getItem()->getById($this->session->getUserData()->getUserProfileId()));
                } catch (CryptoException $e) {
                    debugLog($e->getMessage());

                    SessionContext::restart();
                    return;
                }
            }

            $this->context->setLastActivity(time());
        }
    }

    /**
     * Obtener el timeout de sesión desde la configuración.
     *
     * @return int con el tiempo en segundos
     */
    private function getSessionLifeTime()
    {
        if (($timeout = $this->context->getSessionTimeout()) === null) {
            return $this->context->setSessionTimeout($this->configData->getSessionTimeout());
        }

        return $timeout;
    }
}