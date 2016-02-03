<?php
//dev_control.php

class DevControl {

var $title;
var $tables;
var $dev_objects;
var $options;
var $options_on;
var $href;
var $logo;
var $icons;
var $field_types;
var $field_headers;
var $field_data;
var $field_defaults;
var $fs_path;
var $ws_path;
var $file_dir;
var $image_dir;

//Control View Members
var $status, $tasks, $output;

function DevControl($title = '', $tables = '', $active_fields = '', $options = '', $href = '', $logo = '', $icons = '') {
	$this->dev_objects = array();
	$this->options_on = true;
	$this->setTables($tables);
	$this->setControlObject(0, $this->getTables(), $active_fields);
	$this->setTitle($title);
	$this->setOptions($options);
	$this->setHref($href);
	$this->setLogo($logo);
	$this->setIcons($icons);
}

function setControlObject($id = '', $tables = '', $active_fields = '') {
	$dev_object = new DevObject($tables);
	$dev_object->setActiveFields($active_fields);
	$this->addControlObject($id, $dev_object);
}

function addControlObject($id, $dev_object) {
	$this->dev_objects[$id] = $dev_object; 
}

function getControlObject($id = '') {
	$dev_object = (dev_valid_number($id, true)) ? $this->dev_objects[$id] : $this->dev_objects;
	return $dev_object;
}

function setLogo($image = '') {
	$this->logo = $image;
}

function getLogo() {
	return $this->logo;
}

function setIcons($icons_r = '') {
	$icons_r = dev_value_to_array($icons_r);
	$this->icons = $icons_r;
}

function getIcons() {
	return $this->icons;
}

function getIcon($action) {
	$icons = $this->getIcons();
	if (is_array($icons)) {
		$icon_types = array_keys($icons);
		return ((in_array($action, $icon_types)) ? dev_print_image($icons[$action], '', $action, '32', '32', false, '', false) : '');
	} else {
		return null;
	}
} 

function setTables($tables = '') {
	$this->tables = $tables;
}

function getTables() {
	return $this->tables;
}

function setWSPath($path = '') {
	$this->ws_path = $path;
}

function getWSPath() {
	return $this->ws_path;
}

function setFSPath($path = '') {
	$this->fs_path = $path;
}

function getFSPath() {
	return $this->fs_path;
}

function setImageDir($path = '') {
	$this->image_dir = $path;
}

function getImageDir() {
	return $this->image_dir;
}

function setFileDir($path = '') {
	$this->file_dir = $path;
}

function getFileDir() {
	return $this->file_dir;
}

function setHref($href = '') {
	$this->href = $href;
}

function getHref() {
	return $this->href;
}

function setTitle($title = '') {
	$this->title = $title;
}

function getTitle() {
	return $this->title;
}

function setStatus($status = '') {
	$this->status = $status;
}

function getStatus() {
	return $this->status;
}

function setTasks($tasks = '') {
	$this->tasks = $tasks;
}

function getTasks() {
	return $this->tasks;
}

function setOutput($output = '') {
	$this->output = $output;
}

function getOutput() {
	return $this->output;
}

function addOption($option = '') {
	$option_r = dev_value_to_array($this->getOptions());
	if (!in_array($option, $option_r)) {
		$option_r[] = $option;
	}
	$this->options = $option_r;
}

function removeOption($option = '') {
	$option_r = dev_value_to_array($this->getOptions());
	if (in_array($option, $option_r)) {
		unset($option_r[array_search($option)]);
	}
}

function setOptions($options_r = '') {
	$options_r = dev_value_to_array($options_r);
	$this->options = $options_r;
}

function getOptions() {
	return $this->options;
}

function addFieldType($field = '', $type = '') {
	$field_r = dev_value_to_array($this->getFieldTypes());
	$field_r[$field] = $type;
	$this->field_types = $field_r;
}

function removeFieldType($field) {
	$option_r = dev_value_to_array($this->getFieldType());
	unset($option_r[$field]);
}

function setFieldTypes($field_r = '') {
	$field_r = dev_value_to_array($field_r);
	$this->field_types = $field_r;
}

function getFieldTypes() {
	return $this->field_types;
}

function addFieldHeader($field = '', $type = '') {
	$field_r = dev_value_to_array($this->getFieldHeaders());
	$field_r[$field] = $type;
	$this->field_headers = $field_r;
}

function removeFieldHeader($field) {
	$option_r = dev_value_to_array($this->getFieldHeaders());
	unset($option_r[$field]);
}

function setFieldHeaders($field_r = '') {
	$field_r = dev_value_to_array($field_r);
	$this->field_headers = $field_r;
}

function getFieldHeaders() {
	return $this->field_headers;
}

function setFieldDefaults($field_r = '') {
	$field_r = dev_value_to_array($field_r);
	$this->field_defaults = $field_r;
}

function getFieldDefaults() {
	return $this->field_defaults;
}

function getObjectMember($id, $var) {
	$dev_object = getControlObject($id = '');
	$output = $dev_object->$var;
	return $output;
}

function setOptionsOn($val = '') {
	if ($val == '' && $val !== true && $val !== false) $this->options_on = dev_flip_bool($this->options_on);
	else $this->options_on = $val;
}

function getOptionsOn() {
	return $this->options_on;
}

function drawManageForm(&$object, $form_title = '', $field_type_array = '', $submit_text = '', $reset_text = '', $load = false) {
	if ($load) {
		if (dev_is_assoc($this->field_data)) { foreach ($this->field_data as $a=>$b) $object->setField($a, $b); }
	}
	
	$manage_form = $form_title . ':<br /><br />
	<span style="font-size: 11px; color: #ff0000; font-family: arial, helvitica, sans-serif;">
	All fields marked by an asterix (*) are required.
	</span><br /><br />' .
	dev_draw_form_field('hidden', 'MAX_FILE_SIZE', '', '1024000000') .
	$object->drawForm($field_type_array) .
	dev_draw_form_field('submit', 'submit', $submit_text, $submit_text) .
	dev_draw_form_field('reset', 'reset', $reset_text, $reset_text);
	
	return $manage_form;
}

function drawDetailForm(&$object, $form_title = '', $field_type_array = '', $submit_text = '', $reset_text = '') {
	$manage_form = $form_title . ':<br /><br />
	<span style="font-size: 11px; color: #ff0000; font-family: arial, helvitica, sans-serif;">
	All fields marked by an asterix (*) are required.
	</span><br /><br />' .
	$object->drawForm($field_type_array);
	
	return $manage_form;
}

function drawControl() {
	//Author: Devon .M. Scott
	//Database and file management vars.
	//$action = (isset($_POST['action']) && $_POST['action'] != '') ? $_POST['action'] : $_GET['action'];
	$view = ((isset($_POST['view']) && $_POST['view'] != '') ? $_POST['view'] : ((isset($_GET['view']) && $_GET['view'] != '') ? $_GET['view'] : ((isset($_COOKIE['view']) && $_COOKIE['view'] != '') ? $_COOKIE['view'] : 'select')));
	
	$file = (isset($_POST['file']) && $_POST['file'] != '') ? $_POST['file'] : $_GET['file'];
	$dir = (isset($_POST['dir']) && $_POST['dir'] != '') ? $_POST['dir'] : $_GET['dir'];
	$action = (isset($_POST['action']) && $_POST['action'] != '') ? $_POST['action'] : $_GET['action'];
	$mode = (isset($_POST['mode']) && $_POST['mode'] != '') ? $_POST['mode'] : $_GET['mode'];

	$options_on = $this->getOptionsOn();

	$href = dev_href($this->getHref($href));
	//$href = 'index.php';
	
	$output = '';
	$status = '';
	
	if (count($this->getOptions()) <= 0) {
		$option_list = new DevObject(DEV_OPTIONS);
		$option_list->clearMembers();
		$option_list->setCondition('option_enable', '=', '1');
		$option_list->setCondition('option_group', '=', $this->getTables());
		
		$options_set = array();
		foreach($option_list->createMemberArray() as $a) $options_set[] = $a['option_name'];
	} else {
		$options_set = $this->getOptions();
	}
	if (!in_array($action, $options_set) && $options_on === true) {
		if ($action != '') $output .= '<b>You do not have permission to take this action.</b><br /><br />';
		$action = 'list';
	}
	
	$option = array();

	$image_dir = $this->getWSPath() . $this->getImageDir();
	$image_upload_dir = $this->getFSPath() . $this->getImageDir();
	$file_dir = $this->getWSPath() . $this->getFileDir();
	$file_upload_dir = $this->getFSPath() . $this->getFileDir();

	$query = array();
	//$filedir = 'filedir/';
	if ($dir != $file_upload_dir && $dir != '') {
		$query['d'] = $dir;
		$file_upload_dir = $dir;
	}
	
	$object = $this->getControlObject(0);
	
	$options = array();	
	
	if ($this->getLogo() != '') $output .= dev_print_image($this->getLogo());
	if ($this->getTitle() != '')  $output .= "<h1>" . $this->getTitle() . "</h1>";
	
	//$object->toggleActiveFieldsOn(true);
	$object->clearMembers();
	
	$object->setActiveFields($this->getFieldHeaders());
	
	$entry_id_var = $object->getPrimaryKey();
	$$entry_id_var = (isset($_POST[$entry_id_var]) && $_POST[$entry_id_var] != '') ? $_POST[$entry_id_var] : $_GET[$entry_id_var];
	
	//$files = new DevObject('files');
	//$files->clearMembers();
	
	
	if ($action == 'edit' || $action == 'delete' || $action == 'manageimages' || $action == 'changeimages' || $action == 'upload' || $action == 'view' || $action == 'default') {
		//$object->loadFreshMembers();
		$object->clearMembers();
		$object->loadGetVars();
		$object->loadPostVars();
		if ($action == 'default' || $action == 'view') $object->loadArrayVars($this->getFieldDefaults());

		$object->loadMembers();
		
		foreach ($object->getMemberArray() as $a=>$b)  { global $$a; $$a = $b; }	
		//echo $object->getQuery();
	} elseif ($action == 'deletefile') {
		$files->loadFreshMembers();
		foreach ($files->getMemberArray() as $a=>$b) { $$a = $b; }
	}
	
	$delete_form = '
	Really delete entry \'' . $$entry_id_var . '\' From the database? (Deletion is NOT undoable).<br />' .
	dev_draw_form_field('hidden', $entry_id_var, '', $$entry_id_var) .
	'<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $$entry_id_var . '">No, return to this entry</a><br /><br />' . 
	'<input type="submit" value="Yes, Delete Forever" /><br />';
	
	//$options[] = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $$entry_id_var . '">No, return to this entry</a>';
	
	/*
	$delete_file_form = '
	Really delete file \'' . $files_document . '\'? (Deletion is NOT undoable).<br />' .
	dev_draw_form_field('hidden', $entry_id_var, '', $$entry_id_var) .
	dev_draw_form_field('hidden', 'files_id', '', $files_id) .
	'<a href="' . $href . '?action=managefiles&' . $entry_id_var . '=' . $$entry_id_var . '">No, return to this entry\'s files</a><br /><br />' . 
	'<input type="submit" value="Yes, Delete Forever" /><br />';
	*/
	$delete_file_form .= "Really delete file '$file'? (Deletion is <b>NOT</b> undoable).<br />\n" .
	'<a href="'.$href.'?dir='.$dir.'&file='.$file.'&action=removefile">Yes, delete forever.</a><br /><br />' .
	'<a href="'.$href.'?dir='.$dir.'&action=listfiles">Back to List</a><br /><br />';

	//$options[] = '<a href="' . $href . '?action=managefiles&' . $entry_id_var . '=' . $$entry_id_var . '">No, return to this entry\'s files</a>';

	$static_fields_r = array();
	
	foreach ($object->getFullFieldArray() as $a=>$b) $static_fields_r[$a] = 'static';
	
	//$view_form = $this->drawManageForm($object, 'View Details', $static_fields_r, 'Edit', 'Reset');
	
	$view_form = dev_detail_box($object->getFullFieldArray());
	
	$edit_form = $this->drawManageForm($object, 'Manage Entry', $this->getFieldTypes(), 'Submit', 'Clear', (($action == 'edit' || $action == 'new' ||  $action == 'post') ? true : false));

	$mail_form = $this->drawManageForm($object, 'Send Message', $this->getFieldTypes(), 'Send', 'Clear');
	
	//$options[] = '<a href="' . $href . '?action=new">Create New</a>';
	//$options[] = '<a href="' . $href . '?action=list">Back to List</a>';
	
	/*
	$upload_form = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $_GET[$entry_id_var] . '">Return to this entry</a><br /><br />' . 
	'<a href="' . $href . '?action=managefiles&' . $entry_id_var . '=' . $_GET[$entry_id_var] . '">Manage existing uploaded files</a><br /><br />' . 
		'Upload files to associate to your entry<br />' . 
		dev_draw_form_field('hidden', $entry_id_var, '', $_GET[$entry_id_var]) .
		dev_draw_form_field('hidden', 'MAX_FILE_SIZE', '', '1024000000') .
		dev_draw_form_field('text', 'project_program_title', 'Programs Title', $project_programs_title) .
		dev_draw_form_field('file', 'program', 'Programs', $programs) .
		dev_draw_form_field('text', 'project_minutes_title', 'Minutes Title', $project_minutes_title) .
		dev_draw_form_field('file', 'minutes', 'Minutes', $minutes) .
		dev_draw_form_field('text', 'project_notices_title', 'Notices Title', $project_notices_title) .
		dev_draw_form_field('file', 'notices', 'Notices', $notices) .
		dev_draw_form_field('text', 'project_images_title', 'Images Title', $project_images_title) .
		dev_draw_form_field('file', 'images', 'Images', $images) .
		dev_draw_form_field('text', 'project_misc_title', 'Miscellaneous Title', $project_misc_title) .
		dev_draw_form_field('textarea', 'project_misc', 'Miscellaneous', $project_misc) .
		'<input type="submit" value="Upload" />';
	*/
	
	$upload_form = dev_draw_form_field('hidden', 'dir', '', $dir) .
	//dev_draw_form_field('hidden', 'action', '', 'send') .
	dev_draw_form_field('file', 'file', 'File') .
	dev_draw_form_field('submit', 'uploadfiles', 'Upload', 'Upload');
	
	$edit_file_form = '<a href="'.$href.'?dir='.$dir.'&file='.$file.'&action=edit&mode=richtext">Rich Text Mode</a><br /><br />';
	//dev_draw_form_field('hidden', 'action', '', 'savefile') .
	dev_edit_file($dir . $file);
	
	$view_file_form = dev_draw_form_field('hidden', 'dir', '', $dir) .
	//dev_draw_form_field('hidden', 'action', '', 'edit') .
	dev_draw_form_field('static', 'file', 'Filename', $file) .
	dev_draw_form_field('static', 'data', 'Data', dev_view_file($dir . $file)) .
	dev_draw_form_field('submit', 'editfile', 'Edit', 'Edit');
	
	//$options[] = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $_GET[$entry_id_var] . '">Return to this entry</a>';
	//$options[] = '<a href="' . $href . '?action=managefiles&' . $entry_id_var . '=' . $_GET[$entry_id_var] . '">Manage existing uploaded files</a>';

	$output .= dev_draw_form();
	
	switch ($action) {
	case 'remove':
		if ($_POST[$entry_id_var] != '' && $_POST[$entry_id_var] > 0) {
			$object->clearMembers();
			$object->loadPostVars();
			$status .= ($object->deleteObject()) ? "Successfully Deleted Entry " : "Deletion Failed";
			$object->clearMembers();
		}
	default:
	case 'list':
	//$object->setSortOrder('entry_name', 'ASC');
	$action = 'edit';
	$field_names = array_values($this->getFieldHeaders());
	if (is_array($field_names)) $object->setSortOrder($field_names[1], 'ASC');
	$object->loadPostVars();
	$object->setDistinction($entry_id_var);
	$options['new'] = '<a href="' . $href . '?action=new">'.$this->getIcon('new').'Create New</a>';
	$options['search'] = '<a href="' . $href . '?action=search">'.$this->getIcon('search').'Search</a>';

	$query_r = array('action'=>'view');
	//$output .= dev_content_box($object->createMemberArray(), '', $href, $query_r, '#c0c0c0', '', '1', true, '../forum/');
	//$output .= dev_list_results($object->createMemberArray(), 'begin', 'end', $href, true, 1, $query_r, '', $image_dir, $file_dir, $this->getFieldHeaders());
	$output .= 'View: <a href="' . $href . '?view=icon">icons</a> | <a href="' . $href . '?view=search">list</a> | <a href="' . $href . '?view=select">selection</a><br />';
	$output .= dev_display_box($object->createMemberArray(), $view, $href, $query_r, '#c0c0c0', $this->getFieldHeaders(), '1', true, $image_dir, $file_dir, '', 15);
	break;
	case 'advancedsearch':
	//if ($action == 'advancedsearch') {
		$options['search'] = '<a href="' . $href . '?action=search">'.$this->getIcon('search').'Basic Search</a>';
		$options['new'] = '<a href="' . $href . '?action=new">'.$this->getIcon('new').'Create New</a>';
		$options['list'] = '<a href="' . $href . '?action=list">'.$this->getIcon('list').'Back to List</a>';
		//$output .= dev_draw_form() .
		$action = 'list';
		$output .= 
		dev_draw_form_field('hidden', 'view', '', 'search') .
		$object->drawForm($field_types) .
		dev_draw_form_field('submit', 'submit', 'Finding...', 'Find');
		//dev_close_form();
	//}
	break;
	case 'search':
	//if ($action == 'search') {
		$options['advancedsearch'] = '<a href="' . $href . '?action=advancedsearch">'.$this->getIcon('advancedsearch').'Advanced Search</a>';
		$options['new'] = '<a href="' . $href . '?action=new">'.$this->getIcon('new').'Create New</a>';
		$options['list'] = '<a href="' . $href . '?action=list">'.$this->getIcon('list').'Back to List</a>';
		$field_names = array_values($this->getFieldHeaders());
		//$object->setDistinction($entry_id_var);
		//$object->setSortOrder('entry_location', 'ASC');
		$action = 'list';
		$output .=
		'<table>' . 
		dev_draw_form_field('hidden', 'view', '', 'search') .
		$object->formField($field_names[1]) .
		'</table>' .
		dev_draw_form_field('submit', 'submit', 'Finding...', 'Find');		//dev_close_form();
	//}
	break;
	//OBJECT ACTION CASES
		case 'save':
		$action = 'save';
		$object->loadPostVars();
		$success = dev_save_file($file, $view_form, $mode);
		
		$options['back'] = ((strpos($success, 'successful') === false) ? ' <a class="dev_option" href="javascript:history.back(1)">'.$this->getIcon('back').'Go back</a>' : '');
	break;
	case 'cookie':
		$action = 'cookie';
		$object->loadPostVars();
		foreach ($object->getMemberArray() as $a=>$b) $success .= dev_set_cookie($a, $b);
		
		$options['back'] = ((!$success) ? ' <a class="dev_option" href="javascript:history.back(1)">'.$this->getIcon('back').'Go back</a>' : '');
	break;
	case 'session':
		$action = 'save';
		$object->loadPostVars();
		foreach ($object->getMemberArray() as $a=>$b) $success .= dev_set_session($a, $b);
		
		$options['back'] = ((!$success) ? ' <a class="dev_option" href="javascript:history.back(1)">'.$this->getIcon('back').'Go back</a>' : '');
	break;
	case 'sendmail':
		$action = 'semdmail';
		$sucess = dev_send_email($rcpt, $from, $subject, $message, $cc, $bcc, $html, $headers_r, $additional);
		$options['back'] = ((strpos($success, 'successful') === false) ? ' <a class="dev_option" href="javascript:history.back(1)">'.$this->getIcon('back').'Go back</a>' : '');
	case 'mail':
		$action = 'sendmail';
		$output .= $mail_form;
	break;
	case 'post':
		$action = 'post';
		$object->loadPostVars();
		foreach ($_FILES as $a=>$b) {
			if ($b['size'] > 0) {
				$b['name'] = str_replace(' ' , '_', $b['name']);
				$object->$a = $b['name'];
				$status .= dev_upload_file($b, 1, $image_upload_dir);
			}
		}
		$success .= $object->writeObject();
			
		$options['back'] = ((strpos($object->getStatusMessage(), 'Unsuccessful') !== false) ? ' <a class="dev_option" href="javascript:history.back(1)">'.$this->getIcon('back').'Go back</a>' : '');
	case 'new':
		$options['new'] = '<a href="' . $href . '?action=new">'.$this->getIcon('new').'Create New</a>';
		$options['list'] = '<a href="' . $href . '?action=list">'.$this->getIcon('list').'Back to List</a>';
		$action = 'post';
		$output .= "<h5>Create New Entry</h5><br />";
		$output .= $edit_form;
	break;
	case 'uploadfiles':
		$options['listfiles'] = '<a href="' . $href . '?action=listfiles&dir=' . $dir . '">'.$this->getIcon('listfiles').'Back to List</a>';
		$options['choosefile'] = '<a href="' . $href . '?action=choosefile&dir=' . $dir . '">'.$this->getIcon('choosefile').'Upload Another File</a>';
		$output .= dev_upload_file($_FILES['file'], 0, getcwd() . '/' . $dir);
		break;
	case 'removefile':
		$files->loadPostVars();
		//$output .= ($files->deleteObject()) ? "Successfully Deleted Entry<br />" : "Deletion Failed<br />";
		$output .= (dev_delete_file($file, true)) ? "Successfully Deleted Entry<br />" : "Deletion Failed<br />";
	case 'listfiles':
	//$output .= dev_list_dir('file.php', 'document', $file_upload_dir, $query);
		$options['newfile'] = '<a href="' . $href . '?action=newfile&dir=' . $dir . '">'.$this->getIcon('newfile').'Create New File</a>';
		$options['choosefile'] = '<a href="' . $href . '?action=choosefile&dir=' . $dir . '">'.$this->getIcon('choosefile').'Upload New File</a>';
		$output .= dev_list_dir('file.php', '', $file_upload_dir, $query);
	case 'choosefile';
		$options['listfiles'] = '<a href="' . $href . '?action=listfiles&dir=' . $dir . '">'.$this->getIcon('listfiles').'Back to List</a>';
		$output .= $upload_form;
	break;
	case 'savefile':
		$options['listfiles'] = '<a href="' . $href . '?action=listfiles&dir=' . $dir . '">'.$this->getIcon('listfiles').'Back to List</a>';
		$output .= dev_save_file($file, $data);
	case 'openfile':
		$options['listfiles'] = '<a href="' . $href . '?action=listfiles&dir=' . $dir . '">'.$this->getIcon('listfiles').'Back to List</a>';
		$options['editfile'] = '<a href="' . $href . '?action=editfile&dir=' . $dir . '&file=' . $file . '">'.$this->getIcon('editfile').'Back to List</a>';
		$output .= $view_file_form;
	break;
	case 'newfile':
	case 'editfile':
		$output .= $edit_file_form;
	break;
	case 'upload': //Case following special "files" case
		//$object_files->loadPostVars();
		//$files = new DevObject('files');
		foreach ($_FILES as $a=>$b) {
			if ($b['size'] > 0) {
				$files->clearMembers();
				$files->loadPostVars();
				$status .= dev_upload_file($b, 1, $image_upload_dir);
				$object->writeObject();
			}
		}
	case 'default':
	case 'view':
		$action = 'edit';
		//$output .= "<h5>View Existing Entry</h5><br />";
		$options['list'] = '<a href="' . $href . '?action=list">'.$this->getIcon('list').'Back to List</a>';
		$options['edit'] = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('edit').'Edit</a>';
		$options['delete'] = '<a href="' . $href . '?action=delete&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('delete').'Delete this entry</a>';
		$options['files'] = '<a href="' . $href . '?action=files&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('files').'Upload files for this entry</a>';
		$options['manageimages'] = '<a href="' . $href . '?action=manageimages&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('manageimages').'Manage Images</a>';
		$output .= $view_form;
	break;
	case 'edit':
		$action = 'post';
		$output .= "<h5>Edit Existing Entry</h5><br />";
		$options['list'] = '<a href="' . $href . '?action=list">'.$this->getIcon('list').'Back to List</a>';
		$options['delete'] = '<a href="' . $href . '?action=delete&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('delete').'Delete this entry</a>';
		$options['files'] = '<a href="' . $href . '?action=files&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('files').'Upload files for this entry</a>';
		$options['manageimages'] = '<a href="' . $href . '?action=manageimages&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('manageimages').'Manage Images</a>';
		//$output .= $object->formField($entry_id_var, '', '', 'hidden');
		$output .= $edit_form;
	break;
	case 'delete':
		$action = 'remove';
		$options['edit'] = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('edit').'No, return to this entry</a>';
		$output .= "<h5>Delete Existing Entry</h5><br />";
		$output .= $delete_form;
	break;
	case 'files': //Special case for minutes and programs
		$options['edit'] = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $_GET[$entry_id_var] . '">'.$this->getIcon('edit').'Return to this entry</a>';
		$options['managefiles'] = '<a href="' . $href . '?action=managefiles&' . $entry_id_var . '=' . $_GET[$entry_id_var] . '">'.$this->getIcon('managefiles').'Manage existing uploaded files</a>';
		$files = new DevObject($tables);
		$files->loadFreshMembers();
		$action = 'upload';
		$output .= $upload_form;
	break;	
	case 'managefiles':
	//$object->setSortOrder('entry_name', 'ASC');
	$files->loadGetVars();
	$files->setSortOrder('files_document', 'ASC');
	$files->setCondition('files_id', '>=', '1');
	$options['edit'] = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . (($_GET[$entry_id_var] == '') ? $_POST[$entry_id_var] : $_GET[$entry_id_var]). '">'.$this->getIcon('edit').'Return to this entry</a><br />'; 
	$output .= '<table width="100%" border="1">';
		$output .= '<tr>
			<td>File Name</td>
			<td>File Type</td>
			<td>Delete</td>
		</tr>' . "\n";
	foreach ($files->createMemberArray() as $a) {
		$output .= '<tr>
			<td><a href="' . DOMAIN . SITE_ROOT . 'files/' . $a['files_document'] . '" target="_blank">' . $a['files_document'] . '</a></td>
			<td>' . $a['files_type'] . '</td>
			<td><a href="' . $href . '?action=deletefile&files_id=' . $a['files_id'] . '">Remove</a></td>
		</tr>' . "\n";
	}
	$output .= '</table>';
	$output .= dev_list_dir();
	break;
	case 'deletefile':
		$options['managefiles'] = '<a href="' . $href . '?action=managefiles&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('managefiles').'No, return to this entry\'s files</a>';
		$action = 'removefile';
		$output .= "<h5>Delete Existing Document</h5><br />";
		$output .= $delete_file_form;
	break;
	case 'changeimages';
		$old_image = new DevObject($table);
		$old_image->clearMembers();
		$old_image->$entry_id_var = $_POST[$entry_id_var];
		$old_image->loadMembers();

		foreach ($old_image->getMemberArray() as $a=>$b) if ($_POST[$a] == 'NULL') $old_image->$a = '';
		$success .= $old_image->writeObject(1);
	case 'manageimages':
	//$object->loadFreshMembers();
	$action = 'changeimages';
	$options['new'] = '<a href="' . $href . '?action=new">'.$this->getIcon('new').'Create New</a>';
	$options['edit'] = '<a href="' . $href . '?action=edit&' . $entry_id_var . '=' . $$entry_id_var . '">'.$this->getIcon('edit').'Return to this entry</a>';
	
	$output .= '<span style="font-size: 11px; color: #ff0000; font-family: arial, helvitica, sans-serif;">
	Select the checkbox to remove the image from this entry.
	</span><br />' . 
	dev_draw_form_field('hidden', $entry_id_var, '', $$entry_id_var) . 
	dev_draw_form_field('checkbox', 'entry_image', 'Main Image: ' . $entry_image, '') . 
	dev_draw_form_field('checkbox', 'entry_image_1', 'Additional Image 1: ' . $entry_image_1, 'NULL') . 
	dev_draw_form_field('checkbox', 'entry_image_2', 'Additional Image 2: ' . $entry_image_2, 'NULL') . 
	dev_draw_form_field('checkbox', 'entry_image_3', 'Additional Image 3: ' . $entry_image_3, 'NULL') . 
	dev_draw_form_field('checkbox', 'entry_image_4', 'Additional Image 4: ' . $entry_image_4, 'NULL') . 
	dev_draw_form_field('checkbox', 'entry_image_5', 'Additional Image 5: ' . $entry_image_5, 'NULL') . 
	dev_draw_form_field('checkbox', 'entry_image_6', 'Additional Image 6: ' . $entry_image_6, 'NULL') . 
	dev_draw_form_field('checkbox', 'entry_image_7', 'Additional Image 7: ' . $entry_image_7, 'NULL') . 
	dev_draw_form_field('checkbox', 'entry_image_8', 'Additional Image 8: ' . $entry_image_8, 'NULL') .
	'<input type="submit" value="Remove" />';
	break;


	}
	$output .= dev_draw_form_field('hidden', 'action', '', $action);
	$output .= dev_close_form();
	
	$status .= ($object->getStatusMessage() != '') ? '<span class="dev_status">' . $object->getStatusMessage() . '</span><br />' : '';
	
	$option_types = array_keys($options);

	if ($options_on === true)
		foreach ($option_types as $a) if (!in_array($a, $options_set)) unset($options[$a]);

	$options_str = implode(' ', $options);
	
	$this->setStatus($status);
	$this->setTasks($options_str);
	$this->setOutput($output);
	
	$output = "$status$options_str<br />$output";
	
	//echo $object->getQuery();
	
	return $output;
}

} //End class DevControl
?>
