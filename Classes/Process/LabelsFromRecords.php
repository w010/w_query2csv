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



/**
 * Process value: Labels from fields from table's records with given uids
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class LabelsFromRecords	{


	/**
	 * Generate string with label values from uid-commalist of records, like titles of referenced items
	 * params[conf][table] - table name
	 * params[conf][field] - field to take the label from
	 * params[conf][delimiter] - separate labels in result. you can use here: -LINEBREAK- or -SPACE-. default is comma+space (,-SPACE-)
	 * params[conf][lineBreakType] - string - linebreak type, may be LF (default), CR, CRLF
	 *
	 * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf' (details above), array 'row', string 'fieldname'
	 * @param \WoloPl\WQuery2csv\Core $pObj
	 * @return string
	 */
	public function run($params, \WoloPl\WQuery2csv\Core &$pObj)    {
		$conf = $params['conf'];

		if (!$conf['table']  ||  !$conf['field'])
			return __METHOD__ . '() - NO TABLE OR FIELD SPECIFIED!';

		$lineBreak = \WoloPl\WQuery2csv\Utility::getLineBreak($conf['lineBreakType']);

		if (!$conf['delimiter'])
			$conf['delimiter'] = ',-SPACE-';

		$conf['delimiter'] = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $conf['delimiter']);

		$labels = [];

		$res = $pObj->getDatabaseConnection()->exec_SELECTquery(
			$conf['field'],
			$conf['table'],
			'uid IN ('.$pObj->getDatabaseConnection()->cleanIntList($params['value']).')'
		);
		while($row = $pObj->getDatabaseConnection()->sql_fetch_assoc($res))   {
			$labels[] = $row[ $conf['field'] ];
		}

		$value = implode($conf['delimiter'], $labels);

		return $value;
	}

}


