<?php
//dev_general
//General function for dev library/framework

///
//URL/HTTP Functions
///////
	if(!function_exists('http_build_query')) {
   function http_build_query( $formdata, $numeric_prefix = null, $key = null ) {
       $res = array();
       foreach ((array)$formdata as $k=>$v) {
           $tmp_key = urlencode(is_int($k) ? $numeric_prefix.$k : $k);
           if ($key) $tmp_key = $key.'['.$tmp_key.']';
           if ( is_array($v) || is_object($v) ) {
               $res[] = http_build_query($v, null /* or $numeric_prefix if you want to add numeric_prefix to all indexes in array*/, $tmp_key);
           } else {
               $res[] = $tmp_key."=".str_replace('%2F', '/', urlencode($v));
           }
           /*
           If you want, you can write this as one string:
           $res[] = ( ( is_array($v) || is_object($v) ) ? http_build_query($v, null, $tmp_key) : $tmp_key."=".urlencode($v) );
           */
       }
       $separator = ini_get('arg_separator.output');
       return implode($separator, $res);
   }
}

function dev_define($name, $value)
{
	if ( !defined( $name ) )
	{
		define( $name, $value );
	}
}

function dev_http_query( $formdata, $numeric_prefix = null, $key = null ) {
       $res = array();
       foreach ((array)$formdata as $k=>$v) {
           $tmp_key = urlencode(is_int($k) ? $numeric_prefix.$k : $k);
           if ($key) $tmp_key = $key.'['.$tmp_key.']';
           if ( is_array($v) || is_object($v) ) {
               $res[] = dev_http_query($v, null /* or $numeric_prefix if you want to add numeric_prefix to all indexes in array*/, $tmp_key);
           } else {
               $res[] = $tmp_key."=".str_replace('%2F', '/', urlencode($v));
           }
           /*
           If you want, you can write this as one string:
           $res[] = ( ( is_array($v) || is_object($v) ) ? http_build_query($v, null, $tmp_key) : $tmp_key."=".urlencode($v) );
           */
       }
       $separator = ini_get('arg_separator.output');
       return implode($separator, $res);
}

function dev_domain( $wholedomain = false ) {
	$domain = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '';
	if ($domain != '') {
		$domain = (strtolower(substr($domain, 0, 4)) == 'www.' && !$wholedomain ) ? substr($domain, 3) : $domain;
		$port = strpos($domain, ':');
		$domain = ($port) ? substr($domain, 0, $port) : $domain;
	}
	return $domain; 
}

function dev_href($href = '', $doc = true) {
	if (dev_is_null($href)) {
		if (!defined('PAGE_EXTENSION')) define('PAGE_EXTENSION', '.php');
		$href = '';
		if ($doc === false) {
			$href .= $_SERVER['DOCUMENT_ROOT'];
		} else {
			$href = 'http://' . $_SERVER['SERVER_NAME'];
			$href .= $_SERVER['REQUEST_URI'];
			if (dev_strrpos($href, PAGE_EXTENSION)) $href = substr($href, 0, dev_strrpos($href, PAGE_EXTENSION) + strlen(PAGE_EXTENSION));
			elseif (dev_strrpos($href, '/')) $href = substr($href, 0, dev_strrpos($href, '/') + strlen('/'));
		}
	}
	
	return $href;
}

function dev_control_var($var) {
	$cookie = ( array_key_exists( $var, $_COOKIE) ) ? $_COOKIE[$var] : NULL;  
	$get = ( array_key_exists( $var, $_GET) ) ? $_GET[$var] : NULL;
	$post = ( array_key_exists( $var, $_POST) ) ? $_POST[$var] : NULL;
	return ( dev_not_null($cookie) ) ? $cookie : (( dev_not_null($post) ) ? $post : $get);
}

//
//Array Functions 
///////
//checks to see if a variable is an associative array
function dev_is_assoc($var) {
	return ((is_array( $var )) && !is_numeric( implode( array_keys( $var ))));
}

//checks to see if a variable is a numerically indexed array
function dev_is_index($var) {
	return ((is_array( $var )) && is_numeric( implode( array_keys( $var ))));
}

function dev_get_from_array($array, $key)
{
	$keys = array_keys( $array );
	if ( in_array( $key, $keys ) )
	{
		return $array[$key];
	}
}

//get the largest integer value from an array
function dev_max_value($array) {
	$array = dev_value_to_array($array);
	if (sort($array)) {
		$max = (int)array_pop($array);
	}
	return $max;
}

//get the lowest integer value from an array
function dev_min_value($array) {
	$array = dev_value_to_array($array);
	if (rsort($array)) {
		$max = (int)array_pop($array);
	}
	return $max;
}

function dev_implode_ref(&$array, $delimiter = ' ') {
	$array = implode($delimiter, $array);
	return true;
}

function dev_explode_ref(&$array, $delimiter = ' ') {
	$array = explode($delimiter, $array);
	return true;
}

