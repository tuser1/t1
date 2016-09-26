<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

use AppVvp\Validators\Validator;

/**
 * GLOBAL FUNCTIONS AND CONSTANTS EXISTING IN GLOBAL NAMESPACE
 * class-autoloading is performed here
 */

/**
 * *************************************************************************
 * Autoload function for include-as-needed Class declaration files
 * *************************************************************************
 *  * NOTE: PHP frameworks and the PSR-4:Autoloader specification use 
 * [classfile.php] rather than [classfile.class.php].
 */
function autoloadClasses($className)
{
//	echo '[', $className, ']<br />';

	// Map class include file using full classname with NS prefix [repl backslashes]
	$NsAndClassFlnam = str_replace('\\', '/', $className) . '.' . PG_FILEEXT;

	/**
	 * - 'require_once' not necessary; only loads classes once as needed
	 * - accounts for both [classfile.class.php] -AND- [classfile.php]
	 */
	$classFile = dirname(__DIR__) . '/' . $NsAndClassFlnam;
	if (is_file($classFile)) {
		require $classFile;
	} else {
		// AppVvp class not found - use 'vendor' libraries
		$classFile = dirname(__DIR__) . '/' . DN_VENDOR . '/' . $NsAndClassFlnam;
		require $classFile;
	}
}

/**
 *  Global convenience functions for SANITIZATION.
 */

// Sanitize HTML output
function hh($str)
{
	return htmlspecialchars($str);
}

// Sanitize JSON/JavaScript output
function jj($str)
{
//	return json_encode($str, JSON_PRETTY_PRINT); // debugging only - causes syntax errs
	return json_encode($str);
}

// Sanitize DYNAMIC CSS output - unique ftn name used for identifying DCSS
function cc($str)
{
	return htmlspecialchars($str);
}

// Sanitize URL output - unique ftn name used for identifying URL links
function uu($str)
{
	return htmlspecialchars($str);
}

// For URL path: rawurlencode - restore forward-slashes in path using str_replace
function ru($str)
{
	return str_replace('%2F', '/', rawurlencode($str));
}

// For GET parms: urlencode
function ur($str)
{
	return urlencode($str);
}

// ------------------------------------------------------------------------

spl_autoload_register('autoloadClasses');

define('PG_FILEEXT', 'php');
define('TMPL_FILEEXT', 'phtml');
define('DN_VENDOR', 'vendor');

// URLRW = Apache URL rewriting / mod_rewrite
define('URLRW_ENABLED', true);
//define('URLRW_ENABLED', false);

// QSDLM1 = query-string delimiter
if (URLRW_ENABLED) {
	define('QSDLM1', '?');
} else {
	define('QSDLM1', '&');
}
define('PG_QSKEY', 'pg');

// Save empty buffer size to later determine if buffering has started:
// !!! NOTE: Is NOT zero at startup !!!
// if (ob_get_length() > OB_START_LENGTH) {
//     // OUTPUT HAS STARTED ...
// }
define('OB_START_LENGTH', ob_get_length());


/*
 * Sanitize selected $_SERVER values
 * NOTE: FILTER_DEFAULT does NO filtering
 */

// ------------------------------------------------
define('SAFE_UU_ACTUAL_PHP_SELF', uu($_SERVER['PHP_SELF']));

// current-page filename ONLY, NO PATH
define('SAFE_UU_ACTUAL_PHP_SELF_FNAM_ONLY', basename(SAFE_UU_ACTUAL_PHP_SELF));

// current-page filename ONLY, NO PATH, w/ NO EXTENSION
if (isset($_GET[PG_QSKEY])) {
	define('URLRW_PGVAL', strtolower(Validator::cleanInput1($_GET[PG_QSKEY])));
} else {
	define('URLRW_PGVAL', '');
}
if (URLRW_PGVAL !== '') {
	define('SAFE_UU_PHP_SELF_FNAM_NO_EXT', URLRW_PGVAL);
} else {
	define('SAFE_UU_PHP_SELF_FNAM_NO_EXT', basename(SAFE_UU_ACTUAL_PHP_SELF_FNAM_ONLY, 
			'.' . PG_FILEEXT));
}
//echo 'SAFE_UU_PHP_SELF_FNAM_NO_EXT: [', SAFE_UU_PHP_SELF_FNAM_NO_EXT, ']<br />';
// ------------------------------------------------

define('SAFE_RU_ACTUAL_PHP_SELF', ru($_SERVER['PHP_SELF']));

define('SAFE_RU_REQUEST_URI', ru($_SERVER['REQUEST_URI']));

define('RAW_SERVER_NAME', $_SERVER['SERVER_NAME']);
