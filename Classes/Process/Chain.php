<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2025 wolo '.' studio <wolo.wolski@gmail.com>
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use WoloPl\WQuery2csv\Core;
use WoloPl\WQuery2csv\Utility;


/**
 * Process value: Chain-launch many processors, replacing or joining values
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class Chain implements ProcessorInterface	{


	/**
	 * Launch a sequence of processors
     * 
     * params[conf][chain.] - (array) sub-processors sequence configuration 
     * params[conf][mode] - (string) each process result can: "join", or default = "replace" the previous chain item result value
     * params[conf][delimiter] - (string) when the results are joined in one, they will be glued with that string (can be -LINEBREAK- or -SPACE-. default is -SPACE-. pass empty string to stick together)
     * params[conf][lineBreakType] - (string) - when -LINEBREAK- is used, can be set to linebreak type - LF (default), CR, CRLF
	 *
	 * @param array $params string 'value', array 'conf' (details above), array 'row' 
	 * @param Core $Core
	 * @return string
	 */
	public function run(array $params, Core &$Core): string {
        $conf = $params['conf'] ?? [];
		$value = $params['value'] ?? '';
        $row = $params['row'] ?? [];

        Utility::nullCheckArrayKeys($conf, [['chain.' => []], 'mode', ['delimiter' => '-SPACE-'], 'lineBreakType']); // delimiter default is set here to make possible to configure empty string override that space

        $lineBreak = Utility::getLineBreak(''.$conf['lineBreakType']);
        $join_glue = str_replace(['-LINEBREAK-', '-SPACE-'], [$lineBreak, ' '], $conf['delimiter']);

        $result = $value;
        $results_collected = [];

        foreach ($conf['chain.'] as $chain_index => $chain_process_method)   {
            if (intval($chain_index) !== $chain_index)  // skip "10." array items!
                continue;
            if ($chain_process_method) {
                $process_conf = $conf['chain.'][$chain_index.'.'] ?? [];


                $chain_process_params = [
                    'value' => $result,
                    'row' => $row,
                    'conf' => $process_conf,
                    'fieldName' => $params['fieldName'] ?? '',
                ];
                if (!strstr($chain_process_method, '->')) {
                    // method not specified
                    $chain_process_method .= '->run';
                }

                // in mode = "join" chain processors adds their result to the end of previous one
                // (but the original value is first removed, though)
                if ($conf['mode'] == 'join') {
                    $results_collected[] = GeneralUtility::callUserFunction($chain_process_method, $chain_process_params, $Core);
                }
                // default mode = "replace" - the processed value replaces original 
                else    {
                    $result = GeneralUtility::callUserFunction($chain_process_method, $chain_process_params, $Core);
                }
            }
        }

        if ($conf['mode'] == 'join') {
            return implode($join_glue, $results_collected);
        }

        return $result;
	}

}


