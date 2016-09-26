<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\FilNams;
use AppVvp\Pages\Page;
use AppVvp\Validators\Validator;
use SysMobile\SysMobile;

/**
 * TODO: [document]
 */
class SysMain
{
   // pause-seconds before auto-directing to main start-page from docroot/index page:
	const AUTODIRECT_MAIN = 0;
	const SV_1ST_PAG_HAS_BEEN_ACCESSED = '1stPageHasBeenAccessed';
	const SV_SESSION_TIMESTAMP = 'session_timestamp';

	const SV_JS_DISABLED = 'js_disabled';

	const LOCALHOST = 'localhost';

	const CLIENT_OS_WIN = 'Windows';
	const CLIENT_OS_MAC = 'Mac OS X';
	const CLIENT_OS_LIN = 'Linux';
	const CLIENT_OS_UNKNOWN = '(unknown OS)';

	const BROWSER_MSIE = 'MSIE';
	const BROWSER_CHROME = 'Chrome';
	const BROWSER_FIREFOX = 'Firefox';
	const BROWSER_UNKNOWN = '(unknown browser)';

	const LOGFILE_DLM = '|';

	private static $sessVarsSave = array();
	private static $clientRemoteHostNam;
	private static $clientOs;
	private static $nsBrowser;
	private static $isMobileBrowser;
	private static $browser;
	private static $browserVers = '';
	private static $msiePreVers9 = false;
	private static $internetServer;
	private static $pagNamActualWithArgs;
	private static $refererHost = '';



	public static function init()
	{
		// Force dates to be in PST timezone regardless of hoster/server timezone
		putenv("TZ=US/Pacific");

		// also use $_SERVER['REMOTE_HOST']
		self::$clientRemoteHostNam = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		self::setvalRefererHost();
		$httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
		self::$clientOs = self::setvalClientOs($httpUserAgent);
		self::setvalIsMobileBrowser($httpUserAgent);
		self::$browser = self::setvalBrowserInfo($httpUserAgent);
		if (self::$browser === self::BROWSER_MSIE && self::$browserVers < 9.0) {
			self::$msiePreVers9 = true;
		}
		self::serverVars();
		self::sessionVars();
		self::checkIfLoggingSession();
	}

	private static function sessionVars()
	{
		Validator::issetGetAddAry1('XDEBUG_SESSION_START');

		if (PgLinkFactory::getDescr1() !== '') {
			if (PgLinkFactory::isAdminPage()) {
				// admin-page: save the pagename as sessvar to redirect to later
				$_SESSION[Page::SV_ADMINPAG] = SAFE_UU_PHP_SELF_FNAM_NO_EXT;
				if (!isset($_SESSION[Page::SV_USERPAG])) {
					$_SESSION[Page::SV_USERPAG] = FilNams::PN_VIDSMY;
				}
			} else {
				$_SESSION[Page::SV_USERPAG] = SAFE_UU_PHP_SELF_FNAM_NO_EXT;
			}
		}
		if (Validator::issetGetAddAry1(self::SV_JS_DISABLED)) {
			$jsDisabled = trim($_GET[self::SV_JS_DISABLED]);
			if ($jsDisabled) {
				$_SESSION[self::SV_JS_DISABLED] = 1;
			} else {
				unset($_SESSION[self::SV_JS_DISABLED]);
			}
		}
		if (isset($_SESSION[self::SV_JS_DISABLED])) {
			PgMsgs::set('E0300', 'JAVASCRIPT HAS BEEN DISABLED IN YOUR BROWSER', 
						'', '', $dupeKeysOk = true);
		}
	}


	public static function saveSessionVar($key)
	{
		if (isset($_SESSION[$key])) {
			self::$sessVarsSave[$key] = $_SESSION[$key];
		}
	}

	public static function restorSessionVars()
	{
		foreach (self::$sessVarsSave as $sessKey => $sessVal) {
//			echo "RESTORing SESSION var: $sessKey - $sessVal" . '<br />';
			$_SESSION[$sessKey] = $sessVal;
			unset(self::$sessVarsSave[$sessKey]);
		}
	}


	private static function serverVars()
	{
		self::$pagNamActualWithArgs = basename(SAFE_RU_REQUEST_URI);

		if (RAW_SERVER_NAME === BusDefs::WEBNAME1_F1  || 
			RAW_SERVER_NAME === BusDefs::WEBNAME1_F1B) {
			self::$internetServer = true;
		} else {
			self::$internetServer = false;
		}
	}


