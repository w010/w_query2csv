<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2025 wolo '.' studio <wolo.wolski@gmail.com>
*  All rights reserved
*
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


