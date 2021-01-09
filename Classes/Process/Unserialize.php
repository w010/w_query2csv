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
 * Process value: Unserialize
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class Unserialize implements ProcessorInterface	{


	/**
	 * Unserialize value
     * params[conf][delimiter] - separate labels in result. you can use here: -LINEBREAK- (default) or -SPACE-
     * params[conf][lineBreakType] - string - linebreak type, may be LF (default), CR, CRLF
	 *
	 * @param array $params: 'value' - serialized string, 'conf' array with 'delimiter'
	 * @param Core $Core
	 * @return string
	 */
	public function run(array $params, Core &$Core): string    {
		$conf = $params['conf'];

		$lineBreak = \WoloPl\WQuery2csv\Utility::getLineBreak(''.$conf['lineBreakType']);

		if (!$conf['delimiter'])
			$conf['delimiter'] = '-LINEBREAK-';

        $conf['delimiter'] = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $conf['delimiter']);

        $array = unserialize($params['value']);
	    $out = '';
        foreach (is_array($array) ? $array : [] as $key => $val)	{
        	$out .= "$key: $val" . $conf['delimiter'];
		}
        return $out;
    }

}


