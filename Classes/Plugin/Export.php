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


use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Plugin 'Export records to file' for the 'w_query2csv' extension.
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class tx_wquery2csv_export extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $prefixId      = 'tx_wquery2csv_export';		// Same as class name
	var $scriptRelPath = 'Classes/Plugins/Export.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'w_query2csv';	// The extension key.

    public $conf = [];

    /**
     * File configuration
     *
     * @var array
     */
    protected $file_config = [];

    /**
     * File config key
     *
     * @var string
     */
    protected $file_key = '';


	/**
	 * The main method of the plugin - prints output and exits
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return  string
	 */
	function main($content, $conf)	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_USER_INT_obj = 1;     // Configuring that caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!


        $this->file_key = GeneralUtility::_GP('f');

        try {
            // set config
        	$this->_setFileConfig();

	        // create core object
	        $core = GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Core::class, $this, $this->file_config);

	        if ($this->file_key)    {
	            // get data
	            $csv = $core->getCsv();
	        }
	        else    {
	        	return '<!-- '.$this->prefixId. ': no file key given, nothing to do, exiting -->';
	        }
        } catch (Exception $exception)  {
	        return $this->prefixId . ': ' . $exception->getMessage();
        }


		$currentApplicationContext = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();
        // debugging with ?debug=1 in url: on dev context automatically available, on other needs to be configured in ts to enable
        if (	(GeneralUtility::_GP('debug')  &&  $this->conf['debug_allowed'])
			||  (GeneralUtility::_GP('debug')  &&  $currentApplicationContext->isDevelopment())
        ){

            print '<pre>';
            print_r($this->file_config);
	        print '<br>';
	        print ($core->lastQuery);
	        print '<br><br>';
	        print $csv;
	        print '</pre>';
        }
        else if ($csv)    {

            // set output headers
            //header('Content-type: text/csv; charset=' . ($this->file_config['output.']['charset'] ? $this->file_config['output.']['charset'] : 'utf-8'));
            //header('Content-disposition: attachment; filename=' . $this->file_config['output.']['filename']);

            // todo: check if strlen is sure enough - shouldn't it be mb_strlen? is this length header really necessary here?
            // in case some problems with not complete output it may be this Content-Length
			$len = strlen($csv);
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header('Content-Type: text/csv; charset=' . ($this->file_config['output.']['charset'] ? $this->file_config['output.']['charset'] : 'utf-8'));
            header('Content-Disposition: attachment; filename=' . $this->file_config['output.']['filename']);
            header("Content-Length: $len;\n");
            // for ie problem:
            header('Pragma: private');
			header('Cache-Control: private, must-revalidate');
            // var_dump(headers_list());

            // output content
            print $csv;
            // from q3i version - is this needed like that?
	        //print utf8_decode($csv);
        }
        else    {
            print 'Error: no data to output';
        }

        // stop script to prevent adding typo stuff like debug comments on end... if you want to keep them enabled and have them on standard pages anyway
        // so we may now exit the script after output
        exit;
	}


	/**
	 *
	 * @throws Exception
	 */
    private function _setFileConfig()   {

    	if ($this->file_key) {
		    $this->file_config = $this->conf['files.'][$this->file_key . '.'];
		    if ($this->file_key === '_default')
		    	return;
	    }
	    else    {
	        if (!$this->file_key) {
	            if ($this->conf['default_config_if_missed'])    {
				    $this->_setDefaultConfig();
			    }
			    else    {
		            // if no key and default is disabled
				    return;
			        //Throw new Exception('<b>Error:</b> No file key given!');
			    }
	        }
	    }

	    // if no key config and defaults not allowed
	    if ($this->file_key  &&  !$this->file_config) {
		    Throw new Exception('<b>Error:</b> Given file key "' . $this->file_key . '" is not configured!');
	    }


	    // set some default values - must be set here (Core doesn't know, what the file_key is, it only takes input/output)
	    if (!$this->file_config['output.']['filename'])
		    $this->file_config['output.']['filename'] = $this->file_key . '.csv';
    }


    /**
     * Sets default config if not given in typoscript.
     * In some cases you might need to modify the code and adjust this to your needs
     */
    private function _setDefaultConfig()    {

        // check if _default key is configured in TS
        $this->file_key = '_default';
	    $this->_setFileConfig();

	    // if no default config, use some hardcoded settings... in general never used, but someday you may need to
	    // mod this ext, if it won't fit all your needs, so this section may be a good start
        if (!$this->file_config) {

            $this->file_key = '_default_hardcoded';

            return [
                /*'input.' => [
                    'table' => 'tx_somestuff',
                    'fields' => 'tstamp,name,email,phone,stuff_id',
                    'where' => 'AND category = 2',
                    'group' => '',
                    'order' => '',
                    'limit' => '',
                    'default_enableColumns' => false,
                ],
                'output.' => [
                    'filename' => 'order.csv',
                    'separator' => ',',
                    'charset' => 'UTF-8',
                    'process_fields.' => ['tstamp' => 'WoloPl\WQuery2csv\Process->parseDate'],   // field => methodname
                ]*/
            ];
        }
    }
}


