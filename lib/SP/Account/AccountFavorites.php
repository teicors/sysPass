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

namespace SP\Account;

use SP\Storage\DbWrapper;
use SP\Storage\QueryData;

/**
 * Class AccountFavorites para la gestión de las cuentas favoritas de los usuarios
 *
 * @package SP\Account
 */
class AccountFavorites
{
    /**
     * Obtener un array con los Ids de cuentas favoritas
     *
     * @param $userId int El Id de usuario
     * @return array
     */
    public static function getFavorites($userId)
    {
        $query = /** @lang SQL */
            'SELECT accountId FROM AccountToFavorite WHERE userId = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($userId);

        $queryRes = DbWrapper::getResultsArray($Data);

        $favorites = [];

        foreach($queryRes as $favorite){
            $favorites[] = (int)$favorite->accfavorite_accountId;
        }

        return $favorites;
    }

    /**
     * Añadir una cuenta a la lista de favoritos
     *
     * @param $accountId int El Id de la cuenta
     * @param $userId    int El Id del usuario
     * @throws \SP\Core\Exceptions\SPException
     */
    public static function addFavorite($accountId, $userId)
    {
        $query = /** @lang SQL */
            'INSERT INTO AccountToFavorite SET accountId = ?, userId = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($accountId);
        $Data->addParam($userId);
        $Data->setOnErrorMessage(__('Error al añadir favorito', false));

        DbWrapper::getQuery($Data);
    }

    /**
     * Eliminar una cuenta de la lista de favoritos
     *
     * @param $accountId int El Id de la cuenta
     * @param $userId    int El Id del usuario
     * @return void
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public static function deleteFavorite($accountId, $userId)
    {
        $query = /** @lang SQL */
            'DELETE FROM AccountToFavorite WHERE accountId = ? AND userId = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($accountId);
        $Data->addParam($userId);
        $Data->setOnErrorMessage(__('Error al eliminar favorito', false));

        DbWrapper::getQuery($Data);
    }
}