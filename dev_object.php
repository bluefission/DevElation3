<?php
//DevObject 2.0
///
//TOP LEVEL OBJECT CLASS
///////
class DevObject extends DevModel {

function DevObject() {
	$count = func_num_args();
	$tables = array();
	$table = null;
	for ($i = 0; $i < $count; $i++) {
		$table = func_get_arg($i);
		if ( is_array( $table ) )
		{
			$tables = array_merge( $tables, $table);
		}
		else
		{
			$tables[] = $table;
		}
	}
	parent::DevModel($tables);
}

//php4, php5
function loadMembers($table = '', $join = true) {
	$this->read($table, $join);
}

function loadFreshMembers($table = '', $join = true) {
	$this->readNew($table, $join);
}

function createObject($table = '') {
	if (dev_is_null($table)) $table = $this->getReferenceTable();
	$result = $this->getResult($table);
	$new_object = mysql_fetch_object($result);
	return $new_object;
}

function createDevJoinObject($table = '') {
	return $this->createModel();
}

function createMemberArray($table = '') {
	return $this->getRecordSet( $table );
}

function writeObject($update = false, $table = '', $ignore_null = 0) {
	return $this->write($update, $table, $ignore_null);
}

function writeJoinObject($update = false, $table = '', $ignore_null = 0) {
	return $this->save($update, $table, $ignore_null);
}	

function deleteObject($table = '', $join = true) {
	return $this->delete($table, $join);
}

function clearMembers($table = '') {
	$this->clear( $table );
}

function getFullFieldArray($table = '') {
	return $this->getValues( $table ); 
}

function formField($name = '', $value = '', $label = '', $type = '', $required = false, $readonly = false, $id = '', $properties = '') {
	$field_info = $this->getFieldInfo();	
	
	if (dev_not_null($name)) {
		$value = ($value == '') ? $this->getField($name) : $value;
		$label = ($label == '') ? $name : $label;
		$id = ($id == '') ? $name : $id;

		if (isset($field_info[$name])) {
			$this_field = $field_info[$name];
			$required = ($required == '') ? ((!$this_field['Null'] || $this_field['Null'] == 'NO') ? true : false) : $required;
			if ($type == '') {
				$field_type = strtolower($this_field['Type']);
				$field_type = ($field_type == 'longtext') ? 'richtext' : ((dev_is_substr($field_type, 'text')) ? 'textarea' : $field_type);
				$field_type = (dev_is_substr($field_type, 'lob')) ? 'textarea' : $field_type;
				$field_type = (dev_is_substr($field_type, 'int')) ? 'int' : $field_type;
				$field_type = (dev_is_substr($field_type, 'char')) ? 'char' : $field_type;
				$field_type = (dev_is_substr($field_type, 'date')) ? 'date' : $field_type;
				
				if ($this_field['Key'] == 'PRI') $field_type = 'hidden';
				elseif ($field_type == 'int' && isset($this_field['Length']) && $this_field['Length'] <= 1 ) $field_type = 'check';
				elseif (dev_is_assoc($value)) $field_type = 'select';
				elseif ($field_type == 'set' && dev_is_index($value)) $field_type = 'radio';
				elseif ($field_type != 'set' && dev_is_index($value)) $field_type = 'radio';
				
				switch ($field_type) {
				case 'int':
					$value = (int)$value;
				case 'char':
				default:
					$type = 'text';
					break;
				case 'text':
				case 'textarea':
				case 'lob':
					$type = 'textarea';
					break;
				case 'textarea':
					$type = 'textarea';
					break;
				case 'date':
					$type = 'date';
					break;
				case 'set':
				case 'check':
					if (dev_is_null($value)) $value = 1;
					else $value = 1;
					$type = 'checkbox';
					break;
				case 'radio':
					$type = 'radio';
					break;
				case 'select':
					$type = 'select';
					break;
				case 'hidden':
					$type = 'hidden';
					break;
				}
			}
		}
	}

	return "<tr><td>" . (($type != 'hidden') ? (($required) ? '*' : '') . $label : '') . "</td><td>" . dev_draw_form_field($type, $name, '', $value, $required, $id, $readonly, $properties) . "</td></tr>";
}

function drawForm($field_type_r = '', $active_field_r = '', $property_r = '') {
	$output = '';
	$required = '';
	$id = '';
	$active_field_r = dev_value_to_array($active_field_r);
	$field_type_r = dev_value_to_array($field_type_r);
	$property_r = dev_value_to_array($property_r);
	$readonly = false;
	if (count($active_field_r) <= 0) $active_field_r = $this->getActiveFields();
	$output .= '<table>';
	foreach ($this->getFullFieldArray() as $a=>$b) {
		if ($this->memberExists($a))	 {
			$type = (array_key_exists($a, $field_type_r)) ? $field_type_r[$a] : '';
			$properties = (array_key_exists($a, $property_r)) ? $property_r[$a] : '';
			$label_r = (dev_is_assoc($active_field_r) && in_array($a, $active_field_r)) ? array_keys($active_field_r, $a) : '';
			$label = (is_array($label_r)) ? $label_r[0] : '';
			$output .= $this->formField($a, $b, $label, $type, $required, $readonly, $id, $properties);
		}
	}
	$output .= '</table>';
	return $output;
}

function drawList( $header = '', $href = '', $query_r = '', $type = '', $highlight = '', $link_style = '', $show_image = '', $img_dir = '', $file_dir = '', $icon = '', $trunc = '') {
	$content_r = $this->getRecordSet();
	return dev_display_box($content_r, $type, $href, $query_r, $highlight, $header, $link_style, $show_image, $img_dir, $file_dir, $icon, $trunc );
}

} //End Class DevObject

class DevJoinObject extends DevObject {

function DevJoinObject() {
	parent::DevObject( func_get_args() );
}

} //End Class DevJoinObject
?>