<?php

/**~******************************************************************
 * Copyright 2015 Voila! Video Productions. All rights reserved.
 * Author: Kris Showman
 *********************************************************************
 */

/**
 * GLOBAL METHODS AND TWIDDLY-DIDDLIES
 */


define('PN_FILEEXT1', 'php');


/**
 *  Global convenience methods for SANITIZATION.
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

/*
 * Sanitize selected $_SERVER values
 * NOTE: FILTER_DEFAULT does NO filtering
 */

define('SAFE_UU_PHP_SELF', uu($_SERVER['PHP_SELF']));
// current-page filename ONLY, NO PATH
define('SAFE_UU_PHP_SELF_FNAM_ONLY', basename(SAFE_UU_PHP_SELF));
// current-page filename ONLY, NO PATH, w/ NO EXTENSION
define('SAFE_UU_PHP_SELF_FNAM_NO_EXT', basename(SAFE_UU_PHP_SELF_FNAM_ONLY, '.' . PN_FILEEXT1));
// ------------------------------
define('SAFE_RU_PHP_SELF', ru($_SERVER['PHP_SELF']));
// current-page filename ONLY, NO PATH
define('SAFE_RU_PHP_SELF_FNAM_ONLY', basename(SAFE_RU_PHP_SELF));
// current-page filename ONLY, NO PATH, w/ NO EXTENSION
define('SAFE_RU_PHP_SELF_FNAM_NO_EXT', basename(SAFE_RU_PHP_SELF_FNAM_ONLY, '.' . PN_FILEEXT1));
// ------------------------------
  
define('SAFE_RU_REQUEST_URI', ru($_SERVER['REQUEST_URI']));

define('RAW_SERVER_NAME', $_SERVER['SERVER_NAME']);
