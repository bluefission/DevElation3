<?php
//dev_html_out.php
//created: 1/19/05
//last modified: 2/05/05
///////
//contains html output functions

//outputs standard html content box
function dev_content_box($content_r, $cols = 0, $href = '', $query_r = '', $highlight = '', $header = '', $link_style = '1', $show_image = false, $img_dir = 'images/', $file_dir = '', $icon = '', $trunc = '', $fields = '') {
	$hori = 0;
	$output = "<table class=\"dev_table\"" . (($header === false) ? '' : " id=\"anyid\"") . ">\n";
	
	$bg = false;
	$href = dev_href($href);
	$count = 0;
	$new_row = 1;
	
	foreach ($content_r as $row) {
		$fields = ($fields != '' && $fields >= 0 && $fields < count( $row )) ? $fields : count( $row );

		if ($count == 0) {
			if ($header !== false) {
				$header = (is_array($header) && count($header) == count($row)) ? $header : $row;
				if (dev_is_assoc($header)) $header = array_keys($header);
				$output .= '<tr>';
				$i = 0;
				foreach ($header as $a) {
					if ($i == 0 && $link_style != 1 && $link_style !== 0) {
						$output .= '<th>';
						$output .= '';
						$output .= "</th>";
					}
					if ($i > 0) {
						$output .= '<th>';
						$output .= $a;
						$output .= "</th>";
					}
					$i++;
					if ($i > $fields) break;
				}
				$output .= "</tr>\n";
			}
		}
		
		//manage columns
		//$new_row = ($hori < $cols) ? 0 : 1;
		
		if ($hori == 0) {
			$output .= '<tr';
			if ($highlight != '') {
				$output .= ($bg) ? ' bgcolor="' . $highlight . '"' : '';
				$bg = dev_flip_bool($bg);
			}
			$output .= '>';
		}
		
		$i = 0;
		
		foreach ($row as $a=>$b) {
			
			if ($i > 0 || ($header === false && $link_style != 1)) {
				if (!($icon != '' && $fields == 2 && $i == 2)) $output .= '<td>';
				if (($i == 1 || ($icon != '' && $i = 2)) && $link_style == 1) $output .= '<a class="contentBox" href="' . $href . '?' . $varname . '=' . $value . ((dev_is_assoc($query_r)) ? '&' . http_build_query($query_r) : '') . '">';
				if (!$show_image || $trunc != '') $data = dev_format_data($b, '', $trunc);
				else $data = $b;
				if ($i != 1) $data = dev_link_file($data, $file_dir);
				if ($icon != '' && $i == 1) $data = dev_print_image((($data == '')?$icon:$data), $img_dir, '', '50', '', '', '', '', '', $icon) . (($fields == 1) ? "<br />".$data:'');
				elseif ($show_image) $data = dev_print_image($data, $img_dir, $data, '100', '', true, '', false);
				$output .= $data;
				if ($i == 1 && $link_style == 1) $output .= "</a>";
				if (!($icon != '' && $fields == 2 && $i == 1)) $output .= "</td>";
			} elseif ($i == 0) {
				if ($link_style == 2) {
					$output .= '<td>';
					$output .= dev_draw_form($href) . dev_draw_form_field('hidden', $a, '', $b) . dev_draw_form_field('submit', 'submit', '', 'Go'); 
					if (dev_is_assoc($query_r)) foreach ($query_r as $c=>$d) $output .= dev_draw_form_field('hidden', $c, '', $d);
					$output .= dev_close_form();
					$output .= "</td>";
				} elseif ($link_style == 3) {
					$output .= '<td>';
					//$output .= dev_draw_form($href);
					$output .= dev_draw_form_field('checkbox', $a . '[]', '', $b);
					if (dev_is_assoc($query_r)) foreach ($query_r as $c=>$d) $output .= dev_draw_form_field('hidden', $c, '', $d);
					//$output .= dev_close_form();
					$output .= "</td>";
				} else {
					$varname = $a;
					$value = $b;
				}			
			}
			$i++;
			if ($i > $fields) break;
		}		
		
		$output .= "\n";
		if ($hori < $cols) {
			$hori++;
		} else {
			$output .= "</tr>\n";
			$hori = 0;
		}
		$count++;
	}
	if ($hori != 0) $output .= "</tr>\n";
	$output .= "</table>\n";
	
	return $output;
}

