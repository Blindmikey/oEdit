<?php
/*/////////////////////////////////////////////////////////////
 *
 *		DO NOT TOUCH THIS.
 *		CLOSE THIS DOCUMENT NOW!!!
 *
 */////////////////////////////////////////////////////////////
  
// For < PHP 5.3 compatability
	function rstrstr($haystack,$needle) {
        return substr($haystack, 0,strpos($haystack, $needle));
    }
  
// Define Essentials
	if ( !defined('ABSPATH') ) {
		define('ABSPATH', dirname(__FILE__) . '/');
	}
	if ( !defined('LOCPATH') ) {
		$path = $_SERVER['REQUEST_URI'];
		if (strstr($path, '?')) {
			define('LOCPATH', rstrstr($path, '?'));
		}
		else {
			define('LOCPATH', $path);
		}
	}
	
// Require Config
	require(ABSPATH . 'config.php');
	
// Require Start
	require(ABSPATH . 'core/start.php');