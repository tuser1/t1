<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\App;
use AppVvp\Db\DbAccess;
use AppVvp\FilNams;
use AppVvp\Validators\Validator;

/**
 * Authentication
 */
class AuthUser
{
	const SV_LOGIN_USRNAM = 'login_usrnam';
	const SV_LOGIN_LAST_REQUEST = 'login_last_request';
	const SV_LOGIN_SESSID_CREATED = 'login_sessid_created';
	const SV_LOGIN_IP = 'login_IP';
	const SV_LOGIN_USR_AGENT = 'login_usr_agent';

	const SV_LOGIN_FIRSTNAM = 'login_firstnam';
	const SV_LOGIN_REFERER = 'login_referer';
	const SV_LOGOUT_TARGETURL = 'logout_targeturl';

	// NOTE: FTN 'time()' returns current time in SECONDS

	// When logged in: max time between REQUESTS in SECONDS
	const LOGIN_REQUEST_LIMIT_SECS = 900; // 15 min

	// When logged in: max life for SESSION-ID in SECONDS - regen ID if exceeded
	// Used for avoiding session attacks
	const LOGIN_SESSID_LIMIT_SECS = 420; // 7 min

	// For php.ini 'session.gc_maxlifetime'
	const SESSION_MAXLIFETIME_SECS = 21600; // 6 hrs

	// Number of login attempts allowed before throttling
	const FAILED_LOGINS_ALLOWED = 10;

	// Number of seconds to throttle login attempts
	const THROTTLE_DELAY_SECS = 300; // 5 min

	private static $loginStatusMsg1;
	private static $loginLinkText;
	private static $loginLinkUrl;
	private static $newUserLinkText = '';
	private static $newUserLinkUrl = '';

	private static $authenticated;
	private static $adminUserFlag = 0;

	private static $failedUserLogin = null;
	private static $remainingThrottleSecs;

	private static $userSubPath = '';



	public static function init()
	{
		if (PgLinkFactory::isSecurePage()) {
			// This is a SECURE PAGE: If unauth'd user -or- non-login attempts to go 
			// directly to a secure URL, redirect them to login page.
			$securePageRedirect = FilNams::getPgUrl(FilNams::PN_LOGIN);
		} else {
			$securePageRedirect = '';
		}
		self::chkLoginAndAuthentication($securePageRedirect);
	}

