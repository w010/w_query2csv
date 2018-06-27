Dump SQL table/query to CSV

w_query2csv readme/quick manual


wolo.pl '.' studio 
2009 - 2018
wolo.wolski@gmail.com



0. SEE MANUAL IN /doc/manual.sxw !!!

This readme is a short version of it.
(They will be merged to new format in future...)





1. WHAT IS THIS?

This directory contains a TYPO3 cms extension that provides exporting given database query to a CSV file.




2. FOR WHAT DO I NEED IT?

If you have data set in db, that must be quick sent to someone eg. by email, or periodically downloaded by someone,
this extension is very useful. It's simple and easy to configure, additionally you may process selected fields
using some functions, eg. convert timestamps to string readable by normal human.
If you want something more functional, it is a good base to develop something bigger.




3. HOW TO USE IT? 

Simply insert plugin on new page named eg. 'CSV export' and configure input and output using TypoScript.
A Backend Section type page is good idea, you might control download access for backend non-admin users.
To download file, you have to access url using ?f=[configuration_key], as here:
http://example.com/csv_export.html?f=somestuff_orders

Note, that this plugin is not basing on standard TYPO3 page type handling, but is changing headers for whole page
containg it, so do not insert it on standard frontend pages with normal content.

Caution where do you insert this plugin and what is exported!




4. HOW TO CONFIGURE?

For selected page containing plugin, create new TypoScript template and edit Setup field.

Example config:

plugin.tx_wquery2csv_export  {
    files   {
        somestuff_orders  {
            input {
              table = tx_somestuff_orders
              fields = tstamp,name,email,phone,stuff_uid
              where = category = 2
              group =
              order = tstamp DESC
              limit =
              enableFields = 1
            }
            output {
              filename = somestuff_orders.csv
              separator = ,
              encoding =
              process_fields {
                tstamp = WoloPl\WQuery2csv\Process->parseDate
              }
            }
        }

        # .... (some other keys with input and output options to access file using ?f=some_other_key)
    }

    debug_allowed = 1
}

File is available by ?f=somestuff_orders.



Shortest working example:

When you take a look at reference below, you'll see, that only db table is really required in configuration, so shortest config would look like this:

plugin.tx_wquery2csv_pi1.files.my_file.input.table = tx_sometable

the file will be accessed like: [download_csv.html]?f=my_file
when you put the plugin on page download_csv.

The easiest way to quick configure is to just copy example config from above.


See more examples in Configuration/TypoScript/setup.ts



Options description:

