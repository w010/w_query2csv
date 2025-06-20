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
 * Utility helper
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class Utility	{


    /**
     * Converts linebreak type string to that linebreak itself
     * @param string $lineBreakType
     * @return string
     */
	static public function getLineBreak(string $lineBreakType): string   {
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


    /**
     * Check the array and ensure the given keys do exist (to avoid mess with null-checking everything)
     * Useful in methods where config array is passed (like Processors) 
     * @param array $array Incoming array to check and set required keys
     * @param array $keys Keys to check and set if not set. It can be a string (will set that key to null),
     *      or a [mykey => somevalue] array, if you need other default than null  
     * @return void
     */
    static public function nullCheckArrayKeys(array &$array, array $keys): void   {

        foreach ($keys as $keyItem) {
            if (is_array($keyItem)) {
                $key = array_keys($keyItem)[0];
                $defaultValue = $keyItem[$key];
            }
            else {
                $key = $keyItem;
                $defaultValue = null;
            }

            if (!isset($array[$key])) {
                $array[$key] = $defaultValue;
            }
        }
    }
}


