<?php
//dev_xml.php

class DevXML {
var $filename;
var $parser;
var $data;
var $msg;

function DevXML($file = '') {
	$this->parser = xml_parser_create();
	xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, true);
	xml_set_object($this->parser, $this);
	xml_set_element_handler($this->parser, 'startHandler', 'endHandler');
	xml_set_character_data_handler($this->parser, 'dataHandler');
	if (dev_not_null($file)) {
		$this->setFile($file);
		$this->parseXML($file);
	}
}

function setFile($file = '') {
	if (dev_not_null($file)) {
		$this->filename = $file;
	}
}

function getFile() {
	return $this->filename;
}

function parseXML($file = '') {
	if (dev_is_null($file)) {
		$file = $this->getFile();
	}
	if ($stream = dev_stream_file($file, $msg)) {
		while ($data = fread($stream, 4096)) {
			if (!xml_parse($this->parser, $data, feof($stream))) {
				$this->setMsg(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($this->parser)), xml_get_current_line_number($this->parser)));
				return false;
			}
		}
	} else {
		$this->setMsg($msg);
		return false;
	}
	return true;
}

function startHandler($parser, $name = '', $attributes = '') {
	$data['name'] = $name;
	if ($attributes) $data['attributes'] = $attributes;
	$this->data[] = $data;
}

function dataHandler($parser, $data = '') {
	if ($data = trim($data)) {
		$index = count($this->data)-1;
		$this->data[$index]['content'] .= $data;
	}
}
 
function endHandler($parser, $name = '') {
	if (count($this->data) > 1) {
		$data = array_pop($this->data);
		$index = count($this->data)-1;
		$this->data[$index]['child'][] = $data;
	}
}

function buildXML($data = '', $indent = 0) {
	$xml = '';
	$tabs = "";
	for ($i=0; $i<$indent; $i++) $tabs .= "\t";
	//if (!is_array($data)) $data = dev_value_to_array($data);
	if (is_array($data)) {
		foreach($data as $b=>$a) {
			if (!dev_is_assoc($a)) {
				$xml .= $this->buildXML($a, $indent);
			} else {
				$attribs = '';
				if (dev_is_assoc($a['attributes'])) foreach($a['attributes'] as $c=>$d) $attribs .= " $c=\"$d\"";
				$xml .= "$tabs<" . $a['name'] . "" . $attribs . ">" . ((count($a['child']) > 0) ? "\n" . $this->buildXML($a['child'], ++$indent) . "\n$tabs" : $a['content']) . "</" . $a['name'] . ">\n";
			}
		}
	}
	return $xml;
}

function setMsg($msg) {
	$this->msg = $msg;
}

function getMsg() {
	return $this->msg;
}

function getData() {
	return $this->data;
}

function outputXML($data = '') {
	header("Content-Type: XML");
	$xml = 'No XML';
	if ($data == '') $data = $this->data;
	$xml = $this->buildXML($data);
	echo $xml;
}

} //End class DevXML
?>