'files' (TypoScript properties) - set of file keys to separate configs for misc files. if you want only one file, the key may be whatever.
every key must have configured input and output like this:

    ['somestuff_orders'] (TypoScript properties) - configuration key named by you
        'input':
            (required)
            'table' (String) - (part of query) db table, of course you may use joins and other...

            (optional)
            'fields' (String) - (part of query) fields that we want to select. Default is *
            'where', 'group', 'order', 'limit' (String) - standard parts of database query
            'where_wrap' (Array) - it may overwrite normal where string with stdWrapped version, to allow ts values, like this:
            	where_wrap = TEXT
				where_wrap.value = pid = {page:uid}
				where_wrap.stdWrap.insertData = 1
			'enableFields' (Bool/Int) - use system enableFields for excluding unavailable records. works of course only for TCA configured tables
				 DEFAULT IS ENABLED (so if you was given empty csv, check that selected table has these fields and set this option to 0 if not)
            'default_enableColumns' (Bool/Int) - use hardcoded "deleted=0 AND hidden=0" in WHERE clause. In case you have some records from a table
            	that is not currently loaded to TCA (like disabled ext) you can enable this option to simply filter them instead of writing this in where.
            	(note, that not all Typo3 tables has such fields, ie. users tables, for some reason, have 'disable' instead of 'hidden'.)


        'output':
            (optional)
            'filename' (String) - output filename. Default is same as config file key with csv extension added
            'separator' (String) - csv separator used in file
            'charset' (String) - set alternative charset encoding. Default = UTF-8
            'hsc' (Bool/Int) - htmlspecialchars every value. Default = 0
            'no_header_row' (Bool/Int) - don't make first line header with fieldnames
            'process_fields' (TypoScript properties) - process selected db field [key] with callable method [value]
            	set of TS properties like:
            	fieldName = [callable_process_method] (typo3 native way, preferable modern namespace call, but may be classic file:class->method reference, anything allowed by GeneralUtility::callUserFunction
            	fieldName.someOption = someValue
            		@SEE reference below
			'add_fields' (String) - commalist of fieldnames - to make additional columns in output, that are not present in database. values for them are
				set in processing, so they may be configured in process_fields.
			'remove_fields' (String) - commalist of fieldnames - removes some fields/columns from output. so why select them from db, you might ask?
				sometimes you may use their values in postprocessing, but don't want to have it in csv. like record's uid for mm query


(optional)
'debug_allowed' (Bool/Int) - allow to use &debug=1 url param. Default = 0
'default_config_if_missed' (Bool/Int) - load '_default' ts key config (or hardcoded ext conf, if not set) when config for given key not found / key not given. Default = 0



			process_fields - built-in processors

				Example:

					process_fields	{
						tstamp = WoloPl\WQuery2csv\Process->parseDate
						tstamp.format = d.m.Y


				Reference:

					->parseDate - Converts timestamp to human-readable date, parsed using date() function
						config:
							.format = [string] - date mask, default: Y.m.d H:i

					->valueMap - Static value maps, input (like some id) to output (like some nice labels). (input val = output val pairs)
						(note that you can't use spaces in source val, and dots need to be escaped for typoscript - use \.)
						config:
							.map {
								source_value = Export Value
							}

					->staticValue - Replaces original value with given, always the same – may be handy in some cases:
						config:
							.value = [string]

					->unserialize - Unserializes an array and generates key: value pairs separated by given delimiter
						config:
							.delimiter = [string] - may be anything, also you can use special keywords: -LINEBREAK- and -SPACE-. defaults to linebreak
							.lineBreakType = [string] - if use linebreak, may be configured to use CR, LF or CRLF. defaults to LF

					->tableLabelsFromRecordsCommalist - Generate string with some values from related records, using uid-commalist, like, ie. titles of referenced categories
						config:
							.table = [string] - table name to read referenced items from
							.field = [string] - use value from this field
							.delimiter = [string] - may be anything, also you can use special keywords: -LINEBREAK- and -SPACE-. defaults to linebreak
							.lineBreakType = [string] - may be CR, LF or CRLF. defaults to LF

					->tableLabelsFromMmRelations - Same as previous, but uses mm table to read records
						(note, that to make this work, you MUST add "uid" field to your .input.fields. if you don't wanna uid column in csv, use .output.remove_fields = uid)
						config:
							the same as above, plus:
							.table_mm = [string] - mm relations table




5. FAQ

Q:
	How to add own processing methods for field values?
A:
	Write any Typo3-callable userfunc with params ($params, &$pObj) and return a string.
	In $params array you can expect to be passed:
		'value' - field value from db
		'fieldName' - name of current processed field
		'row' - whole record of current item (I mean, fields which you set to read in .input.fields, if not "*")
		'conf' - typoscript configuration of this processor

	Best way is to just write your method in your extension, the same way like I did in w_query2csv/Classes/Process.php, and configure processing ts in standard namespace way.
	In case you don't know what I mean: add your class in your own ext, respecting standard Typo3 path and naming convention to make the class be found using namespace
	(like: class in file typo3conf/ext/my_ext/Classes/Extensions/WQuery2csv/Process.php = available by namespace MyNamespace\MyExt\Extensions\WQuery2csv\Process
	write a processing method like that:
		public function doSomethingWithValue($params, \WoloPl\WQuery2csv\Core &$pObj)    { return $myProcessedString }
	and register using:
		plugin.tx_wquery2csv_export.files.myFile.output.process_fields.some_field = MyNamespace\MyExt\Extensions\WQuery2csv\Process->doSomethingWithValue
		plugin.tx_wquery2csv_export.files.myFile.output.process_fields.some_field.someAdditionalOptionToPass = something



Q:
	My output file is empty!
A:
	- try to set debug_allowed = 1 in config and access file with &debug=1 to check if the config is passed properly.
	- try to set "fields" to *, comment other parts of db query
	- check another table
	- set "default_enableColumns" to 0 (not just delete line!), maybe the table hasn't "deleted" and "hidden" fields
	- set "enableFields" to 0, maybe the table is not configured in TCA



Q:
	What if be user edit has template edit priviliges and exports something that he is not allowed?
	Why I cannot export my fe_users?
A:
	Some tables would never be accessed even by other backend users. To prevent situation when a user configure plugin to see ie. users passwords,
	or allow a table which is originally blocked, set selected tables as comma separated list on "not_allowed_tables", in LocalConfiguration or AdditionalConfiguration
	$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_query2csv'] = [
		'not_allowed_tables' => 'be_users,be_groups,be_sessions,fe_users,fe_groups,fe_sessions,fe_session_data',
	];




6. Migrate from version 0.1.x

- typoscript setup key is now: plugin.tx_wquery2csv_export instead of plugin.tx_wquery2csv_pi1
- the plugin content element embeded on page must be selected again for the same reason
- process_fields now expects full callable userfunc reference
- so, _process_parseDate option is now \WoloPl\WQuery2csv\Process->parseDate
- process_fields_user is now removed, use process_fields instead, just like the rest
- output.where now need to be full, not starting from "AND"



7. ChangeLog

0.1.1
	First release with full documentation.

0.1.5
	Last built query is saved and available on debug
	Quotes in input are escaped to csv-compatible double quotes (was messing whole csv)
	Additional tables added to not_allowed_tables

0.2.0
	Code reworked and compatible with TYPO3 version 7.x and 8.x
	Userfunc field processing
	Many minor improvements – some of them breaks compatibility. Please refer to Migration section, if updating

0.3.0
	Charset converting - iconv changed to mbstring (thanks to Henri Nathanson for suggestion)
	Inactive records filtering changed to native - now use enableFields = 1 for this. use default_enableColumns only when table isn't in TCA
	Option add_fields fixed - works again
	Debugging now works automatically when TYPO3_CONTEXT == Development, no need to set debug_allowed = 1
	Process->unserialize now can use custom delimiter
	New processors: ->tableLabelsFromRecordsCommalist, ->tableLabelsFromMmRelations
