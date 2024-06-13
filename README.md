# Dump SQL table/query to CSV

w_query2csv
readme / manual


wolo '.' studio
2009 - 2024
wolo.wolski (at) gmail (dot) com


https://github.com/w010/w_query2csv/


Credits:
Q3i
Hint Intermedia


##


## 1. WHAT IS THIS?

It's a simple but powerful CSV generator, in general it renders CSV data from preconfigured input source, which may be SQL built from TypoScript setup,
given query, or SQL templates, to prepare complicated predefined joins, etc.
Queries may be static, dynamically generated, preprocessed or marker-filled. The data rows and cells may be processed/converted/parsed before output.
You can use some built-in processors, like autoconvert timestamp to human-readable date, map uids to values, fill labels using values from related tables,
preg replace, or parse in some other way. In a minute you can make a custom processor to do whatever you need.
Prepare and keep any number of file configs which will be available, each configured what-to-read and how-to-output.

But if you need simple thing, there's no need to dig in-deep in all these settings, you can go minimalistic and try out the simpliest one-option config,
which is basically to set an input table name. That's enough to get a download point with a beautiful shiny CSV file built from that table.

You can get the final output in several ways: - insert a plugin wrapper element on a page and then call url with file key, to download a file configured
in Typoscript, - create your Scheduler task to build CSV from PHP array configuration, - integrate generator in your own extension and do anything with it.


