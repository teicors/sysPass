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

namespace SP\Mgmt\CustomFields;

defined('APP_ROOT') || die();

use SP\Core\Exceptions\SPException;
use SP\DataModel\CustomFieldDefinitionData;
use SP\Mgmt\ItemInterface;
use SP\Mgmt\ItemTrait;
use SP\Storage\DbWrapper;
use SP\Storage\QueryData;
use SP\Util\Util;

/**
 * Class CustomFieldDef para la gestión de definiciones de campos personalizados
 *
 * @package SP
 * @property CustomFieldDefinitionData $itemData
 */
class CustomFieldDef extends CustomFieldBase implements ItemInterface
{
    use ItemTrait;

    /**
     * @return mixed
     * @throws \SP\Core\Exceptions\SPException
     */
    public function add()
    {
        $query = /** @lang SQL */
            'INSERT INTO CustomFieldDefinition SET customfielddef_module = ?, customfielddef_field = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getModule());
        $Data->addParam(serialize($this->itemData));
        $Data->setOnErrorMessage(__('Error al crear el campo personalizado', false));

        DbWrapper::getQuery($Data);

        return $this;
    }

    /**
     * @param $id int|array
     * @return mixed
     * @throws SPException
     */
    public function delete($id)
    {
        if ($this->deleteItemsDataForDefinition($id) === false) {
            throw new SPException(__('Error al eliminar el campo personalizado', false), SPException::ERROR);
        }

        $query = /** @lang SQL */
            'DELETE FROM CustomFieldDefinition WHERE customfielddef_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($id);
        $Data->setOnErrorMessage(__('Error al eliminar el campo personalizado', false));

        DbWrapper::getQuery($Data);

        return $this;
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
        $query = /** @lang SQL */
            'DELETE FROM CustomFieldData WHERE customfielddata_defId = ?';
        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($id);

        return DbWrapper::getQuery($Data);
    }

    /**
     * @return mixed
     * @throws \SP\Core\Exceptions\SPException
     */
    public function update()
    {
        $curField = $this->getById($this->itemData->getId());

        $query = /** @lang SQL */
            'UPDATE CustomFieldDefinition SET
            customfielddef_module = ?,
            customfielddef_field = ?
            WHERE customfielddef_id= ? LIMIT 1';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getModule());
        $Data->addParam(serialize($this->itemData));
        $Data->addParam($this->itemData->getId());
        $Data->setOnErrorMessage(__('Error al actualizar el campo personalizado', false));

        DbWrapper::getQuery($Data);

        if ($curField->getModule() !== $this->itemData->getModule()) {
            $this->updateItemsModulesForDefinition();
        }

        return $this;
    }

    /**
     * @param $id int
     * @return CustomFieldDefinitionData
     * @throws \SP\Core\Exceptions\SPException
     */
    public function getById($id)
    {
        $query = /** @lang SQL */
            'SELECT customfielddef_id,
              customfielddef_module,
              customfielddef_field
              FROM CustomFieldDefinition
              WHERE customfielddef_id = ? LIMIT 1';

        $Data = new QueryData();
        $Data->setMapClassName($this->getDataModel());
        $Data->setQuery($query);
        $Data->addParam($id);

        /** @var CustomFieldDefinitionData $CustomFieldDef */
        $CustomFieldDef = DbWrapper::getResults($Data);

        if ($CustomFieldDef === false) {
            throw new SPException(__('Campo personalizado no encontrado', false), SPException::INFO);
        }

        /** @var CustomFieldDefinitionData $fieldDef */
        $fieldDef = Util::unserialize($this->getDataModel(), $CustomFieldDef->getCustomfielddefField());
        $fieldDef->setCustomfielddefId($CustomFieldDef->getCustomfielddefId());
        $fieldDef->setId($CustomFieldDef->getCustomfielddefId());

        return $fieldDef;
    }

    /**
     * Actualizar el módulo de los elementos con campos personalizados
     *
     * @return bool
     * @throws \SP\Core\Exceptions\SPException
     */
    protected function updateItemsModulesForDefinition()
    {
        $query = /** @lang SQL */
            'UPDATE CustomFieldData SET
            customfielddata_moduleId = ?
            WHERE customfielddata_defId = ?';

        $Data = new QueryData();
        $Data->setQuery($query);
        $Data->addParam($this->itemData->getModule());
        $Data->addParam($this->itemData->getId());

        return DbWrapper::getQuery($Data);
    }

    /**
     * @return CustomFieldDefinitionData[]|array
     * @throws \SP\Core\Exceptions\SPException
     */
    public function getAll()
    {
        $query = /** @lang SQL */
            'SELECT customfielddef_id,
              customfielddef_module,
              customfielddef_field
              FROM CustomFieldDefinition
              ORDER BY customfielddef_module';

        $Data = new QueryData();
        $Data->setMapClassName($this->getDataModel());
        $Data->setQuery($query);

        /** @var CustomFieldDefinitionData[] $queryRes */
        $queryRes = DbWrapper::getResultsArray($Data);

        if (count($queryRes) === 0) {
            throw new SPException(__('No se encontraron campos personalizados', false), SPException::INFO);
        }

        $fields = [];

        foreach ($queryRes as $CustomFieldDef) {

            /** @var CustomFieldDefinitionData $fieldDef */
            $fieldDef = Util::unserialize($this->getDataModel(), $CustomFieldDef->getCustomfielddefField());
            $fieldDef->setId($CustomFieldDef->getCustomfielddefId());

            $fields[] = $fieldDef;
        }

        return $fields;
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
     * Devolver los elementos con los ids especificados
     *
     * @param array $ids
     * @return mixed
     */
    public function getByIdBatch(array $ids)
    {
        $query = /** @lang SQL */
            'SELECT customfielddef_id,
              customfielddef_module
              FROM CustomFieldDefinition
              WHERE customfielddef_id IN (' . $this->getParamsFromArray($ids) . ')';

        $Data = new QueryData();
        $Data->setMapClassName($this->getDataModel());
        $Data->setQuery($query);
        $Data->setParams($ids);

        return DbWrapper::getResultsArray($Data);
    }

    /**
     * Inicializar la clase
     *
     * @return void
     * @throws \SP\Core\Exceptions\InvalidClassException
     */
    protected function init()
    {
        $this->setDataModel(CustomFieldDefinitionData::class);
    }
}