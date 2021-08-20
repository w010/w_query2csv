<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2018 wolo.pl '.' studio <wolo.wolski@gmail.com>
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



/**
 * Process value: Labels from fields from table's records with given uids
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class LabelsFromRecords implements ProcessorInterface	{


	/**
	 * Generate string with label values from uid-commalist of records, like titles of referenced items
	 * params[conf][table] - table name
	 * params[conf][field] - field to take the label from
	 * params[conf][delimiter] - separate labels in result. you can use here: -LINEBREAK- or -SPACE-. default is comma+space (,-SPACE-)
	 * params[conf][lineBreakType] - string - linebreak type, may be LF (default), CR, CRLF
	 * params[conf][useValueFromField] - string - instead of current field's value, use another column from current row
	 *
	 * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf' (details above), array 'row', string 'fieldname'
	 * @param Core $Core
	 * @return string
	 */
	public function run(array $params, Core &$Core): string    {
		$conf = $params['conf'];
		$value = $params['value'];

		if (!$conf['table']  ||  !$conf['field'])
			return __METHOD__ . '() - NO TABLE OR FIELD SPECIFIED!';

		if ($conf['useValueFromField'])
		    $value = $params['row'][ $conf['useValueFromField'] ];

		$lineBreak = \WoloPl\WQuery2csv\Utility::getLineBreak(''.$conf['lineBreakType']);

		if (!$conf['delimiter'])
			$conf['delimiter'] = ',-SPACE-';

		$conf['delimiter'] = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $conf['delimiter']);

		$labels = [];

        $query = 'SELECT '.$conf['field']
            . ' FROM ' . $conf['table']
            . ' WHERE uid IN (' . implode(',', array_map('intval', explode(',', $value))) . ')'
            . $conf['additional_where'];
        
        $preparedStatement = $Core->getDatabaseConnection()->prepare($query);
        $preparedStatement->execute();
        $Core->lastQuery[] = $query;

        while(($row = $preparedStatement->fetch(\PDO::FETCH_ASSOC)) !== FALSE)   {
			$labels[] = $row[ $conf['field'] ];
		}

		return implode($conf['delimiter'], $labels);
	}

}


