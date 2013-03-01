<?php
/*
 *	oEdit Initialization
 */
if ($_SERVER["PHP_SELF"] == $_SERVER['REQUEST_URI']) die ("Tsk Tsk. Nice Try.");

// State Definitions
	if ( !defined('EDITPATH') ) {
		define('EDITPATH', ABSPATH . '../');
	}
	if ( !defined('COREPATH') ) {
		define('COREPATH', ABSPATH . 'core/');
	}
	if ( !defined('MODELPATH') ) {
		define('MODELPATH', COREPATH . 'models/');
	}
	if ( !defined('CONTROLLER') ) {
		define('CONTROLLER', COREPATH . 'controller/controller.class.php');
	}
	if ( !defined('VIEWPATH') ) {
		define('VIEWPATH', COREPATH . 'views/');
	}
	if ( !defined('IMAGESATH') ) {
		define('IMAGESPATH', LOCPATH . 'core/views/images/');
	}
	if ( !defined('JSPATH') ) {
		define('JSPATH', LOCPATH . 'core/views/js/');
	}
	if ( !defined('STYLESPATH') ) {
		define('STYLESPATH', LOCPATH . 'core/views/css/');
	}
	if ( !defined('STYLE') ) {
		define('STYLE', LOCPATH . 'core/views/css/style.css');
	}
	if ( !defined('THEMEPATH') ) {
		define('THEMEPATH', ABSPATH . 'content/themes/');
	}
	if ( !defined('CODEMIRRORLIB') ) {
		define('CODEMIRRORLIB', LOCPATH . 'core/views/codemirror/lib/');
	}
	if ( !defined('CODEMIRRORMODE') ) {
		define('CODEMIRRORMODE', LOCPATH . 'core/views/codemirror/mode/');
	}
	if ( !defined('CODEMIRRORTHEME') ) {
		define('CODEMIRRORTHEME', LOCPATH . 'core/views/codemirror/theme/');
	}
	if ( !defined('LOCALHOST') ) {
		if ( strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false || strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ) {
			define('LOCALHOST', true);
		}
		else {
			define('LOCALHOST', false);
		}
	}
	
// Determine Error Reporting
	if (isset($errors)) {
		if ($errors) {
			error_reporting(-1);
		}
		else {
			error_reporting(0);
		}
	}
	else {
		error_reporting(0);
	}
	
// Instantiate oEdit
	require(CONTROLLER);
	$oEdit = new oEdit();
	
// check post & get - store as var arrays
	$post_arr = $_POST;
	$get_arr = $_GET;
	
// check to see if file change ocurred.
	$oEdit->didFileChange($post_arr);

// check if is logged in 
	$oEdit->isLoggedIn(emailFilter());