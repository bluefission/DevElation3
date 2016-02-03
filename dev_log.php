<?php
//dev_log.php
//PHP Logger
function dev_write_log($message = '', $log_file = '') {
	$status = dev_save_file($log_file, $message, 'a');
	return $status;
}

function dev_system_log($message = '', $type = 'messages', $log_dir = '', $alert = false, $email = '') {
	if (dev_not_null($message)) {
		if ($alert && dev_not_null($email)) {
			$dest = $email;
			$type = 1;
		} elseif (dev_not_null($type)) {
			$dest = $log_dir . $type;
			$type = 3;
		} else {
			$dest = '';
			$type = 0;
		}
		$status = error_log ($message, $type, $dest);
	} else {
		$message = "Error generated from site with no message\n";
		$type = 0;
		$status = error_log ($message, $type);
	}
	
	return $status;
}

function dev_create_log($message = '', $type = 'messages', $log_dir = '', $alert = false, $email = '', $force = false) {
	$status = false;
	
	if ($message != '') {
	
		$date = dev_datestamp();
		$time = dev_get_time();
		$type = str_replace(' ', '_', $type);

		$log_dir = ($log_dir == '') ? ((is_defined('LOG_DIR') && LOG_DIR != '') ? LOG_DIR : getcwd() . '/logs/') : $log_dir;
			
		$log_file = "$log_dir" . "$type_$date.log";
		
		$message = "$time: $message\n";
		
		if ($alert) {
			$email = ($email == '') ? ((is_defined('ADMIN_EMAIL') && ADMIN_EMAIL != '') ? ADMIN_EMAIL : $_SERVER['SERVER_ADMIN']) : $email;
			//$send = dev_send_email($rcpt = '', "", "Site Alert ($type)", $message);
			$send = dev_email_admin_alert( $message, "Site Alert ($type)", "", $email );
			$message .= "$send\n";
		}
		
		$message .= "---\n";
		$status = dev_write_log($message, $log_file);
	}
	
	if ($status === false && $force) $status = dev_system_log($message, $type, $log_dir, $alert, $email);
	
	return $status;
}

function dev_view_log($type = 'messages', $date = '', $log_dir = '') {
	$date = dev_datestamp();
	$type = str_replace(' ', '_', $type);
	$log_dir = ($log_dir == '') ? ((is_defined('LOG_DIR') && LOG_DIR != '') ? LOG_DIR : getcwd() . '/logs/') : $log_dir;
	
	$log_file = "$log_dir" . "$type_$date.log";
	
	$log = dev_view_file($log_file);
	
	$output = "<pre>$log</pre>";
	
	return $output;
}

function dev_read_log($file) {
	if (file_exists($file)) {
		$log = file($file);
		return $log;
	} else {
		$status = "Failed to open log file. File cannot be found.\n";
	}
	
	return $status;
}

function dev_read_log_r($file, $delimiter = '') {
	if (file_exists($file)) {
		$delimiter = (dev_is_null($delimiter)) ? "\t" : $delimiter;
		$log = file($file);
		$line_r = array();
		foreach ($log as $a) {
			$line_r[] = explode($a, $delimiter);
		}
		return $line_r;
	} else {
		$status = "Failed to open log file. File cannot be found.\n";
	}
	
	return $status;
}

function dev_write_log_r($file, $data, $delimiter = '') {
	if (is_array($file)) {
		$delimiter = (dev_is_null($delimiter)) ? "\t" : $delimiter;
		array_walk($data, 'dev_implode_ref', "\t");
		$status = dev_save_file($file, $data);
	} else {
		$status = "Data not valid. Argument requires array.\n";
	}
	return $status;
}
?>
