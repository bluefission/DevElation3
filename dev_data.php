<?php
//dev_data.php
//Data management functions for the dev lib/framework
function dev_set_cookie($var, $value = '', $expire = '3600', $path = '', $secure = false, $httponly = false) {
	$domain = ($path == '') ? dev_domain() : substr($path, 0, strpos($path, '/'));
	$dir = ($path == '') ? '/' : substr($path, strpos($path, '/'), strlen($path));
	$cookiedie = ($expire > 0) ? time()+(int)$expire : (int)$expire; //expire in one hour
	$cookiesecure = $secure;
	//return setcookie ($var, $value, $cookiedie, $dir, $domain, $cookiesecure, $httponly);	
	return setcookie ($var, $value, $cookiedie, $dir, $domain, $cookiesecure);	
}

function dev_clear_cookies() {
	foreach ($_COOKIE as $a=>$b) {
		if (isset($_COOKIE[$a]) && !dev_set_cookie($a, false)) unset($_COOKIE[$a]);
	}
}

function dev_set_session($var, $value = '', $expire = '3600', $path = '', $secure = false, $httponly = false) {
	if (session_id() == '') {
		$domain = ($path == '') ? dev_domain() : substr($path, 0, strpos($path, '/'));
		$dir = ($path == '') ? '/' : substr($path, strpos($path, '/'), strlen($path));
		$cookiedie = ($expire > 0) ? time()+(int)$expire : ''; //expire in one hour
		$cookiesecure = $secure;
		
		session_set_cookie_params($cookiedie, $dir, $domain, $cookiesecure);
		session_start();
	}
	
	$status = ($_SESSION[$var] = $value) ? true : false;
	
	return $status;
}

function dev_clear_session() {
	foreach ($_SESSION as $a=>$b) {
		if (isset($_SESSION[$a])) $_SESSION[$a] = null;
	}
	session_destroy();
}

function dev_assoc_to_cookies($assoc, $expire = '3600', $path = '', $secure = false, $httponly = false) {
	if (dev_is_assoc($assoc)) foreach ($assoc as $a=>$b) dev_set_cookie($a, $b, $expire, $path, $secure, $httponly);
}

function dev_assoc_to_session($assoc, $expire = '3600', $path = '', $secure = false, $httponly = false) {
	if (dev_is_assoc($assoc)) foreach ($assoc as $a=>$b) dev_set_session($a, $b, $expire, $path, $secure, $httponly);
}

function dev_socket($host = '', $port = '', $url = '', $method = 'GET', $info = '') {
	$data = '';
	
	if ($method == '') $method = 'GET';
	if ($port == '') $port = '80';
	$cmd = '';
	$sock = fsockopen($host, $port, $errno, $errstr, 30);
	$method = strtolower($method);
	if (!$sock) {
		$data = "error: <br />\n";
		$data .= "$errstr ($errno)<br />\n";
	} elseif ($method == 'get') {
		//$request = urlencode($request);
		$request .= '/' . $url . '?';
		$request .= urlencode($info);
		$request .= "\r\nHost: " . $host . "\r\n" . 
		"User-Agent: Dev-Elation\r\n" . 
		"Connection: Close\r\n" . 
		"Content-Length: 0\r\n";
		$cmd = "GET $request HTTP/1.0\r\nHost: $host\r\n\r\n";
	} elseif ($method == 'post') {
		
		
	} elseif ($method == 'put') {
	
	
	} else {
		$data = "No valid data submission type specified.\n";
	}
	if ($cmd != '') {
		fputs($sock, $cmd);
	
		while (!feof($sock)) {
			$data .= fgets($sock, 1024);
		}
	}
	
	fclose($sock); 
	
	return $data;
}

function dev_url_exists($url){
	if(stristr($url, "http://")) {
		$url = str_replace("http://", "", $url);
		$fp = @fsockopen($url, 80);
		if($fp === false) return false;
		return true;
	} else {
		return false;
	}
}
?>
