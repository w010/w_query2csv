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
 * Process value: Value map
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class ValueMap implements ProcessorInterface	{


	/**
	 * Replace with predefined value from given map array. If not found, return original
	 *
	 * @param array $params: array 'map' - [oldValue = New Value] pairs
	 * @param Core $Core
	 * @return string
	 */
    public function run(array $params, Core &$Core): string {
		$conf = $params['conf'];
	    if (!$conf['map.'])
		    $conf['map.'] = [];
	    if ($conf['map.'][ $params['value'] ])
	    	return (string) $conf['map.'][ $params['value'] ];
	    return (string) $params['value'];
    }

}
