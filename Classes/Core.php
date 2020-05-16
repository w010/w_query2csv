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
	    
	    if (!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('typo3db_legacy'))   {
	        Throw new \TYPO3\CMS\Core\Exception('w_query2csv: extension typo3db_legacy is required, but not found.', 3409273549);
        }
	    
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

        $counter = 0;
	    $csv = '';
	    $csv_header = '';
	    $fieldNames = [];
	    $rowBuffer = [];

        // set default values for not given options
        if (!isset($input['fields']))           $input['fields'] = '*';
        if (!isset($input['enable_fields']))    $input['enable_fields'] = 1;
        if (!isset($output['separator']))       $output['separator'] = ',';
        if (!isset($output['quote']))           $output['quote'] = '"';


        // database read
        $preparedStatement = $this->_readData($input);


        // main iteration
        if ($preparedStatement)   {
            while(($row_db = $preparedStatement->fetch()) !== FALSE)   {

	            // make sure that $fields array is empty and ready for new row data
	            $fields = [];

		        $add_fields = GeneralUtility::trimExplode(',', $output['add_fields'], true);
	            $remove_fields = GeneralUtility::trimExplode(',', $output['remove_fields'], true);

	            if ($add_fields)    {
		            $row_db = array_merge($row_db, array_flip($add_fields));
	            }

                // in first iteration row from db get field names
                if (!$counter)  {
                    $fieldNames = array_keys($row_db);
                }


                // build output row from each db field
                foreach($row_db as $field => $value)  {
                    // process field with userfunction
                    if ($process_method = $output['process_fields.'][$field])    {
                    	// process method configured in typoscript
	                    $params = [
	                    	'value' => $value,
		                    'row' => $row_db,
		                    'conf' => $output['process_fields.'][$field.'.'],
		                    'fieldName' => $field
	                    ];
	                    if (!strstr($process_method, '->')) {
		                    // method not specified
		                    $process_method .= '->run';
	                    }
                    	$value = GeneralUtility::callUserFunction($process_method, $params, $this);  // 2: allow exception on error, instead of only debug log
                    } else if (strstr($value, 'USER_FUNC')) {
                        // process method taken from sql value (in most cases set in query template)
                        // imho we can get the same result using ts add_fields and process_fields, but maybe sometimes it's more handy to do it from sql file
	                    $tmp = GeneralUtility::trimExplode(':', $value);
	                    $userFuncDef = $tmp[1];
	                    $userFuncParams = [
		                    'fieldName' => $field,
		                    'row' => $row_db,
		                    'value' => $value,  // pass value containing userfunc name, we could pass params there this way
		                    'INFO' => '\'field\' and \'data\' keys are for compatibility only, use \'fieldName\' and \'row\' instead!',
		                    'field' => $field,  // deprecated, used in q3i projects
		                    'data' => $row_db   // deprecated, used in q3i projects
	                    ];
	                    $value = GeneralUtility::callUserFunction($userFuncDef, $userFuncParams, $this);
                    }

                    $fields[$field] = $value;
                }


	            // postprocess single row of data
	            if (is_array($output['postprocessors_row.'])) {
		            foreach ($output['postprocessors_row.'] as $key => $config) {
			            $userFuncDef = $config['class'];
			            if (!strstr($userFuncDef, '->')) {
				            // method not specified
				            $userFuncDef .= '->process';
			            }
			            $userFuncParams = [
				            'config' => $config,
				            'data' => $fields
			            ];
			            $fields = GeneralUtility::callUserFunction($userFuncDef, $userFuncParams, $this);
		            }
	            }

	            // wrap all values by string delimiter
	            foreach ($fields as $fieldKey => $fieldValue) {

					// apply htmlspecialchars
					if ($output['htmlspecialchars'] || $output['hsc'])  {
						$fieldValue = htmlspecialchars($fieldValue);
					}

					 // if set, linebreaks are removed (changed to space) from value
		            if ($output['strip_linebreaks'] || $output['nbr'])  {
			            $fieldValue = preg_replace("/\r|\n/s", " ", $fieldValue);
		            }

                	// make quotes csv-compatible double quotes (if not hsc-ed already...)
                	$fieldValue = str_replace($output['quote'], $output['quote'].$output['quote'], $fieldValue);

		            // wrap value in string delimiter
		            $fields[$fieldKey] = $output['quote'] . $fieldValue . $output['quote'];

		            // add a header field name if not already present
		            if (!in_array($fieldKey, $fieldNames)) {
			            $fieldNames[] = $fieldKey;
		            }
				}

                // if configured fieldnames to remove (like read only for processing use, but not needed in output file)
	            if (count($remove_fields)) {
                    foreach($remove_fields as $field){
	                    $fieldNames = array_flip($fieldNames);
                        if(isset($fields[$field])){
                            unset($fields[$field]);
                            unset($fieldNames[$field]);
                        }
	                    $fieldNames = array_flip($fieldNames);
                    }
                }

                // make csv row
                //$csv .= implode($output['separator'], $fields) . "\r\n";

                // q3i way: add csv row to buffer. what is the advantage to have this in array before build csv row?
	            $rowBuffer[] = $fields;
                $counter++;
            }

	        // free prepared statement resources
	        $preparedStatement->free();
        }

	    if (!$output['no_header_row']) {
		    //$csv_header = implode($output['separator'], $fieldNames) . "\r\n";
		    $csv_header = $output['quote']
			    . implode($output['quote'] . $output['separator'] . $output['quote'], $fieldNames)
			    . $output['quote'] . "\n";
	    }

		// normalize rows (todo: describe what for is this exactly)
	    foreach ($rowBuffer as $row) {
		    $normalizedRow = [];
		    foreach ($fieldNames as $fieldName) {
			    $normalizedRow[$fieldName] = $row[$fieldName];
		    }
		    $csv .= implode($output['separator'], $normalizedRow) . "\n";
	    }

        // merge header with rest of csv content
        $csv = $csv_header . $csv;

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

        if ($input['sql_file']) {
            // File based query (sql template)
            if (is_file($input['sql_file'])) {
                $fileContent = file_get_contents($input['sql_file']);
                if ($input['sql_markers.']) {
                    foreach ($input['sql_markers.'] as $key => $val) {
                        $fileContent = str_replace("###{$key}###", $val, $fileContent);
                    }
                }
            } else {
                die ("<b>Fatal error:</b> Given file {$input['sql_file']} does not exist or is not readable.");
            }
	        $preparedStatement = GeneralUtility::makeInstance(\TYPO3\CMS\Typo3DbLegacy\Database\PreparedStatement::class, $fileContent, '', []);
	        $preparedStatement->execute();

        } else {
        	// standard-typoscript build sql query
        	if ($input['where_wrap.'])
	        	$input['where'] = $this->cObj->cObjGetSingle($input['where_wrap'], $input['where_wrap.']);
        	$preparedStatement = $this->getDatabaseConnection()->prepare_SELECTquery(
                	$input['fields'],
                	$input['table'],
                	($input['where'] ? $input['where'] : '1=1') . ($input['default_enableColumns']
                        ? ' AND NOT deleted AND NOT hidden'
                        : $input['enableFields']
                                ? $this->cObj->enableFields($input['table'])
                                : ''),
                $input['group'],
                $input['order'],
                $input['limit']
        	);
        	$preparedStatement->execute();
        }

        $this->lastQuery = $this->getDatabaseConnection()->debug_lastBuiltQuery;
        return $preparedStatement;
    }

    /**
     * Returns the database connection
     *
     * @return \TYPO3\CMS\Typo3DbLegacy\Database\DatabaseConnection
     */
    public function getDatabaseConnection()  {
        return $GLOBALS['TYPO3_DB'];
    }
}


