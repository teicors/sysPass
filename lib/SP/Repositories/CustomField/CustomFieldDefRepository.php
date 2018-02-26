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

namespace SP\Repositories\CustomField;

use SP\Core\Acl\ActionsInterface;
use SP\Core\Exceptions\SPException;
use SP\DataModel\CustomFieldDefinitionData;
use SP\DataModel\ItemSearchData;
use SP\Repositories\Repository;
use SP\Repositories\RepositoryItemInterface;
use SP\Repositories\RepositoryItemTrait;
use SP\Storage\DbWrapper;
use SP\Storage\QueryData;

/**
 * Class CustomFieldDefRepository
 *
 * @package SP\Repositories\CustomField
 */
class CustomFieldDefRepository extends Repository implements RepositoryItemInterface
{
    use RepositoryItemTrait;

    /**
     * @param $id
     * @return mixed
     */
    public static function getFieldModuleById($id)
    {
        $modules = self::getFieldModules();

        return isset($modules[$id]) ? $modules[$id] : $id;
    }

    /**
     * Devuelve los módulos disponibles para los campos personalizados
     *
     * @return array
     */
    public static function getFieldModules()
    {
        $modules = [
            ActionsInterface::ACCOUNT => __('Cuentas'),
            ActionsInterface::CATEGORY => __('Categorías'),
            ActionsInterface::CLIENT => __('Clientes'),
            ActionsInterface::USER => __('Usuarios'),
            ActionsInterface::GROUP => __('Grupos')

        ];

        return $modules;
    }

    /**
     * Creates an item
     *
     * @param CustomFieldDefinitionData $itemData
     * @return mixed
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function create($itemData)
    {
        $query = /** @lang SQL */
            'INSERT INTO CustomFieldDefinition SET name = ?, moduleId = ?, required = ?, help = ?, showInList = ?, typeId = ?';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($itemData->getName());
        $queryData->addParam($itemData->getModuleId());
        $queryData->addParam($itemData->getRequired());
        $queryData->addParam($itemData->getHelp());
        $queryData->addParam($itemData->getShowInList());
        $queryData->addParam($itemData->getTypeId());
        $queryData->setOnErrorMessage(__u('Error al crear el campo personalizado'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getLastId();
    }

    /**
     * Updates an item
     *
     * @param CustomFieldDefinitionData $itemData
     * @return mixed
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function update($itemData)
    {
        $query = /** @lang SQL */
            'UPDATE CustomFieldDefinition 
              SET name = ?, moduleId = ?, required = ?, help = ?, showInList = ?, typeId = ?
              WHERE id = ? LIMIT 1';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($itemData->getName());
        $queryData->addParam($itemData->getModuleId());
        $queryData->addParam($itemData->getRequired());
        $queryData->addParam($itemData->getHelp());
        $queryData->addParam($itemData->getShowInList());
        $queryData->addParam($itemData->getTypeId());
        $queryData->addParam($itemData->getId());
        $queryData->setOnErrorMessage(__u('Error al actualizar el campo personalizado'));

        return DbWrapper::getQuery($queryData, $this->db);
    }

    /**
     * Returns the item for given id
     *
     * @param int $id
     * @return CustomFieldDefinitionData
     */
    public function getById($id)
    {
        $query = /** @lang SQL */
            'SELECT id, name, moduleId, required, help, showInList, typeId
              FROM CustomFieldDefinition
              WHERE id = ? LIMIT 1';

        $queryData = new QueryData();
        $queryData->setMapClassName(CustomFieldDefinitionData::class);
        $queryData->setQuery($query);
        $queryData->addParam($id);

        return DbWrapper::getResults($queryData, $this->db);
    }

    /**
     * Returns all the items
     *
     * @return CustomFieldDefinitionData[]
     */
    public function getAll()
    {
        $query = /** @lang SQL */
            'SELECT id, name, moduleId, required, help, showInList
              FROM CustomFieldDefinition
              ORDER BY moduleId';

        $queryData = new QueryData();
        $queryData->setMapClassName(CustomFieldDefinitionData::class);
        $queryData->setQuery($query);

        return DbWrapper::getResultsArray($queryData, $this->db);
    }

