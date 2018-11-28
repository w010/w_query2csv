<?php
defined('TYPO3_MODE')  OR  die ('Access denied.');


$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_query2csv'] = [
    // list of tables, that can't be exported
    'not_allowed_tables' => 'be_users,be_groups,be_sessions,fe_users,fe_groups,fe_sessions,fe_session_data',
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
	$_EXTKEY,
	'Classes/Plugin/Export.php',
	'_export',
	'list_type',
	0
);
