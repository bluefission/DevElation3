<?php
//dev_email.php
//Email functions for dev library and framework

function dev_validate_email($address = '') {
	$address = dev_value_to_array($address);
	$p = '/^[a-z0-9!#$%&*+-=?^_`{|}~]+(\.[a-z0-9!#$%&*+-=?^_`{|}~]+)*';
	$p.= '@([-a-z0-9]+\.)+([a-z]{2,3}';
	$p.= '|com|net|edu|org|gov|mil|int|biz|pro|info|arpa|aero|coop|name|museum|au|jp|tv|us|nz|nt)$/ix';
	$pattern = $p;
	$passed = false;
	$i = 0;
	$count = count($address);
	if ( $count > 0 )
	{
		do {
			$match = preg_match($pattern, $address[$i]);
			$passed = ($match > 0 && $match !== false) ? true : false;
			$i++;
		} while ($passed === true && $i < $count);
	}
	
	return $passed;
}

function dev_use_valid_emails($address = '') {
	$address_r = dev_value_to_array($address);
	$valid_address_r = array();
	foreach ($address_r as $a) if (dev_validate_email($a)) $valid_address_r[] = dev_sanitize_email($a);
	return $valid_address_r;
}

function dev_sanitize_email( $field )
{
	//Remove line feeds
	$ret = str_replace("\r", "", $field);
	// Remove injected headers
	$find = array("/bcc\:/i",
	        "/Content\-Type\:/i",
	        "/Mime\-Type\:/i",
	        "/cc\:/i",
	        "/to\:/i");
	$ret = preg_replace($find, "", $ret);
	return $ret;
}

function dev_send_email($rcpt = '', $from = '', $subject = '', $message = '', $cc = '', $bcc = '', $html = false, $headers_r = '', $additional = '', $attachments = '') {
	$status = 'Failed to send mail. ';
	
	//Prepare addresses
	$rcpt = dev_value_to_array($rcpt);
	$cc = dev_value_to_array($cc);
	$bcc = dev_value_to_array($bcc);
	$from = dev_sanitize_email( $from );
	$subject = dev_sanitize_email( $subject );

	$eol = "\r\n";
	$mime_boundary=md5(time());
	
	//Build Headers
	$headers = array();
	if ($html) {
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: multipart/related; boundary=\"{$mime_boundary}\"";
	}
	/*
	if ($attachments ) 
	{
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-Type: multipart/mixed; boundary=\"{$mime_boundary}\"";
	}
	*/
	if ($from != '' && dev_validate_email($from)) {
		$headers[] = "From: {$from}";
   		$headers[] = "Reply-To: {$from}";
   		$headers[] = "Return-Path: {$from}";
   		$headers[] = "Message-ID: <".time()."-{$from}>";
	}
	if (count($cc) > 0) $headers[] = "Cc: " . implode(', ', dev_use_valid_emails($cc));
	if (count($bcc) > 0) $headers[] = "Bcc: " . implode(', ', dev_use_valid_emails($bcc));
	$headers = array_merge(dev_value_to_array($headers_r), $headers);
	$headers[] = "X-Mailer: PHP/" . phpversion();
	
	//Compile mail data
	$header_info = implode($eol, $headers);
	$message = dev_sanitize_email( $message );
	$message = wordwrap($message, 70);
	
	$body = "";
	
	if ($attachments !== false)
	{
		$attachments = dev_value_to_array($attachments);
		foreach( $attachments as $file )
		{
			if (is_file($file["file"]))
			{  
				if ( file_exists($file["file"]) )
				{
					$file_name = substr($file["file"], (strrpos($file["file"], "/")+1));
					
					$handle=fopen($file["file"], 'rb');
					$f_contents=fread($handle, filesize($file["file"]));
					$f_contents=chunk_split(base64_encode($f_contents));    //Encode The Data For Transition using base64_encode();
					fclose($handle);
					
					// Attach
					$body .= "--{$mime_boundary}{$eol}";
					$body .= "Content-Type: {$file["type"]}; name=\"{$file_name}\"{$eol}";
					$body .= "Content-Transfer-Encoding: base64{$eol}";
					$body .= "Content-Disposition: attachment; filename=\"{$file_name}\"{$eol}{$eol}"; // !! This line needs TWO end of lines !! IMPORTANT !!
					$body .= $f_contents.$eol.$eol;
				}
			}
		}
	}
	
	// Begin message text
	if( $html === true )
	{
	  // HTML Text
	  $body .= "Content-Type: multipart/alternative{$eol}";
	  $body .= "--".$mime_boundary.$eol;
	  $body .= "Content-Type: text/html; charset=iso-8859-1{$eol}";
	  $body .= "Content-Transfer-Encoding: 8bit{$eol}{$eol}";
	  $body .= $message.$eol.$eol;
	}	
	
	// Plain Text
	if( $html === true || !is_array( $attachments ) )
	{
		$body .= "--".$mime_boundary.$eol;
		$body .= "Content-Type: text/plain; charset=iso-8859-1{$eol}";
		$body .= "Content-Transfer-Encoding: 8bit{$eol}{$eol}";
	}
	$body .= strip_tags(dev_br2nl( $message )).$eol.$eol;
	
	// Body end
	if( $html === true || !is_array( $attachments ) )
	{
		$body .= "--{$mime_boundary}--{$eol}{$eol}";  // finish with two eol's for better security. see Injection.
	}
  
	
	// the INI lines are to force the From Address to be used
	ini_set( "sendmail_from", $from ); 
	
	if (count($rcpt) <= 0) {
		$status .= "The send to address is empty.\n";
	} elseif (!dev_validate_email($rcpt)) {
		$status .= "Email address '" . implode(', ', $rcpt) . "' is invalid.\n";
	} elseif ($subject == '') {
		$status .= "Subject line is empty.\n";
	} elseif ($message == '') {
		$status .= "Message is empty.\n";
	} elseif (count($cc) > 0 && !dev_validate_email($cc)) {
		$status .= "Invalid address in the CC line\n";
	} elseif (count($bcc) > 0 && !dev_validate_email($bcc)) {
		$status .= "Invalid address in the BCC line\n";
	} elseif (mail ( implode(', ', dev_use_valid_emails($rcpt)), $subject, $body, $header_info, $additional )) {
		$status = "Mail delivered successfully\n";
	}
	ini_restore( "sendmail_from" );
	return $status;
}

function dev_email_form($href = '', $to = '', $additional_fields = '') {
	$href = dev_href($href);
	$additional_fields = dev_value_to_array($additional_fields);
	
	$output = '';
	$output .= dev_draw_form() . 
	dev_draw_form_field((($to == '') ? 'text' : 'hidden'), 'rcpt', 'To', '') .
	dev_draw_form_field('text', 'subject', 'Subject', '');
	foreach ($additional_fields as $a=>$b) {
		$output .= dev_draw_form_field('text', $a, $b, '');
	}
	$output .= dev_draw_form_field('textarea', 'message', 'Message', '') .
	dev_draw_form_field('submit', 'submit', 'Send', 'Send') .
	dev_draw_form_field('reset', 'reset', 'Reset', 'Reset') .
	dev_close_form();
	
	return $output;
}
?>