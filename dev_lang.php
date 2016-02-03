<?php
function dev_lang_file($file = 'legend.txt') {
	$legend = file($file);
	
	$lang = array();
	foreach ($legend as $a) {
		$line = explode(" = ", $a);
		$lang[$line[0]] = $line[1];
	}
}

function dev_lang($index, $num=0, $arr = '') {
	global $lang;	
	$list = (($arr == '') ? $lang : $arr);
	if (array_key_exists($index, $list)) {
		$val = $list[$index];
	} else {
		$val = '';
	}
	
	if ($num) return intval($val);
	else return $val;
}
?>