function dev_content_select($content_r, $query_r = '', $trunc = '', $fields = 2) {
	$field_name = '';
	$get_name = true;
	$content = '';
	$query_r = dev_value_to_array($query_r);
	$output = '';
	$select_r = array();
	$select_r['-------'] = '';
	foreach ($content_r as $row) {
		$row = dev_value_to_array($row);
		$value_r = array();
		$i = 0;
		foreach ($row as $a=>$b) {
			if ($get_name) {
				$field_name = $a;
				$get_name = dev_flip_bool($get_name);
			}
			if ($i > $fields) break;
			if ($i > 0) $value_r[] = dev_format_data($b, '', $trunc);
			$i++;
		}
		
		$select_r[implode(' - ', $value_r)] = $row[$field_name];
	}
	foreach ($query_r as $a=>$b) $output .= dev_draw_form_field('hidden', $a, '', $b);
	$output .= dev_draw_form_field('select', $field_name, '', $select_r) .
	dev_draw_form_field('submit', 'submit', 'Submit', 'Submit');
	
	return $output;
}

//outputs page and section headers
function dev_print_header($text = '') {
	return $text;
}

//prints blocks of text in columnar format
function dev_print_columns() {
	//dev_content_box('', 2);
}

//prints an image
function dev_print_image($image, $dir = '', $alt = '', $width = '100', $height = '', $thumb = false, $align = '', $link = true, $defaults = 0, $blank = false) {
	$imgdir = dev_control_var('imgdir');
	$output = '';
	$pattern = "/\.gif$|\.jpeg$|\.tif$|\.jpg$|\.tiff$|\.png$|\.bmp$/i";
	if (preg_match($pattern, $image) || $blank !== false) {
		$height_line = ($height == '') ? '' : " height=" . $height;
		if ($defaults) {
			$width = '100'; 
		}
		if ($dir == '' && $imgdir != '') $dir = $imgdir;
		$image = $dir . $image;
		if ($align != '') $align =  'align="' . $align . '"';
		if ($image != '' && file_exists(getcwd() . '/' . $image)) {
			if ($link) $output .= '<a href="' . $image . '" target="_blank">';
			if ($thumb) $output .= '<img src="thumb.php?f=' . $image . '&d=' . $dir . '" border="0" alt="' . $alt . '" title="' . $alt . '" width="' . $width . $height_line . '" ' . $align . ' />';
			else $output .= '<img src="' . $image . '" border="0" alt="' . $alt . '" title="' . $alt . '" width="' . $width . $height_line . '" ' . $align . ' />';
			if ($link) $output .= '</a>';
		} elseif ($blank != '' && file_exists(getcwd() . '/' . $blank))
			$output .= '<img src="' . $blank . '" border="0" alt="' . $alt . '" title="' . $alt . '" width="' . $width . $height_line . '" ' . $align . ' />';
		else
			$output .= '<img src="noimage.png" border="0" alt="' . $alt . '" title="' . $alt . '" width="' . $width . $height_line . '" align="left" />';
		return $output;
	} else {
		return $image;
	}
}

//prints an image
function dev_link_file($file, $dir, $virtual = false) {
	$href = dev_href();
	$output = '';
	$pattern = "/\.pdf$|\.doc$|\.zip$|\.mp3$|\.mpeg$|\.mov$|\.rar$|\.txt$/i";
	if (preg_match($pattern, $file)) {
		$file = $dir . $file;
		if ($file != '' && file_exists($file))
			if ($virtual) $output .= '<a href="file.php?f=' . ((strpos($file, getcwd())) ? str_replace(getcwd(), '', $file) : $file). '" target="_blank">' . basename($file) . '</a>';
			else $output .= '<a href="' . ((strpos($file, getcwd())) ? str_replace(getcwd(), $href, $file) : $file). '" target="_blank">' . basename($file) . '</a>';
		else
			$output .= 'file "' . basename($file) . '" not found';
		return $output;
	} else {
		return $file;
	}
}

