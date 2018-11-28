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
 * Process value: Parse date
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class ParseDate	{


	/**
	 * Format date timestamp
	 *
	 * @param array $params: string 'value' - timestamp (given in ts, so it's string casted to integer), array 'conf', array 'row', string 'fieldname'
	 * @param \WoloPl\WQuery2csv\Core $pObj
	 * @return string - parsed date
	 */
	public function run($params, \WoloPl\WQuery2csv\Core &$pObj)    {
		$conf = $params['conf'];
		if (!$conf['format'])
			$conf['format'] = 'Y.m.d H:i';
		return date($conf['format'], intval($params['value']));
	}

}


