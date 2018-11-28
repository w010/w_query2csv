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
	 * @deprecated Use \WoloPl\WQuery2csv\Process\ParseDate
	 * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf', array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string - parsed date
	 */
	public function parseDate($params, Core &$pObj)    {
		GeneralUtility::logDeprecatedFunction();
		return GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Process\ParseDate::class)->run($params, $pObj);
	}

	/**
	 * Unserialize value
	 *
	 * @deprecated Use \WoloPl\WQuery2csv\Process\Unserialize
	 * @param array $params: 'value' - serialized string, 'conf' array with 'delimiter'
	 * @param Core $pObj
	 * @return string
	 */
	public function unserialize($params, Core &$pObj)    {
		GeneralUtility::logDeprecatedFunction();
		return GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Process\Unserialize::class)->run($params, $pObj);
    }

	/**
	 * Replace with predefined value from given map array. If not found, return original
	 *
	 * @deprecated Use \WoloPl\WQuery2csv\Process\ValueMap
	 * @param $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf', array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string
	 */
    public function valueMap($params, Core &$pObj) {
	    GeneralUtility::logDeprecatedFunction();
	    return GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Process\ValueMap::class)->run($params, $pObj);
    }

	/**
	 * Set static value given in 'value' key of params array
	 *
	 * @deprecated Use \WoloPl\WQuery2csv\Process\StaticValue
	 * @param $params: string 'value', array 'conf', array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string
	 */
	public function staticValue($params, Core &$pObj) {
		GeneralUtility::logDeprecatedFunction();
		return GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Process\StaticValue::class)->run($params, $pObj);
	}

	/**
	 * Generate string with label values from uid-commalist of records, like titles of referenced items
	 *
	 * @deprecated Use \WoloPl\WQuery2csv\Process\LabelsFromRecords
	 * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf' (details above), array 'row', string 'fieldname'
	 * @param Core $pObj
	 * @return string
	 */
	public function tableLabelsFromRecordsCommalist($params, Core &$pObj)    {
		GeneralUtility::logDeprecatedFunction();
		return GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Process\LabelsFromRecords::class)->run($params, $pObj);
	}

    /**
     * Generate string with label values from uid-commalist of records, like titles of referenced items
     *
     * @deprecated Use \WoloPl\WQuery2csv\Process\LabelsFromMmRelations
     * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf' (details above), array 'row', string 'fieldname'
     * @param Core $pObj
     * @return string
     */
    public function tableLabelsFromMmRelations($params, Core &$pObj)    {
	    GeneralUtility::logDeprecatedFunction();
	    return GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Process\LabelsFromMmRelations::class)->run($params, $pObj);
    }

}


