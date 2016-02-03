<?php
ini_set('display_errors', 1);
if (!defined('INCLUDE_BASE')) define('INCLUDE_BASE', 'dev-elation/');
//dev-elation.php
//Dev-Elation web development function and tool library
///
//Settings
///////
include_once(INCLUDE_BASE . 'dev_settings.php');

///
//Functions
///////
include_once(INCLUDE_BASE . 'dev_general.php'); //General url, variable, and function management tools
include_once(INCLUDE_BASE . 'dev_db.php'); //Manages databases, database input preparation, and data management
include_once(INCLUDE_BASE . 'dev_form.php'); //Manages forms, and input management
include_once(INCLUDE_BASE . 'dev_html_out.php'); //Functions used to output tables, images, links, and html formatting to browser
include_once(INCLUDE_BASE . 'dev_display.php'); //Allows changes of views for listings and entry details
include_once(INCLUDE_BASE . 'dev_file_system.php'); //Tools used to view and manipulate files and directories on a server
include_once(INCLUDE_BASE . 'dev_email.php'); //Function library to send emails from a browser
include_once(INCLUDE_BASE . 'dev_data.php'); //Handles the sending and recieving of data between server and/or clients
include_once(INCLUDE_BASE . 'dev_date_time.php'); //Utilities for creating time stamps, calendars, and date information
include_once(INCLUDE_BASE . 'dev_log.php'); //Used to manage the creation, reading, and deletion of log files
include_once(INCLUDE_BASE . 'dev_admin.php'); //Tools for managing ip blocking, redirecting, and otherwise control movement through a site or web application

///
//MVC
///////
include_once(INCLUDE_BASE . 'dev_model.php'); //Develation Model Class
include_once(INCLUDE_BASE . 'dev_template.php'); //Manage View Templating
include_once(INCLUDE_BASE . 'dev_interface.php'); //Manage View Templating

///
//Classes
///////
include_once(INCLUDE_BASE . 'dev_xml.php'); //Class used to open, parse, and create XML documents
include_once(INCLUDE_BASE . 'dev_object.php'); //Top level object used to create object from database models
include_once(INCLUDE_BASE . 'dev_view.php'); //Class for building an interface and front end using dev_elation libary features
include_once(INCLUDE_BASE . 'dev_control.php'); //Class made to create, build, and output management control panels for the Dev-Elation library

///
//Configuration
///////
//Load dynamic config
include_once(INCLUDE_BASE . 'dev_config.php');
?>