	private static function chkLoginAndAuthentication($securePageRedirect)
	{
		if	(self::authenticateSession($securePageRedirect)) {
			if (PgLinkFactory::isCurrentPage(FilNams::PN_LOGIN) ||
				PgLinkFactory::isCurrentPage(FilNams::PN_NEWUSER)) {
				// Already logged-in: EXIT/REDIRECT ...
				App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY));
			}
			self::$loginStatusMsg1 = 'Hello ' . $_SESSION[self::SV_LOGIN_FIRSTNAM] . '!' . 
				" You are logged in as '" . $_SESSION[self::SV_LOGIN_USRNAM] . "'";
			self::$loginLinkText = 'Logout';
			self::$loginLinkUrl = FilNams::getPgUrl(FilNams::PN_LOGOUT) . QSDLM1 . 
					self::SV_LOGOUT_TARGETURL . '=' . ur(SAFE_UU_PHP_SELF_FNAM_NO_EXT);
			if (!empty($securePageRedirect)) { // This is a SECURE PAGE ...
				App::$dbMain->connect1();
				DbAccess::getUserInfo($_SESSION[self::SV_LOGIN_USRNAM]);
				if (PgLinkFactory::isAdminPage()) {
					// This is an ADMIN page.  If not an admin-user redirect them to
					// normal logged-in default page.
					if (!self::isAdminUser()) {
						App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY));
					}
				}
			}
		} else {
			self::$loginStatusMsg1 = 'You are not logged in';
			self::$loginLinkText = 'Login';
			self::$loginLinkUrl = FilNams::getPgUrl(FilNams::PN_LOGIN);
			self::$newUserLinkText = 'Register';
			self::$newUserLinkUrl = FilNams::getPgUrl(FilNams::PN_NEWUSER);
			self::setUserSubPath('');
			self::unsetLoginSessVars();
		}
	}

	public static function authenticated()
	{
		return self::$authenticated;
	}

	private static function authenticateSession($securePageRedirect)
	{
		self::$authenticated = false;
		if (!self::loggedIn()) {
			if (!empty($securePageRedirect)) {
				// Attempt to access secure-page when NOT logged-in.
				// Redirect them to login page [$securePageRedirect].
				self::sessionDestroyRegen1();
				$loginReferer = SAFE_RU_CURR_PAGE_URL;
//				TODO: TEST/FIX?:
//				if (!empty($_SERVER['QUERY_STRING'])) {
//					$loginReferer.= QSDLM1 . ur($_SERVER['QUERY_STRING']);
//				}
//				$_SESSION[self::SV_LOGIN_REFERER] = SAFE_RU_REQUEST_URI;
				$_SESSION[self::SV_LOGIN_REFERER] = $loginReferer;
				PgMsgs::set('E004', 'Secure page - you must login ...');
				App::redirect($securePageRedirect);
			} else {
				return false;
			}
		}
		self::sessionHijackChk();
		if (time() > ($_SESSION[self::SV_LOGIN_LAST_REQUEST] + 
				self::LOGIN_REQUEST_LIMIT_SECS)) {
			PgMsgs::set('E014', 
				'Login session has EXPIRED - you have been logged-out');
			self::sessionDestroyRegen1();
			if (!empty($securePageRedirect)) {
				App::redirect($securePageRedirect);
			} else {
				return false;
			}
		}
		if (time() > ($_SESSION[self::SV_LOGIN_SESSID_CREATED] + 
				self::LOGIN_SESSID_LIMIT_SECS)) {
			// Change current session's ID (invalidates old session ID)
			session_regenerate_id(true);
			$_SESSION[self::SV_LOGIN_SESSID_CREATED] = time();
		}	
		$_SESSION[self::SV_LOGIN_LAST_REQUEST] = time();
		return (self::$authenticated = true);
	}

	public static function loggedIn()
	{
		// Login check: returns true ONLY if ALL sessvars are set
		if (!isset($_SESSION[self::SV_LOGIN_USRNAM]))		  {return false;}
		if (!isset($_SESSION[self::SV_LOGIN_LAST_REQUEST]))	  {return false;}
		if (!isset($_SESSION[self::SV_LOGIN_SESSID_CREATED])) {return false;}
		if (!isset($_SESSION[self::SV_LOGIN_IP]))			  {return false;}
		if (!isset($_SESSION[self::SV_LOGIN_USR_AGENT]))	  {return false;}
		return true;
	}

	public static function userIsThrottled($usrNam)
	{
		self::$failedUserLogin = DbAccess::getFailedUserLogins($usrNam);
		if (self::$failedUserLogin && 
					self::$failedUserLogin['fail_count'] >= self::FAILED_LOGINS_ALLOWED) {
			// remaining seconds to throttle login
			self::$remainingThrottleSecs = 
					(self::$failedUserLogin['fail_time'] + self::THROTTLE_DELAY_SECS) - time();
			return self::$remainingThrottleSecs > 0; // Username is throttled if > zero
		} else {
			self::$remainingThrottleSecs = 0;
			return false;
		}
	}

	private static function sessionHijackChk()
	{
		if ($_SESSION[self::SV_LOGIN_IP] != $_SERVER['REMOTE_ADDR']) {
			self::sessionHijackAbort1('E005I', 
				'Possible sess hijack attempt - LOGIN IP / REMOTE ADDR mismatch: ' . 
				$_SESSION[self::SV_LOGIN_IP] . ' / ' . $_SERVER['REMOTE_ADDR']);
		}
		if ($_SESSION[self::SV_LOGIN_USR_AGENT] != $_SERVER['HTTP_USER_AGENT']) {
			self::sessionHijackAbort1('E005U', 
				'Possible sess hijack attempt - LOGIN USER AGENT mismatch');
		}
	}

	private static function sessionHijackAbort1($errKey, $errMsg)
	{
		// Possible session hijack attempt
		PgMsgs::set($errKey, 'UNauthorized login - you have been logged-out');
		trigger_error('[' . $errKey . '] ' . $errMsg, E_USER_ERROR);
	}

	public static function pageLogout()
	{
		// NOTE: '$urlTyp' is only used when pausing at logout to display
		//  info, e.g. when '$pauseSecs' is set to value > 0.
		$urlDflt = FilNams::getPagNamStart();
		if (Validator::issetGetAddAry1(self::SV_LOGOUT_TARGETURL)) {
			$urlPassed = Validator::cleanInput1($_GET[self::SV_LOGOUT_TARGETURL]);
			if (PgLinkFactory::isSecurePage($urlPassed)) {
				$urlTarget = $urlDflt;
				$urlTyp = 1;
			} else {
				if (PgLinkFactory::getDescr1($urlPassed)) {  //If valid menu url
					$urlTarget = $urlPassed;
					$urlTyp = 2;
				} else {
					$urlTarget = $urlDflt;
					$urlTyp = 3;
				}
			}
		} else {
			// $_GET[self::SV_LOGOUT_TARGETURL] was not set
			$urlTarget = $urlDflt;
			$urlTyp = 4;
		}
		//=================================
		Validator::validatePassedGetKeys1();
		//=================================

		// Is NOT logged-in.  Must be logged-in to logout.  REdirect ...
		if (!self::authenticated()) {
			App::redirect(FilNams::getPgUrl(FilNams::getPagNamStart()));
		}

		self::sessionDestroyRegen1();

		$pauseSecs = 0; // Normally set to 0 seconds
		if ($pauseSecs != 0) {
			echo 'In ftn [page_logout]<br /><br />';
			echo '     Passed URL arg: [', hh($urlPassed), ']<br />';
			echo 'Rendered target URL: [', uu(FilNams::getPgUrl($urlTarget)), ']<br />';
			echo '    Passed URL type: ', hh($urlTyp);
			App::redirect(FilNams::getPgUrl($urlTarget), $pauseSecs);
		} else {
			App::redirect(FilNams::getPgUrl($urlTarget));
		}
	}


	private static function unsetLoginSessVars()
	{	// Logout (unregister the login)
		unset($_SESSION[self::SV_LOGIN_USRNAM]);
		unset($_SESSION[self::SV_LOGIN_LAST_REQUEST]);
		unset($_SESSION[self::SV_LOGIN_SESSID_CREATED]);
		unset($_SESSION[self::SV_LOGIN_IP]);
		unset($_SESSION[self::SV_LOGIN_USR_AGENT]);
		// NOT required for authentication:
		unset($_SESSION[self::SV_LOGIN_FIRSTNAM]);
	}


	public static function sessionDestroyRegen1()
	{
		// -----------------------------------------------------
		// Destroy current session and regenerate new session ID
		// -----------------------------------------------------
		SysMain::saveSessionVar(Validator::SV_CFV_METHOD);
		SysMain::saveSessionVar(Validator::SV_ENFORCE_PW_RULES);

		PgMsgs::savePageMsgSessVars();

		self::destroyTheSession();
		session_start();
		session_regenerate_id();

		SysMain::restorSessionVars();
		PgMsgs::restorPageMsgSessVars();
	}

	public static function destroyTheSession()
	{
		// -----------------------------------------------------
		// Destroy current session
		// -----------------------------------------------------
		//***** FROM PHP MANUAL ************************
		$_SESSION = array();   // Unset all session vars
		if (ini_get("session.use_cookies")) {    //Delete session cookie
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		session_destroy();
	}

	public static function requestFromSameDomain()
	{
		if (SysMain::getRefererHost() === '') {
			return false;  //NO http refererer was sent
		} else {
			return (SysMain::getRefererHost() === $_SERVER['HTTP_HOST']);
		}
	}

	public static function userSubPathPrependDs()
	{
		return (empty(self::$userSubPath)) ? '' : (FilNams::DS . self::$userSubPath);
	}

	// GETTERS / SETTERS

	public static function setLoginSessVars($loginUsr)
	{
		session_regenerate_id($deleteOldSession = true);  //create new sess-ID
		$_SESSION[self::SV_LOGIN_USRNAM] = $loginUsr;
		$_SESSION[self::SV_LOGIN_LAST_REQUEST]   = time();
		$_SESSION[self::SV_LOGIN_SESSID_CREATED] = time();
		$_SESSION[self::SV_LOGIN_IP] = $_SERVER['REMOTE_ADDR'];
		$_SESSION[self::SV_LOGIN_USR_AGENT] = $_SERVER['HTTP_USER_AGENT'];
	}

	public static function isAdminUser()
	{
		return self::$adminUserFlag;
	}

	public static function setAdminUserFlag($val)
	{
		self::$adminUserFlag = $val;
	}

	public static function getFailedUserLogin()
	{
		return self::$failedUserLogin;
	}

	public static function getRemainingThrottleSecs()
	{
		return self::$remainingThrottleSecs;
	}

	public static function getUserSubPath()
	{
		return self::$userSubPath;
	}

	public static function setUserSubPath($userDirname)
	{
		self::$userSubPath = (empty($userDirname)) ? '' : 
				(FilNams::DN_CLIENTS . FilNams::DS . $userDirname);
		FilNams::setAbsPathImages();
		FilNams::setAbsPathVideo();
	}

	public static function getLoginStatusMsg1()
	{
		return self::$loginStatusMsg1;
	}

	public static function getLoginLinkText()
	{
		return self::$loginLinkText;
	}

	public static function getLoginLinkUrl()
	{
		return self::$loginLinkUrl;
	}

	public static function getNewUserLinkText()
	{
		return self::$newUserLinkText;
	}

	public static function getNewUserLinkUrl()
	{
		return self::$newUserLinkUrl;
	}

}
