<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2025 wolo '.' studio <wolo.wolski@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

namespace WoloPl\WQuery2csv\Process;

use WoloPl\WQuery2csv\Core;
use WoloPl\WQuery2csv\Utility;


/**
 * Process value: Labels from fields from table's records related by mm
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class LabelsFromMmRelations implements ProcessorInterface	{



    /**
     * Generate string with label values from mm-relations of records, like titles of referenced items
     *
     * params[conf][table] - table name
     * params[conf][table_mm] - intermediate table name
     * params[conf][field] - field to take the label from
     * params[conf][delimiter] - separate labels in result. you can use here: -LINEBREAK- or -SPACE-. default is comma+space (,-SPACE-)
     * params[conf][lineBreakType] - string - linebreak type, may be LF (default), CR, CRLF
     * params[conf][additional_where] - string - optional query where part (must start with AND)
     *
     * @param array $params string 'value', array 'conf' (details above), array 'row', string 'fieldname'
     * @param Core $Core
     * @return string
     */
    public function run(array $params, Core &$Core): string    {
		$conf = $params['conf'] ?? [];
        $value = $params['value'] ?? '';
        $row = $params['row'] ?? [];

        Utility::nullCheckArrayKeys($conf, ['table', 'table_mm', 'field', 'delimiter', 'lineBreakType', 'additional_where']);
        Utility::nullCheckArrayKeys($row, ['uid']);


        if (!$conf['table']  ||  !$conf['table_mm']  ||  !$conf['field'])
            return __METHOD__ . '() - NO TABLE OR TABLE_MM OR FIELD SPECIFIED!';

        if (!$row['uid'])
            return __METHOD__ . '() - NO RECORD\'S uid PASSED! NOTE: To make processors with references work, uid must be selected (in "input.fields"), it can be removed from output using "remove_fields = uid"';

        $lineBreak = Utility::getLineBreak(''.$conf['lineBreakType']);

        if (!$conf['delimiter'])
            $conf['delimiter'] = ',-SPACE-';

        $conf['delimiter'] = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $conf['delimiter']);

        $labels = [];

        $query = 'SELECT r.'.$conf['field']
            . ' FROM ' . $conf['table'] . ' AS r '
            . ' JOIN ' . $conf['table_mm'] . ' AS m '
            . ' ON  r.uid = m.uid_local'
            . ' WHERE m.uid_foreign = '.intval($row['uid'])
            . ' ' . $conf['additional_where'];
        
        $Core->lastQuery[] = $query;
        $preparedStatement = $Core->getDatabaseConnection()->query($query);

        while(($row = $preparedStatement->fetch_assoc()) !== null)   {
            $labels[] = $row[ $conf['field'] ];
        }

        return implode($conf['delimiter'], $labels);
    }

}


