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
	'title' => 'Export database/query to CSV',
	'description' => 'This is a CSV generator, which combines simplicity with high configurability and data processing powers. It provides download points
for your preconfigured database queries dumped to customized CSV files. If you have some data collected in database tables, which must be periodically, like, downloaded by admins,
sent to external CRM, where some raw values must be converted to something meaningful, like uids replaced with labels from relations, timestamps visualized, arrays deserialized,
this extension is very useful. It\'s simple and easy to configure, but very flexible with input/output settings and scalability.',
	'category' => 'plugin',
	'version' => '0.6.2',
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

