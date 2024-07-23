<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2021 wolo.pl '.' studio <wolo.wolski@gmail.com>
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
 * Process value: Preg replace
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class PregReplace implements ProcessorInterface	{


	/**
	 * Performs regular expression replacement using preg_replace
	 *
	 * @param array $params: array 'pattern' (string) - regexp pattern, 'replacement' (string) - replacement, 'limit' (int) - max replacements (see preg_replace manual)
	 * @param Core $Core
	 * @return string
	 */
    public function run(array $params, Core &$Core): string {
		$conf = $params['conf'];
	    if (!$conf['pattern'])
	        return (string) $params['value'];

        $conf['replacement'] = str_replace(['-SPACE-'], [' '], $conf['replacement']);

	    return (string) preg_replace($conf['pattern'], $conf['replacement'], $params['value'], $conf['limit'] ?? -1);
    }

}