/* 
//In Development
function dev_array_to_tree($array, $categories = '') {
	$array = dev_value_to_array($array);
	$categories = dev_value_to_array($categories);
	$last_cat = array();
	if dev_is_assoc($array) {}
	
	$tree_items = array();
	foreach ($array as $a) {
	
		for ($i=0;$i<count($categories);$i++) {
			//$last_cat[$i] = $a[$categories[$i]];
			if ($a[$categories[$i]] != $last_cat[$i]) {
				if ($last_cat[$i] != '') {
					//$tree_items .= "],\n";
					$more--;
				}
				$last_cat = $cat;
				
				if ($last_sub != '') {
					$tree_items .= "],\n";
					$more--;
				}
				$last_sub = $sub;
				
				$tree_items .= "\t['$cat', null, \n";
				$more++;
			} 
		}
		
		$title = $a['title'];
		$name = $a['name'];
		$url = 'http://www.bluefission.com/';
	
		if ($cat != $last_cat) {
			if ($last_cat != '') {
				$tree_items .= "],\n";
				$more--;
			}
			$last_cat = $cat;
			
			if ($last_sub != '') {
				$tree_items .= "],\n";
				$more--;
			}
			$last_sub = $sub;
			
			$tree_items .= "\t['$cat', null, \n";
			$more++;
		} 
		if ($sub != $last_sub ) {
			if ($last_sub != '') {
				$tree_items .= "],\n";
				$more--;
			}
			$last_sub = $sub;
			$tree_items .= "\t\t['$sub', null, \n";
			$more++;
		}
		
		for ($i=0;$i<$more;$i++) $tree_items .= "\t";
		$tree_items .= "[" . dev_prep_input($title) . ", '$url$cat/$sub/$name.emc'],\n";
	}

	for ($i=0;$i<$more;$i++) {
	$tree_items .= "]\n";
	}
	$tree_items .= ';';
}
*/

function dev_array_tree($array, $categories = '', $nodes = '', $start=1) {
	$categories = array();
	if ($nodes=='' || !dev_is_assoc($nodes)) $nodes = array('name'=>'name','attribs'=>'attribs','content'=>'content','child'=>'child');
	$categories[] = 'name';
	$categories[] = 'category';
	$categories[] = 'subcategory';
	$array = dev_value_to_array($array);
	$categories = dev_value_to_array($categories);
	$tree = array();
	
	foreach($array as $a=>$b) {
		if ($b[$categories[$start]] != $last_cat) {
			$tree['child'] = dev_array_tree($b, ++$start);
		} else {
			$tree['name'] = 'li';
			//$tree['attribs'] = '';
			$tree['content'] = $a[$categories[0]];
		}
	}
}

function dev_remove_dupes(&$array) {
	if (is_array($array)) {
		$hold = array();
		 foreach ($array as $a=>$b) {
			if (!in_array($b, $hold, true)) { 
				$hold[$a] = $b;
			}
		}
		$array = $hold;
		unset($hold);
		return true;
	} else return false;
}

function dev_remove_idupes(&$array) {
	if (is_array($array)) {
		$hold = array();
		 foreach ($array as $a=>$b) {
			if (!in_array(strtolower($b), $hold) && !is_array($b)) { 
				$hold[$a] = strtolower($b); 
			}
		}
		$array = $hold;
		unset($hold);
		return true;
	} else return false;
}


///
//String functions
///////
function dev_random_string($length = 8, $symbols = false){
	$alphanum = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
	if ($symbols) $alphanum .= "~!@#\$%^&*()_+=";
	$rand_string = '';
	for($i=0;	$i<$length; $i++)
		$rand_string .= $alphanum[rand(0, strlen($alphanum))];
	return $rand_string;
}

function dev_trunc_str($string, $limit = 40) {
	$string_r = explode(' ', $string, ($limit+1));
	if (count($string_r) >= $limit && $limit > 0) array_pop($string_r);
	$output = implode (' ', $string_r);
	return $output;
}

function dev_match_strings($str1, $str2) {
	return ($str1 == $str2);
}

function dev_encrypt_string($string = '', $mode = '') {
	switch ($mode) {
	default:
	case 'md5':
		$string = md5($string);
		break;
	case 'password':
		$string = md5($string);
		break;
	}
	
	return $output;
}

function dev_hash_string($string = '', $mode = '') {
	switch ($mode) {
	default:
	case 'md5':
		$string = md5($string);
		break;
	case 'password':
		$string = md5($string);
		break;
	}
	
	return $string;
}

//Not Yet Implemented
function dev_dictionary_word($string) {
	$dictionary = file('dictionary.txt');
	return in_array(strtolower($string), $dictionary);
}

function dev_strrpos($haystack, $needle) {
	$i = strlen($haystack);
	while ( substr( $haystack, $i, strlen( $needle ) ) != $needle ) {
		$i--;
		if ( $i < 0 ) return false;
	}
	return $i;
}

function dev_is_substr($haystack, $needle) {
	return (strpos($haystack, $needle) !== false);
}

///
//Variable value functions
///////
function dev_not_null(&$var) {
	return (isset($var) && $var != null && $var != '');
}

function dev_is_null(&$var) {
	return !dev_not_null($var);
}

function dev_flip_bool($bool) {
     return (!($bool === true));
}

function dev_toggle_bool_ref(&$bool) {
	$bool = dev_flip_bool($bool);
}

function dev_not_empty( $var )
{
	return ( dev_not_null( $var ) || dev_valid_number( $var, true ) );
}

///
//Math Functions
///////
function dev_valid_number($num, $allow_zero = false) {
	return (is_numeric($num) && ((dev_not_null($num) && $num != 0) || $allow_zero));
}

function dev_ratio($part = 0, $whole = 100, $percent = false) {
	if (!dev_valid_number($part)) $part = 0;
	if (!dev_valid_number($whole)) $whole = 1;
	
	$ratio = ($part * 100)/$whole;
	
	return $ratio*(($percent) ? 100 : 1);
}
?>