<?php

namespace WoloPl\WQuery2csv\Process;

use WoloPl\WQuery2csv\Core;



Interface ProcessorInterface	{


	/**
	 * @param array $params
	 * @param Core $pObj
	 * @return string
	 */
    public function run(array $params, Core &$pObj): string;

}
