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

namespace SP\Repositories\Notification;

use SP\DataModel\ItemSearchData;
use SP\DataModel\NotificationData;
use SP\Repositories\Repository;
use SP\Repositories\RepositoryItemInterface;
use SP\Repositories\RepositoryItemTrait;
use SP\Storage\DbWrapper;
use SP\Storage\QueryData;

/**
 * Class NotificationRepository
 *
 * @package SP\Repositories\Notification
 */
class NotificationRepository extends Repository implements RepositoryItemInterface
{
    use RepositoryItemTrait;

    /**
     * Creates an item
     *
     * @param NotificationData $itemData
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function create($itemData)
    {
        $query = /** @lang SQL */
            'INSERT INTO Notification 
            SET type = ?,
            component = ?,
            description = ?,
            `date` = UNIX_TIMESTAMP(),
            checked = 0,
            userId = ?,
            sticky = ?,
            onlyAdmin = ?';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($itemData->getType());
        $queryData->addParam($itemData->getComponent());
        $queryData->addParam($itemData->getDescription());
        $queryData->addParam($itemData->getUserId() ?: null);
        $queryData->addParam($itemData->isSticky());
        $queryData->addParam($itemData->isOnlyAdmin());
        $queryData->setOnErrorMessage(__u('Error al crear la notificación'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getLastId();
    }

    /**
     * Updates an item
     *
     * @param NotificationData $itemData
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function update($itemData)
    {
        $query = /** @lang SQL */
            'UPDATE Notification 
            SET type = ?,
            component = ?,
            description = ?,
            `date` = UNIX_TIMESTAMP(),
            checked = ?,
            userId = ?,
            sticky = ?,
            onlyAdmin = ? 
            WHERE id = ? LIMIT 1';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($itemData->getType());
        $queryData->addParam($itemData->getComponent());
        $queryData->addParam($itemData->getDescription());
        $queryData->addParam($itemData->isChecked());
        $queryData->addParam($itemData->getUserId() ?: null);
        $queryData->addParam($itemData->isSticky());
        $queryData->addParam($itemData->isOnlyAdmin());
        $queryData->addParam($itemData->getId());
        $queryData->setOnErrorMessage(__u('Error al modificar la notificación'));

        DbWrapper::getQuery($queryData, $this->db);

        return $queryData->getQueryNumRows();
    }

    /**
     * Deletes an item
     *
     * @param $id
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function delete($id)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM Notification WHERE id = ? AND sticky = 0 LIMIT 1');
        $queryData->addParam($id);
        $queryData->setOnErrorMessage(__u('Error al eliminar la notificación'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getNumRows();
    }

    /**
     * Deletes an item
     *
     * @param $id
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function deleteAdmin($id)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM Notification WHERE id = ? LIMIT 1');
        $queryData->addParam($id);
        $queryData->setOnErrorMessage(__u('Error al eliminar la notificación'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getNumRows();
    }

    /**
     * Deletes an item
     *
     * @param array $ids
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function deleteAdminBatch(array $ids)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM Notification WHERE id IN (' . $this->getParamsFromArray($ids) . ')');
        $queryData->setParams($ids);
        $queryData->setOnErrorMessage(__u('Error al eliminar las notificaciones'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getNumRows();
    }

    /**
     * Returns the item for given id
     *
     * @param int $id
     * @return NotificationData
     */
    public function getById($id)
    {
        $query = /** @lang SQL */
            'SELECT id, 
            type,
            component,
            description,
            `date`,
            userId,
            checked,
            sticky,
            onlyAdmin 
            FROM Notification 
            WHERE id = ? LIMIT 1';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($id);
        $queryData->setMapClassName(NotificationData::class);
        $queryData->setOnErrorMessage(__u('Error al obtener la notificación'));

        return DbWrapper::getResults($queryData, $this->db);
    }

    /**
     * Returns all the items
     *
     * @return NotificationData[]
     */
    public function getAll()
    {
        $query = /** @lang SQL */
            'SELECT id 
            notice_type,
            component,
            description,
            `date`,
            userId,
            checked,
            sticky,
            onlyAdmin 
            FROM Notification';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->setMapClassName(NotificationData::class);
        $queryData->setOnErrorMessage(__u('Error al obtener las notificaciones'));

        return DbWrapper::getResultsArray($queryData, $this->db);
    }

    /**
     * Returns all the items for given ids
     *
     * @param array $ids
     * @return NotificationData[]
     */
    public function getByIdBatch(array $ids)
    {
        $query = /** @lang SQL */
            'SELECT id, 
            type,
            component,
            description,
            `date`,
            userId,
            checked,
            sticky,
            onlyAdmin 
            FROM Notification 
            WHERE id IN (' . $this->getParamsFromArray($ids) . ')';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->setParams($ids);
        $queryData->setMapClassName(NotificationData::class);

        return DbWrapper::getResultsArray($queryData, $this->db);
    }

