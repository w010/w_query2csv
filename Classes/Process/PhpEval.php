<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2025 wolo '.' studio <wolo.wolski@gmail.com>
*  All rights reserved
*
***************************************************************/

namespace WoloPl\WQuery2csv\Process;

use WoloPl\WQuery2csv\Core;
use WoloPl\WQuery2csv\Utility;


/**
 * Process value: PHP function adapter
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class PhpEval implements ProcessorInterface	{


	/**
	 * Any function call, for example: strtoupper
     * 
     * params[conf][callable] - (string) function / method name to exec, with the value as param
     * params[conf][args.] - (array) optional arguments to pass as well. key names matter if they are strings. if numeral - then order is important
	 *
	 * @param array $params string 'value', array 'conf', array 'row', string 'fieldname'
	 * @param Core $Core
	 * @return string
	 */
	public function run(array $params, Core &$Core): string    {
		$conf = $params['conf'] ?? [];
        $value = $params['value'] ?? '';

        Utility::nullCheckArrayKeys($conf, ['callable', ['args.' => []]]);

		if (!is_callable($conf['callable']))
			return $value;

        // prepare arguments array - value must be first
        $args = $conf['args.'];
        array_unshift($args, $value);

		return call_user_func_array($conf['callable'], $args);
	}

}


