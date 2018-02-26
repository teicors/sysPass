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

namespace SP\Mgmt\Plugins;

use SP\DataModel\ItemSearchData;
use SP\Mgmt\ItemSearchInterface;
use SP\Storage\DbWrapper;
use SP\Storage\QueryData;

/**
 * Class PluginSearch
 *
 * @package SP\Mgmt\Plugins
 */
class PluginSearch extends PluginBase implements ItemSearchInterface
{
    /**
     * Búsqueda de plugins
     *
     * @param ItemSearchData $SearchData
     * @return mixed
     */
    public function getMgmtSearch(ItemSearchData $SearchData)
    {
        $Data = new QueryData();
        $Data->setSelect('plugin_id, plugin_name, plugin_enabled, plugin_available');
        $Data->setFrom('plugins');
        $Data->setOrder('plugin_name');

        if ($SearchData->getSeachString() !== '') {
            $Data->setWhere('plugin_name LIKE ?');

            $search = '%' . $SearchData->getSeachString() . '%';
            $Data->addParam($search);
        }

        $Data->setLimit('?,?');
        $Data->addParam($SearchData->getLimitStart());
        $Data->addParam($SearchData->getLimitCount());

        DbWrapper::setFullRowCount();

        /** @var array $queryRes */
        $queryRes = DbWrapper::getResultsArray($Data);

        $queryRes['count'] = $Data->getQueryNumRows();

        return $queryRes;
    }
}