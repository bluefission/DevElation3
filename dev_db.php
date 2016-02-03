<?php
//dev_db_control
//Last modified: 11/18/05
//Author Devon .M. Scott
// dscott@bluefission.com
//Written for php_version 4.3.3 or later. May work with earlier versions
///////
/* 
 * This document contains functions for editing database content through an
 * DevObject content management system. 
*/

function dev_dbconn($username, $password, $database, $server = '') {
	$server = ($server == '') ? 'localhost' : $server;
	$link = mysql_connect ($server, $username, $password) or die ('I cannot connect to the database because: ' . mysql_error());
	mysql_select_db ($database);
	
	return $link;
}

function dev_dbclose($link = '') {
	mysql_close($link);
}

//outputs any value as an array element or returns value if it is an array
//$value argument takes any mixed variable
//returns an array
function dev_value_to_array($value, $allow_empty = false) {
	$value_r = array();
	if (!is_string($value) || (!$value == '' || $allow_empty))
	(is_array($value)) ? $value_r = $value : $value_r[] = $value;
	return $value_r;
}

//splits a date in MySQL format into it's various elements or convetes it to mm/dd/yyyy format
//$date argument takes the mySQL date
//$section argument takes the string 'day', 'month', or 'year'
//returns a date string
function dev_split_date($date, $section = '', $timestamp = 0) {
	$output = '';
	
	if ($timestamp == 1) $pattern = '/^(\d{4})\-(\d+)\-(\d+)[\w\W\d\D\s]*$/';
	else $pattern = '/^(\d{4})\-(\d+)\-(\d+)$/';
	switch ($section) {
	case 'day':
		$replacement = '$3';
		break;
	case 'month':
		$replacement = '$2';
		break;
	case 'year':
		$replacement = '$1';
		break;
	default: 
		$replacement = '$2/$3/$1';
		break;
	}
	
	$output = preg_replace($pattern, $replacement, $date);
	
	return $output;
}

//joins variables from a form post or get into a single date string
//$name argument takes as string as the base name for the date elements and the name of the final date var
//returns a standard format date as a string (mm/dd/yyyy)
function dev_join_date($name = 'date') {
     $entry_month = $name . '_month';
     $entry_day = $name . '_day';
     $entry_year = $name . '_year';
     
     $array = array_merge ($GLOBALS, $_SESSION, $_COOKIE, $_POST, $_GET);
     //global $$entry_month, $$entry_day, $$entry_year;
     
     $date = $array[$entry_month] . '/' . $array[$entry_day] . '/' . $array[$entry_year];
     //$date = $$entry_month . '/' . $$entry_day . '/' . $$entry_year;
     
     return $date;
}

//Prepares raw input for entry into a MySQL database. Handles single quotes (apostrophes), 
//mm/dd/yyyy date format to MySQL date format conversion, and null entries
//$string takes the item that is to be formatted
//$datetime takes the argument 1 or 0 (true or false) and will append a "12:00:00" timestamp to the date if true
//returns a string
function dev_prep_input($string, $datetime = 0) {
	
	$output = '';
	//Create regular expression patterns
	$pattern = array( '/\'/', '/^([\w\W\d\D\s]+)$/', '/(\d+)\/(\d+)\/(\d{4})/', '/\'(\d)\'/', '/\$/', '/^\'\'$/' );
	$replacement = array( '&#39;', '\'$1\'', '$3-$1-$2', '$1', '&#36;', 'NULL' );
	if ($datetime === true) $replacement = array( '&#39;', '\'$1\'', '$3-$1-$2 12:00:00', '$1', '&#36;', 'NULL' );
	
	$string = @mysql_info() ? mysql_real_escape_string( $string ) : $string;
	$string = preg_replace($pattern, $replacement, stripslashes($string));
	
	if (strlen($string) <= 0) $string = 'NULL';
	if ($string == '\'NOW()\'') $string = 'NOW()';
	
	$output = $string;
	
	return $output;
}

