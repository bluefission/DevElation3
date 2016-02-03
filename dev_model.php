<?php
//DevModel 2.0
///
//TOP LEVEL MODEL CLASS
///////
class DevModel {
var $_reference_table;
var $_reference_tables;
var $_related_fields;
var $_query_string;
var $_status_message;
var $_field_info;
var $_last_row_affected;
var $_row_select_limit_start;
var $_row_select_limit_end;
var $_active_fields;
var $_active_fields_on;
var $_conditional_vars, $_distinct_vars, $_sort_vars;
var $_db_link;

function DevModel() {
	$tables = ( func_num_args() > 0 ) ? ( is_array( func_get_arg(0) ) ? func_get_arg(0) : dev_value_to_array( func_get_args() ) ) : array();
	if (count($tables) > 1 )
	{
		$count = count($tables);
	}
	else 
	{
		$count = func_num_args();
	}
		
	$this->_status_message = array();
	$this->_reference_tables = array();
	$this->_active_fields = array();
	$this->_related_fields = array();
	$this->_active_fields_on = false;
	$this->_conditional_vars = $this->_distinct_vars = $this->_sort_vars = array();
	$ref_table = $tables;
	$this->setReferenceTable($ref_table[0]);
	$this->loadDefaults( $this->getReferenceTable() );
	for ($i = 1; $i < $count; $i++) {
		$table = (count($tables)) ? $tables[$i] : dev_value_to_array(func_get_arg($i));
		if ( is_array ($table ) )
		{
			foreach ($table as $a) {
				if (is_string($a) && $a != $this->getReferenceTable()) {
					$this->_reference_tables[] = $a;
					$this->loadDefaults($a);
				}
			}
		}
		else
		{
			if (is_string($table) && $table != $this->getReferenceTable()) {
				$this->_reference_tables[] = $table;
				$this->loadDefaults($table);
			}
		}
	}
	$this->loadFieldInfo();
	$this->clear();
}

function loadDefaults( $table )
{
	foreach ($this->getObjectFields($table) as $a=>$b) 
	{
		$this->setField($a, $b);
	}
}

function getField($field) {
	if ($this->memberExists($field))
	{
		return $this->$field;
	}
	else
	{
		return NULL;
	}
}

function setField($field, $value) {
	$members = $this->getFieldInfo();
	//If we are filtering fields
	if ( array_key_exists( $field, $members ) ) 
	{
		$this->$field = $value;
	}
}

function setID( $id )
{
	$this->setField($this->getPrimaryKey(), $id);
}

function getID()
{
	return $this->getField($this->getPrimaryKey());
}

function setStatusMessage($status = '') {
	if (dev_not_null($status)) $this->_status_message[] = $status;
}

function getStatusMessage() {
	return implode('<br />', $this->_status_message);
}

function clearStatusMessage() {
	$this->_status_mssage = null;
	$this->_status_mssage = array();
}

function getObjectFields($table = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$field_r = array();
	$query = "SHOW COLUMNS FROM `$table`";
	$result = mysql_query($query);
	$this->setStatusMessage(mysql_error());
	while ($column = mysql_fetch_assoc($result)) {
		$field_r[$column['Field']] = $column['Default'];
	}
	
	return $field_r; 
}

function loadPostVars() {
	foreach ($_POST as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

function loadGetVars() {
	foreach ($_GET as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

function loadSessionVars() {
	foreach ($_SESSION as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

function loadCookieVars() {
	foreach ($_COOKIE as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

function loadGlobalVars() {
	foreach ($GLOBALS as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

function loadModelVars($dev_model) {
	foreach ($dev_model->getValues() as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

function loadArrayVars($array) {
	if (dev_is_assoc($array)) foreach ($array as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

function loadObjectVars($object)
{
	$vars = get_object_vars($object);
	if (is_array($vars)) foreach ($vars as $a=>$b) if ($this->memberExists($a)) $this->setField($a, $b);
}

//php4, php5
function read($table = '', $join = true) {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$result = ( $this->getQuery() ) ? $this->runQuery() : $this->getResult($table, $join);
	$members_r = mysql_fetch_array($result, MYSQL_ASSOC);
	
	$members_r = (is_array($members_r)) ? $members_r : array();
	
	foreach ($members_r as $a=>$b) 
	{ 
		if($this->memberExists($a)) 
		{ 
			$this->$a=$b; 
		} 
	}
	
	return $this->getValues();
}

function readNew($table = '', $join = true) {
	$this->clear();
	$this->loadGlobalVars();
	$this->read($table, $join);
}

function createObject($table = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$result = $this->getResult($table);
	$new_object = mysql_fetch_object($result);
	return $new_object;
}

function createModel($table = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$dev_model = new DevModel($table);
	$dev_model->_reference_tables = $this->_reference_tables;
	
	$var1_r = get_object_vars($this);
	$var2_r = get_object_vars($dev_object);
	$member_r = $this->arrayKeyIntersect($var1_r, $var2_r);
	foreach ($member_r as $a=>$b) {
		if ( dev_not_empty( $this->getField($a) ) ) $dev_model->$a = $b;
	}
	$dev_model->read();
	return $dev_model;
}

function getObjectArray($table = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$result = ( $this->getQuery() ) ? $this->runQuery() : $this->getResult($table);
	while ($object_r[] = mysql_fetch_object($result));
	array_pop($object_r);
	return $object_r;
}

function getRecordSet($table = '') {
	$result = ( $this->getQuery() ) ? $this->runQuery() : $this->getResult($table);
	$member_r = array();
	while ($member_r[] = mysql_fetch_array($result, MYSQL_ASSOC));
	array_pop($member_r);
	return $member_r;
}

function getRecordCount( $table = '' )
{
	$result = ( $this->getQuery() ) ? $this->runQuery() : $this->getResult($table);
	$count = mysql_num_rows( $result );
	return $count;
}

function validateInput($field_name = '', $table = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$field_info = $this->getFieldInfo();

	$passed = true;
	
	if (isset($field_info[$field_name])) {
			$this_field = $field_info[$field_name];
			$field_type = strtolower($this_field['Type']);
			
		//If duplicate entry
		if ($this_field['Key'] == 'PRI' || $this_field['Key'] == 'UNI') {
			if (!dev_check_dupes($field_name, $this->getField($field_name), $table)) {
				//$this->setStatusMessage("A row having field '$field_name' with value '" . $this->getField($field_name) . "' already exists.");
				// No longer needs to send failing state but still good to log
				// $passed = false;
			}
			
		} else {
				
			if ( $this->getField($field_name) !== 0 && $this->getField($field_name) == '' ) {
				if (!$this_field['Null'] || $this_field['Null'] == 'NO') {
					if (dev_is_substr($field_type, 'date')) {
						$this->setField($field_name, dev_join_date($field_name));
						if (!is_string($this->getField($field_name)) || !dev_is_date_string($this->getField($field_name))) {
							$this->setStatusMessage("Field '$field_name' contains an inaccurate date format!");
							$passed = false;
						}
					} else {
						$this->setStatusMessage("Field '$field_name' cannot be empty!");
						$passed = false;
					}
				}
			} else {
				//Correct Datatype/Size
				if (dev_is_substr($field_type, 'int') || dev_is_substr($field_type, 'double') || dev_is_substr($field_type, 'float')) {
					if (!is_numeric($this->getField($field_name))) {
						$this->setStatusMessage("Field '$field_name' must be numeric!");
						$passed = false;
					}
				}
				if (dev_is_substr($field_type, 'char') || dev_is_substr($field_type, 'text')) {
					if (!is_string($this->getField($field_name))) {
						$this->setStatusMessage("Field '$field_name' is not text!");
						$passed = false;
					}
					if (dev_not_null($this_field['LENGTH']) && strlen($this->getField($field_name)) > $this_field['LENGTH'])  {
						$this->setStatusMessage("Field '$field_name' is greater than maximum allowed string length!");
						$passed = false;
					}
				}
				if (dev_is_substr($field_type, 'date')) {
					if (!is_string($this->getField($field_name)) || !dev_is_date_string($this->getField($field_name))) {
						$this->setField($field_name, dev_join_date($field_name));
						if (!is_string($this->getField($field_name)) || !dev_is_date_string($this->getField($field_name))) {
							$this->setStatusMessage("Field '$field_name' contains an inaccurate date format!");
							$passed = false;
						}
					}
				}
				if (dev_is_substr($field_type, 'set')) {
					if (is_array($this->getField($field_name))) {
						$this->setField($field_name, implode(', ', $this->getField($field_name)));
					} elseif (!is_string($this->getField($field_name))) {
						$this->setStatusMessage("Field '$field_name' contains invalid input!");
						$passed = false;
					}
				}
			}
		}
	}
	
	return $passed;
}

function write($update = false, $table = '', $ignore_null = 0) {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$where_r = array();
	$query = "SELECT * FROM `$table`";
	$result = mysql_query($query);
	
	for ($i=0; $i < mysql_num_fields($result); $i++) {
		$column = mysql_fetch_field($result, $i);
		$name = $column->name;
		
		if ($this->validateInput($name, $table)) {
			if ($column->primary_key == 1) {
				if ( dev_not_empty( $this->getField($name) ) ) {
					$update = !dev_check_dupes($name, $this->getField($name), $table);
					if ( $update )
					{
						$where_r[] = "`$column->name` = " . dev_prep_input( $this->getField( $name ) );
					}
					
				}
			} 
		} else {
			return false;
		}
	}
	
	$var_r = get_object_vars($this);
	$field_r = $this->getObjectFields($table);
	$member_r = $this->arrayKeyIntersect($var_r, $field_r);
	
	$type = ($update) ? (($ignore_null) ? '3' : '2') : '1';
	$this->setStatusMessage(dev_db_post($table, $member_r, implode(' AND ', $where_r), $type, $query));
	
	$this->setQuery($query);
	
	$this->setLastRowAffected(mysql_insert_id());
	return true;
}

function save($update = false, $table = '', $ignore_null = 0) {
	
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	
	//if is a DevModel, use all tables
	if (isset($this->_reference_tables) && is_array($this->_reference_tables) && count($this->_reference_tables) >= 1) {
		for ($i = (count($this->_reference_tables) - 1); $i >= 0 ; $i--) {
			if (!$this->write($update, $this->_reference_tables[$i])) return false;
			
			//Fix to get unique join tables
			$query = "SELECT * FROM `" . $this->_reference_tables[$i] . "`";
			$result = mysql_query($query);
			for ($j=0; $j < mysql_num_fields($result); $j++) {
				$column = mysql_fetch_field($result, $j);
				$name = $column->name;
		
				if ($column->primary_key == 1) {
					if ( $this->getField( $name ) <= 0 ) $this->setField( $name,  $this->getLastRowAffected() );
				}
			}
			
			$this->clearQuery();			
			//end fix
			$this->read($this->_reference_tables[$i], false);
		}		
	}
	
	if ($this->write($update, $table)) return true;
	else return false;
}	

function getClass() {
	return get_class($this);
}

function setReferenceTable($table = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getClass() : $table;
	$this->_reference_table = $table;
}

function getReferenceTable() {
	return $this->_reference_table;
}

function setReferenceTables($table = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getClass() : $table;
	$count = func_num_args();
	for ($i = 0; $i < $count; $i++) {
		if (is_string(func_get_arg($i))) {
			$this->_reference_tables[] = func_get_arg($i);
		}
	}
}

function clearReferenceTables() {
	$this->_reference_tables = null;
}

function getReferenceTables() {
	if (isset($this->_reference_tables) && is_array($this->_reference_tables)) {
		return $this->_reference_tables;
	} else {
		return false;
	}
}

function getResult($table = '', $join = true) {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$where_r = array('1');
	$sort_r = array();
	$distinct_r = array();
	$var_r = get_object_vars($this);
	$field_r = $this->getObjectFields($table);
	//$member_r = array_intersect_key($var_r, $field_r); //php5
	$member_r = $this->arrayKeyIntersect($var_r, $field_r);
	if (!is_array($this->_conditional_vars)) $this->conditional_vars = array();
	if (!is_array($this->_sort_vars)) $this->_sort_vars = array();
	if (!is_array($this->_distinct_vars)) $this->_distinct_vars = array();
	foreach ($member_r as $a=>$b) {
	
		$where = $this->createWhereCase($table, $a, $b);
		$distinct = $this->createDistinctCase($table, $a);
		if (dev_not_null($where)) $where_r[] = $where;
		if (dev_not_null($distinct)) $distinct_r[] = $distinct;
	}
	
	// Use Ordered Sort Cases
	foreach ( $this->_sort_vars as $a=>$b )
	{
		if ( $this->memberExists($a) )
		{
			$sort = $this->createSortCase($table, $a);
			if (dev_not_null($sort)) $sort_r[] = $sort;
		}
	}	
	//if JoinObject, use all tables
	$left_join = '';
	if ((isset($this->_reference_tables) && is_array($this->_reference_tables) && count($this->_reference_tables) >= 1) && $join) {
		$using_r = array();
		$on_r = array();
		$count = 1;
		foreach ($this->_reference_tables as $a) {
			$join_r = $this->getObjectFields($a);
			if (is_array($join_r)) {
				$field_r = $this->arrayKeyIntersect($this->getObjectFields($table), $join_r);
				foreach ($field_r as $b=>$c) {
					if (!$this->getActiveFieldsOn() || (count($this->_active_fields) > 0 && in_array($b, $this->_active_fields))) $on_r[] = $this->getReferenceTable() . ".$b  = $a.$b";
				}

				$related_fields = $this->getRelatedFields();
				if (count($related_fields) >= 1) {
					$field_r = $this->arrayKeyIntersect($related_fields, $join_r);
					foreach ($field_r as $b=>$c) {
						$on_r[] = $this->getReferenceTable() . "." . $this->getRelatedField($b) . "  = $a.$b";
					}
				}

				for ($i = $count; $i < count($this->_reference_tables); $i++) {
					$b = $this->_reference_tables[$i];
					if ($a != $b) {
						$join_r_2 = $this->getObjectFields($b);
						if (is_array($join_r_2)) {
							$field_r = $this->arrayKeyIntersect($this->getObjectFields($a), $join_r_2);
							foreach ($field_r as $c=>$d) {
								$on_r[] = $a . ".$c  = $b.$c";
							}
						}
						
						$join_r_2 = $this->arrayKeyIntersect($this->getObjectFields($b), $this->getRelatedFields());
						if (is_array($join_r_2)) {	
							$field_r = $this->arrayKeyIntersect($this->getObjectFields($a), $join_r_2);
							foreach ($field_r as $c=>$d) {
								$on_r[] = $a . ".$c  = $b.$c";
							}
						}
					}
				}
				$count++;
				
				$member_r = $this->arrayKeyIntersect($var_r, $join_r);
	
				foreach ($member_r as $b=>$c) {
					$where = $this->createWhereCase($a, $b, $c);
					$sort = $this->createSortCase($a, $b);
					if (dev_not_null($where)) $where_r[] = $where;
					if (dev_not_null($sort)) $sort_r[] = $sort;
				}			
			}
		}
		
		$left_join = "INNER JOIN (" . implode(', ', $this->_reference_tables) . ") ON (" . implode(' AND ', $on_r) . ")";
	}	
	
	
	$field_info = $this->getFieldInfo();
	$select_r = array();
	foreach($this->_active_fields as $a) if ($this->memberExists($a)) $select_r[] = $field_info[$a]['Table'].'.'.$a;
	if (count($select_r) <= 0) $select_r[] = '*';
	
	$query = "SELECT " . implode(', ', $select_r) . " FROM `$table` $left_join WHERE " . implode(' AND ', $where_r); 
	if (count($distinct_r) > 0) $query .= " GROUP BY " . implode(', ', $distinct_r); 
	if (count($sort_r) > 0) $query .= " ORDER BY " . implode(', ', $sort_r); 
	$start = $this->getRowLimitStart();
	$end = $this->getRowLimitEnd();
	$query .= ((dev_not_null($start)) ? " LIMIT " . $this->getRowLimitStart() . ((dev_not_null($end)) ? ", " . $this->getRowLimitEnd() : '') : '');
	$this->setQuery($query);
	$result = mysql_query($query);
	$this->setStatusMessage(mysql_error());
	return $result;
}

function setQuery($query = '') {
	$this->_query_string = $query;
}

function getQuery() {
	return $this->_query_string;
}

function runQuery($query = '') {
	$query = (($query == '') ? (($this->getQuery() == '') ? $this->getResult() : $this->getQuery()) : $query);
	$result = mysql_query($query);
	$this->setStatusMessage(mysql_error());
	return $result;
}

function clearQuery() {
	$this->setQuery('');
}

function createWhereCase($table = '', $member, $value = '') {
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;
	$where = '';
	$where_r = array();
	$condition = $this->getCondition( $member );
	$condition_str = is_array( $condition ) ? $condition[0] : $condition; 
	if ( dev_not_empty( $this->getField($member) ) ) 
	{		
		//Allow for fulltext searches
		if ( strtoupper( $condition_str ) == 'MATCH' ) 
		{
			$match_var = $this->getConditionKey( $member );
			if ( strpos( $match_var, ',' ) ) 
			{
				$match_r = explode( ',', $match_var );
				foreach ( $match_r as $c=>$d ) 
				{
					$match_r[$c] = "$table." . trim($d);
				}
				$match_str = implode(', ', $match_r);
			} 
			elseif ( array_key_exists( $member, $this->_conditional_vars ) ) 
			{
				$match_str = "$table.$member";
			}
			if ( is_array( $value ) ) 
			{
				foreach ( $value as $a ) 
				{
					if ( dev_not_null( $a ) ) 
					{
						$where_r[] = "MATCH($match_str) AGAINST (" . dev_prep_input($a) . ")";
					}
				}
				$where = implode(' OR ', $where_r);
			} 
			else 
			{
				$where = "MATCH($match_str) AGAINST (" . dev_prep_input($value) . ")";
			}
		} 
		elseif ( strtoupper( $condition_str ) == 'IN' ) 
		{
			if ( is_array( $value ) ) 
			{
				foreach ( $value as $a )
				{
					if ( dev_not_null( $a ) ) 
					{
						$where_r[] = $table . ".$member " . ((array_key_exists($member, $this->_conditional_vars)) ? "$condition ": "= ") . $a;
					}
					$where = implode( ' OR ', $where_r );
				}
			} 
			else 
			{
				$where = $table . ".$member " . ((array_key_exists($member, $this->_conditional_vars)) ? $condition : " = ") . "( $value )";
			}
		} 
		elseif ( strtoupper( $condition_str ) == 'NOT IN' ) 
		{
			if ( is_array( $value ) ) 
			{
				foreach ( $value as $a )
				{
					if ( dev_not_null( $a ) ) 
					{
						$where_r[] = $table . ".$member " . ((array_key_exists($member, $this->_conditional_vars)) ? "$condition ": "= ") . $a;
					}
					$where = implode( ' OR ', $where_r );
				}
			} 
			else 
			{
				$where = $table . ".$member " . ((array_key_exists($member, $this->_conditional_vars)) ? $condition : " = ") . "( $value )";
			}
		} 
		else 
		{
			if ( is_array( $value ) ) 
			{
				$count = 0;
				foreach ( $value as $a ) 
				{
					if ( dev_not_null( $a ) ) 
					{
						$temp_where = '';
						$temp_where = $table . ".$member " . ((array_key_exists($member, $this->_conditional_vars)) ? ((is_array($condition)) ? $condition[$count] : $condition) : " = ");
						if ( $condition_str == 'Like' ) 
						{
							$a = "$a%";
						}
						elseif ( $condition_str == 'likE' ) 
						{
							$a = "%$a";
						}
						elseif ( strtoupper( $condition_str ) == 'LIKE' ) 
						{
							$a = "%$a%";
						}
						$temp_where .= dev_prep_input( $a );	
						$where_r[] = $temp_where;
						$count++;
					}
				}
				$where = implode( ( is_array( $condition ) ) ? ' AND ' : ' OR ', $where_r );
			} 
			else 
			{
				$where = $table . ".$member " . ( ( array_key_exists( $member, $this->_conditional_vars ) ) ? $condition : " = " );
				if ( $condition_str == 'Like' ) 
				{
					$value = "$value%";
				}
				elseif ( $condition_str == 'likE' ) 
				{
					$value = "%$value";
				}
				elseif ( strtoupper( $condition_str ) == 'LIKE' ) 
				{
					$value = "%$value%";
				}
				$where .= dev_prep_input( $value );	
			}
		}
		//$where_r[] = $where;
		if ( dev_not_null( $where ) ) $where = "($where) ";
	}
	
	return $where;
}

function createSortCase($table = '', $member) {
	if (dev_is_null($table)) $table = $this->getReferenceTable();
	$sort = '';
	if (array_key_exists($member, $this->_sort_vars)) {
		if (strtoupper($this->getSortOrder($member)) == 'RAND()') $sort = " " . $this->getSortOrder($member);
		else $sort = $table . ".$member " . $this->getSortOrder($member);
	}
	
	return $sort;
}

function createDistinctCase($table = '', $member) {
	if (dev_is_null($table)) $table = $this->getReferenceTable();
	$distinct = '';
	//if (array_key_exists($member, $this->_distinct_vars)) {
	if (in_array($member, $this->_distinct_vars)) {
		//$distinct = ' DISTINCT ' . $table . ".$member " . $this->getDistinction($member);
		$distinct = ' ' . $table . ".$member ";
	}
	
	return $distinct;
}

function delete($table = '', $join = true) {
	if (dev_is_null($table)) $table = $this->getReferenceTable();
	$where_r = array('1');
	$sort_r = array();
	$distinct_r = array();
	$var_r = get_object_vars($this);
	$field_r = $this->getObjectFields($table);
	//$member_r = array_intersect_key($var_r, $field_r); //php5
	$member_r = $this->arrayKeyIntersect($var_r, $field_r);
	if (!is_array($this->_conditional_vars)) $this->_conditional_vars = array();
	if (!is_array($this->_sort_vars)) $this->_sort_vars = array();
	if (!is_array($this->_distinct_vars)) $this->_distinct_vars = array();
	foreach ($member_r as $a=>$b) {
	
		$where = $this->createWhereCase($table, $a, $b);
		$sort = $this->createSortCase($table, $a);
		$distinct = $this->createDistinctCase($table, $a);
		if (dev_not_null($where)) $where_r[] = $where;
		if (dev_not_null($sort)) $sort_r[] = $sort;
		if (dev_not_null($distinct)) $distinct_r[] = $distinct;
		
	}
	
	//if JoinObject, use all tables
	$left_join = '';
	if ((isset($this->_reference_tables) && is_array($this->_reference_tables) && count($this->_reference_tables) >= 1) && $join) {
		$using_r = array();
		$on_r = array();
		foreach ($this->_reference_tables as $a) {
			$join_r = $this->getObjectFields($a);
			if (is_array($join_r)) {
				//$using_r = array_merge($using_r, $this->arrayKeyIntersect($this->getObjectFields($table), $join_r));
				$field_r = $this->arrayKeyIntersect($this->getObjectFields($table), $join_r);
				foreach ($field_r as $b=>$c) {
					$on_r[] = $this->getReferenceTable() . ".$b  = $a.$b";
				}
				
				$member_r = $this->arrayKeyIntersect($var_r, $join_r);
	
				foreach ($member_r as $b=>$c) {
					$where = $this->createWhereCase($a, $b, $c);
					$sort = $this->createSortCase($a, $b);
					if (dev_not_null($where)) $where_r[] = $where;
					if (dev_not_null($sort)) $sort_r[] = $sort;
				}			
			}
		}
		
		//$using_r = array_flip($using_r);
		
		//$left_join = "NATURAL JOIN (" . implode(', ', $this->_reference_tables) . ")"; // USING (" . implode(', ', $using_r) . ")";
		//$left_join = "NATURAL JOIN " . implode(' NATURAL JOIN ', $this->_reference_tables) . ""; // USING (" . implode(', ', $using_r) . ")";
		$left_join = "INNER JOIN (" . implode(', ', $this->_reference_tables) . ") ON (" . implode(' AND ', $on_r) . ")";
		$join_tables = implode(', ', $this->_reference_tables);
	}	
	
	//$query = "DELETE $table" .  ((dev_not_null($join_tables)) ? ", $join_tables" : '') . " FROM `$table` $left_join WHERE " . implode(' AND ', $where_r); 
	$query = "DELETE " .  ((dev_not_null($join_tables)) ? ", $join_tables" : '') . " FROM `$table` $left_join WHERE " . implode(' AND ', $where_r); 
	
	$this->setQuery($query);
	
	if (mysql_query($query))
	{
		$this->setStatusMessage('Deletion Successful.');
		return true;
	}
	else 
	{
		$this->setStatusMessage(mysql_error());
		return false;
	}
}

function joinMembers() {
		$member_r = get_object_vars($this);
		$count = func_num_args();
		for ($i = 0; $i < $count; $i++) {
			if (is_object(func_get_arg($i))) {
				$join_r = get_object_vars((func_get_arg($i)));
				if (is_array($join_r)) {
					$member_r = $this->arrayKeyIntersect($member_r, $join_r);
				}
			}
		}
		return $member_r;
}

function setCondition($member, $condition = '<', $value = '') {
	$this->_conditional_vars[$member] = $condition;
	if ( dev_not_empty( $value ) ) {
		if (strpos($member, ',')) {
			$member_r = explode(',', $member);
			foreach ($member_r as $a) $this->setField($a, $value);
		} else {
			$this->setField($member, $value);
		}
	}
}

function getCondition($member) {
	foreach ($this->_conditional_vars as $a=>$b) {
		foreach (explode(',', $a) as $c) {
			if (trim($c) == $member) return $b;
		}
	}
}

function getConditionKey($member) {
	foreach ($this->_conditional_vars as $a=>$b) {
		foreach (explode(',', $a) as $c) {
			if (trim($c) == $member) return $a;
		}
	}
}

function clearCondition() {
	$this->_conditional_vars = NULL;
}

function setActiveFields($field_r = '') {
	if (is_array($field_r)) $this->_active_fields = $field_r;
	else return false;
	return true;
}

function getActiveFields() {
	return $this->_active_fields;
}

function clearActiveFields() {
	$this->_active_fields = null;
	$this->_active_fields = array();
}

function addActiveField($field_name) {
	if (!is_array($this->_active_fields)) $this->_active_fields = array();
	if (!is_string($field_name)) $this->_active_fields[] = $field_name;
	else return false;
	return true;
}

function toggleActiveFieldsOn($use_active_fields = '') {
	$this->_active_fields_on = ($use_active_fields == '') ? dev_flip_bool($this->_active_fields_on) : $use_active_fields;
}

function getActiveFieldsOn() {
	return $this->_active_fields_on;
}

function setSortOrder($member, $order = 'ASC') {
	$this->_sort_vars[$member] = $order;
}

function getSortOrder($member) {
	foreach ($this->_sort_vars as $a=>$b) {
		foreach (explode(',', $a) as $c) {
			if (trim($c) == $member) return $b;
		}
	}
}

function clearSortOrder() {
	$this->_sort_vars = NULL;
}

function getRelatedField($member) {
	return $this->_related_fields[$member];
}

function getRelatedFields() {
	return $this->_related_fields;
}

function setRelatedField($member, $field) {
	$this->_related_fields[$field] = $member;
}

function clearRelatedFields() {
	$this->_related_fields = NULL;
}

function setDistinction($member) {
	//$this->_distinct_vars[$member] = $order;
	$this->_distinct_vars[] = $member;
}

function getDistinction($member) {
	foreach ($this->_distinct_vars as $a=>$b) {
		//foreach (explode(',', $a) as $c) {
		foreach (explode(',', $b) as $c) {
			if (trim($c) == $member) return $b;
		}
	}
}

function clearDistinction() {
	$this->_distinct_vars = NULL;
}

function setRowLimit($start = 0, $end = '') {
	$this->_row_select_limit_start = $start;
	$this->_row_select_limit_end = $end;
}

function getRowLimitStart () {
	return $this->_row_select_limit_start;
}

function getRowLimitEnd () {
	return $this->_row_select_limit_end;
}

function clearRowLimit() {
	unset($this->_row_select_limit_start);
	unset($this->_row_select_limit_end);

}

function getPrimaryKey() {
	$output = false;
	foreach ($this->getFieldInfo() as $a) {
		if($a['Key'] == 'PRI') return $a['Field'];
		if($a['Key'] == 'MUL') return $a['Field'];
		if($a['Key'] == 'UNI') return $a['Field'];
		
	}
	return $output;
}

function clear($table = '') {
	//foreach ($this->getMembers() as $a=>$b) $this->$a = NULL;
	$table = ( dev_is_null( $table ) ) ? $this->getReferenceTable() : $table;

	$members_r = $this->getFields($table);

	$members_r = (is_array($members_r)) ? $members_r : array();

	$this->setQuery('');
	      		
	foreach ($members_r as $a=>$b) 
	{
		if($this->memberExists($a)) 
		{
			$this->setField($a, '');
		}
	}
}

function reset()
{
	$this->clearActiveFields();
	$this->clearCondition();
	$this->clearDistinction();
	$this->clearQuery();
	$this->clearRowLimit();
	$this->clearSortOrder();
	$this->clearStatusMessage();
}

function getValues($table = '') {
	if (dev_is_null($table)) $table = $this->getReferenceTable();
	$field_r = array();
	
	$query = "SHOW COLUMNS FROM `$table`";
	$result = mysql_query($query);
	
	while ($column = mysql_fetch_assoc($result)) {
		//$field_r[$column['Field']] = $column['Default'];
		if($this->memberExists($column['Field'])) $field_r[$column['Field']] = $this->$column['Field'];
	}
	
	if (is_array($this->_reference_tables)) {
		foreach ($this->_reference_tables as $a) {
	
			$query = "SHOW COLUMNS FROM `$a`";
			$result = mysql_query($query);
	
			while ($column = mysql_fetch_assoc($result)) {
				//$field_r[$column['Field']] = $column['Default'];
				if($this->memberExists($column['Field'])) $field_r[$column['Field']] = $this->getField( $column['Field'] );
			}
		}
	}
	
	return $field_r; 
}

function getFields($table = '') {
	if (dev_is_null($table)) $table = $this->getReferenceTable();
	$field_r = array();
	
	$query = "SHOW COLUMNS FROM `$table`";
	$result = mysql_query($query);
	
	while ($column = mysql_fetch_assoc($result)) {
		$field_r[$column['Field']] = $column['Default'];
	}
	
	if (is_array($this->_reference_tables)) {
		foreach ($this->_reference_tables as $a) {
	
			$query = "SHOW COLUMNS FROM `$a`";
			$result = mysql_query($query);
	
			while ($column = mysql_fetch_assoc($result)) {
				$field_r[$column['Field']] = $column['Default'];
			}
		}
	}
	
	return $field_r; 
}

function loadFieldInfo($table = '') {
	if (dev_is_null($table)) $table = $this->getReferenceTable();
	$field_r = array();
	$match = array();
	
	$query = "SHOW COLUMNS FROM `$table`";
	$result = mysql_query($query);
	
	while ($column = mysql_fetch_assoc($result)) {
		if (preg_match('/([\d]+)/', $column['Type'], $match) ) $column['Length'] = $match[0];
		$column['Table'] = $table;
		$field_r[$column['Field']] = $column;
	}
	
	if (is_array($this->_reference_tables)) {
		foreach ($this->_reference_tables as $a) {
			$query = "SHOW COLUMNS FROM `$a`";
			$result = mysql_query($query);

			while ($column = mysql_fetch_assoc($result)) {
				if (preg_match('/\(([\d*])\)/', $column['Type'], $match) ) $column['Length'] = $match[0];
				$column['Table'] = $a;
				$field_r[$column['Field']] = $column;
			}
		}
	}
	
	$this->_field_info = $field_r; 
	return true;
}

function getFieldInfo() {
	if (!dev_is_assoc($this->_field_info)) {
		$this->loadFieldInfo();
	}
	return $this->_field_info;
}

function clearFieldInfo() {
	$this->_field_info = null;
	return true;
}

function getMembers() {
	return get_object_vars($this);
}

function arrayKeyIntersect(&$arr1, &$arr2) {
	$array = array();
	if (dev_not_null($arr2)) {
		foreach ($arr1 as $a=>$b) if (array_key_exists ( $a, $arr2)) $array[$a] = $b;
	}
	return $array;
}

function arrayKeyIntersectRef(&$arr1, &$arr2) {
	$array = array();

	foreach ($arr1 as $a=>$b) if (array_key_exists ( $a, $arr2)) $array[$a] = $b;
	return $array;
}

function memberExists(&$var, $use_active_fields = '') {
	$members = $this->getFieldInfo();
	//If we are filtering fields

	if ($var != '' && array_key_exists( $var, $members ) ) {
		$valid_array = (is_array($this->_active_fields) && count($this->_active_fields) >= 1);
		$use_active_fields = (($use_active_fields === true && $valid_array) ? true : (($use_active_fields === false || !$valid_array) ? false : ($this->getActiveFieldsOn() !== false)));
		
		if (in_array($var, $this->_active_fields) && $use_active_fields) 
		{
			return $this->memberExists($var, false);
		}	
		//otherwise, proceed as normal
		else
		{
			return true;
		}
	} else {
		return false;
	}
}

function setLastRowAffected($row) {
	$this->_last_row_affected = $row;
}

function getLastRowAffected() {
	return $this->_last_row_affected;
}

function connect($user = '', $pass = '', $database = '', $server = 'localhost') {
	if (dev_not_null($this->_db_link)) $this->disconnect();
	
	$this->_db_link = dev_dbconn($user, $pass, $database, $server);
	
	$this->setStatusMessage(mysql_error());
	
	return $this->_db_link;
}

function disconnect($link = '') {
	
	if ($link == '') {
		$link = $this->_db_link;
		$this->_db_link = null;
	}
	dev_dbclose($link);
}

} //End Class DevModel
?>