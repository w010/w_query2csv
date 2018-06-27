<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2017 wolo.pl '.' studio <wolo.wolski@gmail.com>
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

namespace WoloPl\WQuery2csv;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;



/**
 * Main class used for db read and file data output
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class Core	{

    /**
    * File configuration array
    *
    * @var array
    */
	public $config = [];

	/**
	* Stored last select query
	*
	* @var string
	*/
	public $lastQuery = '';


	/**
	 * @var object parent instance
	 */
	public $pObj = null;

	/**
	 * @var ContentObjectRenderer
	 */
	public $cObj = null;



	/**
	 * Construct
	 *
	 * @param $pObj
	 * @param array $file_config
	 */
	public function __construct(&$pObj, $file_config)   {
		$this->_init($pObj);
        $this->config = $file_config;

        if ($this->config['disable'])  {
            die ('file is disabled.');
        }

        // for security reasons, don't allow to export tables like fe/be users
        if (GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_query2csv']['not_allowed_tables'], $this->config['input.']['table']))   {
            die ('table not allowed.');
        }
    }


	/**
	 * Initialize object
	 * (it may not always be AbstractPlugin, Core is meant to be used from any context)
	 * @param $pObj
	 */
    protected function _init(&$pObj)   {
		$this->pObj = $pObj;
		if (is_object($pObj->cObj))
			$this->cObj = $pObj->cObj;
		else
			$this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }


    /**
    * Main method to render csv content
    *
    * @return string $csv - csv file content
    */
    public function getCsv()     {
        $input = $this->config['input.'];
        $output = $this->config['output.'];

        $counter = 0;               // row counter, used mainly for header

        // set default values for not given options
        if (!$input['fields'])      $input['fields'] = '*';
        if (!isset($input['enable_fields']))   $input['enable_fields'] = 1;
        if (!$output['separator'])  $output['separator'] = ',';


        // database read
        $res = $this->_readData($input);

	    $csv_header = '';
	    $csv = '';

        // main iteration
        if ($res)   {
            while($row_db = $this->getDatabaseConnection()->sql_fetch_assoc($res))   {

	            // make sure that $fields array is empty and ready for new row data
	            $fields = [];

		        $add_fields = GeneralUtility::trimExplode(',', $output['add_fields'], true);
	            $remove_fields = GeneralUtility::trimExplode(',', $output['remove_fields'], true);

	            if ($add_fields)    {
		            $row_db = array_merge($row_db, array_flip($add_fields));
	            }


                // in first row make header from db field names
                if (!$counter && !$output['no_header_row'])  {
                    $fieldNames = array_keys($row_db);

                    // remove fields
					if (count($remove_fields)) {
                        $fieldNames = array_flip($fieldNames);
                        foreach($remove_fields as $field){
                            if(isset($fieldNames[$field])){
                                unset($fieldNames[$field]);
                            }
                        }
                        $fieldNames = array_flip($fieldNames);
                    }

                    // set header fields
                    foreach($fieldNames as $field => $value)  {
                        $fieldNames[$field] = '"'.$value.'"';
                    }

                    $csv_header = implode($output['separator'], $fieldNames);
                }


                // build csv row from each db field
                foreach($row_db as $field => $value)  {
                    // process field with userfunction
                    if ($process_method = $output['process_fields.'][$field])    {
	                    $params = [
	                    	'value' => $value,
		                    'row' => $row_db,
		                    'conf' => $output['process_fields.'][$field.'.'],
		                    'fieldName' => $field
	                    ];
                    	$value = GeneralUtility::callUserFunction($process_method, $params, $this, '');
                    }


                    // apply htmlspecialchars
                    if ($output['hsc'])  {
                        $value = htmlspecialchars($value);
                    }

                    // make quotes csv-compatible double quotes (if not hsc-ed already...)
                    $value = str_replace('"', '""', $value);

                    // build fields array
                    $fields[$field] = '"'.$value.'"';
                }


	            if (count($remove_fields)) {
                    foreach($remove_fields as $field){
                        if(isset($fields[$field])){
                            unset($fields[$field]);
                        }
                    }
                }

                // make csv row
                $csv .= implode($output['separator'], $fields) . "\r\n";
            }

            $counter++;
        }

        // merge header with rest of csv content
        $csv = $csv_header . "\r\n" . $csv;

        // convert charset, if needed
        if ($output['charset'])   {
            $csv = mb_convert_encoding($csv, $output['charset'], 'UTF-8');
        }

        return $csv;
    }



    /**
    * Reads data from database with given config
    *
    * @param array $input
    * @return object mysql_res
    */
    private function _readData($input)    {
        $this->getDatabaseConnection()->store_lastBuiltQuery = true;
        if ($input['where_wrap.'])
	        $input['where'] = $this->cObj->cObjGetSingle($input['where_wrap'], $input['where_wrap.']);
        $res = $this->getDatabaseConnection()->exec_SELECTquery(
                $input['fields'],
                $input['table'],
                $input['where'] . ($input['default_enableColumns']
                        ? ' AND NOT deleted AND NOT hidden'
                        : $input['enableFields']
                                ? $this->cObj->enableFields($input['table'])
                                : ''),
                $input['group'],
                $input['order'],
                $input['limit']
        );

        $this->lastQuery = $this->getDatabaseConnection()->debug_lastBuiltQuery;
        return $res;
    }

    /**
     * Returns the database connection
     *
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    public function getDatabaseConnection()  {
        return $GLOBALS['TYPO3_DB'];
    }
}


