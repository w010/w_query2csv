# Dump SQL table/query to CSV

w_query2csv  
readme / manual


wolo.pl '.' studio  
2009 - 2018  
wolo.wolski@gmail.com

Credits:  
Q3i  
Hint Intermedia  


##


## 1. WHAT IS THIS?

This is an extension for quick export SQL table or query/ies to CSV file/s. You may configure number of files 
which will be available, for every file using their own settings, select different table, fields which will be 
exported, standard parts of MySQL query, file charset, csv fields separator and more. All that is fast and easy, 
using included example TypoScript.  
You may also parse selected fields with own or built-in methods, like tstamp to human-readable date.


	PLEASE SEE "SECURITY" SECTION BEFORE USE!



## 2. FOR WHAT DO I NEED IT?

If you have data set in db, eg. some orders or contest answers, that must be quick sent to someone by email, or 
periodically downloaded by someone, or something like that, this extension is very useful.

Just make new page, insert plugin and set up the file, that will be downloaded by other or specified users, which you 
may specify by standard TYPO3 access settings.  
It's simple and easy to configure, additionally you may process selected fields using some functions, eg. convert 
timestamps to human-readable strings.

If you want something more functional it's a good base to develop something bigger.

> ![download file screen](https://docs.typo3.org/typo3cms/extensions/w_query2csv/_images/img-3.jpeg)



## 3. HOW TO USE IT? 

### 3.1 Basic

Simply insert plugin on new page named eg. 'CSV export' and configure input and output using TypoScript.
A Backend Section type page is good idea, you might control download access for backend non-admin users.
To download file, you have to access url using _?f=[configuration_key]_, as here:  
http://example.com/csv_export.html?f=somestuff_orders


Note, that this plugin is not basing on standard TYPO3 page type handling, but is changing headers for whole page
containg it, so do not insert it on standard frontend pages with normal content.  
But it's possible to make CSV output of any page uid using page type, if you use a snippet from 
Configuration/TypoScript/Setup/setup.ts

**Caution where do you insert this plugin and what is exported!**

> ![backend typoscript](https://docs.typo3.org/typo3cms/extensions/w_query2csv/_images/img-4.jpeg)


### 3.2 Using in own plugins

One time I needed to display link to csv in my other plugin context. This needs to make query2csv object, prepare 
and pass config to it, and call when requested.  
This can be done like that:

```
$content .= '<a href="'.GeneralUtility::linkThisUrl($_SERVER['REQUEST_URI'], array('action' => 'getfile', 'f' => 'my_file')) . '">download file</a>';

if ($_GET['action'] == 'getfile')	{
	require_once(ExtensionManagementUtility::extPath('w_query2csv').'Classes/Plugin/Export.php');

	$conf = [
		'debug_allowed' => 0,
		'files.' => [
				'my_file.' => [
				'input.' => [
					'table' => 'tx_myext_table',
					'fields' => 'uid, crdate, name, value',
					'where' => 'AND type = 2',
					'order' => 'uid DESC',
					'limit' => 100,
				],
				'output.' => [
					'filename' => 'mydata.csv',
					'process_fields.' => [
						'crdate' => 'WoloPl\WQuery2csv\Process\ParseDate',
	]]]]];

	$Q2csv = GeneralUtility::makeInstance('tx_wquery2csv_export');

	$Q2csv->main('', $conf);
	// note, that the script now throws output and exits!
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
              }
            }
        }

        # .... (some other keys with input and output options to access file using ?f=some_other_key)
    }

    debug_allowed = 1
}
```


Above file is available using url param ?f=somestuff_orders.

##  

### 4.2 Shortest working example:

When you take a look at reference below, you'll see, that only db table is really required in configuration, so 
shortest config would look like this:

```
plugin.tx_wquery2csv_pi1.files.my_file.input.table = tx_sometable
```

> Explaining:  
file key “**my_file**” has set input table to “**tx_sometable**” (and plugin is inserted on _download_csv_ page)  
so:  
the csv file containing records from this table will be accessed using: [download_csv.html]?f=my_file  
and:  
prompted to download as my_file.csv  

The easiest way to quick configure is to just copy example config from above.

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
				 DEFAULT IS ENABLED (so if you was given empty csv, check that selected table has these fields and set this option to 0 if not)
	
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

			'hsc'							- (deprecated) same as htmlspecialchars

			'strip_linebreaks' (Bool/Int) 	- strip line breaks from value. Default = 0

			'nbr' - (deprecated) same as strip_linebreaks

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
						class = NameSpace\MyExt\CsvExport\Postprocessor\DataPostprocessor->myMethod
						someConfParam = myValue
					}

		
		'disable' (Bool/Int)	- disable this file (quick restrict download access). Default = 0



'debug_allowed' (Bool/Int) 				- allow to use &debug=1 url param. Default = 0

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
	
	Unserialize - Unserializes an array and generates key: value pairs separated by given delimiter
		config:
			.delimiter = [string] - may be anything, also you can use special keywords: -LINEBREAK- and -SPACE-. defaults to linebreak
			.lineBreakType = [string] - if use linebreak, may be configured to use CR, LF or CRLF. defaults to LF
	
	LabelsFromRecords - Generate string with some values from related records, using uid-commalist, like, ie. titles of referenced categories
		config:
			.table = [string] - table name to read referenced items from
			.field = [string] - use value from this field
			.delimiter = [string] - may be anything, also you can use special keywords: -LINEBREAK- and -SPACE-. defaults to linebreak
			.lineBreakType = [string] - may be CR, LF or CRLF. defaults to LF
	
	LabelsFromMmRelations - Same as previous, but uses mm table to read records
		(note, that to make this work, you MUST add "uid" field to your .input.fields. if you don't wanna uid column in csv, use .output.remove_fields = uid)
		config:
			the same as above, plus:
			.table_mm = [string] - mm relations table
	
	...or write your own.
```




## 5. SECURITY

### ! PLEASE REMEMBER !

This is a low-level database exporter, which was intended to use by admins to easy download customized csv with product orders. Such things are always potentially dangerous if used in wrong way. Downloading full database tables by people who are not permitted may be a disaster - passwords leak, session hijack, etc... - so better check twice where do you put this plugin and what have you configured there, to not allow downloading any sensitive data by mistake!

I recommend always embeding it on pages with BE-user or admin access only, unless you are sure what are you doing.




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
Write any Typo3-callable class with a run() public method with params ($params, &$pObj) and return a string.
(Optionally you can use any method name and configure process using class->methodname)  
In $params array you can expect to be passed:
	'value' - field value from db  
	'fieldName' - name of current processed field  
	'row' - whole record of current item (I mean, fields which you set to read in .input.fields, if not "*")  
	'conf' - typoscript configuration of this processor
$pObj is a WoloPl\WQuery2csv\Core instance

> Best way is to just write your method in your extension, the same way like I did in w_query2csv/Classes/Process.php, and configure processing ts in standard namespace way.    
In case you don't know what I mean: add your class in your own ext, respecting standard Typo3 path and naming convention to make the class be found using namespace  
(like: class in file typo3conf/ext/my_ext/Classes/Extensions/WQuery2csv/Process.php = available by namespace MyNamespace\MyExt\Extensions\WQuery2csv\Process  
write a processing method like that:

> public function doSomethingWithValue($params, \WoloPl\WQuery2csv\Core &$pObj)    { return $myProcessedString }

> and register using:  
	plugin.tx_wquery2csv_export.files.myFile.output.process_fields.some_field = MyNamespace\MyExt\Extensions\WQuery2csv\Process->doSomethingWithValue  
	plugin.tx_wquery2csv_export.files.myFile.output.process_fields.some_field.someAdditionalOptionToPass = something


--  
**Q:  
My output file is empty!**

> A:
- try to set debug_allowed = 1 in config and access file with &debug=1 to check if the config is passed properly.
- try to set "fields" to *, comment other parts of db query
- check another table
- set "default_enableColumns" to 0 (not just delete line!), maybe the table hasn't "deleted" and "hidden" fields
- set "enableFields" to 0, maybe the table is not configured in TCA


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

* from version 0.1.x
	- typoscript setup key is now: plugin.tx_wquery2csv_export instead of plugin.tx_wquery2csv_pi1
	- the plugin content element embeded on page must be selected again for the same reason
	- process_fields now expects full callable userfunc reference
	- so, _process_parseDate option is now \WoloPl\WQuery2csv\Process\ParseDate
	- process_fields_user is now removed, use process_fields instead, just like the rest
	- output.where now need to be full, not starting from "AND" (1=1 is removed, so just remove this AND from beginning)

* from version 0.3.0
	- process_fields: processors are now in separate classes, so instead WoloPl\WQuery2csv\Process->parseDate use now:  WoloPl\WQuery2csv\Process\ParseDate
	(also \Process->tableLabelsFromRecordsCommalist becames \Process\LabelsFromRecords and \Process->tableLabelsFromMmRelations becames \Process\LabelsFromMmRelations)




## 8. Q3i: migrate projects still using modified 0.1.5

- 'USERFUNC' field value: change parameters array passed to userfunc, keys: 'field' -> 'fieldName', 'data' -> 'row'. old names will be still working for some time, in future would be removed
- q3i mods are now integrated since 0.4.0, so ts needs to be changed:  
	- input.q3i.postprocessors  ->  output.postprocessors_row (it is actually output option, so I moved it where it becomes)  
    - input.q3i.sql_file  ->  input.sql_file  
    - input.q3i.sql_markers  ->  input.sql_markers
- nbr -> strip_linebreaks, hsc -> htmlspecialchars



## 9. ChangeLog

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

