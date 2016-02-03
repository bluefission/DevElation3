<?php
//dev_template
class DevTemplate {

	var $_template;
	var $_status;
	var $_cached;
	var $_file;
	var $_data;
	var $_tokens;
	
	function DevTemplate ( $file = '' ) {
		if ( dev_not_null( $file) ) {
			$this->load($file);
		}
		$this->_cached = false;
		$this->_tokens = array();
	}

	function load ( $file ) {
		$this->_file = $file;
		$this->_template = dev_view_file($this->_file);
	}
	
	function clear () {
		$this->load( $this->_file );
	}

	function set ( $var, $content ) {
		$this->_template = str_replace ( TEMPLATE_VALUE_DELIMITER_START . $var . TEMPLATE_VALUE_DELIMITER_END, $content, $this->_template );
	}

	function assign( $var, $content ) {
		$this->_tokens[$var] = $content;
	}

	function assignFormatted( $var, $content ) {
		$content = dev_format_data($content);
		$this->_tokens[$var] = $content;
	}

	function setFormatted ( $var, $content ) {
		$content = dev_format_data($content);
		$this->_template = str_replace ( TEMPLATE_VALUE_DELIMITER_START . $var . TEMPLATE_VALUE_DELIMITER_END, $content, $this->_template );
	}
	
	function cache ( $minutes = 60) {
		$file = CACHE_DIR.$_SERVER['REQUEST_URI'];
		if (file_exists($file) && filectime($file) <= strtotime("-{$time} minutes")) {
			$this->_cached = true;
			$this->load ( $file );
		}
	}
	
	function getCached ( ) {
		return $this->_cached;
	}
	
	function loadModule ( $file ) {
		ob_start( );
		include ( $file );
		return ob_get_clean( );
	}
	
	function setArray ( $dataArray ) {
		if ( is_array($dataArray) )
		{
			foreach ($dataArray as $a=>$b) {
				$this->set($a, $b);
			}
		}
	}
	
	function setArrayFormatted ( $dataArray ) {
		$count = 0;
		foreach ($dataArray as $a=>$b) {
			$this->setFormatted($a, $b);
			dev_parachute($count, 1000);
		}
	}
	
	function assignArray( $dataArray ) {
		foreach ($dataArray as $a=>$b) {
			$this->assign($a, $b);
		}	
	}
	
	function assignArrayFormatted( $dataArray ) {
		foreach ($dataArray as $a=>$b) {
			$this->assignFormatted($a, $b);
		}	
	}
	
	function loadData ( $data )
	{
		$this->_data = $data;
	}
	
	function setData ()
	{
		$this->setArray ( $this->_data );
	}
	
	function setDataFormatted ()
	{
		$this->setArrayFormatted ( $this->_data );
	}
	
	function renderRecordSetFormatted( $recordSet ) {
		$output = '';
		$count = 0;
		foreach ($recordSet as $a) {
			$this->clear();
			$this->setArrayFormatted($a);
			$output .= $this->render();
			dev_parachute($count, 1000);
		}
		return $output;
	}
	
	function renderRecordSet( $recordSet ) {
		$output = '';
		$count = 0;
		foreach ($recordSet as $a) {
			$this->clear();
			$this->setArray($a);
			$output .= $this->render();
			dev_parachute($count, 1000);
		}
		return $output;
	}
	
	function executeComponents() 
	{
		$search_string = '/\{' . EXEC_COM_TAG . ' ([.*]|[^\n]*?)src="(.*?)"([.*]|[^\n]*?)(([\W\D_]*?)="(.*?)"([.*]|[^\n]*?)*?) \/\}/mxi';
		$search_strings = array();
		$replace_strings = array();
		print_r($com);
		preg_match_all($search_string, $this->_template, $com);
		for ($i = 0; $i < count($com); $i++) {
			$search_strings[] = $search_string;
			
			$component = file_get_contents(COMPONENT_DIR.$com[2][$i]);
			$key = trim($com[5][$i]);
			$val = trim($com[6][$i]);
			
			$mod_out = str_replace("%{$key}%", $val, $component);
	
			$replace_strings[] = $mod_out;
		}
		
		$this->_template = preg_replace($search_strings, $replace_strings, $this->_template, 1);
	}
	
	function executeModules() 
	{
		$search_string = '/\{' . EXEC_MOD_TAG . ' ([.*]|[^\n]*?)src="(.*?)"([.*]|[^\n]*?) \}/mxi';
		$search_strings = array();
		$replace_strings = array();
		preg_match_all($search_string, $this->_template, $matches);

		foreach ($matches[2] as $a) {
			$search_strings[] = $search_string;
			ob_start();
			include_once(MODULE_DIR . $a);
			$mod_out = ob_get_clean();

			$replace_strings[] = $mod_out;
		}
		
		$this->_template = preg_replace($search_strings, $replace_strings, $this->_template, 1);
	}

	function render ( ) {
		$this->executeModules();
		$this->setArray( $this->_tokens );
		ob_start();
		eval ( ' ?> ' . $this->_template . ' <?php ' );
		return ob_get_clean();
	}

	function publish ( ) {
		return print($this->render());
	}
}
?>