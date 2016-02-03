<?php
//dev_view.php

class DevView {

var $_syntax_file;
var $_syntax;
var $_token_file;
var $_token_map;
var $_data_file;
var $_data;
var $_parse_data;
var $_message;

function DevView($token_file = '', $syntax_file = '', $data_file = '', $data = '') {
	$this->prepareView($token_file, $syntax_file, $data_file, $data);
}

function prepareView($token_file = '', $syntax_file = '', $data_file = '', $data = '') {
	$this->_token_map = array();
	$this->setTokenFile($token_file);
	$this->loadTokens();
	$this->setSyntaxFile($syntax_file);
	$this->loadSyntax();
	$this->setDataFile($data_file);
	$this->setData($data);
	return true;
}

function setTokenFile($file = '') {
	if (file_exists($file)) {
		$this->_token_file = $file;
		return true;
	}
}

function getTokenFile() {
	return $this->_token_file;
}

function setSyntaxFile($file = '') {
	if (file_exists($file)) {
		$this->_syntax_file = $file;
		return true;
	}
}

function getSyntaxFile() {
	return $this->_syntax_file;
}

function setDataFile($file = '') {
	if (file_exists($file)) {
		$this->_data_file = $file;
		return true;
	}
}

function getDataFile() {
	return $this->_data_file;
}

function setData($data = '') {
	if (dev_not_null($data)) {
		$this->_data = $data;
		return true;
	}
}

function getData() {
	return $this->_data;
}

function setParsedData($data = '') {
	if (dev_not_null($data)) {
		$this->_parsed_data = $data;
		return true;
	}
}

function getParsedData() {
	return $this->_parsed_data;
}

function getTokenMap() {
	return $this->_token_map;
}

function setTokenMap($array = '') {
	if (is_array($array)) {
		$this->_token_map = $array;
		return true;
	}
}

function getToken($token) {
	if (dev_not_null($this->_token_map[$token]))
		return $this->_token_map[$token];
	else
		return false;
}

function loadTokens($file = '', $delimiter = '') {
	$token_file = $this->getTokenFile();
	if (dev_not_null($token_file)) {
		$token_r = file($token_file);
		$token_map = array_walk($token_r, 'dev_read_log_r', '==>');
		$this->setTokenMap($token_map);
	}
}

function getSyntaxData() {
	return $this->_syntax_data;
}

function setSyntaxData($array = '') {
	if (is_array($array)) {
		$this->_syntax_data = $array;
		return true;
	}
}

function getSyntax($line) {
	if (dev_not_null($line)) {
		$error_type = '';
		$syntax_data = $this->getSyntaxData();
		$test = preg_match($syntax_data, $line);
		return $test;
	} else {
		$this->setMessage($error_type);
		return false;
	}
}

function loadSyntax($file = '', $delimiter = '') {
	$syntax_file = $this->getSyntaxFile();
	if (dev_not_null($syntax_file)) {
		$syntax_r = file($syntax_file);
		$syntax_data = array_walk($syntax_r, 'dev_read_log_r', '==>');
		return $this->setSyntaxData($syntax_map);
	}
}

function parseDataFile($data = '') {
	$status = '';
	$data_file = $this->getDataFile();
	if (dev_not_null($data_file)) {
		if (file_exists($data_file)) {
			$file = fopen($data_file, 'r');
			$data = file_get_contents($file);
			$this->setData($data);
			$status = $this->parseData();
		} else {
			$status = false;
		}
	} else {
		$status = false;
	}
	
	return $status;
}

function parseData($data = '') {
	$status = '';
	if (dev_is_null($data)) $data = $this->getData();
	if (dev_not_null($data)) {
		if ($this->checkSyntax($data)) {
			if ($this->replaceTokens($data)) {
				$status = $this->setParsedData($data);
			} else {
				$status = false;
			}
		} else {
			$status = false;
		}
	} else {
		$status = false;
	}
	
	return false;
}

function executeData($token_file = '', $syntax_file = '', $data_file = '', $data = '') {
	$output = '';
	if ($this->prepareView($token_file, $syntax_file, $data_file, $data)) {
		if (dev_not_null($data_file)) {
			$output = $this->parseDataFile($data_file);
		} else {
			$output = $this->ParseData($data);
		}
	} else {
		$this->setStatus("Failed to properly prepare view!\n");
	}
		if (dev_is_null($output)) $output = $this->getMessage();
	return $output;
}

function checkSyntax($data) {
	$data_r = explode("\n", $data);
	$error = false;
	$status = false;
	while (list($line) = $data_r && !$error) {
		if ($this->getSyntax($line)) {
			$status = true;
		} else {
			$status = false;
			$error = true;
		}
	}
	return $status;
}

function replaceTokens(&$data) {
	$token_map = $this->getTokens();
	$token_map_keys = array_keys($token_map);
	if ($data = preg_replace($token_map_keys, $token_map, $data)) {
		$status = true;
	} else {
		$this->setMessage();
		$status = false;
	}
	return $status;
}

function setMessage($message = '') {
	if (dev_not_null($message)) {
		$this->_message = $message;
		return true;
	}
}

function getMesage() {
	return $this->_message;
}

} //End DevView Class
?>