	private static function setvalClientOs($httpUserAgent)
	{
		/* -------------------------------
		'Windows nt 6.3' return 'Windows 8.1';
		'Windows nt 6.2' return 'Windows 8';
		'Windows nt 6.1' return 'Windows 7';
		'Windows nt 6.0' return 'Windows Vista';
		'Windows nt 5.2' return 'Windows Server 2003/XP x64';
		'Windows nt 5.1' return 'Windows XP';
		'Windows xp'     return 'Windows XP';
		'Windows nt 5.0' return 'Windows 2000';
		'Windows me'     return 'Windows ME';
		'Win98'          return 'Windows 98';
		'Win95'          return 'Windows 95';
		'Win16'          return 'Windows 3.11';
		------------------------------- */
		// 'stristr' NOT case-sensitive
		if	   (stristr($httpUserAgent, 'Windows'))		{return self::CLIENT_OS_WIN;}

		elseif (stristr($httpUserAgent, 'Macintosh'))	{return self::CLIENT_OS_MAC;}
		elseif (stristr($httpUserAgent, 'mac_powerpc'))	{return 'Mac OS 9';}
		elseif (stristr($httpUserAgent, 'Android'))		{return 'Android';}
		elseif (stristr($httpUserAgent, 'Linux'))		{return self::CLIENT_OS_LIN;}
		elseif (stristr($httpUserAgent, 'Ubuntu'))		{return 'Ubuntu';}
		elseif (stristr($httpUserAgent, 'iPhone'))		{return 'iPhone';}
		elseif (stristr($httpUserAgent, 'iPod'))		{return 'iPod';}
		elseif (stristr($httpUserAgent, 'iPad'))		{return 'iPad';}
		elseif (stristr($httpUserAgent, 'BlackBerry'))	{return 'BlackBerry';}
		elseif (stristr($httpUserAgent, 'webos'))		{return 'Mobile';}
		else											{return self::CLIENT_OS_UNKNOWN;}
	}

	private static function setvalIsMobileBrowser($useragent)
	{
		self::$isMobileBrowser = SysMobile::browserIsMobile($useragent);
	}

	private static function setvalBrowserInfo($httpUserAgent)
	{
		if (stristr($httpUserAgent, self::BROWSER_MSIE)) {
			self::$nsBrowser = false;
			self::$browserVers = self::setvalMsieVers($httpUserAgent);
			return self::BROWSER_MSIE;
		}
		elseif (stristr($httpUserAgent, 'Trident/7.0; rv:11.0') ||        // MSIE vers 11.0
				stristr($httpUserAgent, 'Trident/7.0; Touch; rv:11.0')) { // MSIE vers 11.0 on Surface
			self::$nsBrowser = false;
			self::$browserVers = '11.0';
			return self::BROWSER_MSIE;
		}
		elseif (stristr($httpUserAgent, self::BROWSER_CHROME)) {
			self::$nsBrowser = true;
			self::$browserVers = self::setvalBrowser1Vers(self::BROWSER_CHROME, $httpUserAgent);
			return self::BROWSER_CHROME;
		}
		elseif (stristr($httpUserAgent, self::BROWSER_FIREFOX)) {
			self::$nsBrowser = true;
			self::$browserVers = self::setvalBrowser1Vers(self::BROWSER_FIREFOX, $httpUserAgent);
			return self::BROWSER_FIREFOX;
		}
		elseif (stristr($httpUserAgent, 'Safari'))	{self::$nsBrowser = true;  return 'Safari';}
		elseif (stristr($httpUserAgent, 'Opera'))	{self::$nsBrowser = false; return 'Opera';}
		elseif (stristr($httpUserAgent, 'Netscape')){self::$nsBrowser = true;  return 'Netscape';}
		elseif (stristr($httpUserAgent, 'Maxthon'))	{self::$nsBrowser = false; return 'Maxthon';}
		elseif (stristr($httpUserAgent,'Konqueror')){self::$nsBrowser = true;  return 'Konqueror';}
		elseif (stristr($httpUserAgent, 'Mobile'))	{self::$nsBrowser = true;  return 'Mobile Browser';}
		else										{self::$nsBrowser = false; return self::BROWSER_UNKNOWN;}
	}

	private static function setvalMsieVers($httpUserAgent)
	{
		preg_match('/MSIE (.*?);/', $httpUserAgent, $matchesAry);
//		if (count($matchesAry) < 2) { // == SAVE FOR MSIE VERS > 11.0  ==
//			preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*.\d{1,2})/', 
//					$httpUserAgent, $matchesAry);
//		}
		if (count($matchesAry) > 1) {
			return $matchesAry[1];
		} else {
			return ''; // ERROR or NOT MSIE
		}
	}

