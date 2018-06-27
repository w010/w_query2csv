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

namespace WoloPl\WQuery2csv;

use \TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Processing class for transforming some raw data to human-readable, like timestamps
 * May be used as base for own processors
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class Process	{


	/**
	 * Format date timestamp
	 *
	 * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf', array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string - parsed date
	 */
	public function parseDate($params, Core &$pObj)    {
		if (!$params['conf']['format'])
			$params['conf']['format'] = 'Y.m.d H:i';
		return date($params['conf']['format'], intval($params['value']));
	}

	/**
	 * Unserialize value
     * params[conf][delimiter] - separate labels in result. you can use here: -LINEBREAK- (default) or -SPACE-
     * params[conf][lineBreakType] - string - linebreak type, may be LF (default), CR, CRLF
	 *
	 * @param array $params: 'value' - serialized string, 'conf' array with 'delimiter'
	 * @param Core $pObj
	 * @return string
	 */
	public function unserialize($params, Core &$pObj)    {

        $lineBreak = $this->getLineBreak($params['conf']['lineBreakType']);

		if (!$params['conf']['delimiter'])
			$params['conf']['delimiter'] = '-LINEBREAK-';

        $params['conf']['delimiter'] = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $params['conf']['delimiter']);

        $array = unserialize($params['value']);
	    $out = '';
        foreach (is_array($array) ? $array : [] as $key => $val)	{
        	$out .= "$key: $val" . $params['conf']['delimiter'];
		}
        return $out;
    }

	/**
	 * Replace with predefined value from given map array. If not found, return original
	 *
	 * @param $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf', array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string
	 */
    public function valueMap($params, Core &$pObj) {
	    if (!$params['conf']['map.'])
		    $params['conf']['map.'] = [];
	    if ($params['conf']['map.'][ $params['value'] ])
	    	return $params['conf']['map.'][ $params['value'] ];
	    return $params['value'];
    }

	/**
	 * Set static value given in 'value' key of params array
	 *
	 * @param $params: string 'value', array 'conf', array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string
	 */
	public function staticValue($params, Core &$pObj) {
		return $params['conf']['value'];
	}

	/**
	 * Generate string with label values from uid-commalist of records, like titles of referenced items
	 * params[conf][table] - table name
	 * params[conf][field] - field to take the label from
	 * params[conf][delimiter] - separate labels in result. you can use here: -LINEBREAK- or -SPACE-. default is comma+space (,-SPACE-)
	 * params[conf][lineBreakType] - string - linebreak type, may be LF (default), CR, CRLF
	 *
	 * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf' (details above), array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string
	 */
	public function tableLabelsFromRecordsCommalist($params, Core &$pObj)    {

		if (!$params['conf']['table']  ||  !$params['conf']['field'])
			return __METHOD__ . '() - NO TABLE OR FIELD SPECIFIED!';

		$lineBreak = $this->getLineBreak($params['conf']['lineBreakType']);

		if (!$params['conf']['delimiter'])
			$params['conf']['delimiter'] = ',-SPACE-';

		$params['conf']['delimiter'] = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $params['conf']['delimiter']);

		$labels = [];

		$res = $pObj->getDatabaseConnection()->exec_SELECTquery(
			$params['conf']['field'],
			$params['conf']['table'],
			'uid IN ('.$pObj->getDatabaseConnection()->cleanIntList($params['value']).')'
		);
		while($row = $pObj->getDatabaseConnection()->sql_fetch_assoc($res))   {
			$labels[] = $row[ $params['conf']['field'] ];
		}

		$value = implode($params['conf']['delimiter'], $labels);

		return $value;
	}

    /**
     * Generate string with label values from uid-commalist of records, like titles of referenced items
     * params[conf][table] - table name
     * params[conf][field] - field to take the label from
     * params[conf][delimiter] - separate labels in result. you can use here: -LINEBREAK- or -SPACE-. default is comma+space (,-SPACE-)
     * params[conf][lineBreakType] - string - linebreak type, may be LF (default), CR, CRLF
     *
     * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf' (details above), array 'row', string 'fieldname'
     * @param Core $pObj
     * @return string
     */
    public function tableLabelsFromMmRelations($params, Core &$pObj)    {

        if (!$params['conf']['table']  ||  !$params['conf']['field'])
            return __METHOD__ . '() - NO TABLE OR FIELD SPECIFIED!';

        $lineBreak = $this->getLineBreak($params['conf']['lineBreakType']);

        if (!$params['conf']['delimiter'])
            $params['conf']['delimiter'] = ',-SPACE-';

        $params['conf']['delimiter'] = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $params['conf']['delimiter']);

        $labels = [];

        $res = $pObj->getDatabaseConnection()->exec_SELECTquery(
                'r.'.$params['conf']['field'],
                $params['conf']['table'] . ' AS r  JOIN ' . $params['conf']['table_mm'] . ' AS m  ON  r.uid = m.uid_foreign',
                'm.uid_local = '.intval($params['row']['uid'])
        );
        while($row = $pObj->getDatabaseConnection()->sql_fetch_assoc($res))   {
            $labels[] = $row[ $params['conf']['field'] ];
        }

        $value = implode($params['conf']['delimiter'], $labels);

        return $value;
    }

    /**
     * Converts linebreak type string to that linebreak itself
     * @param string $lineBreakType
     * @return string
     */
	protected function getLineBreak($lineBreakType)   {
	    switch ($lineBreakType) {
            case 'CR':
                return CR;
            case 'CRLF':
                return CRLF;
            case 'LF':
            default:
                return LF;
        }
    }
}