function dev_ascii_to_html($text = '') {
	$search = array('/^==========/mx',
		'/^\b([\W\w\D\d\s]|[^\n]+)\n----------/mx',
		'/^\b([\W\w\D\d\s]|[^\n]+)\n-------/mx',
		'/^\~ ([\W\w\D\d^\n][^\n]+)\n/mx',
		'/(^<li>.*<\/li>)+/mx',
		'/<\/ol>([\s]*)<ol>/mx',

		'/^\~ ([\W\w\D\d^\n][^\n]+)\n(<ol>[.*?]<\/ol>){0,1}/mx',
		'/(^<li>.*<\/li>)+/mx',
		'/<\/ul>([\s]*)<ul>/mx',

		'/^\* ([\W\w\D\d^\n][^\n]+)\n(<ul>[.*?]<\/ul>){0,1}/mx',
		'/(^<li>.*<\/li>)+/mx',
		'/<\/ul>([\s]*)<ul>/mx',
		
		'/^\- ([\W\w\D\d^\n]|[^\n]+)\n/mx',
		'/\[b\](.*?)\[\/b\]/mxi',
		'/\[i\](.*?)\[\/i\]/mxi',
		'/\[u\](.*?)\[\/u\]/mxi',
		"/^([\s])$/m"
	);
	$replace = array('<hr class="hr">' . "\n",
		'<h2 class="h2">$1</h2>',
		'<h3 class="h3">$1</h3>',
		'<li>$1</li>' . "\n",
		'<ol>' . "\n" . '$1' . "\n" . '</ol>' . "\n",
		'',

		'<li>$1</li>' . "\n",
		'<ul>' . "\n" . '$1' . "\n" . '</ul>' . "\n",
		'',

		'<li>$1</li>' . "\n",
		'<ul>' . "\n" . '$1' . "\n" . '</ul>' . "\n",
		'',
		'<li>$1</li>' . "\n",
		'<b>$1</b>',
		'<i>$1</i>',
		'<u>$1</u>',
		'<br />'
	);
	
	$output = preg_replace($search, $replace, $text);
	
	return $output;
}

function dev_format_data($data = '', $convert = false, $trunc = '') {
	$output = '';
	
	//format:
		//images
		//hrefs
		//dates
		//files
	$pattern = array( '/\'/', '/^([\w\W\d\D\s]+)$/', '/(\d{4})\-(\d+)\-(\d+)/', '/(\d)/', '/\$/', '/(http\:\/\/[\w\W\d\D\S]+)/', '/([\w\W\d\D\S]+@[\w\W\d\D\S]+.[\w\S]+)/');
	$replacement = array( '&#39;', '$1', '$2/$3/$1', '$1', '&#36;', '<a href="$1" target="_blank">$1</a>', '<a href="MAILTO:$1">$1</a>');
	$string = preg_replace($pattern, $replacement, stripslashes($data));
	
	if ($convert) $string = dev_ascii_to_html($string);
	//else $string = htmlentities($string);
	if ($trunc != '' && $trunc > 0) $string = dev_trunc_str(strip_tags($string), (int)$trunc);
	elseif (!$convert) $string = nl2br($string);
	
	return $string;
}

function dev_list_chapter($list_r, $begin = 'start', $end = 'lim', $href = '', $limit = 20) {
	$output = '';
	$chapter_r = array();
	$count = count($list_r);
	$list_r = (($count) <= 0) ? array() : $list_r;
	$href = dev_href($href);
		
	$start = (isset($_GET[$begin]) && is_numeric($_GET[$begin])) ? $_GET[$begin] : 0;
	$lim = (isset($_GET[$end]) && is_numeric($_GET[$end])) ? $_GET[$end] : $limit;
	$query_r = array_merge($_POST, $_GET);
	unset($query_r[$begin]);
	unset($query_r[$end]);
	$get_query = http_build_query($query_r);

	if ( $start > 0 )
	{
		$chapter_r[] = '&lt; <a href="' . $href . '?' . $begin . '=' . ((($start) >= $lim) ? ($start - $lim) : 0) . '&amp;' . $get_query . '">Previous</a> ';
	}
	if ( ($count/$lim) > 1 )
	{
		for ($i=0; $i<(($count/$lim)); $i++) $chapter_r[] = '<a href="' . $href . '?' . $begin . '=' . ($i * $lim) . '&amp;' . $get_query . '">' . ($i + 1) . '</a>';
	}
	if ( $start < round($count/$lim) )
	{
		$chapter_r[] = '<a href="' . $href . '?' . $begin . '=' . (($start + $lim) >= ($count) ? $start : ($start + $lim)) . '&amp;' . $get_query . '">Next</a> &gt;';
	}
	
	$output .= (($count > 0) ? 'Showing ' . ($start + 1) . '-' . (($count < ($start+$lim)) ? $count : ($start+$lim)) . ' of ' . $count . ' results.' : 'No matching results') . '<br />
	' . implode(' | ', $chapter_r);
	
	$output .= "<br />\n";
	return $output;
}

