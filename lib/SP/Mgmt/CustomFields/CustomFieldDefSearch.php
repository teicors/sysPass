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

use SP\DataModel\CustomFieldDefinitionData;
use SP\DataModel\ItemSearchData;
use SP\Mgmt\ItemSearchInterface;
use SP\Storage\DbWrapper;
use SP\Storage\QueryData;
use SP\Util\Util;

/**
 * Class CustomFieldSearch
 *
 * @package SP\Mgmt\CustomFields
 */
class CustomFieldDefSearch extends CustomFieldBase implements ItemSearchInterface
{
    /**
     * @param ItemSearchData $SearchData
     * @return array|\SP\DataModel\CustomFieldDefinitionData[]
     */
    public function getMgmtSearch(ItemSearchData $SearchData)
    {
        $Data = new QueryData();
        $Data->setMapClassName($this->getDataModel());
        $Data->setSelect('customfielddef_id, customfielddef_module, customfielddef_field');
        $Data->setFrom('customFieldsDef');
        $Data->setOrder('customfielddef_module');

        $Data->setLimit('?,?');
        $Data->addParam($SearchData->getLimitStart());
        $Data->addParam($SearchData->getLimitCount());

        DbWrapper::setFullRowCount();

        /** @var CustomFieldDefinitionData[] $queryRes */
        $queryRes = DbWrapper::getResultsArray($Data);

        $customFields = [];

        foreach ($queryRes as $CustomField) {

            /** @var CustomFieldDefinitionData $fieldDef */
            $fieldDef = Util::unserialize($this->getDataModel(), $CustomField->getCustomfielddefField());

            if ($SearchData->getSeachString() === ''
                || stripos($fieldDef->getName(), $SearchData->getSeachString()) !== false
                || stripos(CustomFieldTypes::getFieldsTypes($fieldDef->getType(), true), $SearchData->getSeachString()) !== false
                || stripos(CustomFieldTypes::getFieldsModules($CustomField->getCustomfielddefModule()), $SearchData->getSeachString()) !== false
            ) {
                $fieldDef->setId($CustomField->getCustomfielddefId());

                $customFields[] = $fieldDef;
            }
        }

        $customFields['count'] = $Data->getQueryNumRows();

        return $customFields;
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