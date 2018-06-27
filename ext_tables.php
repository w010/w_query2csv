<?php
defined('TYPO3_MODE')  OR  die ('Access denied.');


$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_export'] = 'layout,select_key';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin([
		'LLL:EXT:w_query2csv/Resources/Private/Language/locallang_db.xml:tt_content.list_type_export',
		$_EXTKEY.'_export'
	],
	'list_type'
);


//TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/', 'Default settings');



/*if (TYPO3_MODE == 'BE')	{
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('file', 'txwquery2csvM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'mod1/');
}*/
