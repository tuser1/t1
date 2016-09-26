<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\App;
use AppVvp\Db\DbAccess;
use AppVvp\FilNams;

/**
 * Custom error-handling
 * 1) TO SET: set_error_handler('ErrHandler::customErrHandler');
 * 2) [trigger_db_err1] is special ftn for DB error handling
 */
class ErrHandler
{
	private static $showError = 1;
	private static $errLevelDescr = array(
		1     => 'E_ERROR', // Fatal
		2     => 'E_WARNING',
		4     => 'E_PARSE',
		8     => 'E_NOTICE',
		16    => 'E_CORE_ERROR',
		32    => 'E_CORE_WARNING',
		64    => 'E_COMPILE_ERROR',
		128   => 'E_COMPILE_WARNING',
		256   => 'E_USER_ERROR',
		512   => 'E_USER_WARNING',
		1024  => 'E_USER_NOTICE',
		2048  => 'E_STRICT',
		4096  => 'E_RECOVERABLE_ERROR',
		8192  => 'E_DEPRECATED',
		16384 => 'E_USER_DEPRECATED',
		);



	/** This method can be used at shutdown/exit to catch ERROR not handled by custom error_handler
	 */
	public static function checkLastError() {
		$errAry = error_get_last();
		if ($errAry['type'] !== NULL) { // is set to NULL if NO errors detected
			if (array_key_exists('file', $errAry)) {
				$fileLine = 'File: ' . $errAry['file'] . ' on line ' . $errAry['line'] . "\n";
			} else {
				$fileLine = '';
			}
			// !!! Must precede msg w/ at least ONE "\n" as show_err ignores 1st line:
			self::showErr("\n" . 'An *ERROR* has occurred that was not ' . 
				'caught by the custom error_handler - ' . 
				'*** SEE ERROR LOGFILE FOR GREATER DETAIL ***' . "\n\n" . 
				'Msg: "' . $errAry['message'] . '"' . "\n" . $fileLine . 
				'Error type: ' . self::$errLevelDescr[$errAry['type']] . "\n" . 
						'', $errorWasHandled = false);
		}
	}


	//** '$usrErrtyp' CURRENTLY UNUSED [e.g. E_USER_ERROR, ... ] **
	public static function triggerDbErr1($usrErrnum, $usrErrMsg, $usrErrtyp, $usrDbo)
	{
		// *** DO NOT USE 'trigger_error' HERE - $errfile shows as 
		//   'custom_err_handler.php' instead of actual errfile
		PgMsgs::set($usrErrnum, 'DATABASE ERROR - PLEASE CONTACT SITE ADMINISTRATOR.');
		$ftnTrace = debug_backtrace();	// backtrace of the ftn calls
		$btIdx = 0;
		// use "d-M-Y H:i:s" to match PHP's dflt log_errors date format
		$msglinePrefix = "\n[" . date("d-M-Y H:i:s") . "] [{$usrErrnum}] ";
		self::errMsgHdr($errMsg);
		if (array_key_exists('file', $ftnTrace[$btIdx])) {
			$errMsg .= "\nLine# {$ftnTrace[$btIdx]['line']} in {$ftnTrace[$btIdx]['file']}.";
		}

		// PDO's DB Error Info Array:
		//   0 SQLSTATE error code (5 char alphanum ID def'd in the ANSI SQL standard
		//   1 Driver-specific error code
		//   2 Driver-specific error message
		if ($getDbErrInfo = $usrDbo->getDbErrorInfo()) {
			if ($getDbErrInfo[2] !== NULL) {
				$errMsg .= "\nDB SQLSTATE => " . $getDbErrInfo[0];
				$errMsg .= "\nDB MySQL CD => " . $getDbErrInfo[1];
				$errMsg .= "\nDB MySQL MSG: \"" . $getDbErrInfo[2] . '"';
			}
		}
		if ($getStmtErrInfo = $usrDbo->getStmtErrorInfo()) {
			if ($getStmtErrInfo[2] !== NULL) {
				$errMsg .= "\nStmt SQLSTATE => " . $getStmtErrInfo[0];
				$errMsg .= "\nStmt MySQL CD => " . $getStmtErrInfo[1];
				$errMsg .= "\nStmt MySQL MSG: \"" . $getStmtErrInfo[2] . '"';
			}
		}

		$errMsg .= "\nApp MSG: \"$usrErrMsg" . '"';
		$errMsg = preg_replace('/\n/', $msglinePrefix, $errMsg);
		self::showErr($errMsg);
	}


