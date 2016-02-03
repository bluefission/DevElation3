<?php
//dev_config.php
if ( defined( "INCLUDE_BASE" ))
{
	$status = '';
	$config_r = array(
	'SITE_DOMAIN'=>dev_domain(),
	'SITE_LOG_DIR'=>'logs/',
	'SITE_ADMIN_EMAIL'=>'admin@' . dev_domain(),
	'PHP_DISPLAY_ERRORS'=>0,
	);
	//Connect to DB
	//$link = dev_dbconn("user", "pass", "db", "localhost");
	//Load dynamic config content
	//$status .= dev_config_from_db(DEV_CONFIG_TABLE, DEV_CONFIG_VAR_FIELD, DEV_CONFIG_VALUE_FIELD);
	$status .= dev_config_from_array($config_r);
	
	ini_set('display_errors', PHP_DISPLAY_ERRORS);
	
	session_start();
}
?>