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

namespace WoloPl\WQuery2csv;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Driver\DriverStatement;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
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
	public $file_config = [];

	/**
	* Stored queries
	*
	* @var array
	*/
	public $lastQuery = [];


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
	 * @param Object $pObj - (basically not used for anything anymore. it may be AbstractPlugin or other object, Core is meant to be used from any context)
	 * @param array $file_config
	 */
	public function __construct(Object &$pObj, array $file_config)   {
        $this->pObj = $pObj;
        $this->file_config = $file_config;
		$this->_init();
    }


	/**
	 * Initialize
	 */
    protected function _init(): void  {
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        if ($this->file_config['disable'] ?? 0)  {
            die ('file is disabled.');
        }

        // for security reasons, don't allow to export tables like fe/be users
        if (GeneralUtility::inList($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_query2csv']['not_allowed_tables'], $this->file_config['input.']['table']))   {
            die ('table not allowed.');
        }
    }


    /**
    * Main method to render csv content
    *
    * @return string $csv - csv file content
    */
    public function getCsv(): string     {
        $input = $this->file_config['input.'] ?? [];
        $output = $this->file_config['output.'] ?? [];

        $counter = 0;
	    $csv = '';
	    $csv_header = '';
	    $fieldNames = [];
	    $rowBuffer = [];

        // set default values for not given options
        if (!isset($input['fields']))                   $input['fields'] = '*';
        if (!isset($input['where']))                    $input['where'] = '';
        if (!isset($input['where_wrap']))               $input['where_wrap'] = '';
        if (!isset($input['where_wrap.']))              $input['where_wrap.'] = [];
        if (!isset($input['group']))                    $input['group'] = '';
        if (!isset($input['order']))                    $input['order'] = '';
        if (!isset($input['limit']))                    $input['limit'] = '';
        if (!isset($input['enableFields']))             $input['enableFields'] = 0;
        if (!isset($input['default_enableColumns']))    $input['default_enableColumns'] = 0;
        if (!isset($input['sql_file']))                 $input['sql_file'] = '';
        if (!isset($input['sql_markers']))              $input['sql_markers'] = [];

        if (!isset($output['filename']))                $output['filename'] = '';
        if (!isset($output['separator']))               $output['separator'] = ',';
        if (!isset($output['quote']))                   $output['quote'] = '"';
        if (!isset($output['charset']))                 $output['charset'] = 'UTF-8';
        if (!isset($output['htmlspecialchars']))        $output['htmlspecialchars'] = 0;
        if (!isset($output['strip_linebreaks']))        $output['strip_linebreaks'] = 0;
        if (!isset($output['no_header_row']))           $output['no_header_row'] = 0;
        if (!isset($output['process_fields']))          $output['process_fields'] = [];
        if (!isset($output['add_fields']))              $output['add_fields'] = '';
        if (!isset($output['remove_fields']))           $output['remove_fields'] = '';
        if (!isset($output['postprocessors_row.']))     $output['postprocessors_row.'] = [];
        if (!isset($output['postprocessors_header.']))  $output['postprocessors_header.'] = [];
        if (!isset($output['additionalHeaders']))       $output['additionalHeaders'] = [];
        if (!isset($output['additionalHeadersProcessor']))   $output['additionalHeadersProcessor'] = '';





        // database read
        // for some reason I don't get now, it needs to ->execute() here again, even if it just was in the _readData()
        $preparedStatement = $this->_readData($input)->execute();


        // main iteration
        if ($preparedStatement)   {
            //$preparedStatementExecute = $preparedStatement
            while(($row_db = $preparedStatement->fetchAssociative()) !== FALSE)   {

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
                    if ($process_method = $output['process_fields.'][$field] ?? '')    {
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
                    	$value = GeneralUtility::callUserFunction($process_method, $params, $this);
                    } else if (strstr((string) $value, 'USER_FUNC')) {
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

                    // if we want to add the unserialized array to the csv as new columns (merge with current fields) we need it as an array.
                    // the Unserialize processor handles this by itself - with mergeAsColumns=1 it automatically returns json
                    if (isset($output['process_fields.'][$field.'.']['mergeAsColumns']) && $output['process_fields.'][$field.'.']['mergeAsColumns'])    {
                        $mergeColumns = \GuzzleHttp\json_decode($value,true);
                        if (is_array($mergeColumns)) {
                            $fields = array_merge($fields, $mergeColumns);
                        }
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
					if ($output['htmlspecialchars'])  {
						$fieldValue = htmlspecialchars($fieldValue);
					}

					 // if set, linebreaks are removed (changed to space) from value
		            if ($output['strip_linebreaks'])  {
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
        }

	    if (!$output['no_header_row']) {
	        $headerRowLabels = $fieldNames;
			// postprocess header row - fieldnames / labels
			if (is_array($output['postprocessors_header.'])) {
				foreach ($output['postprocessors_header.'] as $key => $config) {
					$userFuncDef = $config['class'];
					if (!strstr($userFuncDef, '->')) {
						// method not specified
						$userFuncDef .= '->process';
					}
					$userFuncParams = [
						'config' => $config,
                        // make labels array combined with keys: [label]=>'label', instead of numerical keys, to easy override values in userfunc
						'data' => array_combine($headerRowLabels, $headerRowLabels),
					];
					$headerRowLabels = GeneralUtility::callUserFunction($userFuncDef, $userFuncParams, $this);
				}
			}

		    $csv_header = $output['quote']
			    . implode($output['quote'] . $output['separator'] . $output['quote'], $headerRowLabels)
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
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function _readData(array $input): \Doctrine\DBAL\Driver\Statement {

        // File based query (sql template)
        if ($input['sql_file']) {
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
	        $preparedStatement = $this->getDatabaseConnection()->prepare($fileContent);
        	$preparedStatement->execute();
            $this->lastQuery[] = $fileContent;
        }

        // standard: sql build from typoscript setup
        else {
        	if ($input['where_wrap.'])
	        	$input['where'] = $this->cObj->cObjGetSingle($input['where_wrap'], $input['where_wrap.']);

        	if ($input['enableFields']) {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($input['table']);
                $restrictions = $queryBuilder->getRestrictions();
                $ExpressionBuilder = GeneralUtility::makeInstance(ExpressionBuilder::class,
                    GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName('Default'));
                $restrictionsExpression = $restrictions->buildExpression([$input['table'] => $input['table']], $ExpressionBuilder);
            }

        	$query = 'SELECT ' . $input['fields']
                . ' FROM ' . $input['table']
                . ' WHERE ' . ($input['where'] ? $input['where'] : '1=1')
                    . ($input['default_enableColumns'] ? ' AND NOT deleted AND NOT hidden'
                        : ($input['enableFields'] && $restrictionsExpression ? ' AND ' . $restrictionsExpression
                            : ''))
                . ($input['group'] ? ' GROUP BY ' . $input['group'] : '')
                . ($input['order'] ? ' ORDER BY ' . $input['order'] : '')
                . ($input['limit'] ? ' LIMIT ' . $input['limit'] : '');

            $this->lastQuery[] = $query;
        	$preparedStatement = $this->getDatabaseConnection()->prepare($query);
        	$preparedStatement->execute();
        }

        return $preparedStatement;
    }

    /**
     * Returns the database connection
     *
     * @return \Doctrine\DBAL\Driver\Connection
     */
    public function getDatabaseConnection(): \Doctrine\DBAL\Driver\Connection {
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
		return $pool->getConnectionByName('Default')->getWrappedConnection();
    }
}