	public static function customErrHandler($errno, $errstr, $errfile, 
			$errline, $errcontext)
	{
		$E_USER_ERR = false;
		switch ($errno)
		{
		case E_USER_ERROR:
			$E_USER_ERR = true;
			break;
		case E_USER_WARNING:
			$E_USER_ERR = true;
			break;
		case E_USER_NOTICE:
			$E_USER_ERR = true;
			break;
		}
		if (array_key_exists($errno, self::$errLevelDescr)) {
			$errdescr = self::$errLevelDescr[$errno]; // e.g. 'E_USER_ERROR', etc
		} else {
			$errdescr = '(UNHANDLED)';
		}
		// match PHP's dflt log_errors date format
		$msglinePrefix = "\n[" . date("d-M-Y H:i:s") . "][{$errdescr}] ";
		self::errMsgHdr($errMsg);
		$errMsg .= "\nLine# {$errline} in {$errfile}.";
		$errMsg .= "\nMsg: \"{$errstr}\".";
		$errMsg .= self::backTrace($errcontext);
		$errMsg = preg_replace('/\n/', $msglinePrefix, $errMsg);
		self::showErr($errMsg);
	}



	private static function errMsgHdr(&$errMsg)
	{
		$errMsg = '-----------------------------------------------------------';
		$errMsg .= "\n" . SAFE_RU_CURR_PAGE_URL . " == Client IP address: {$_SERVER['REMOTE_ADDR']}";
		$errMsg .= "\n" . 'SESSION ID: ' . session_id();
	}

	private static function showErr($errMsg, $errorWasHandled = true)
	{
		if (ini_get('log_errors') && $errorWasHandled) {
			error_log($errMsg); // if error was NOT handled then it has already been logged
		}
		if (!AuthUser::isAdminUser()) {
			AuthUser::sessionDestroyRegen1();
//			if (DbAccess::tablesLocked()) {
//				DbAccess::unlockTables();
//			}
			DbAccess::close_conn();
		}
		if (self::getShowError()) {
			ob_end_clean(); // erase all output generated so far
			if (PgLinkFactory::isCurrentPage(FilNams::PN_ERRMSG) 
			||  !PgLinkFactory::pgLinkCreated(FilNams::PN_ERRMSG)) {
//					|| !is_object(App::$page)) {
				PgMsgs::renderPageMsgs();
				echo '<h3>An error has occurred</h3>';
				if (ini_get('display_errors')) {
					echo '<pre>', hh($errMsg), '</pre>';
				}
				exit();
			} else {
				$token = CsrfToken::genToken();
				$errMsgFnam = FilNams::getErrorMsgFnam($token);
				$fh = fopen($errMsgFnam, 'w'); // Create NEW errMsg file [filename + token]
				fwrite($fh, $errMsg);
				fclose($fh);
				if (ini_get('display_errors')) {
					App::redirect(FilNams::getPgUrl(FilNams::PN_ERRMSG) . 
							'?token=' . ur($token));
				} else {
					App::redirect(FilNams::getPgUrl(FilNams::PN_ERRMSG));
				}
			}
		}
	}

	public static function renderErrMsg1($token)
	{
		if (ini_get('display_errors')) {
			echo "\n" . '<p id="pagErrMsgTxt">' . "\n";
			$errMsgFnam = FilNams::getErrorMsgFnam($token);
			if (is_file($errMsgFnam)) {
				$fileAry = file($errMsgFnam); // read entire file into an array
				foreach ($fileAry as $lineNum => $msgLine) {
					if ($lineNum > 0) { // skip 1st rec
						echo hh($msgLine), '<br />', "\n";
					}
				}
			} else {
				echo '***ERROR*** FILE NOT FOUND: \'', hh($errMsgFnam), '<br />' . "\n";
			}
			echo '</p>' . "\n";
		}
	}