//Determines if the entry will be replaced with a null value or kept the same when NULL is passed
//$table takes the databased table to search in
//$field is the column that the value is in
//$value is the original value to be checked or preserved
//$where is the where clause that determines the row of the entry
function dev_ignore_null_entry($table, $field, $value, $where) {
     if ($value == 'NULL') {
          $query = "SELECT `$field` FROM `$table` WHERE $where";
          $result = mysql_query($query);
          $selection = mysql_fetch_array($result, MYSQL_BOTH);
          echo mysql_error();
          $value = dev_prep_input($selection[0]);
     }
          
     return $value;
}

//inserts data into a MySQL database. 
//$table takes a string that represents the name of the database table
//(old) $fields is an array of all fields to be affected
//(old) $values is an array of all values to be inserted
//$data is an associative array of fields and values to be affected
//returns a true if insert was successful, false if not
function dev_db_insert($table, $data, &$query_str) {
	//echo 'inserting, ';
	$field_string = '';
	$value_string = '';  
	$temp_values = array();
	$temp_fields = array();
	
	//turn array to string
	$keys = array_keys($data);
	foreach ($keys as $a) array_push($temp_fields, "`{$a}`");

	$field_string = implode( ', ', $temp_fields);
	//prepare each value for input
	foreach ($data as $a) array_push($temp_values, dev_prep_input($a));
	
	$value_string = implode(', ', $temp_values);
	
	$query = "INSERT INTO `$table`($field_string) VALUES($value_string)";
	
	$query_str = $query;
	
	(mysql_query($query) ) ? $status = true : $status = false;
	
	return $status;
}

//updates data in a MySQL database. 
//$table takes a string that represents the name of the database table
//(old) $fields is an array of all fields to be affected 
//(old) $values is an array of all values to be changed
//$data is an associative array of fields and values to be affected
//returns a true if update was successful, false if not
//$ignore_null takes either a 1 or 0 (true or false) and determines if the entry 
//   will be replaced with a null value or kept the same when NULL is passed
function dev_db_update($table, $data, $where, $ignore_null = 0, &$query_str) {
	//echo 'updating, ';
	$updates = array();
	$temp_values = array();
	$update_string = '';
	$query_str;
	
	foreach ($data as $a) array_push($temp_values, dev_prep_input($a));
	
	$count = 0;
	foreach (array_keys($data) as $a) {
		//convert into query string
		if ($ignore_null == 1) {
			$temp_values[$count] = dev_ignore_null_entry($table, $a, $temp_values[$count], $where);
		}
		array_push($updates, "$a = $temp_values[$count]");
		$count++;
	}
	
	$update_string = implode(', ', $updates);
	
	$query = "UPDATE `$table` SET $update_string WHERE $where";
	
	//echo "$query<br />";
	$query_str = $query;
	 
	(mysql_query($query) ) ? $status = true : $status = false;
	
	return $status;
}

