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
	'title' => 'Export database/query to a CSV',
	'description' => 'This extension provides download points for predefined database queries to customized CSV files. If you have some data stored in db, that must be quick sent to someone, periodically downloaded by someone,
and the fields parsed for readability, this extension is very useful. It\'s simple and easy to configure.',
	'category' => 'plugin',
	'version' => '0.6.1',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'clearcacheonload' => 0,
	'author' => 'Wolo',
	'author_email' => 'wolo.wolski@gmail.com',
	'author_company' => 'wolo.pl \'.\' studio, Q3i',
	'constraints' => [
		'depends' => [
			'typo3' => '9.5.0-10.4.99',
		],
		'conflicts' => [
		],
		'suggests' => [
		],
	],
];