    /**
     * Returns all the items for given ids
     *
     * @param array $ids
     * @return array
     */
    public function getByIdBatch(array $ids)
    {
        $query = /** @lang SQL */
            'SELECT id, name, moduleId, required, help, showInList, typeId
              FROM CustomFieldDefinition
              WHERE id IN (' . $this->getParamsFromArray($ids) . ')';

        $queryData = new QueryData();
        $queryData->setMapClassName(CustomFieldDefinitionData::class);
        $queryData->setQuery($query);
        $queryData->setParams($ids);

        return DbWrapper::getResults($queryData, $this->db);
    }

    /**
     * Deletes all the items for given ids
     *
     * @param array $ids
     * @return int
     * @throws SPException
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    public function deleteByIdBatch(array $ids)
    {
        if ($this->deleteItemsDataForDefinitionBatch($ids) === false) {
            throw new SPException(__u('Error al eliminar los campos personalizados'), SPException::ERROR);
        }

        $query = /** @lang SQL */
            'DELETE FROM CustomFieldDefinition WHERE id IN (' . $this->getParamsFromArray($ids) . ')';

        $queryData = new QueryData();
        $queryData->setQuery($query);
        $queryData->addParam($ids);
        $queryData->setOnErrorMessage(__u('Error al eliminar los campos personalizados'));

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getNumRows();
    }

    /**
     * Eliminar los datos de los elementos de una definición
     *
     * @param array $ids
     * @return bool
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     */
    protected function deleteItemsDataForDefinitionBatch(array $ids)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM CustomFieldData WHERE id IN (' . $this->getParamsFromArray($ids) . ')');
        $queryData->setParams($ids);

        DbWrapper::getQuery($queryData, $this->db);

        return $this->db->getNumRows();
    }

    /**
     * Deletes an item
     *
     * @param $id
     * @return bool
     * @throws \SP\Core\Exceptions\ConstraintException
     * @throws \SP\Core\Exceptions\QueryException
     * @throws \SP\Core\Exceptions\SPException
     */
    public function delete($id)
    {
        if ($this->deleteItemsDataForDefinition($id) === false) {
            throw new SPException(__u('Error al eliminar el campo personalizado'), SPException::ERROR);
        }

        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM CustomFieldDefinition WHERE id = ? LIMIT 1');
        $queryData->addParam($id);
        $queryData->setOnErrorMessage(__u('Error al eliminar el campo personalizado'));

        return DbWrapper::getQuery($queryData, $this->db);
    }

    /**
     * Eliminar los datos de los elementos de una definición
     *
     * @param $id
     * @return bool
     * @throws \SP\Core\Exceptions\SPException
     */
    protected function deleteItemsDataForDefinition($id)
    {
        $queryData = new QueryData();
        $queryData->setQuery('DELETE FROM CustomFieldData WHERE id = ?');
        $queryData->addParam($id);

        return DbWrapper::getQuery($queryData, $this->db);
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
     * @param ItemSearchData $SearchData
     * @return CustomFieldDefinitionData[]
     */
    public function search(ItemSearchData $SearchData)
    {
        $queryData = new QueryData();
        $queryData->setMapClassName(CustomFieldDefinitionData::class);
        $queryData->setSelect('CFD.id, CFD.name, CFD.moduleId, CFD.required, CFD.help, CFD.showInList, CFD.typeId, CFT.name AS typeName');
        $queryData->setFrom('CustomFieldDefinition CFD INNER JOIN CustomFieldType CFT ON CFD.typeId = CFT.id');
        $queryData->setOrder('CFD.moduleId');

        $queryData->setLimit('?,?');
        $queryData->addParam($SearchData->getLimitStart());
        $queryData->addParam($SearchData->getLimitCount());

        DbWrapper::setFullRowCount();

        /** @var CustomFieldDefinitionData[] $queryRes */
        $queryRes = DbWrapper::getResultsArray($queryData, $this->db);

        $queryRes['count'] = $queryData->getQueryNumRows();

        return $queryRes;
    }
}