//Posts data into DB by whatever means specified
//$table takes a string that represents the name of the database table
//(old) $fields is an array of all fields to be affected
//(old) $values is an array of all values to be changed
//$data is an associative array of fields and values to be affected
//$where takes a MySQL where clause. Uses an update query if given.
//$type determines what type of query will be used. 1 gives an insert, 2 gives an update, 3 give and update ignoring nulls
//Returns string with error or success statement.
function dev_db_post($table, $data, $where = '', $type = 1, &$query_str) {
	//echo 'posting, ';
	$output = '';
	$ignore_null = 0;
	if ($where == '' && ($type == 2 || $type == 3)) {
		$where = "1";
	} elseif (isset($where) && $where != '' && $type != 3) {
		$type = 2;
	}
	if (isset($table) && $table != '') { //if a table is specified
		if (count($data) >= 1) { //validates number of fields and values
			switch ($type) {
			case 1:
				//attempt a database insert
				if (dev_db_insert($table, $data, $query_str)) {
					$output = "Successfully Inserted Entry.";
				} else {
					$output = "Insert Failed. Reason: " . mysql_error();
				}
				break;
			case 3:
				$ignore_null = 1;
			case 2:
				//attempt a database update
				if (isset($where) && $where != '') {
					if (dev_db_update($table, $data, $where, $ignore_null, $query_str)) {
						$output = "Successfully Updated Entry.";
					} else {
						$output = "Update Failed. Reason: " . mysql_error();
					}
				} else {
					//if where clause is empty
					$output = "No Target Entry Specified.";
				}
			break;
			default:
				//if type is not registered
				$output = "Query Type Not Supported.";
				break;
			}
		} else {
		//if the arrays do not align or match
		$output = "Fields and Values do not match or Insufficient Fields.";
		}
	} else {
		//no table has been assigned
		$output = "No Target Table Specified";
	}
	
	return $output;
}

//creates a form from which to choose an entry to edit through the CMS
//$table is the name of the table to choose from
//$entry_id is the name of the unique primary key field in the table
//$entry_title is the name of the field that provides an identifiable name of the entry
//$date is the name of the field that holds a date for that entry to be edited
function dev_write_edit_form($table = '', $entry_id = 'entry_id', $entry_title = 'entry_title', $date = '', $label = '', $hidden_input = '') {
     $output = '';
     if ($label != '') $output .= $label . ': <br />';
     $output .= '
     <form name="edit" action="" method="POST">
     <input type="hidden" name="action" value="edit">
     ';
     if (is_array($hidden_input)) {
     	foreach ($hidden_input as $a=>$b)
	     	$output .= dev_draw_form_field('hidden', $a, '', $b);
     }
     $output .= '<select name="entry_id">';

     /*
     $pattern = '/^(\d{4})\-(\d+)\-(\d+)[\w\W\d\D\s]+$/';
     $replacement = '$2/$3/$1';
     */
     $query = "SELECT `$entry_id`, `$entry_title`";
     //also select date field if field is specified
     if ($date != '') $query .=  ", `$date`";
     $query .= " FROM `$table`";
     if ($date != '') $query .= " ORDER BY `$date` ASC";
     else $query .= " ORDER BY `$entry_title` ASC";
     $result = mysql_query($query);
     //echo $query;
     while($entry = mysql_fetch_array($result)) {
		$output .= '<option value="' . $entry[$entry_id] . '">' . $entry[$entry_title];
		//turn to standard date format
		if ($date != '') $output .= ' - ' . dev_split_date($entry[$date]);
		$output .= '</option>';
     }
     $output .= '
     </select><br />
     <input type="submit" name="edit" value="edit...">
     </form>
     ';

     return $output;
}

//Gets all information for a table row (an object) and places it into an array
//$query is a mysql select query
//returns an array of a row
function dev_get_members($query) {
     $status = '';
     $object_data = array();
     $result = mysql_query($query);
     $status = mysql_error();
     //echo $query;
     //echo mysql_num_rows($result);
     echo $status;
     if (mysql_num_rows($result) < 1) $object_data = '';
     else $object_data = mysql_fetch_array($result, MYSQL_ASSOC);
     
     unset($status, $query);
     return $object_data; 
}

function dev_check_dupes($field, $value = '', $table, $message = '', $redirect = '') {
	$href = dev_href($redirect);
	if ($message == '') {
		$message = "Sorry, but an entry already exists for field `$field` having value '$value'. Please try again with a new value";
	}
	$rows = new DevModel( $table );
	$rows->clear();
	if ($value != '') {
		$rows->setCondition($field, '=', $value);
		if (count($rows->getRecordSet()) > 0) {
			if ($redirect != '') dev_redirect($href, array('fld'=>$field, 'msg'=>$message));
			else return false;
		}
	}
	return true;
}
?>