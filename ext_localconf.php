<?php
defined('TYPO3')  OR  die ('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['w_query2csv'] = [
    // list of tables, that can't be exported
    'not_allowed_tables' => 'be_users,be_groups,be_sessions,fe_users,fe_groups,fe_sessions,fe_session_data',
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
	'w_query2csv',
	'',
	'_export',
	'list_type',
	0
);


// needed to avoid error 404
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = array_merge(
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'],
    [
        'f', 'debug'
    ]
);
