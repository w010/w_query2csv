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
	function main(string $content, array $conf): string	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();


        $this->file_key = GeneralUtility::_GP('f');

        // set config
        $this->_setFileConfig();

        // create core object
        $Core = GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Core::class, $this, $this->file_config);


        try {
	        if ($this->file_key)    {
	            // get data
	            $csv = $Core->getCsv();
	        }
	        else    {
	        	return '<!-- '.$this->prefixId. ': no file key given, nothing to do, exiting -->';
	        }
        } catch (Exception $exception)  {
            if ($this->isDebugEnabled())    {
                if ($this->file_config['input.']['q3i.'])   {
                    print "Error - Configuration option input.q3i. is set, which is not supported since version 0.4. See manual -> Migrate<br><br>";
                }
                if (!$this->file_config['input.']['table'] && !$this->file_config['input.']['sql_file']){
                    print "Error - Configuration incomplete - at least .input.table or input.sql_file is needed to be set<br><br>";
                    
                }
                print "<pre>";
                print "<br><br>FILE CONFIG:<br><br>";
                var_export($this->file_config);
                print "<br><br>LAST QUERY:<br><br>";
                var_export($Core->lastQuery);
                die("<br><br>Exception: ".$this->prefixId . ': ' . $exception->getMessage());
            }
	        return $this->prefixId . ': ' . $exception->getMessage();
        }
        // debugging with ?debug=1 in url: on dev context automatically available, on other needs to be configured in ts to enable
        if ($this->isDebugEnabled())    {
            print '<pre>';
            print_r($this->file_config);
            print '<br>';
            print_r($Core->lastQuery);
            print '<br><br>';
            print $csv;
            print '</pre>';
        }
        else if ($csv)    {
            GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Disposition::class)->sendFile($csv, $this->file_config['output.']);
        }
        else    {
            print 'Error: no data to output';
        }

        // stop script to prevent adding typo stuff like debug comments on end... if you want to keep them enabled and have them on standard pages anyway
        // so we may now exit the script after output
        exit;
	}


    /**
     * @return bool
     */
	protected function isDebugEnabled()  {
	    return (GeneralUtility::_GP('debug')  &&  $this->conf['debug_allowed'])
			||  (GeneralUtility::_GP('debug')  &&  \TYPO3\CMS\Core\Core\Environment::getContext()->isDevelopment());
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
    private function _setDefaultConfig(): array    {

        // check if _default key is configured in TS
        $this->file_key = '_default';

	    // if no default config, use some hardcoded settings... in general never used, but someday you may need to
	    // mod this ext, if it won't fit all your needs, so this section may be a good start
        if (!$this->file_config) {

            $this->file_key = '_default_hardcoded';

            $this->file_config = [
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
                    'process_fields.' => ['tstamp' => \WoloPl\WQuery2csv\Process\ParseDate::class],   // field => ProcessClass
                ]*/
            ];
        }
    }
}


