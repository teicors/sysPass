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

namespace SP\Util;

defined('APP_ROOT') || die();


/**
 * Class Filter para el filtrado de datos
 *
 * @package SP\Util
 */
class Filter
{
    /**
     * Limpiar una cadena de búsqueda de carácteres utilizados en expresiones regulares
     *
     * @param $string
     * @return mixed
     */
    public static function safeSearchString($string)
    {
        return preg_replace(/** @lang RegExp */
            '/[\[\]%{}*$]+/', '', (string)$string);
    }
}