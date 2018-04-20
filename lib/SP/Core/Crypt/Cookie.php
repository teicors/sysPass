<?php
/**
 * sysPass
 *
 * @author    nuxsmin
 * @link      https://syspass.org
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

namespace SP\Core\Crypt;

use SP\Bootstrap;

/**
 * Class Cookie
 *
 * @package SP\Core\Crypt
 */
abstract class Cookie
{
    /**
     * @var string
     */
    private $cookieName;

    /**
     * Cookie constructor.
     *
     * @param string $cookieName
     */
    public function __construct($cookieName)
    {
        $this->cookieName = $cookieName;
    }

    /**
     * Firmar la cookie para autentificación
     *
     * @param string $data
     * @param string $cypher
     * @return string
     */
    protected final function sign($data, $cypher)
    {
        $data = base64_encode($data);

        return hash_hmac('sha256', $data, $cypher) . ';' . $data;
    }

    /**
     * Comprobar la firma de la cookie y devolver los datos
     *
     * @param string $data
     * @param string $cypher
     * @return bool|string
     */
    protected final function getCookieData($data, $cypher)
    {
        list($signature, $data) = explode(';', $data, 2);

        if (!empty($signature) && !empty($data)) {
            return hash_equals($signature, hash_hmac('sha256', $data, $cypher)) ? base64_decode($data) : false;
        }

        return false;
    }

    /**
     * Returns cookie raw data
     *
     * @return bool|string
     */
    protected function getCookie()
    {
        return isset($_COOKIE[$this->cookieName]) ? $_COOKIE[$this->cookieName] : false;
    }

    /**
     * Sets cookie data
     *
     * @param $data
     * @return bool
     */
    protected function setCookie($data)
    {
        return setcookie($this->cookieName, $data, 0, Bootstrap::$WEBROOT);
    }
}