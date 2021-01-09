<?php

namespace WoloPl\WQuery2csv\Process;

use WoloPl\WQuery2csv\Core;



Interface ProcessorInterface	{


	/**
	 * @param array $params
	 * @param Core $Core
	 * @return string
	 */
    public function run(array $params, Core &$Core): string;

}
