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

namespace SP\Mgmt\Users;

use SP\Core\Exceptions\SPException;
use SP\DataModel\UserData;
use SP\DataModel\UserPassRecoverData;
use SP\Mgmt\ItemInterface;
use SP\Storage\DbWrapper;
use SP\Storage\QueryData;

defined('APP_ROOT') || die();

/**
 * Class UserPassRecover para la gestión de recuperaciones de claves de usuarios
 *
 * @package SP
 * @property UserPassRecoverData $itemData
 */
class UserPassRecover extends UserPassRecoverBase implements ItemInterface
{
    /**
     * Tiempo máximo para recuperar la clave
     */
    const MAX_PASS_RECOVER_TIME = 3600;
    /**
     * Número de intentos máximos para recuperar la clave
     */
    const MAX_PASS_RECOVER_LIMIT = 3;
    const USER_LOGIN_EXIST = 1;
    const USER_MAIL_EXIST = 2;

    /**
     * Comprobar el límite de recuperaciones de clave.
     *
     * @param UserData $UserData con el login del usuario
     * @return bool
     */
    public static function checkPassRecoverLimit(UserData $UserData)
    {
        $query = /** @lang SQL */
            'SELECT userId 
            FROM UserPassRecover
            WHERE userId = ?
            AND used = 0
            AND date >= ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($UserData->getId());
        $Data->addParam(time() - self::MAX_PASS_RECOVER_TIME);

        try {
            DbWrapper::getQuery($Data);
        } catch (SPException $e) {
            return false;
        }

        return $Data->getQueryNumRows() >= self::MAX_PASS_RECOVER_LIMIT;
    }

    /**
     * Comprobar el hash de recuperación de clave.
     *
     * @param $hash
     * @return $this
     * @throws SPException
     */
    public function getHashUserId($hash)
    {
        $query = /** @lang SQL */
            'SELECT userId
            FROM UserPassRecover
            WHERE hash = ?
            AND used = 0
            AND date >= ?
            ORDER BY date DESC LIMIT 1';

        $Data = new QueryData();
        $Data->setMapClassName($this->getDataModel());
        $Data->setQuery($query);
        $Data->addParam($hash);
        $Data->addParam(time() - self::MAX_PASS_RECOVER_TIME);

        /** @var UserPassRecoverData $queryRes */
        $queryRes = DbWrapper::getResults($Data);

        if ($queryRes === false) {
            throw new SPException(__('Error en comprobación de hash', false), SPException::ERROR);
        } elseif ($Data->getQueryNumRows() === 0) {
            throw new SPException(__('Hash inválido o expirado', false), SPException::INFO);
        }

        $this->itemData = $queryRes;

        $this->update();

        return $this;
    }

    /**
     * @return $this
     * @throws SPException
     */
    public function update()
    {
        $query = /** @lang SQL */
            'UPDATE UserPassRecover SET used = 1 WHERE hash = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getHash());
        $Data->setOnErrorMessage(__('Error interno', false));

        DbWrapper::getQuery($Data);

        return $this;
    }

    /**
     * @return $this
     * @throws SPException
     */
    public function add()
    {
        $query = /** @lang SQL */
            'INSERT INTO UserPassRecover SET 
            userId = ?,
            hash = ?,
            date = UNIX_TIMESTAMP(),
            used = 0';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getUserId());
        $Data->addParam($this->itemData->getHash());
        $Data->setOnErrorMessage(__('Error al generar el hash de recuperación', false));

        DbWrapper::getQuery($Data);

        return $this;
    }

    /**
     * @param $id int
     * @return mixed
     */
    public function delete($id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $id int
     * @return mixed
     */
    public function getById($id)
    {
        // TODO: Implement getById() method.
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    /**
     * @param $id int
     * @return mixed
     */
    public function checkInUse($id)
    {
        // TODO: Implement checkInUse() method.
    }

    /**
     * @return bool
     */
    public function checkDuplicatedOnUpdate()
    {
        // TODO: Implement checkDuplicatedOnUpdate() method.
    }

    /**
     * @return bool
     */
    public function checkDuplicatedOnAdd()
    {
        // TODO: Implement checkDuplicatedOnAdd() method.
    }

    /**
     * Eliminar elementos en lote
     *
     * @param array $ids
     * @return $this
     */
    public function deleteBatch(array $ids)
    {
        // TODO: Implement deleteBatch() method.
    }

    /**
     * Devolver los elementos con los ids especificados
     *
     * @param array $ids
     * @return mixed
     */
    public function getByIdBatch(array $ids)
    {
        // TODO: Implement getByIdBatch() method.
    }
}