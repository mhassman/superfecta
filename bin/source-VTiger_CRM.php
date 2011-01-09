<?php
//this file is designed to be used as an include that is part of a loop.
//If a valid match is found, it should give $caller_id a value
//available variables for use are: $thenumber


//configuration / display parameters
//The description cannot contain "a" tags, but can contain limited HTML. Some HTML (like the a tags) will break the UI.
$source_desc = "Look up First Name and Last Name in vTiger CRM DB, local or remote.<br>This data source requires Superfecta Module version 2.2.1 or higher.";
$source_param = array();
$source_param['DB_Host']['desc'] = 'Host address of the vTiger CRM database. (localhost if the database is on the same server as FreePBX)';
$source_param['DB_Host']['type'] = 'text';
$source_param['DB_Host']['default'] = 'localhost';
$source_param['DB_Name']['desc'] = 'schema name of the vTiger CRM database';
$source_param['DB_Name']['type'] = 'text';
$source_param['DB_Name']['default'] = 'vtigercrm521';
$source_param['DB_User']['desc'] = 'Username used to connect to the  vTiger CRM database';
$source_param['DB_User']['type'] = 'text';
$source_param['DB_User']['default'] = 'root';
$source_param['DB_Password']['desc'] = 'Password used to connect to the  vTiger CRM database';
$source_param['DB_Password']['type'] = 'password';
$source_param['DB_Password']['default'] = 'passw0rd';
$source_param['Search_Office_Phone']['desc'] = 'Perform search on Office Number Field';
$source_param['Search_Office_Phone']['type'] = 'checkbox';
$source_param['Search_Office_Phone']['default'] = true;
$source_param['Search_Mobile_Phone']['desc'] = 'Perform search on Mobile Number Field';
$source_param['Search_Mobile_Phone']['type'] = 'checkbox';
$source_param['Search_Mobile_Phone']['default'] = true;
$source_param['Search_Fax_Phone']['desc'] = 'Perform search on Fax Number Field';
$source_param['Search_Fax_Phone']['type'] = 'checkbox';
$source_param['Search_Fax_Phone']['default'] = true;
$source_param['Search_Home_Phone']['desc'] = 'Perform search on Home Number Field';
$source_param['Search_Home_Phone']['type'] = 'checkbox';
$source_param['Search_Home_Phone']['default'] = true;
$source_param['Search_Other_Phone']['desc'] = 'Perform search on Other Number Field';
$source_param['Search_Other_Phone']['type'] = 'checkbox';
$source_param['Search_Other_Phone']['default'] = true;
$source_param['Filter_Length']['desc']='The number of rightmost digits to check for a match';
$source_param['Filter_Length']['type']='number';
$source_param['Filter_Length']['default']= 10;

//run this if the script is running in the "get caller id" usage mode.
if($usage_mode == 'get caller id')
{
	if($debug)
	{
		print "Searching vTiger ... ";
	}
	$numbererror = false;
        $vtfound = false;
	$wquery_input = "";
	$wquery_string = "";
	$wquery_result = "";

	if (strlen($thenumber) < $run_param['Filter_Length'])
	{
		If ($debug)
		{
			Print "Not enough digits";
		}
		$numbererror = true;
	}
	else
	{
	//  Build regular expression from $thenumber to avoid non-digit characters
	//  run with 123456789 for testing
         $wquery_input = "'[^0-9]*1[^0-9]*2[^0-9]*3[^0-9]*4[^0-9]*5[^0-9]*6[^0-9]*7[^0-9]*8[^0-9]*9[^0-9]*0[^0-9]*'";
        }


	// Connect to vTiger db
        if (!$numbererror)
        {
		$wdb_handle = mysql_connect($run_param['DB_Host'], $run_param['DB_User'], $run_param['DB_Password']) or die("vTiger connection failed" . mysql_error());
		mysql_select_db($run_param['DB_Name']) or die("vTiger db open error: " . mysql_error());
		mysql_query("SET NAMES 'utf8'") or die("UTF8 set query  failed: " . mysql_error());
	}

	// search Office Phone field
       	if(($run_param['Search_Office_Phone'] != "off") && !$numbererror && !$vtfound)
	{
		$wquery_string = "SELECT firstname,lastname FROM vtiger_contactdetails WHERE phone REGEXP ".$wquery_input." LIMIT 1";
                $wquery_result = mysql_query($wquery_string) or die("SugarCRM accounts query failed" . mysql_error());
		if(mysql_num_rows($wquery_result)>0)
		{
			$vtfound = true;
		}
	}

	// no result yet? search mobile phone field
       	if(($run_param['Search_Mobile_Phone']!= "off") && !$numbererror && !$vtfound)
	{
                $wquery_string = "SELECT firstname,lastname FROM vtiger_contactdetails WHERE mobile REGEXP ".$wquery_input." LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM users query failed" . mysql_error());
                if(mysql_num_rows($wquery_result)>0)
		{
			$vtfound = true;
		}
	}

	// no result yet? search fax number field
       	if(($run_param['Search_Fax_Phone']!= "off") && !$numbererror && !$vtfound)
	{
                $wquery_string = "SELECT firstname,lastname FROM vtiger_contactdetails WHERE fax REGEXP ".$wquery_input." LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM users query failed" . mysql_error());
                if(mysql_num_rows($wquery_result)>0)
		{
			$vtfound = true;
		}
	}

	// no result yet? search Home phone number field
       	if(($run_param['Search_Home_Phone']!= "off") && !$numbererror && !$vtfound)
	{
                $wquery_string = "SELECT firstname,lastname FROM vtiger_contactdetails INNER JOIN vtiger_contactsubdetails ON vtiger_contactsubdetails.contactsubscriptionid=vtiger_contactdetails.contactid WHERE vtiger_contactsubdetails.homephone REGEXP ".$wquery_input." LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM users query failed" . mysql_error());
                if(mysql_num_rows($wquery_result)>0)
		{
			$vtfound = true;
		}
	}

	// no result yet? search Other phone number field
       	if(($run_param['Search_Other_Phone']!= "off") && !$numbererror && !$vtfound)
	{
                $wquery_string = "SELECT firstname,lastname FROM vtiger_contactdetails INNER JOIN vtiger_contactsubdetails ON vtiger_contactsubdetails.contactsubscriptionid=vtiger_contactdetails.contactid WHERE vtiger_contactsubdetails.otherphone REGEXP ".$wquery_input." LIMIT 1";
		$wquery_result = mysql_query($wquery_string) or die("SugarCRM users query failed" . mysql_error());
                if(mysql_num_rows($wquery_result)>0)
		{
			$vtfound = true;
		}
	}

	mysql_close($wdb_handle);

	if(mysql_num_rows($wquery_result)>0)
	{
                $wquery_row = mysql_fetch_array($wquery_result);
		$caller_id = $wquery_row["firstname"]." ".$wquery_row["lastname"];
	}
	else if($debug)
	{
		print "not found<br>\n";
	}
}
?>
