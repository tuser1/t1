<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp;

use AppVvp\Forms\MyInfoForm;
use AppVvp\General\AuthUser;
use AppVvp\General\PgLinkFactory;
use AppVvp\General\PgMsgs;
use AppVvp\Pages\Page;
use AppVvp\Validators\Validator;

/**
 * TODO: document
 */
class App
{
    private $data = array();      // Location for overloaded data (magic methods)

	public static $page = NULL;   // Instantiated object for the current loaded page
	public static $dbMain = NULL; // Instantiated object for the main DB connect
	public static $dbTest = NULL; // Instantiated object for the test DB connect


    public function __set($name, $value)
    {
//		echo "[__set] Setting '$name' to '$value'\n";
		/**
		 * !!! DISABLE __set / __get magic methods: DISALLOW assignments to 
		 * UNDECLARED class properties of instantiated objects by using 
		 *    'if (property_exists())':
		 */
		if (property_exists($this, $name)) { // PHP 5.1.0
			$this->data[$name] = $value;
		} else {
			$trace = debug_backtrace();
			trigger_error(
				'UNDECLARED property ['. $name . ']' . 
				' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], 
				E_USER_ERROR);
		}
	}

    public function __get($name)
	{
//		echo "[__get] Getting '$name'\n";
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}
		$trace = debug_backtrace();
		trigger_error(
			'UNDEFINED property ['. $name . '] via __get() ' . 
			' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'], 
			E_USER_ERROR);
		return null;
	}


	/**  As of PHP 5.1.0  */
	public function __isset($name)
	{
//		echo "[__isset] Is '$name' set?\n";
		return isset($this->data[$name]);
	}

	/**  As of PHP 5.1.0  */
    public function __unset($name)
	{
//		echo "[__unset] Unsetting '$name'\n";
		unset($this->data[$name]);
	}

	// ----------------------------------------------------------------

	public static function renderCurrentPage($pageObjCls)
	{
		if (!empty($pageObjCls) && !PgLinkFactory::isSpecializedPage()) {
			// ----------------------------------
			self::createPage(new $pageObjCls(''));
			// ----------------------------------
		} else {
			self::renderSpecializedPage($pageObjCls);
		}
	}

	private static function renderSpecializedPage($pageObjCls)
	{
		switch(SAFE_UU_PHP_SELF_FNAM_NO_EXT) {

		case FilNams::PN_MYINFO:
			if (!isset($_POST[Validator::CFV_AJAX])) { // Non-AJAX post
				self::createPage(new $pageObjCls(''));
			} else {
				// AJAX - NO current page object.  Create stand-alone 
				//  instantiation of 'MyInfoForm' that is not linked to a page:
				$TEMP_FORM1 = new MyInfoForm(NULL);
			}
			break;

		case FilNams::PN_LOGOUT:
			AuthUser::pageLogout();
			break;

		case FilNams::PN_SETSESSVARSP:
			$validGetParm = true;
			if (Validator::issetGetAddAry1(Validator::SV_CFV_METHOD)) {
				$tmp1 = Validator::cleanInput1($_GET[Validator::SV_CFV_METHOD]);
				if ($tmp1 != Validator::CFV_JS && $tmp1 != Validator::CFV_AJAX && $tmp1 != Validator::CFV_NONE) {
					echo 'Invalid value [', hh($tmp1), '] passed in parm, sessvar NOT set: [', 
						Validator::SV_CFV_METHOD, ']<br />';
					$validGetParm = false;
				} else {
					//echo 'Setting sessvar: [', hh(Validator::SV_CFV_METHOD), '=', hh($tmp1), ']<br />';
					$_SESSION[Validator::SV_CFV_METHOD] = $tmp1;
				}
			} elseif (Validator::issetGetAddAry1(Validator::SV_ENFORCE_PW_RULES)) {
				$tmp1 = Validator::cleanInput1($_GET[Validator::SV_ENFORCE_PW_RULES]);
				if ($tmp1 !== '0' && $tmp1 !== '1') {
					echo 'Invalid value [', hh($tmp1), '] passed in parm, sessvar NOT set: [', 
						Validator::SV_ENFORCE_PW_RULES, ']<br />';
					$validGetParm = false;
				} else {
					//echo 'Setting sessvar: [', hh(Validator::SV_ENFORCE_PW_RULES), '=', hh($tmp1), ']<br />';
					$_SESSION[Validator::SV_ENFORCE_PW_RULES] = $tmp1;
				}
			} else {
				echo '*ERROR* Valid parm/value was NOT passed, sessvar NOT set.<br />';
				$validGetParm = false;
			}
			//=================================
			// Instance of 'Page' is NOT created in this script [App::$page = new \AppVvp\Pages\Page('')]
			//   therefore you must call 'Validator::validatePassedGetKeys1':
			//=================================
			if ($validGetParm && Validator::validatePassedGetKeys1($echo = true)) {
			//	App::redirect(FilNams::getPgUrl($_SESSION[Page::SV_ADMINPAG]));
			//	App::redirect(FilNams::getPgUrl(FilNams::PN_SETSESSVARS));
				App::redirect(FilNams::getPgUrl($_SESSION[Page::SV_USERPAG]));
			}
			break;

		case FilNams::PN_ERRMSG_TST:
			$errCode = 'E0000';
			$errMsg  = 'This is for testing the [trigger_error] ftn --AND-- the [' . 
						FilNams::PN_ERRMSG . '] page';
			PgMsgs::set($errCode, $errMsg);
			trigger_error('[' . $errCode . '] ' . $errMsg, E_USER_ERROR);
			break;

		case FilNams::PN_ULF_CANCEL:
			/**
			 * The following is NOT REQUIRED, just protocol.  Simply redirecting the page is 
			 * enough to terminate the upload.
			 */
			$key = ini_get('session.upload_progress.prefix') . 'Video~File~Upload';
			$cancelKey = 'cancel_upload';
			if (isset($_SESSION[$key])) {
				$_SESSION[$key][$cancelKey] = true;
			}
			// Display 'UPLOAD CANCELLED' msg and terminate the upload by page redirection
			echo '<br /><br />';
			echo '================================================================', 
						'=====================================<br />';
			echo '======================================&nbsp;&nbsp;&nbsp;&nbsp; UPLOAD CANCELLED', 
						' &nbsp;&nbsp;&nbsp;&nbsp;======================================<br />';
			echo '================================================================', 
						'=====================================<br />';
			App::redirect(FilNams::getPgUrl(FilNams::PN_UPLOAD), $pauseSecs = 2);

		case FilNams::PN_ULF_EXISTS:
			// Do NOT exec self::createPage here - just assign the page obj to $page
			App::$page = new $pageObjCls('');
			break;
		}
	}

	public static function createPage($pageObj)
	{
		self::$page = $pageObj;
		//=================================
		self::$page->runPage();
		//=================================
	}

	/**
	 * Redirect to new page/url location
	 */
	public static function redirect($newUrl, $pauseSecs = 0)
	{
		if ($pauseSecs === 0) {
			header('Location: ' . $newUrl);
		} else {
			header('refresh: ' . $pauseSecs . '; URL=' . $newUrl);
		}
		exit();
	}

}