    /**
     * Deletes all the items for given ids
     *
     * @param array $ids
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function deleteByIdBatch(array $ids)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM Notification WHERE id IN (' . $this->getParamsFromArray($ids) . ') AND sticky = 0');
        $queryData->setParams($ids);
        $queryData->setOnErrorMessage(__u('Error al eliminar las notificaciones'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getNumRows();
    }

    /**
     * Checks whether the item is in use or not
     *
     * @param $id int
     */
    public function checkInUse($id)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * Checks whether the item is duplicated on updating
     *
     * @param mixed $itemData
     */
    public function checkDuplicatedOnUpdate($itemData)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * Checks whether the item is duplicated on adding
     *
     * @param mixed $itemData
     */
    public function checkDuplicatedOnAdd($itemData)
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * Searches for items by a given filter
     *
     * @param ItemSearchData $itemSearchData
     * @return mixed
     */
    public function search(ItemSearchData $itemSearchData)
    {
        $queryData = new QueryData();
        $queryData->setSelect('id, type, component, description, `date`, checked, userId, sticky, onlyAdmin');
        $queryData->setFrom('Notification');
        $queryData->setOrder('`date` DESC');

        if ($itemSearchData->getSeachString() !== '') {
            $queryData->setWhere('type LIKE ? OR component LIKE ? OR description LIKE ?');

            $search = '%' . $itemSearchData->getSeachString() . '%';
            $queryData->addParam($search);
            $queryData->addParam($search);
            $queryData->addParam($search);
        }

        $queryData->setLimit('?,?');
        $queryData->addParam($itemSearchData->getLimitStart());
        $queryData->addParam($itemSearchData->getLimitCount());

        DbWrapper::setFullRowCount();

        /** @var array $queryRes */
        $queryRes = DbWrapper::getResultsArray($queryData, $this->db);

        $queryRes['count'] = $queryData->getQueryNumRows();

        return $queryRes;
    }

    /**
     * Searches for items by a given filter
     *
     * @param ItemSearchData $itemSearchData
     * @param int            $userId
     * @return mixed
     */
    public function searchForUserId(ItemSearchData $itemSearchData, $userId)
    {
        $queryData = new QueryData();
        $queryData->setSelect('id, type, component, description, `date`, checked, userId, sticky, onlyAdmin');
        $queryData->setFrom('Notification');
        $queryData->setOrder('`date` DESC');

        $filterUser = '(userId = ? OR (userId = NULL AND onlyAdmin = 0) OR sticky = 1)';

        if ($itemSearchData->getSeachString() !== '') {
            $queryData->setWhere('(type LIKE ? OR component LIKE ? OR description LIKE ?) AND ' . $filterUser);

            $search = '%' . $itemSearchData->getSeachString() . '%';
            $queryData->addParam($search);
            $queryData->addParam($search);
            $queryData->addParam($search);
            $queryData->addParam($userId);
        } else {
            $queryData->setWhere($filterUser);
            $queryData->addParam($userId);
        }

        $queryData->setLimit('?,?');
        $queryData->addParam($itemSearchData->getLimitStart());
        $queryData->addParam($itemSearchData->getLimitCount());

        DbWrapper::setFullRowCount();

        /** @var array $queryRes */
        $queryRes = DbWrapper::getResultsArray($queryData, $this->db);

        $queryRes['count'] = $queryData->getQueryNumRows();

        return $queryRes;
    }

    /**
     * Marcar una notificación como leída
     *
     * @param $id
     * @return int
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function setCheckedById($id)
    {
        $query = /** @lang SQL */
            'UPDATE Notification SET checked = 1 WHERE id = ? LIMIT 1';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($id);
        $queryData->setOnErrorMessage(__u('Error al modificar la notificación'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getLastId();
    }

    /**
     * Devolver las notificaciones de un usuario para una fecha y componente determinados
     *
     * @param $component
     * @param $id
     * @return NotificationData[]
     */
    public function getForUserIdByDate($component, $id)
    {
        $query = /** @lang SQL */
            'SELECT type,
            component,
            description,
            `date`,
            userId,
            checked,
            sticky,
            onlyAdmin 
            FROM Notification 
            WHERE component = ? AND 
            (UNIX_TIMESTAMP() - date) <= 86400 AND
            userId = ?';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($component);
        $queryData->addParam($id);
        $queryData->setMapClassName(NotificationData::class);
        $queryData->setOnErrorMessage(__u('Error al obtener las notificaciones'));

        return DbWrapper::getResultsArray($queryData, $this->db);
    }

    /**
     * @param $id
     * @return NotificationData[]
     */
    public function getAllForUserId($id)
    {
        $query = /** @lang SQL */
            'SELECT id,
            type,
            component,
            description,
            `date`,
            userId,
            checked,
            sticky,
            onlyAdmin 
            FROM Notification 
            WHERE (userId = ? OR userId IS NULL OR sticky = 1)
            AND onlyAdmin = 0 
            ORDER BY date DESC ';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($id);
        $queryData->setMapClassName(NotificationData::class);
        $queryData->setOnErrorMessage(__u('Error al obtener las notificaciones'));

        return DbWrapper::getResultsArray($queryData, $this->db);
    }

    /**
     * @param $id
     * @return NotificationData[]
     */
    public function getAllActiveForUserId($id)
    {
        $query = /** @lang SQL */
            'SELECT id,
            type,
            component,
            description,
            `date`,
            userId,
            checked,
            sticky,
            onlyAdmin 
            FROM Notification 
            WHERE (userId = ? OR sticky = 1) 
            AND onlyAdmin = 0 
            AND checked = 0
            ORDER BY date DESC ';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($id);
        $queryData->setMapClassName(NotificationData::class);
        $queryData->setOnErrorMessage(__u('Error al obtener las notificaciones'));

        return DbWrapper::getResultsArray($queryData, $this->db);
    }
}