NOTE, that for now there's no general-use nice templated frontend plugin included - only the simple wrapper which triggers file download. That means, currently
there's NO built-in possibility to just insert a templated-fancy-content-element to get a list of friendly typolinks to files available etc.
(you must write the url manually to be able to download file in frontend - you can of course put that url into text contents, but keep in mind - you're doing this on your own)
The main reason is that the ext was meant to be used by admins/editors, not frontend users. This extension by it's nature was born as a low-level admin tool,
and exposing such thing to the whole world without any access control, if someone didn't check twice what data is available through it, maybe is not such a good idea.
So I never wrote that part (also I never needed). Maybe it will come in future.
You can provide it by yourself, ie. writing Fluid template with custom viewhelper which makes the list, or own plugin (but take a good care of what you allow and what users can see).




	PLEASE SEE "SECURITY" SECTION BEFORE USE!



## 2. WHAT FOR DO I NEED IT?

If you have data set in db, eg. some orders or contest answers, that must be quick sent to someone by email, or
periodically downloaded by someone, or something like that, this extension is very useful.

Just make a new page, insert plugin and set up the file, that will be downloaded by other or specified users, which you
may specify by standard TYPO3 access settings.
It's simple and easy to configure, additionally you may process selected fields using some functions, eg. convert
timestamps to human-readable strings.

If you want something more functional it's a good base to develop something bigger.

> ![download file screen](https://docs.typo3.org/typo3cms/extensions/w_query2csv/_images/img-3.jpeg)



## 3. HOW TO USE IT?

### 3.1 Basic

Simply insert plugin on a new page named eg. 'CSV export' and configure input and output using TypoScript.
A Backend Section type page is good idea, you may want to control download access for backend non-admin users.
To download file, you call page url + file id using _?f=[configuration_key]_, like that:
http://example.com/csv_export.html?f=somestuff_orders

NOTE, that embeding this plugin causes that specific page not being cached!


Note, that this plugin's output is not based on standard TYPO3 page type handling, but sends headers and sends output
on a page containing it. But you can insert it on standard frontend page between normal content - it won't output anything until
asked (using 'f' param or using 'default' key - see config).
It's possible to make CSV output using page type, if you use a snippet from
Configuration/TypoScript/Setup/setup.ts

(You may wonder why it's made in such weird way, why not generate nice typolinks with piVars, cHash etc?
  - to not suggest that it's just another content element / simple frontend functionality. I mean, it was made for private use,
	to help admins with downloading orders from forms, so it doesn't have too much security check, user control etc. It's just
	an adapter, a flexible data output renderer. If you need it as a public frontend function, implement the interface by yourself,
	write a plugin and use my ext from inside, taking care of what it have to be taken.)

**Caution where do you insert this plugin and what is exported!**

> ![backend typoscript / screenshot from old version](https://docs.typo3.org/typo3cms/extensions/w_query2csv/_images/img-4.jpeg)


### 3.2 Using in own plugins

One time I needed to display link to csv in my other plugin context. This needs to make query2csv object, prepare
and pass config to it, and call when requested.
This can be done like that:

```
$content .= '<a href="'.GeneralUtility::linkThisUrl($_SERVER['REQUEST_URI'], array('action' => 'getfile', 'f' => 'my_file')) . '">download file</a>';

// prepare file config
$config_myFile = [
	'files.' => [
		'my_file.' => [
			'input.' => [
				'table' => 'tx_myext_table',
				'fields' => 'uid, crdate, name, value',
				'where' => 'type = 2',
				'order' => 'uid DESC',
				'limit' => 100,
			],
			'output.' => [
				'filename' => 'mydata.csv',
				'process_fields.' => [
					'crdate' => 'WoloPl\WQuery2csv\Process\ParseDate',
]]]]];

if ($_GET['action'] === 'getfile')	{
	// make core/file object
	$Q2csvCore_myFile = GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Core::class, $this, $config_myFile['files.']['my_file.']);
	// render csv content
	$csv = $Q2csvCore_myFile->getCsv()
	// send file to client (note, that the script now throws output and exits)
	GeneralUtility::makeInstance(\WoloPl\WQuery2csv\Disposition::class)->sendFile($csv, $myCsvFileConfig['output.']);
}
```





## 4. HOW TO CONFIGURE?

For selected page containing plugin, create new TypoScript template and edit Setup field.

### 4.1 Example config:

```
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
					tstamp = WoloPl\WQuery2csv\Process\ParseDate
					tstamp.format = d.m.Y H:i
				}
            }
        }

        # .... (some other file keys with input/output options)
    }

    debug_allowed = 1
}
```


Above file is available using url param ?f=somestuff_orders.

##

### 4.2 Shortest working example:

When you take a look at the reference below, you'll see, that only db table is really required in configuration, so
the shortest config would look like this:

```
plugin.tx_wquery2csv_export.files.my_file.input.table = tx_sometable
```

> Explanation:
file key “**my_file**” has set input table to “**tx_sometable**” (and plugin is inserted on _download_csv_ page)
so:
the csv file containing records from this table will be accessed using: [download_csv.html]?f=my_file
and:
prompted to download as my_file.csv

The easiest way to quickly configure is to just copy example config from above.

_See more examples in Configuration/TypoScript/setup.ts_




### 4.3 Options reference:

```
'files' (TypoScript properties) - set of file keys to separate configs for misc files.
	if you want only one file, the key may be whatever.
	Every key must have configured input and output like this:

	[your_file_key] (TypoScript properties) - configuration key named by you

		'input':

			[Required]

			'table' (String) 			- (part of query) db table, of course you may use joins and other...

			[Optional]

			'fields' (String) 			- (part of query) fields that we want to select. Default = *

			'where',
			'group',
			'order',
			'limit' (String)			- standard parts of database query

			'where_wrap' (Array)		- it may override normal 'where' string with stdWrapped version, to allow ts values, like this:
				where_wrap = TEXT
				where_wrap.value = pid = {page:uid}
				where_wrap.stdWrap.insertData = 1

			'enableFields' (Bool/Int) 	- use system enableFields for excluding unavailable records. works of course only for TCA configured tables

			'default_enableColumns' (Bool/Int) - use hardcoded "deleted=0 AND hidden=0" in WHERE clause. In case you have some records from a table
				that is not currently loaded to TCA (like disabled ext) you can enable this option to simply filter them instead of writing this in where. Default = 0
				(note, that not all Typo3 tables has such fields, ie. users tables, for some reason, have 'disable' instead of 'hidden'.)

			'sql_file' (String) 		- path to sql query template file. this was originally introduced by Q3i. if set, then instead of using above typoscript query configuration
				a file containing prepared query is used. extremely handy when using complicated queries with processing.
				example of userfunc passing from there:
					SELECT .... , 'USER_FUNC:NameSpace\\Postprocessor\\DataPostprocessor->test' As User_Func_Test,   ....
					makes every row have a field named User_Func_Test with userfunc path as value, which will be called on row processing

			'sql_markers' (Array) 		- optionally given markers can be replaced in sql_file template before send query to database. Array of:
				MARKER = value
				(### is added automatically)



		'output':

			[Optional]

			'filename' (String) 			- output filename. Default is same as config file key with csv extension added

			'separator' (String) 			- csv separator used in file. Default: ,

			'quote' (String) 				- csv value quote. Default: "

			'charset' (String) 				- set alternative charset encoding. Default = UTF-8

			'htmlspecialchars' (Bool/Int) 	- htmlspecialchars every value. Default = 0

			'strip_linebreaks' (Bool/Int) 	- strip line breaks from value. Default = 0

			'no_header_row' (Bool/Int) 		- don't make first line header with fieldnames

			'process_fields' (TypoScript properties) - process selected db field [key] with callable method [value]
				set of TS properties like:
				fieldName = [callable_process_method] (typo3 native way, preferable modern namespace call, but may be classic file:class->method reference, anything allowed by GeneralUtility::callUserFunction
				fieldName.someOption = someValue
				Example:
					process_fields	{
						tstamp = WoloPl\WQuery2csv\Process\ParseDate
						tstamp.format = d.m.Y
				@SEE reference below

			'add_fields' (String) 			- commalist of fieldnames - to make additional columns in output, that are not present in database. values for them are
				set in processing, so they may be configured in process_fields.

			'remove_fields' (String) 		- commalist of fieldnames - removes some fields/columns from output. so why select them from db, you might ask?
				sometimes you may use their values in postprocessing, but don't want to have it in csv. like record's uid for mm query

			'postprocessors_row' (Array)	- postprocess single row with ready values (note: but before htmlspecialchars, strip_linebreaks etc)
				if given class with optional ->methodName, this method will be called. else by default method ->process is called.
				example:
					10	{
						class = NameSpace\MyExt\WQuery2csv\Postprocessor\DataPostprocessor->myMethod
						someConfParam = myValue
					}

			'postprocessors_header' (Array)	- postprocess header row labels, to replace db fieldnames with your fancy labels
				if given class specified with optional ->methodName, this method will be called. else by default method ->process is called.
				example:
					10	{
						class = NameSpace\MyExt\WQuery2csv\Postprocessor\HeaderPostprocessor->myMethod
						someConfParam = myValue
					}

			'additionalHeaders' (Array)		- additional headers to be sent with the file output
				example:
					10 = Content-Type: application/octet-stream

			'additionalHeadersProcessor' (String) - additional output headers processing class
				example:
					NameSpace\MyExt\WQuery2csv\OutputHeaders->sendHeaders
					- php:
					class OutputHeaders   {
						public function sendHeaders(\WoloPl\WQuery2csv\Disposition &$Disposition, array $outputConfig): void	{
        					header('Content-Type: application/octet-stream');
						}
					}


		'disable' (Bool/Int)	- disable this file (quick restrict download access). Default = 0



'debug_allowed' (Bool/Int) 				- allow to use &debug=1 url param. Default = 0
											(note, that on dev instance, where TYPO3_CONTEXT is set to Development, it's set automatically to 1)

'default_config_if_missed' (Bool/Int) 	- load '_default' ts key config (or hardcoded ext conf, if not set) when config for given key not found / key not given. Default = 0


```

### 4.4 Processors reference:
```


	ParseDate - Converts timestamp to human-readable date, parsed using date() function
		config:
			.format = [string] - date mask, default: Y.m.d H:i


	ValueMap - Static value maps, input (like some id) to output (like some nice labels). (input val = output val pairs)
		(note that you can't use spaces in source val, and dots need to be escaped for typoscript - use \.)
		config:
			.map {
				source_value = Export Value
			}


	StaticValue - Replaces original value with given, always the same – may be handy in some cases:
		config:
			.value = [string]


	PregReplace - Performs regular expression replacement using php's preg_replace function
		config:
			.pattern = [string] - regexp pattern to match
			.replacement = [string] - when string found, replace with this one
			.limit = [int] - limit replacements, if matches multiple


	Unserialize - Unserializes an array and generates key: value pairs separated by given delimiter
		config:
			.delimiter = [string] - may be anything, also you can use special keywords: -LINEBREAK- and -SPACE-. defaults to linebreak
			.lineBreakType = [string] - if use linebreak, may be configured to use CR, LF or CRLF. defaults to LF
			.returnJson = [bool/int] - get the whole array as json instead of formatted text. can be used later for some processing
			.mergeAsColumns = [bool/int] - inserts the unserialized array items as new columns to csv output (also forces returnJson)
				(consider unsetting (remove_fields) such field's original value from standard output)


	LabelsFromRecords - Generate string with some values from related records, using uid-commalist, like, ie. titles of referenced categories
		config:
			.table = [string] - table name to read referenced items from
			.field = [string] - use value from this field
			.delimiter = [string] - may be anything, also you can use special keywords: -LINEBREAK- and -SPACE-. defaults to linebreak
			.lineBreakType = [string] - may be CR, LF or CRLF. defaults to LF
			.additional_where = [string] - optional where part to filter joined records (must start with AND)
			.useValueFromField = [string] - instead of current field's value, use another column from current row


	LabelsFromMmRelations - Same as previous, but uses mm table to read records
		(note, that to make this work, you MUST add "uid" field to your .input.fields. if you don't wanna uid column in csv, use .output.remove_fields = uid)
		config:
			the same as above, plus:
			.table_mm = [string] - mm relations table

...or write your own.
```




## 5. SECURITY

### ! PLEASE REMEMBER !

This is a low-level database exporter, which was intended to use by admins to easy download customized csv with product orders. Such things are always
potentially dangerous if used in wrong way. Downloading full database tables by people who are not permitted may be a disaster - passwords leak, session
hijack, etc... - so better check twice where do you put this plugin and what have you configured there, to not allow downloading any sensitive data by mistake!

I recommend embedding it always on pages with BE-user or admin access only, unless you are sure what are you doing.




## 6. FAQ

--
**Q:
I inserted plugin content element on a page, but I don't see anything in frontend**

> A:
If file key (?f=file_key) in url is not specified, plugin doesn't display any output (only some html comment)


--
**Q:
How to add own processing methods for field values?**

> A:
Write any Typo3-callable class with a ``run()`` public method with params ``($params, &$Core)`` and return a string.
(Optionally you can use any method name and configure process using class->methodname)
In $params array you can expect to be passed:
	'value' - field value from db
	'fieldName' - name of current processed field
	'row' - whole record of current item (I mean, fields which you set to read in .input.fields, if not "*")
	'conf' - typoscript configuration of this processor
$Core is a WoloPl\WQuery2csv\Core instance

> Best way is to just make a new class in your extension, (respecting standard Typo3 path and naming convention to make the class be autoloaded)
> the same way as I did in Classes/Process/yyyy.php, and configure typoscript giving your class namespace path.
> (like: class file /my_ext/Classes/WQuery2csv/MyCustomProcessor.php = MyNamespace\MyExt\WQuery2csv\MyCustomProcessor)
> Implement the \WoloPl\WQuery2csv\Process\ProcessorInterface to make things easier. Finally setup in your ts:
	``plugin.tx_wquery2csv_export.files.myFile.output.process_fields.some_field = MyNamespace\MyExt\WQuery2csv\MyCustomProcessor``
	``plugin.tx_wquery2csv_export.files.myFile.output.process_fields.some_field.someAdditionalOptionToPass = something``


--
**Q:
My output file is empty!**

> A:
> - try to set debug_allowed = 1 in config and access file with &debug=1 to check if the config is passed properly.
> - try to set "fields" to *, comment other parts of db query
> - check another table
> - set "default_enableColumns" to 0, maybe the table doesn't have "deleted" and "hidden" fields
> - set "enableFields" to 0 (not just delete line!), maybe the table is not configured in TCA


--
**Q:
What if be user edit has template edit priviliges and exports something that he is not allowed?
	Why I cannot export my fe_users?**

> A:
Some tables would never be accessed even by other backend users. To prevent situation when a user configure plugin to see ie. users passwords,
or allow a table which is originally blocked, set selected tables as comma separated list on "not_allowed_tables", in LocalConfiguration or AdditionalConfiguration
```
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['w_query2csv'] = [
	'not_allowed_tables' => 'be_users,be_groups,be_sessions,fe_users,fe_groups,fe_sessions,fe_session_data',
];
```


## 7. Migrate

* from version 0.6.x
	- Core::config property was renamed to Core::file_config, so if you have your own processors (or xclasses),
	  you may want to check if they used this property. This doesn't impact installations which uses only built-in features.
	  Also, property Core::lastQuery was changed from type string to array (so it may collect also additional queries from processors)

* from version 0.5.x
	- process_fields: deprecated class Process is finally removed, so processing config like: ~~WoloPl\WQuery2csv\Process->\[processingMethod]~~
	  doesn't work anymore. change to: ``WoloPl\WQuery2csv\Process\[ProcessingClass]`` (@see output.process_fields and ref in point 4.4)


* from version 0.3.x
	- process_fields: processors are now in separate classes, so
	  instead ~~WoloPl\WQuery2csv\Process->parseDate~~ use now:  ``WoloPl\WQuery2csv\Process\ParseDate``
	  (also ~~\Process->tableLabelsFromRecordsCommalist~~ became: ``\Process\LabelsFromRecords``
	   and ~~\Process->tableLabelsFromMmRelations~~ is now: ``\Process\LabelsFromMmRelations``)


* from version 0.1.x
	- typoscript setup key is now: ``plugin.tx_wquery2csv_export`` instead of ~~plugin.tx_wquery2csv_pi1~~
	- the plugin content element embeded on page must be selected again for the same reason - or run migrate query:
		UPDATE `tt_content`  SET list_type = "w_query2csv_export"  WHERE list_type = 'w_query2csv_pi1';
	- process_fields now expects full callable userfunc reference
	- so, ~~_process_parseDate~~ option is now ``\WoloPl\WQuery2csv\Process\ParseDate``
	- ~~process_fields_user~~ is now removed, use ``process_fields`` instead, just like the rest
	- output.where now need to be full, not starting from "AND" (1=1 is removed, so just remove the AND from beginning)





## 8. Q3i: migrate projects still using modified 0.1.5

- 'USERFUNC' field value: change parameters array passed to userfunc, keys: 'field' -> 'fieldName', 'data' -> 'row'. old names will be still working for some time, in future would be removed
- q3i mods are now integrated since 0.4.0, so ts needs to be changed:
	- input.q3i.postprocessors  ->  output.postprocessors_row (it is actually output option, so I moved it where it belongs)
    - input.q3i.sql_file  ->  input.sql_file
    - input.q3i.sql_markers  ->  input.sql_markers
- nbr -> strip_linebreaks, hsc -> htmlspecialchars



## 9. ChangeLog

##### 0.6.7
- 12.x compatibility
- php 8 fixes
- removed long deprecated conf settings nbr and hsc
- drop support for older TYPO3

##### 0.6.6
- 11.x compatibility

##### 0.6.5
- Feature: array items from the Unserialize processor can be now merged to csv output as new columns (mergeAsColumns)
- Feature: Unserialize processor can now return json, instead of the original formatted values text (returnJson)

##### 0.6.4
- Feature: header row labels userfunc postprocessing
- Composer.json added

##### 0.6.3
- Minor fix in one of optional postprocessors

##### 0.6.2
- Config validation, debug/migration improvements

##### 0.6.1
- [minor breaking] Core::config property was renamed to Core::file_config (might impact your custom processors or xclasses)
- New options: output.additionalHeaders, output.additionalHeadersProcessor
- File delivery was extracted to its own Disposition object, for more control of output and better extendability
- Some old long-deprecated options (hsc, nbr) now triggers deprecation log/error

##### 0.6.0
- TYPO3 10.4 / full Doctrine compatibility - no more typo3db_legacy
- Removed deprecated old general Process class
- New processor: PregReplace
- Processors now implements processor interface (but it's not yet mandatory when writing own)

##### 0.5.0
- TYPO3 9.5 compatibility (needs typo3db_legacy to be installed, no doctrine support yet)

##### 0.4.2
- Fixed stupid missed bracket in main plugin

##### 0.4.1
- Automatic debug_allowed now works also on Development/[*] instances

##### 0.4.0
- Q3i modifications and features now integrated into ext (sql template file, postprocessors, some tuning)
- Value processing is now split to separate classes for each processor (@see Migrate) - (old way is deprecated but still works yet)
- Minor tweaks

##### 0.3.0
- Charset converting - iconv changed to mbstring (thanks to Henri Nathanson for suggestion)
- Inactive records filtering changed to native - now use enableFields = 1 for this. use default_enableColumns only when table isn't in TCA
- Option add_fields fixed - works again
- Debugging now works automatically when TYPO3_CONTEXT == Development, no need to set debug_allowed = 1
- Process->unserialize now can use custom delimiter
- New processors: ->tableLabelsFromRecordsCommalist, ->tableLabelsFromMmRelations

##### 0.2.0
- Code reworked and compatible with TYPO3 version 7.x and 8.x
- Userfunc field processing
- Many minor improvements – some of them breaks compatibility. Please refer to Migration section, if updating

##### 0.1.5
- Last built query is saved and available on debug
- Quotes in input are escaped to csv-compatible double quotes (was messing whole csv)
- Additional tables added to not_allowed_tables

##### 0.1.1
- First release with full documentation.






## 10. More examples
```typo3_typoscript

    plugin.tx_wquery2csv_export.files  {
        test    {
            input    {
                table = tx_news_domain_model_news
                limit = 3
                fields = uid,title
            }

            output    {
                add_fields = cat_internal
                process_fields    {
                    cat_internal = WoloPl\WQuery2csv\Process\LabelsFromMmRelations
                    cat_internal    {
                        table = sys_category
                        table_mm = sys_category_record_mm
                        field = title
                    }
                }
            }
        }
    }
```

Header postprocess userfunc example:
```php
    namespace Q3i\Q3iewContest\CsvExport\Postprocessor;

    class HeaderPostprocessor   {

        /**
         * Takes 'config' and 'data' keys in $config array, returns $config['data']
         * @param $config array
         * @param $pObj \WoloPl\WQuery2csv\Core
         * @return array
         */
        public function myMethod($config, &$pObj) {
            $headerlabels = $config['data'];
            $headerlabels['current_label'] = 'Nice label';
            return $headerlabels;
        }
    }
```




## 11. Check my other TYPO3 stuff:

### - TYPO3 - BE/FE/Env Handy Switcher

Chrome extension - Magic server teleporter. Extreme workflow accelerator. For jumping between projects
instances / servers / frontend / backend. Has many useful features (most of them optional) like marking current
instance by colored favicon and badge, wide project management with team share features.
It's very helpful on everyday basis, if you work with dozens of projects on a number of dev/staging environments
and need to move fast between them not search every time for an url to each one.

https://github.com/w010/chrome-typo3-switcher
https://chrome.google.com/webstore/detail/typo3-befeenv-handy-switc/ohemimdlihjdeacgbccdkafckackmcmn


### - DUMP

Damn Usable Management Program for TYPO3
Originally a database import/export and backup utility, now a tool for a number of Typo3 system operations
for devs and integrators.
Functions: Database dump, full migration pack, backup, domains update, missing files fetcher, database
manual query quick exec, system actions, quick xclass generator.

https://github.com/w010/DUMP



### - TYPO3 Content Summary

Simple, but extremely helpful script to make a summary and visualize tt_content use in a TYPO3 installation.
Helps to track & analyse content types / plugin instances / header types / frames / image orient / tv fce,
especially when you're going to make a major system update and have to take care of all plugins, migrate
csc -> fsc / other incompatibilities between Typo3 branches. Here you can see where all your customized ts csc
wraps are actually used (and if they're still needed).

https://github.com/w010/contentsummary


