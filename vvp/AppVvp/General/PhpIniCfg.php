<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\FilNams;

/**
 * Programmatically configure php.ini file
 */
class PhpIniCfg
{
	const SD_DFLT = 'DEV1'; // default server def
	const DEV1 = 'DEV1';
	const PROD1 = 'PROD1';

	/*
	-----------------
	RUNTIME CONFIGURATION / PHP.INI OVERRIDES:
	From php.ini:
	Expressions in the INI file are limited to bitwise operators and parentheses:
		|  bitwise OR
		^  bitwise XOR
		&  bitwise AND
		~  bitwise NOT
		!  boolean NOT
	-----------------
	; Default Value: E_ALL & ~E_NOTICE
	; Development Value: E_ALL | E_STRICT     => intval: 32767
	; Production Value: E_ALL & ~E_DEPRECATED => intval: 22527
	  'error_reporting'
	----------------------
	; Default Value: On
	; Development Value: On
	; Production Value: Off
	  'display_errors'
	----------------------
	; Default Value: Off
	; Development Value: On
	; Production Value: Off
	  'display_startup_errors'
	----------------------
	; Default Value: Off
	; Development Value: On
	; Production Value: On
	  'log_errors'
	----------------------
	; Default Value: Off
	; Development Value: On
	; Production Value: Off
	  'track_errors'
	----------------------
	; Default Value: On
	; Development Value: On
	; Production value: Off
	  'html_errors'
	----------------------
	*/

	const PI_ON = 1;
	const PI_OFF = 0;

	private static $displayRuntimeIniVals;
	private static $dspAry = array();
	private static $dspIdx = -1;

	private static $piSrvrDef = '';


	public static function setPhpIni($piSrvrDef, $displayRuntimeIniVals = false)
	{
		if (empty($piSrvrDef)) {
			if (RAW_SERVER_NAME === SysMain::LOCALHOST) {
				self::$piSrvrDef = self::DEV1;
			} else {
				self::$piSrvrDef = self::PROD1;
			}
		} else {
			self::$piSrvrDef = $piSrvrDef;
		}
		self::$displayRuntimeIniVals = $displayRuntimeIniVals;
		if (self::$displayRuntimeIniVals) {
			self::$dspAry[++self::$dspIdx] = '*** RUNTIME php.ini server definition: [' . 
				self::$piSrvrDef . '] ***';
		}
		switch(self::$piSrvrDef) {
		case self::DEV1:
			self::setPhpIniVal('error_reporting', E_ALL | E_STRICT);
			self::setPhpIniVal('display_errors', 		self::PI_ON);
			self::setPhpIniVal('display_startup_errors', self::PI_ON);
			self::setPhpIniVal('log_errors', 			self::PI_ON);
			self::setPhpIniVal('track_errors', 			self::PI_ON);
			self::setPhpIniVal('html_errors', 			self::PI_ON);
			break;
		case self::PROD1:
			self::setPhpIniVal('error_reporting', E_ALL & ~E_DEPRECATED);
			self::setPhpIniVal('display_errors', 		self::PI_OFF);
			self::setPhpIniVal('display_startup_errors', self::PI_OFF);
			self::setPhpIniVal('log_errors', 			self::PI_ON);
			self::setPhpIniVal('track_errors', 			self::PI_OFF);
			self::setPhpIniVal('html_errors', 			self::PI_OFF);
			break;
		default:
			exit('<strong>[' . 'E0102' . '] UNDEFINED php INI server def: [' . self::$piSrvrDef . 
				']</strong><br />');
		}
		self::setPhpIniVal('error_log', FilNams::getErrorLogFnam());
		self::setPhpIniVal('session.save_path', FilNams::getAbsPathSessions());
		self::setPhpIniVal('session.gc_maxlifetime', AuthUser::SESSION_MAXLIFETIME_SECS);

		if (self::$displayRuntimeIniVals) {
			self::dspPhpIniVals();
		}		
	}

	private static function setPhpIniVal($key, $val)
	{
		$stat = ini_set($key, $val); // $stat is the original value in php.ini
  		// You canNOT use 'if (!$stat)' as ini_set SOMETIMES returns '' instead of FALSE

		if ($key != 'error_log') {
			if ($stat === false) {
				exit('<strong>[' . 'E0103' . '] FAIL on ini_set ftn - key: [' . $key . 
					'] - value: [' . $val . ']</strong><br />');
			}
		// 2014-04-01: ini_set('error_log', ...) returns 'false' in production 
		//  even when successful.  Use this approach instead:
		} elseif (ini_get('error_log' != $val)) {
			exit('<strong>[' . 'E0104' . '] FAIL on ini_set ftn - key: [' . $key . 
				'] - value: [' . $val . ']</strong><br />');
		}
		if (self::$displayRuntimeIniVals) {
			self::$dspAry[++self::$dspIdx] = 'RUNTIME php.ini: [' . $key. '] ' . 
				'[' . $val . ']';  // - php.ini FILE: [' . $stat . ']';
		}
	}

	public static function dspPhpIniVals()
	{
		foreach (self::$dspAry as $key => $dsp) {
			echo '[', hh($key), '] ', hh($dsp), '<br />', "\n";
			}
	}


	// GETTERS / SETTERS

	public static function getPiSrvrDef()
	{
		return self::$piSrvrDef;
	}

}
