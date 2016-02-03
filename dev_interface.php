<?php
/*
Dev_interface
*/

class DevInterface {
	var $_template;
	var $_title;
	var $_content;
	
	function DevInterface($title = '', $template = '', $content = '') {
		$this->_template = new DevTemplate();
		if (file_exists($template)) {
			$this->_template->load($template);
		}
		$this->_title = $title;
		$this->_content = $content;
	}
	
	function index() {
		$this->_template->publish();	
	}
}
?>
