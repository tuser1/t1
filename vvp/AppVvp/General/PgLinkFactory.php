<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\App;
use AppVvp\FilNams;

/**
 * Create all PAGE LINK objects and set properties
 */
class PgLinkFactory
{
	private static $pgLnks = array();


	public static function create()
	{
//		$contactLnkNam1 = 'Contact ' . BusDefs::BUSNAME1_F2;
		$contactLnkNam1 = 'Contact Us';

		// ------------------------ PUBLIC PAGES ------------------------

		self::publicAdd(FilNams::PN_INDEX);

		self::publicAdd(FilNams::PN_VIDSWED,   'VideoWedPage', 'Wedding Videos');
		self::publicAdd(FilNams::PN_VIDSMIT,   'VideoMitPage', 'Mitzvah Videos');
		self::publicAdd(FilNams::PN_DEMOSTILLS,'StillImgsPage', 'Wedding Images');
		self::publicAdd(FilNams::PN_OPTIONS,   'OptionsPage', 'Services & Pricing');
		self::publicAdd(FilNams::PN_CONTACT,   'ContactPage', $contactLnkNam1);

		self::publicAdd(FilNams::PN_NEWUSER,   'NewUserPage');
		self::publicAdd(FilNams::PN_LOGIN,     'LoginPage');
		self::publicAdd(FilNams::PN_PW_FORGOT, 'PwForgotPage');
		self::publicAdd(FilNams::PN_PW_RESET,  'PwResetPage');
		self::publicAdd(FilNams::PN_LOGOUT);
		self::publicAdd(FilNams::PN_ERRMSG,    'ErrMsgPage');

		self::publicAdd(FilNams::PN_TEST1);

		// ------------------------ SECURE PAGES ------------------------

		self::secureAdd(FilNams::PN_VIDSMY,    'VideoMyPage', 'My Videos');
		self::secureAdd(FilNams::PN_MYINFO,    'MyInfoPage', 'My Info', $special=1);
		self::secureAdd(FilNams::PN_UPLOAD,    'UploadPage', 'Upload');
		self::secureAdd(FilNams::PN_PASSCHG,   'PassChgPage', 'Change Password');

		self::secureAdd(FilNams::PN_ERRMSG_TST);
		self::secureAdd(FilNams::PN_ULF_CANCEL);
		self::secureAdd(FilNams::PN_ULF_EXISTS, 'UploadPage', '', $special=1);

		// ------------------------ ADMIN PAGES [SECURE] ------------------------

		self::adminAdd(FilNams::PN_LOGFILE,     'ShowLogFilePage', 'Log File');
		self::adminAdd(FilNams::PN_ERASEFILE,   'EraseFilePage');
		self::adminAdd(FilNams::PN_PHPERRS,     'ShowLogFilePage', 'PHP Errors');
		self::adminAdd(FilNams::PN_PHPERRSDEL);
		self::adminAdd(FilNams::PN_SETSESSVARS, 'Page', 'Sessvars');
		self::adminAdd(FilNams::PN_SETSESSVARSP);
		self::adminAdd(FilNams::PN_SITEINFO,    'Page', 'Site Info');
		self::adminAdd(FilNams::PN_PW_HASH,     'Page', 'PW Hash');
		self::adminAdd(FilNams::PN_PHPINFO,     'PhpInfoPage', 'PHP Info');
		self::adminAdd(FilNams::PN_USR_ACTIVITY,'Page', 'Activity');
		self::adminAdd(FilNams::PN_LIST_USERS,  'Page', 'Users');

		// --------------------------------------------------------------

		if (!key_exists(SAFE_UU_PHP_SELF_FNAM_NO_EXT, self::$pgLnks) || 
					URLRW_PGVAL === FilNams::PN_INDEX) {
			// URL/PAGE KEY IS NOT DEFINED - REDIR TO ROOT INDEX SCRIPT:
			App::redirect(FilNams::getRelPathRoot() . '/');
		}
	}


	private static function publicAdd($pgName, $pageObjCls='', $descr1='', $special=0)
	{
		self::newPgLink($pgName, $descr1, $securePg = 0, $pageObjCls, $special);
	}

	private static function secureAdd($pgName, $pageObjCls='', $descr1='', $special=0)
	{
		self::newPgLink($pgName, $descr1, $securePg = 1, $pageObjCls, $special);
	}

	private static function adminAdd($pgName, $pageObjCls='', $descr1='', $special=0)
	{
		self::newPgLink($pgName, $descr1, $securePg = 1, $pageObjCls, $special, $admin=1);
	}

	private static function newPgLink($pgName, $descr1, $securePg, $pageObjCls, $special, 
			$admin=0)
	{
		self::$pgLnks[$pgName]['descr1'] = $descr1;
		self::$pgLnks[$pgName]['securePg'] = $securePg;
		self::$pgLnks[$pgName]['pageObjCls'] = $pageObjCls;
		self::$pgLnks[$pgName]['specialized'] = $special;
		self::$pgLnks[$pgName]['adminPg'] = $admin;
	}

	public static function pgLinkCreated($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		return array_key_exists($pgName, self::$pgLnks);
	}

	public static function isCurrentPage($pgName)
	{
		return $pgName === SAFE_UU_PHP_SELF_FNAM_NO_EXT;
	}

	public static function isSecurePage($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		return self::$pgLnks[$pgName]['securePg'];
	}

	public static function isAdminPage($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		return self::$pgLnks[$pgName]['adminPg'];
	}

	public static function isSpecializedPage($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		return self::$pgLnks[$pgName]['specialized'];
	}


	// GETTERS / SETTERS

	private static function getPageObjCls($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		return self::$pgLnks[$pgName]['pageObjCls'];
	}

	public static function getNsPageObjCls($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		$cls = self::getPageObjCls($pgName);
		if (!empty($cls)) {
			return _VENDOR_NS_ . '\\' . FilNams::NS_DN_PAGES . '\\' . $cls;
		} else {
			return $cls;
		}
	}

	public static function getDescr1($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		return self::$pgLnks[$pgName]['descr1'];
	}

}