function dev_list_results($list_r, $begin = 'start', $end = 'lim', $href = '', $chapters = true, $link_style = 1, $query_r = '', $highlight = '#c0c0ff', $img_dir = 'images/', $file_dir = 'assets/', $headers = '', $trunc = '', $limit = 20) {
	$start = (isset($_GET[$begin]) && is_numeric($_GET[$begin])) ? $_GET[$begin] : 0;
	$end = (isset($_GET[$end]) && is_numeric($_GET[$end])) ? $_GET[$end] : $limit;
	$list_r = (count($list_r) <= 0) ? array() : $list_r;
	$href = dev_href($href);
	
	if ($chapters) {
		$chapter_list = dev_list_chapter($list_r, $begin, $end, $href);
	}
	$output = '';
	$output .= $chapter_list;
	
	$list_r = array_splice($list_r, $start, $end);
	
	//for ($i = $start; $i < (((count($list_r) - $start) > $end) ? ($start + $end) : count($list_r)); $i++) 
	$output .= dev_content_box($list_r, '', $href, $query_r, $highlight, $headers, $link_style, true, $img_dir, $file_dir, '', $trunc);
	
	$output .= $chapter_list;
	
	return $output;
}

function dev_base_href($href = '') {
	$href = dev_href($href, false);
	$output = '<base href="' . $href . '">';
	$output .= "\n";
	return $output;
}

function dev_bar_graph($data, $height = '', $width = '', $max = '', $is_percent = '') {
	$output = '';
	if (dev_is_assoc($data)) {
		if ($max == '') $max = dev_max_value($data);
		$output .= '<table width = "' . (($width > 0) ? $width : 200) . '" height = "' . (($height > 0) ? $height : 100) . '">';
		$output .= "\n";
		foreach ($data as $a=>$b) {
			$output .= '<tr>';
			$output .= "<td>$a</td>" . (($hori) ? '<tr>' : '<td>');
			$output .= '<td width="80%" class="dev_bar"><table width="100%" cellpadding="0" cellspacing="0" border="1" bgcolor="#ffffff"><tr><td width="' . dev_ratio($b, $max, false) . '%" height="5" bgcolor="#c0c0c0"></td><td></td></tr></table></td>';
			$output .= "<td>" . (($is_percent) ? dev_ratio($b, $max, $is_percent) : $b) . "</td>";
			$output .= "</tr>";
		}	
		$output .= "\n";
		$output .= '</table>';
	}
	
	return $output;
}

function dev_menu($options = '', $href = '', $type = '', $properties = '') {
	$output = '';
	if (dev_is_assoc($options)) {
		/*
		$output .= '<ul>' . "\n";
		foreach ($options as $a=>$b) {
			$output .= "<li>$a\n";
          	if (is_array($b)) {
				$output .= "\t" . '<ul>' . "\n";
				foreach ($b as $c=>$d) {
					$output .= "\t\t" . '<li>' . $c . '</li>' . "\n";
				}
				$output .= "\t" . '</ul>' . "\n";
			}
               $output .= "\t" . '</li>' . "\n";
               }
          }
		}
		*/
		$XML = new DevXML();
		$output .= $XML->buildXML($options);
	} else {
		$output = "No valid menu options provided";
	}
	return $output;
}

function dev_nl2li($str) {
	$output = '';
	$str_r = explode("\n", $str);
	foreach ($str_r as $a) if ($a != '' && $a != ' ') $output .= "<li>$a</li>\n";
	return $output;
}

function dev_br2nl($str){ 
	if (version_compare(PHP_VERSION, '5.0.0', '<')) 
	{
		$str = strtolower($str);
		$str = str_replace('<br>', "\n", $str);  
		$str = str_replace('<br />', "\n", $str); 
		$str = str_replace('<br/>',"\n", $str);
	}
	else
	{
		$str = str_ireplace('<br>', "\n", $str);  
		$str = str_ireplace('<br />', "\n", $str); 
		$str = str_ireplace('<br/>',"\n", $str);
	} 
	return $str;
} 
?>