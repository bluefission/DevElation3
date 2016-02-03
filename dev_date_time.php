<?php
//dev_date_time.php
function dev_get_date_info($sm = '', $sy = '', $timestamp = '') {
	$today;
	$count;
	$undefined;
	if ($timestamp == '') $timestamp = mktime();
	$today = getdate($timestamp);
	$timestamp2 = mktime ( 0, 0, 0, $today['mon'], 1, $today['year']);
	$first = getdate($timestamp2);
	$sd = $today['mday'];
	$tsd = 28;
	$numdays = cal_days_in_month(CAL_GREGORIAN, $today['mon'], $today['year']);
	
	$sm = (isset($sm) && is_numeric($sm)) ? $sm : '';
	$sy = (isset($sy) && is_numeric($sy)) ? $sy : '';
	
	if ($sm <= 12 && $sm >=0 && $sy != '') {
	     $jday = juliantojd($sm, $sd, $sy);
	     $timestamp = jdtounix($jday);
		$today = getdate($timestamp);
		$numdays = cal_days_in_month(CAL_GREGORIAN, $today['mon'], $today['year']);
		$timestamp2 = mktime ( 0, 0, 0, $today['mon'], 1, $today['year']);
		$first = getdate($timestamp2);
	}
	
	$last_month = $today['mon'] - 1;
	if ($last_month < 1) {
		$last_month = 11;
		$last_year = $today['year'] - 1;
	} else {
		$last_year = $today['year'];
	}
	
	$next_month = $today['mon'] + 1;
	if ($next_month >= 12) {
		$next_month = 1;
		$next_year = $today['year'] + 1;
	} else {
		$next_year = $today['year'];
	}

	$date = array();
	$date['day'] = $sd;
	$date['firstweekday'] = $first['wday'];
	$date['year'] = $today['year'];
	$date['lastyear'] = $last_year;
	$date['nextyear'] = $next_year;
	$date['month'] = $today['mon'];
	$date['lastmonth'] = $last_month;
	$date['nextmonth'] = $next_month;
	$date['daysinmonth'] = $numdays;
	
	return $date;
}

function dev_month_r($sm = '', $sy = '', $timestamp = '', $event_r = '') {
	$date = dev_get_date_info($sm, $sy, $timestamp);
	$event_r = dev_value_to_array($event_r);
	$month = array();
	$notes = array();
	for ($i=0, ($j=1 - $date['firstweekday']); ($i<5 || $j<=$date['daysinmonth']); $i++) {
		for ($k = 0; $k < 7; $k++, $j++) {
			if (in_array($j, $event_r) && dev_is_assoc($event_r)) {
				$note_r = array_keys($event_r, $j);
				$notes = implode(', ',$note_r);
			}
			$month[$i][$k] = (($i == 0 && $k < $date['firstweekday']) || $j > $date['daysinmonth']) ? ' ' : ((in_array($j, $event_r)) ? "<b>$j</b> ".(($notes)?" - $notes":'') : $j);
		}
	}
	
	return $month;
}

function dev_calendar($sm = '', $sy = '', $timestamp = '', $event_r = '') {
	$output = dev_content_box(dev_month_r($sm, $sy, $timestamp, $event_r), '', '', '', '', false, 0, 1);
	return $output;
}

function dev_get_time($full = false) {
	$format = ($full) ? 'r' : 'h:i:sa T';
	$time = date($format);
	return $time;
}

function dev_datestamp($datetime = false, $timestamp = '') {
	$format = ($datetime) ? 'Ymd_His' : 'Ymd';
	$date = date($format, $timestamp);
	return $date;
}

function dev_time_difference($time1, $time2, $interval = '') {
	if (dev_is_null($interval)) $interval = 'seconds';
	$a = strtotime($time1);
	$b = strtotime($time2);
	$difference = (($a > $b) ? ($a - $b) : ($b - $a));
	
	$div = 1;
	switch ($interval) {
	case 'years':
		$div *= 12;
	case 'months':
		$div *= 4;
	case 'weeks':
		$div *= 30;
	case 'days':
		$div *= 24;
	case 'hours':
		$div *= 60;
	case 'minutes':
		$div *= 60;
	default:
	case 'seconds':
		$div *= 1;
		break;
	}
	
	$output = ($differnce / $div);
	return $output;
}

function dev_is_date_string($string) {
	return preg_match('/^(\d{4}\-\d+\-\d+|\d+\/\d+\/\d{4})/', $string);
}
?>
