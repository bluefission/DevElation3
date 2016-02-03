<?php
//dev_display.php
//created: 12/04/06
//last modified: 12/05/06
///////
//contains object and listing view functions

function dev_display_box($content_r, $view = 'list', $href = '', $query_r = '', $highlight = '', $header = '', $link_style = '', $show_image = '', $img_dir = '', $file_dir = '', $icon = '', $trunc = '', $fields = '') {

	switch ($view) {
	case 'list':
		$cols = 0;
		//$query_r = '';
		//$highlight = '';
		//$header = '';
		$link_style = '1';
		$show_image = false;
		//$icon = '';
	break;
	case 'manage':
		$cols = 0;
		//$query_r = '';
		//$highlight = '';
		//$header = '';
		$link_style = '3';
		$show_image = false;
		//$icon = '';
	break;
	case 'tile':
		$cols = 3;
		//$query_r = '';
		$highlight = '';
		$header = false;
		$fields = 1;
		$link_style = '1';
		$show_image = true;
		if ($icon == '') $icon = 'default.png';
	break;
	case 'icons':
	case 'icon':
		$cols = 5;
		//$query_r = '';
		$highlight = '';
		$fields = 1;
		$header = false;
		$link_style = '1';
		$show_image = true;
		//if ($icon == '') $icon = 'default.png';
		if ($icon == '') $icon = 'icons3/Text-Old-1-48x48.png';
	break;
	case 'thumbnail':
	case 'thumb':
		$cols = 2;
		//$query_r = '';
		//$highlight = '';
		$fields = 2;
		$header = false;
		$link_style = '1';
		$show_image = true;
		if ($icon == '') $icon = 'default.png';
	break;
	case 'detail':
		$cols = 0;
		//$query_r = '';
		$highlight = '';
		//$header = '';
		$link_style = '2';
		$show_image = false;
		//$icon = '';
	break;
	default:
	case 'search':
		$cols = 0;
		//$query_r = '';
		$highlight = '';
		//$header = '';
		$link_style = '1';
		$show_image = false;
		//$icon = '';
		$begin = 'start';
		$end = 'lim';
		$output = dev_list_results($content_r, $begin, $end, $href, true, $link_style, $query_r, $highlight, $img_dir, $file_dir, $header, $trunc);
		return $output;
	break;
	case 'calendar':
		$output = dev_calendar(date('m'), date('Y'), '', $content_r);
		return $output;
	break;
	case 'blog':
		$output = '';
		foreach ($content_r as $a) $output .= dev_detail_box($a, '', true);
		return $output;
	break;
	case 'select':
		$fields = 2;
		$trunc = 3;
		$output = dev_content_select($content_r, $query_r, $trunc, $fields);				
		return $output;
	break;
	}
	
	$output = dev_content_box($content_r, $cols, $href, $query_r, $highlight, $header, $link_style, $show_image, $img_dir, $file_dir, $icon, $trunc, $fields);
	
	return $output;
}

function dev_detail_box($content_r = '', $format_r = '', $convert = '', $trunc = '') {
	$output = '';
	$content_r = dev_value_to_array($content_r);
	$format_r = (is_array($format_r)) ? $format_r : array('b', 'h1', 'i', 'span', 'div', 'div', 'div', 'div');
	$i = 0;
	foreach ($content_r as $a) {
		if ($a != '') {
			if ($i > 0) $output .= "<" . $format_r[$i] . ">" . dev_print_image(dev_format_data($a, $convert, $trunc)) . "</" . $format_r[$i] . ">\n";
			if ($i < (count($format_r) -1)) $i++;
		}
	}
	
	if ($i == 0) $output = $output .= "<" . $format_r[$i] . ">Not Found</" . $format_r[$i] . ">\n";
	return $output;
}

?>
