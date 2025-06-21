<?php
defined('TYPO3')  OR  die ('Access denied.');


$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['w_query2csv_export'] = 'layout,select_key';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin([
		'LLL:EXT:w_query2csv/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_export',
		'w_query2csv_export',
		'ext-wquery2csv-plugin',    // icon
	],
	'list_type',
	'w_query2csv'
);