	private static function setvalBrowser1Vers($browser, $httpUserAgent)
	{
		// Examples:
		//  'Chrome/1.0.154.59'	[found returns] '1.0'
		//  'Firefox/34.0'		[found returns] '34.0'
		preg_match("/$browser\/(\d{1,2}.\d{1,2})/", $httpUserAgent, $matchesAry);
		if (count($matchesAry) > 1) {
			return $matchesAry[1];
		} else {
			return ''; // ERROR or NOT correct browser
		}
	}

	public static function checkIfLoggingSession()
	{
		if (!isset($_SESSION[self::SV_1ST_PAG_HAS_BEEN_ACCESSED])) {
			$_SESSION[self::SV_1ST_PAG_HAS_BEEN_ACCESSED] = true;
			self::writeToLogfile(FilNams::getLogFileFnam());
		}
	}

	private static function writeToLogfile($logFl)
	{
		if (!empty(self::$browserVers)) {
			$browser = self::$browser . ' ' . self::$browserVers;
		} else {
			$browser = self::$browser;
		}
		$fh = fopen($logFl, 'a'); // File access = APPEND
		fwrite($fh, 
		//	date('Y-m-d H:i:s [h:i a]') . self::LOGFILE_DLM . 
			date('Y-m-d His h:ia') . self::LOGFILE_DLM . 
			self::$clientRemoteHostNam . self::LOGFILE_DLM . 
			$_SERVER['REMOTE_ADDR'] . self::LOGFILE_DLM . 
			self::$pagNamActualWithArgs . self::LOGFILE_DLM . 
			self::$clientOs . self::LOGFILE_DLM . 
			$browser . self::LOGFILE_DLM . 
			$_SERVER['HTTP_USER_AGENT'] . self::LOGFILE_DLM . 
			session_id() . 
			"\n");
		fclose($fh);
	}

	public static function dspServerKeys()
	{
		echo '<pre><xmp>';
		print_r($_SERVER);
		echo '</xmp></pre>';
	}

	public static function unsetStrictGlvar($var)
	//-------------------------------------------
	// Use this ftn to UNset a GLOBAL VAR and want to know if it existed before UNsetting.
	// UNsetting an already-unset GLOBAL var will NOT throw an exception !!
	// Check if GLOBAL variable is set before UNsetting
	// !!! VARNAME MUST BE PASSED AS A STRING IN QUOTES W/O PREPENDING THE DOLLAR-SIGN:
	//		if (unset_strict_glvar('foo'))
	//-------------------------------------------
	{
		if (isset($GLOBALS[$var])) {
			unset($GLOBALS[$var]);
			return true;
		} else {
			return false;
		}
	}

	/* TODO - FINISH; $arynam is unref'd
	public static function unsetStrictKey($arynam, $aryobj, $key)
	//-------------------------------------------
	// Use this ftn to UNset an ARRAY key and know if it existed before UNsetting.
	// In PHP, UNsetting a non-def'd key in any array will NOT throw an exception !!
	//   e.g. [ unset($_SESSION['undefdIdx']); ] does NOT gen err when KEY doesn't exist!
	// No need to validate $aryobj as the cmdline will catch it if var does not exist.
	// Example:  if (unset_strict_key('My array', $arynam, $key))
	//-------------------------------------------
	{
		if (isset($aryobj[$key])) {
			unset($aryobj[$key]);
			return true;
		} else {
			return false;
		}
	}
	*/

	private static function setvalRefererHost()
	{
		if (isset($_SERVER['HTTP_REFERER'])) {
			// !!! parse_url/PHP_URL_HOST will not work w/ port#s:
			//    e.g. http://localhost:81/index.php
//			self::$refererHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
			$tmp1 = explode('/', $_SERVER['HTTP_REFERER']);
			self::$refererHost = $tmp1[2];
		}
	}


	// GETTERS / SETTERS

	public static function getRefererHost()
	{
		return self::$refererHost;
	}

	public static function getInternetServer()
	{
		return self::$internetServer;
	}

	public static function getClientOs()
	{
		return self::$clientOs;
	}

	public static function isMobileBrowser()
	{
		return self::$isMobileBrowser;
	}

	public static function getBrowser()
	{
		return self::$browser;
	}

	public static function getBrowserVers()
	{
		return self::$browserVers;
	}

	public static function isMsiePreVers9()
	{
		return self::$msiePreVers9;
	}

	public static function isNsBrowser()
	{
		return self::$nsBrowser;
	}

}
