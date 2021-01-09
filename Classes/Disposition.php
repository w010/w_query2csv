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

use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Content disposition
 *
 * @author	Wolo <wolo.wolski@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_wquery2csv
 */
class Disposition   {


    /**
     * Send headers and file to the client
     *
     * @param string $csv : file content
     * @param array $outputConfig : output part of file configuration coming from ts
     * @param array $additionalConfig : additional optional settings
     * @return void
     */
	public function sendFile(string $csv, array $outputConfig, array $additionalConfig = []): void	{

        // set output headers
        // todo: check if strlen is sure enough - shouldn't it be mb_strlen? is this length header really necessary here?
        // in case some problems with not complete output it may be this Content-Length
        $length = strlen($csv);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Type: text/csv; charset=' . ($outputConfig['charset'] ?? 'utf-8'));
        header('Content-Disposition: attachment; filename=' . $outputConfig['filename']);
        header('Content-Length: ' . $length);
        // for ie problem:
        header('Pragma: private');
        header('Cache-Control: private, must-revalidate');
        // var_dump(headers_list());


        if ($additionalHeaders = $outputConfig['additionalHeaders.'] ?? $additionalConfig['additionalHeaders.'] ?? false)    {
            foreach ((array)$additionalHeaders as $header)  {
                header($header);
            }
        }

        if ($additionalHeadersProcessor = $outputConfig['additionalHeadersProcessor'] ?? $additionalConfig['additionalHeadersProcessor'] ?? false)    {
            GeneralUtility::callUserFunction($additionalHeadersProcessor, $this, $outputConfig);
        }

        // output content
        print $csv;
        // from q3i version - was this needed that way? why?
        //print utf8_decode($csv);

        exit;
	}

}