	private static function backTrace($errcontext)
	{
		$ftnCalls = "\nFTN backtrace:";
		$ftnTrace = debug_backtrace();	// backtrace of the ftn calls
		// Begin with  idx [2] -- bypass this ftn [0] and custom_err_handler() [1]
		for ($btIdx = 2; $btIdx < count($ftnTrace); $btIdx++)
		{
			$ftnCallnum = $btIdx - 2;
			$ftnCalls .= "\n{$ftnCallnum}) {$ftnTrace[$btIdx]['function']} ";
			if (array_key_exists('file', $ftnTrace[$btIdx])) {
				$ftnCalls .= "(line {$ftnTrace[$btIdx]['line']} in {$ftnTrace[$btIdx]['file']})";
			}
		}
		/**
		 * *NOTE* Removed for now because of possibility of writing PWs, etc to 
		 *   logfile and/or sessionvar.
		 *=== Get var info for ftn with the error ===
		 *$ftnCalls .= "\nVariables in static function {$ftnTrace[2]["function"]}():";
		 *foreach($errcontext as $varname => $varval)
		 *{
		 *	if (!empty($varval)) {
		 *	//	$ftnCalls .= "\n  {$varname} is {$varval}"; // unable to convert $varval to string
		 *	//	$ftnCalls .= "\n  {$varname} is {??????}";
		 *		if (!is_object($varval)) {
		 *			$ftnCalls .= "\n => \${$varname} = '" . $varval . "'";
		 *		} else {
		 *			$ftnCalls .= "\n => \${$varname} = '[object]'";
		 *		}
		 *	} else {
		 *		$ftnCalls .= "\n => \${$varname} = NULL";
		 *	}
		 *}
		 */
		return $ftnCalls;
	}

	public static function getUploadErrMsg($uploadErrcode)
    {
		switch ($uploadErrcode) {
			case UPLOAD_ERR_INI_SIZE:	// 1
                $errMsg = 'Uploaded file exceeds upload_max_filesize directive in php.ini';
                break;
			case UPLOAD_ERR_FORM_SIZE:	// 2
                $errMsg = 'Uploaded file exceeds MAX_FILE_SIZE directive in the HTML form';
                break;
			case UPLOAD_ERR_PARTIAL:	// 3
                $errMsg = 'The uploaded file was only partially uploaded';
                break;
			case UPLOAD_ERR_NO_FILE:	// 4
                $errMsg = 'NO FILE WAS UPLOADED';
                break;
			case UPLOAD_ERR_NO_TMP_DIR:	// 6
                $errMsg = 'Missing a temporary folder';
                break;
			case UPLOAD_ERR_CANT_WRITE:	// 7
                $errMsg = 'Failed to write file to disk';
                break;
			case UPLOAD_ERR_EXTENSION:	// 8
                $errMsg = 'A PHP extension STOPPED THE FILE UPLOAD';
                break;
            default:
                $errMsg = 'Unknown upload error';
                break;
        }
		return $errMsg;
	}



	// GETTERS / SETTERS

	public static function getShowError()
	{
		return self::$showError;
	}

	public static function setShowError($bool)
	{
		self::$showError = $bool;
	}

	//----------- ERROR-HANDLING TEST FUNCTIONS --------------------
	// undefined variable '$TEST_errdsp'
	public static function TEST_errdsp() {$fooBar = $TEST_errdsp;}

	// call method with '::' - missing 'static' in declaration
	public        function TEST_strict_stds() {}

	// 'split' cmd is deprecated
	public static function TEST_deprecated() {
		$fooBarAry = split('[/]', 'abc/def');
		return $fooBarAry;
	}
	//--------------------------------------------------------------
}
