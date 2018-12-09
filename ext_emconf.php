<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "w_query2csv".
 *
 * Auto generated 20-01-2010 12:57
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/


$EM_CONF[$_EXTKEY] = [
	'title' => 'Export db query to file',
	'description' => 'This extension provides exporting given database query to a CSV file. If you have some data stored in db, that must be quick sent to someone eg. by email, or periodically downloaded by someone,
this extension is very useful. It\'s simple and easy to configure.',
	'category' => 'plugin',
	'version' => '0.4.1',
	'dependencies' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearcacheonload' => 0,
	'author' => 'Wolo',
	'author_email' => 'wolo.wolski@gmail.com',
	'author_company' => 'wolo.pl \'.\' studio, Q3i',
	'constraints' => [
		'depends' => [
			'typo3' => '7.6.0-8.7.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];

