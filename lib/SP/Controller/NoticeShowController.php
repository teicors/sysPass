<?php
/**
 * sysPass
 *
 * @author nuxsmin 
 * @link https://syspass.org
 * @copyright 2012-2018, Rubén Domínguez nuxsmin@$syspass.org
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

namespace SP\Controller;

defined('APP_ROOT') || die();

use SP\Core\Acl\ActionsInterface;
use SP\Core\SessionUtil;
use SP\DataModel\NotificationData;
use SP\Mgmt\Notices\Notice;
use SP\Mgmt\Users\User;
use SP\Modules\Web\Controllers\ControllerBase;
use SP\Mvc\View\Template;
use SP\Util\Json;

/**
 * Class NoticeShowController
 *
 * @package SP\Controller
 */
class NoticeShowController extends ControllerBase implements ActionsInterface, ItemControllerInterface
{
    use RequestControllerTrait;

    /**
     * Máximo numero de acciones antes de agrupar
     */
    const MAX_NUM_ACTIONS = 3;
    /**
     * @var int
     */
    private $module = 0;

    /**
     * Constructor
     *
     * @param $template Template con instancia de plantilla
     */
    public function __construct(Template $template = null)
    {
        parent::__construct($template);

        $this->init();

        $this->view->assign('isDemo', $this->configData->isDemoEnabled());
        $this->view->assign('sk', SessionUtil::getSessionKey(true));
        $this->view->assign('itemId', $this->itemId);
        $this->view->assign('activeTab', $this->activeTab);
        $this->view->assign('actionId', $this->actionId);
        $this->view->assign('isView', false);
        $this->view->assign('showViewPass', true);
    }

    /**
     * Realizar la acción solicitada en la la petición HTTP
     *
     * @param mixed $type Tipo de acción
     * @throws \SP\Core\Exceptions\SPException
     */
    public function doAction($type = null)
    {
        try {
            switch ($this->actionId) {
                case self::NOTIFICATION_VIEW:
                    $this->view->assign('header', __('Ver Notificación'));
                    $this->view->assign('isView', true);
                    $this->getNotice();
                    break;
                case self::NOTIFICATION_CREATE:
                    $this->view->assign('header', __('Nueva Notificación'));
                    $this->getNotice();
                    break;
                case self::NOTIFICATION_EDIT:
                    $this->view->assign('header', __('Editar Notificación'));
                    $this->getNotice();
                    break;
                default:
                    $this->invalidAction();
            }

            if (count($this->JsonResponse->getData()) === 0) {
                $this->JsonResponse->setData(['html' => $this->render()]);
            }
        } catch (\Exception $e) {
            $this->JsonResponse->setDescription($e->getMessage());
        }

        $this->JsonResponse->setCsrf($this->view->sk);

        Json::returnJson($this->JsonResponse);
    }

    /**
     * Obtener los datos para la ficha de usuario
     *
     * @throws \SP\Core\Exceptions\SPException
     */
    protected function getNotice()
    {
        $this->module = self::USER;
        $this->view->addTemplate('notices');

        $this->view->assign('notice', $this->itemId ? Notice::getItem()->getById($this->itemId) : new NotificationData());
        $this->view->assign('isDisabled', ($this->view->isDemo || $this->view->actionId === self::NOTIFICATION_VIEW) ? 'disabled' : '');
        $this->view->assign('isReadonly', $this->view->isDisabled ? 'readonly' : '');

        if ($this->userData->isIsAdminApp()){
            $this->view->assign('users', User::getItem()->getItemsForSelect());
        }

        $this->JsonResponse->setStatus(0);
    }
}