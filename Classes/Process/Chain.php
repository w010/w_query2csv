<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2024 wolo '.' studio <wolo.wolski@gmail.com>
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



/**
 * Process value: Chain launch many processors
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class Chain implements ProcessorInterface	{


	/**
	 * launch
	 *
	 * @param array $params: array 'chain'
	 * @param Core $Core
	 * @return string
	 */
	public function run(array $params, Core &$Core): string {
        $value = $params['value'];
        foreach ($params['conf']['chain.'] as $chain_index => $chain_process_method)   {
            if (intval($chain_index) !== $chain_index)  // skip "10." array items!
                continue;
            if ($chain_process_method) {
                $process_conf = $params['conf']['chain.'][$chain_index.'.'];
                
                
                $chain_process_params = [
                    'value' => $value,
                    'row' => $params['row'],
                    'conf' => $process_conf,
                    'fieldName' => $params['fieldName']
                ];
                if (!strstr($chain_process_method, '->')) {
                    // method not specified
                    $chain_process_method .= '->run';
                }
                $value = GeneralUtility::callUserFunction($chain_process_method, $chain_process_params, $Core);
            }
        }
        return $value;
	}

}


