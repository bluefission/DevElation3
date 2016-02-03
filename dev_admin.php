<?php
//dev_admin.php
function dev_redirect($href = '', $request_r = '', $ssl = '', $snapshot = '') {
	$href = dev_href($href);
	$request = ($request_r) ? http_build_query($request_r) : "";
	$href = str_replace('http://', '', $href);
	$href = str_replace('https://', '', $href);
	$href = (($ssl == true) ? 'https' : 'http' ) . "://$href" . (($request != '') ? "?$request" : "");
	if ($snapshot != '') dev_set_cookie('href_snapshot', $snapshot);
	header("Location: $href");
}

function dev_get_remote_ip() {
	return $_SERVER['REMOTE_ADDR'];
}

function dev_block_ip($ip, $ip_file = '') {
	$status = "Blocking IP address $ip.\n";
	$status .= dev_save_file($ip_file, "$ip\n", 'a');
	return $status;
}

function dev_allow_ip($ip, $ip_file = '') {
	$status = "IP Allow Failed\n";
	$ip_list = dev_view_file($ip_file);
	$ip_r = explode("\n", $ip_list);
	$index = array_search($ip, $ip_r);
	if ($index !== false) {
		unset($ip_r[$index]);
		$ip_list = implode("\n", $ip_r);
		$status = dev_save_file($ip_file, $ip_list, 'w');
	} else {
		$status = "IP is already not blocked\n";
	}
	return $status;
}

function dev_ip_deny($ip = '', $redirect = '', $exit = false) {
	$blocked = false;
	$status = '';
	
	$ip = ($ip == '') ? dev_get_remote_ip() : $ip;
	
	$ip_list = dev_view_file($ip_file);
	$ip_r = explode("\n", $ip_list);
	$blocked = in_array($ip, $ip_r);
	if ($blocked) {
		$status = "Your IP address has been restricted from viewing this content.\nPlease contact the administrator.\n";
		if ($exit) exit($status);
		if ($redirect != '') dev_redirect($redirect);
	}
	
	return $status;
}

function dev_write_ip_log($file, $href = '', $ip = '') {
	if (file_exists($file)) {
		$line = '';
		$href = dev_href($href);
		$ip = (dev_is_null($ip)) ? dev_get_remote_ip() : $ip;
		$line = dev_read_log_r($file, "\t");
		if (is_array($line)) {
			$quit = false;
			while (list($a, $b) = $line || $quit) {
				if ($b[0] == $ip && $b[1] == $href) dev_toggle_bool($quit);
			}
			if (dev_time_difference($b[2], $timestamp, 'minutes') > 5) {
				$message = "$ip\t$href\t$timestamp\t$count\n";
				$status = dev_create_log($message, $file);
			} else {
				$line[$a][3]++;
				$status = dev_write_log_r($file, $line, "\t");
			}
		}
	} else {
		$status = "Failed to open log file. File could not be found.\n";
	}

	return $status;
}

function dev_check_ip_log($file, $href = '', $ip = '', $limit = '', $interval = '') {
	$line = dev_read_log_r($file, "\t");
	if (is_array($line)) {
		$line = '';
		$href = dev_href($href);
		$ip = (dev_is_null($ip)) ? dev_get_remote_ip() : $ip;
		$quit = false;
		while (list($a, $b) = $line || $quit) {
			if ($b[0] == $ip && $b[1] == $href) dev_toggle_bool($quit);
		}
		if (($b[3] >= $limit) && (dev_time_difference($b[2], $timestamp, 'minutes') <= $interval)) {
			dev_ip_deny($ip);
		}
	} else {
		$status = $line;
	}
	
	return $status;
}

function dev_config_from_array($array) {
	if (dev_is_assoc($array)) {
		foreach ($array as $a=>$b) if (!defined($a)) define($a, $b);
		$status = "Loaded configuration\n";
	} else {
		$status = "Invalid data input\n";
	}
	
	return $status;
}

function dev_config_from_db($table, $var_field = '', $value_field = '') {
	if (dev_is_null($var_field)) $var_field = 'var';
	if (dev_is_null($value_field)) $value_field = 'value';
	$active_fields = array($var_field, $value_field);
	
	$config = new DevModel($table);
	$config->clear();
	$config->setActiveFields($active_fields);
	$config->toggleActiveFieldsOn(true);
	
	$data = array();
	
	foreach ($config->getRecordSet() as $a) $data[$a[$var_field]] = $a[$value_field];
	
	$status = dev_config_from_array($data);
	
	return $status;
}

function dev_email_admin_alert($message = '', $subject = '', $from = '', $rcpt = '') {
	$message = (dev_not_null($message)) ? $message : "If you have recieved this email, then the admnistrative alert system on your website has been activated with no status message. Please check your log files.\n";
	$subject = (dev_not_null($subject)) ? $subject : "Automated Email Alert From Your Site!";
	$from = (dev_not_null($from)) ? $from : "admin@" . dev_domain();
	$rcpt = (dev_not_null($rcpt)) ? $rcpt : "admin@" . dev_domain();
	
	$status = dev_send_email($rcpt, $from, $subject, $message);
	return $status;
}

function dev_parachute(&$count, $max = '', $redirect = '', $log = false, $alert = false) {
	$max = (dev_not_null($rcpt)) ? $max : 400;
	if ($count >= $max) {
		$status = "Loop exceeded max count! Killing Process.\n";
		if ($alert) dev_email_admin_alert($status);
		if ($log) dev_create_log($status);
		if (dev_not_null($redirect)) dev_redirect($redirect, array('msg'=>$status));
		else exit("A script on this page began to loop out of control. Process has been killed. If you are viewing this message, please alert the administrator.\n");
	}
	$count++;
}
?>