# note, that the typoscript below is not to be included as-is
# these are just examples how to use it.
# you need to copy this to your template setup and fine-tune to your needs

# REMEMBER that it may not be safe to allow exporting any database tables, so THINK WHAT ARE YOU DOING




# shortest example, just dumps given table to csv file
#
plugin.tx_wquery2csv_export.files.my_file.input.table = tx_sometable




# more complex:
#
plugin.tx_wquery2csv_export  {
	files {
		somestuff_orders {
			input {
				table = tx_somestuff_orders
				fields = tstamp,name,email,phone,stuff_uid
				where = category = 2
				#where_wrap = TEXT
				#where_wrap.value = pid = {page:uid}
				#where_wrap.stdWrap.insertData = 1
				group =
				order = tstamp DESC
				limit =
				default_enableColumns = 1
			}

			output {
				filename = somestuff-myorders-monthly.csv
				#separator = ,
				#encoding =
				#hsc = 0
				#no_header_row = 0
				process_fields {
					tstamp = WoloPl\WQuery2csv\Process->parseDate
					tstamp.format = d.m.Y H:i
					category = WoloPl\WQuery2csv\Process->valueMap
					category.map	{
						0 = No category
						39 = Event
					}
					some_field = someClass->userfuncReference
				}
				add_fields = newFieldX,newFieldY
			}
			#disable = 0
		}
		_default	{

		}
	}

	debug_allowed = 0
	default_config_if_missed = 0
}




# page type example:
#
wquery2csv = PAGE
wquery2csv	{
	typeNum = 744

	10 =< plugin.tx_wquery2csv_export
	10	{
		files	{
			_default	{
				input {
					table = 
					fields = 
					where_wrap = TEXT
					where_wrap.value = pid = {page:uid}
					where_wrap.stdWrap.insertData = 1
				}
			}
		}
		default_config_if_missed = 1
	}

	config {
		no_cache = 1
		debug = 0
		disableAllHeaderCode = 1
		xhtml_cleaning = 0
		additionalHeaders {
			10 = Content-Type: text/csv; charset=utf-8
			20 = Content-Disposition: attachment; filename=myfile.csv;
		}
	}
}

