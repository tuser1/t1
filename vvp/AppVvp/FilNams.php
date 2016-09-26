<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp;

use AppVvp\General\AuthUser;
use AppVvp\General\FileSystem;
use AppVvp\General\SysMain;

/**
 * *************************************************************************
 * Application file-system filenames, foldernames and paths:
 * - !! FilNams::init() MUST BE EXECUTED BEFORE ANY OTHER ROUTINES !!
 * - sets the application's document root (absolute)
 * - sets the include path
 * - sets all absolute and relative paths
 * *************************************************************************
 */
class FilNams
{
	const NOT_SET = '{~not~set~}';

	// Filename used to determine application's doc root
	// Can be ANY file/dir name that exists in the doc root of the application
	const FN_DOCROOT_LOCATOR = '.docroot-locator-appvvp'; // self::DN_PAGES;

	// Using the forward-slash is o.k. for Windows/WampServer
//	const DS = DIRECTORY_SEPARATOR;
	const DS = '/';

	// Non-pages PATH indicators
	const PTH_ROOT  = 'R';
	const PTH_TASKS = 'T';

	// NAMESPACE names
	const NS_DN_PAGES	= 'Pages';

	// FOLDER names
	const DN_INCLUDES	= 'AppVvp';
	const DN_DCSS		= 'dynamic-css'; // => .dcss => dynamic css
	const DN_GEN_JS		= 'generated-js';
	const DN_CSS		= 'css';
	const DN_JS			= 'js';
	const DN_TEMPLATES	= 'templates';

	const DN_DATA		= 'data';
	const DN_ERRMSGS	= 'errmsgs';
	const DN_LOGS		= 'logs';
	const DN_SESSIONS	= 'sessions';

	const DN_GALLERY	= 'gallery';
	const DN_IMAGES		= 'images';
	const DN_ENTRY		= 'entry-pt';
	const DN_PAGES		= 'pages';
	const DN_ADMIN		= 'admin';
	const DN_TASKS		= 'tasks';
	const DN_VIDEO		= 'video';
	const DN_CLIENTS	= 'clients';

	// PAGE filenames
    const PN_INDEX		= 'index';
    const PN_IDX_PGS	= 'index-pages';
	const PN_VIDSWED	= 'wedding-videos-welcome';
	const PN_VIDSMIT	= 'mitzvah-videos-welcome';
	const PN_DEMOSTILLS = 'los-angeles-videos-stillframes'; // 'stillframes';
	const PN_OPTIONS	= 'wedding-videographers-options';  // 'options';
	const PN_CONTACT	= 'videography-contact-us';

	const PN_NEWUSER		= 'newuser';
	const PN_LOGIN			= 'login';
	const PN_PW_FORGOT		= 'pw-forgot';
	const PN_PW_RESET		= 'pw-reset';
	const PN_VIDSMY			= 'myvideos-welcome';
	const PN_MYINFO			= 'myinfo';
	const PN_UPLOAD			= 'upload';
	const PN_ULF_EXISTS		= 'ulf-file-exists';
	const PN_ULF_PROGRS		= 'ulf-progress';
	const PN_ULF_CANCEL		= 'ulf-cancel';
	const PN_PASSCHG		= 'passchg';
	const PN_LOGOUT			= 'logout';

	const PN_ERRMSG			= 'errmsg';
	const PN_ERRMSG_TST		= 'errmsg-test';

	const PN_LOGFILE		= 'a-log-file';
	const PN_ERASEFILE		= 'a-erase-file';
	const PN_PHPERRS		= 'a-php-errors';
	const PN_PHPERRSDEL		= 'a-php-errors-del';
	const PN_SETSESSVARS	= 'a-set-sessvars';
	const PN_SETSESSVARSP	= 'a-set-sessvars-by-parm';
	const PN_PW_HASH		= 'a-pw-hash';
	const PN_SITEINFO		= 'a-site-info';
	const PN_PHPINFO		= 'a-php-info';
	const PN_USR_ACTIVITY	= 'a-user-activity';
	const PN_LIST_USERS		= 'a-list-users';

	const PN_TEST1		= 'test1';

	// Website FILEnames
	const CO_SHORTCUT_ICON1 = 'VoilaVideoURLicon1.ico';
	const CO_BANNER1 = 'los-angeles-wedding-video-banner.jpg';
	const CO_BANNER2 = 'los-angeles-videography-banner.jpg';
	const CO_BANNER3 = 'los-angeles-videographers-banner.jpg';
	const PAGE_BG1 = 'voila-video-bg-01.jpg';
	const PAGE_BG2 = 'voila-video-bg-02.jpg';
	const PAGE_BG3 = 'voila-video-bg-03.jpg';
	const WM_LOGO1 = 'WM-logo1.gif';
	const QT_LOGO1 = 'QT-logo1.gif';

	const FNAM_PREFIX_GALLERY = 'wedding-photo-';

	const TYPE_CODE_WED = 'WED'; // Wedding
	const TYPE_CODE_BAR = 'BAR'; // Bar Mitzvah
	const TYPE_CODE_BAT = 'BAT'; // Bat Mitzvah

	// Server FILEnames
	const FN_ERRLOG		= 'errors.log';
	const FN_ERRMSG		= 'errmsg';
	const FN_LOGFILE	= 'logfile.log';

  	// The DOCUMENT_ROOT for the application.  In development/test environment
	//  it may or may NOT be the same as the ACTUAL PHP DOCUMENT_ROOT.
	private static $absPathDocRoot = self::NOT_SET;

	// Private root for includes, etc.
	private static $absPathPrivRoot = self::NOT_SET;

	private static $relPathRoot = self::NOT_SET;
	private static $splitPhpSelf = self::NOT_SET;
	private static $absPathIncludes = self::NOT_SET;
	private static $absPathData = self::NOT_SET;

	private static $fnamPrefixVideos = array(
		self::TYPE_CODE_WED => 'wedding-video-',
		self::TYPE_CODE_BAT => 'bat-mitzvah-video-',
		self::TYPE_CODE_BAR => 'bar-mitzvah-video-',
		'' => '',
	);

	private static $nonPagesPaths = array(
		self::PN_INDEX      => self::PTH_ROOT,
		self::PN_ULF_PROGRS => self::PTH_TASKS,
	);

	private static $absPathSessions = self::NOT_SET;
	private static $absPathErrMsgs = self::NOT_SET;
	private static $absPathLogs = self::NOT_SET;
	private static $absPathVideo = self::NOT_SET;
	private static $absPathImages = self::NOT_SET;

	private static $relPathEntry = self::NOT_SET;
	private static $relPathPages = self::NOT_SET;
	private static $relPathCss = self::NOT_SET;
	private static $relPathTasks = self::NOT_SET;
	private static $relPathJs = self::NOT_SET;
	private static $relPathImages = self::NOT_SET;
	private static $relPathGallery = self::NOT_SET;
	private static $relPathVideo = self::NOT_SET;

	private static $errorLogFnam = self::NOT_SET;
	private static $errorMsgFnam = self::NOT_SET;
	private static $logFileFnam = self::NOT_SET;
	private static $actualFullUrl = self::NOT_SET;




	public static function init()
	{
		$paths_found = self::setAbsPathDocRoot();
		if ($paths_found === 1) {
			//-------------------------------
			// Use absolute paths for include & data files
			// Use dirname() to set folder 1 level higher than doc root
			self::$absPathPrivRoot = dirname(self::getAbsPathDocRoot());
			//-------------------------------
			self::setAbsPathIncludes();
			self::$absPathData = self::$absPathPrivRoot . self::DS . self::DN_DATA;
		} else {
			if (ini_get('display_errors')) {
				if ($paths_found > 1) {
					echo '<br />[E0501] *E* ABS doc root - ', 
							'too many occurences of DOCROOT LOCATOR FILE/DIR' , 
							' - number found: ', $paths_found . '<br />';
				} else {
					echo '<br />[E0502] *E* Unable to find DOCROOT LOCATOR FILE/DIR<br />';
				}
			} else {
				echo '<br />[' . 'E0500' . '] An error has occurred<br />';
			}
			exit();
		}

		self::setAbsPathErrMsgs (self::getAbsPathData() . self::DS . self::DN_ERRMSGS);
		self::setAbsPathLogs    (self::getAbsPathData() . self::DS . self::DN_LOGS);
		self::setAbsPathSessions(self::getAbsPathData() . self::DS . self::DN_SESSIONS);

		self::setRelPathEntry(  self::getRelPathRoot().'/'.self::DN_ENTRY);
		/**
		 * ============================================================
		 * !!! ONLY ALLOW SITE ACCESS THROUGH [www] -OR- [www/entry-pt] !!!
		 * OTHER DIRS OR SUBDIRS, EVEN IF VALID, WILL BE REDIRECTED.
		 * E.G. http://www.site.com/DIR-OTHER-THAN-ENTRY-PT/flname.php will redirect to root/index
		 * 
		 * PHP ALLOWS DUPE QUERY STR KEYS ['?pg=foo$pg=bar'] - it takes the LAST key value.
		 * NO way to prevent this BUT its effects appear to be fairly innocuous.
		 * In both cases below 'login' will be launched instead of 'contact-us':
		 * http://www.site.com/pages/contact-us?pg=login  [URL rewriting enabled]
		 * http://www.site.com/entry-pt/index-pages.php?pg=contact-us&pg=login
		 * ============================================================
		 */
		switch(dirname(SAFE_RU_ACTUAL_PHP_SELF)) {
			case self::$relPathEntry:
			case self::$relPathRoot:
				break;
			default:
				// Invalid URL path - redirect to root index script
				App::redirect(self::$relPathRoot . '/');
		}

		self::setRelPathPages(  self::getRelPathRoot().'/'.self::DN_PAGES);
		self::setRelPathCss(    self::getRelPathRoot().'/'.self::DN_CSS);
		self::setRelPathJs(     self::getRelPathRoot().'/'.self::DN_JS);
		self::setRelPathImages( self::getRelPathRoot().'/'.self::DN_IMAGES);
		self::setRelPathGallery(self::getRelPathRoot().'/'.self::DN_IMAGES.'/'.self::DN_GALLERY);
		self::setRelPathVideo(  self::getRelPathRoot().'/'.self::DN_VIDEO);

		self::setRelPathTasks(  self::getRelPathEntry().'/'.self::DN_TASKS);

		// Server-files - use abs path:
		self::setErrorMsgFnam(self::getAbsPathErrMsgs() . self::DS . self::FN_ERRMSG);
		self::setErrorLogFnam(self::getAbsPathLogs() .    self::DS . self::FN_ERRLOG);
		self::setLogFileFnam (self::getAbsPathLogs() .    self::DS . self::FN_LOGFILE);

		self::setActualFullUrl();

		// ----------------------------------------------------
		if (URLRW_ENABLED) {
			define('SAFE_RU_CURR_PAGE_URL', ru(self::getPgUrl()));
		} else {
			define('SAFE_RU_CURR_PAGE_URL', self::getPgUrl());
		}
		// ----------------------------------------------------
	}


	// For the following methods: strrpos() rtns LAST occurrence - faster than explode()/return end()

	// Return $nsParm with namespace REMOVED
	public static function removeNs($nsParm)
	{
		if ($p = strrpos($nsParm, '\\')) {
			return substr($nsParm, $p + 1);
		}
		return $nsParm; // NO namespace DELIM found - return passed parm
	}

	// Return NAMESPACE ONLY from $nsParm
	public static function getNs($nsParm)
	{
		if ($p = strrpos($nsParm, '\\')) {
			return substr($nsParm, 0, $p);
		}
		return ''; // NO namespace DELIM found - return blank value
	}

	public static function getPgUrl($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		if (array_key_exists($pgName, self::$nonPagesPaths)) {
			switch(self::$nonPagesPaths[$pgName]) {
				case self::PTH_ROOT:
					return self::createUrl(self::getRelPathRoot(), $pgName);
				case self::PTH_TASKS:
					return self::createUrl(self::getRelPathTasks(), $pgName);
				default:
					trigger_error('[E015] UNDEFINED path parm [' . 
						self::$nonPagesPaths[$pgName] . ']', E_USER_ERROR);
			}
		}
		if (URLRW_ENABLED) {
			return self::getRelPathPages() . '/' . $pgName;
		} else {
			return self::getRelPathEntry() . '/' . self::PN_IDX_PGS . '.' .
					PG_FILEEXT . '?' . PG_QSKEY . '=' . $pgName;
		}
	}

	public static function createUrl($path, $pgName)
	{
		return $path . '/' . $pgName . '.' . PG_FILEEXT;
	}

	public static function getPgFullUrl($pgName = SAFE_UU_PHP_SELF_FNAM_NO_EXT)
	{
		return 'http://' . ru(RAW_SERVER_NAME . self::getPgUrl($pgName));
	}

//---------------------------------------------

	// GETTERS / SETTERS

	public static function getAbsPathDocRoot() {return self::$absPathDocRoot;}
	public static function getAbsPathData() {return self::$absPathData;}
	public static function getAbsPathIncludes() {return self::$absPathIncludes;}
	public static function getAbsPathErrMsgs() {return self::$absPathErrMsgs;}
	public static function getAbsPathLogs() {return self::$absPathLogs;}
	public static function getAbsPathSessions() {return self::$absPathSessions;}

	public static function getRelPathRoot() {return self::$relPathRoot;}
	public static function getSplitPhpSelf() {return self::$splitPhpSelf;}

	public static function getRelPathGallery() {return self::$relPathGallery;}
	public static function getRelPathImages() {return self::$relPathImages;}
	public static function getRelPathJs() {return self::$relPathJs;}
	public static function getRelPathEntry() {return self::$relPathEntry;}
	public static function getRelPathPages() {return self::$relPathPages;}
	public static function getRelPathCss() {return self::$relPathCss;}
	public static function getRelPathTasks() {return self::$relPathTasks;}
	public static function getRelPathVideo() {return self::$relPathVideo;}
	public static function getErrorLogFnam() {return self::$errorLogFnam;}

	public static function getErrorMsgFnam($token)
	{
		return self::$errorMsgFnam . '.' . $token . '.txt';
	}
	
	public static function getLogFileFnam()
	{
		return self::$logFileFnam;
	}

	public static function getActualFullUrl()
	{
		return self::$actualFullUrl;
	}

	public static function getFnamPrefixVideos($vidtypcod)
	{
		return self::$fnamPrefixVideos[$vidtypcod];
	}

	public static function getPagNamStartCalc()
	{
		if (!AuthUser::authenticated()) {
			return self::PN_VIDSWED;
		} else {
			return self::PN_VIDSMY;
		}
	}

	public static function getPagNamStart()
	{
		return self::PN_VIDSWED;
 	}

	public static function getAutodirectMainUrl()
	{
		return self::getPgUrl(self::getPagNamStart());
	}

	public static function getAdminPagNamStart()
	{
		return self::PN_PHPINFO;
	}

	public static function getAbsPathVideo()
	{
		return self::$absPathVideo;
	}

	public static function getAbsPathImages()
	{
		return self::$absPathImages;
	}

	// -------------------------

	public static function setAbsPathDocRoot()
	{
		self::$splitPhpSelf = explode('/', SAFE_RU_ACTUAL_PHP_SELF);
		//-----------------------------------------------------------
		// NOTE!! On WampServer, DOCUMENT_ROOT has FORWARD slashes with a 
		//   TRAILING slash: [C:/wamp/www/]
		// On Linux there is NO trailing slash
		// PHP, however, will accept ANY of the following as valid:
		//  'C:/dirnam/subdir/file.php'  - forward-slashes
		//  'C:/dirnam//subdir/file.php' - double-slash, will treat as single
		//  'C:\dirnam\subdir/file.php'  - mixed slashes
		//  'C:\dirnam\/subdir/file.php' - mixed double-slashes (\/, /\)
		//  'C:\dirnam\subdir\file.php'  - back-slashes

 		// Strip any trailing '/'s or '\'s - Windows environment
		self::$absPathDocRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
		//-----------------------------------------------------------
		$found = 0;
		if (file_exists(self::$absPathDocRoot . self::DS . self::FN_DOCROOT_LOCATOR)) {
   			// NORMAL PRODUCTION SCENARIO: doc_root = $_SERVER['DOCUMENT_ROOT']
			self::$relPathRoot = '';
			$found = 1;
		}
		// =======================================================================
		// !! COMMENT-OUT IN PRODUCTION WHEN APP IS IN NORMAL DOCUMENT_ROOT !!
//		$found = 0; // Force a docroot subdir search
		// =======================================================================
//		if (!$found) {
			// FOR USE WITH MULTI-VERS PROD DEMO/TEST/DEVELOPMENT WHERE THE ROOT IS IN 
			//    A SUBDIR OF THE  DOCUMENT_ROOT
			// CAN BE ON PRODUCTION -OR- LOCALHOST ENVIRONMENT
			// ** SEARCH TO THE END OF THE 'PHP_SELF' PATH: **
		$tmpRelPathRoot = '';
		$tmpAbsPathDocRoot = self::$absPathDocRoot;
		for ($ii = 1; $ii <= count(self::$splitPhpSelf) - 2; $ii++) {
			$tmpRelPathRoot.= '/' .      self::$splitPhpSelf[$ii];
			$tmpAbsPathDocRoot.= self::DS . self::$splitPhpSelf[$ii];
			if (file_exists($tmpAbsPathDocRoot . self::DS . 
					self::FN_DOCROOT_LOCATOR)) {
				self::$absPathDocRoot = $tmpAbsPathDocRoot;
				self::$relPathRoot     = $tmpRelPathRoot;
				$found++;
			}
		}
//		}
		//-------------------------
		return $found;
	}

	public static function setAbsPathIncludes()
	{
		self::$absPathIncludes = self::$absPathPrivRoot . self::DS . self::DN_INCLUDES;
		//---------------------------------------------------------------------
		// These are PHP lib ftns: set_include_path / get_include_path
		// APPEND path $absPathIncludes to php.ini's include_path:
		set_include_path(get_include_path() . PATH_SEPARATOR . self::getAbsPathIncludes());
		//---------------------------------------------------------------------
	}

	public static function setAbsPathErrMsgs($val) {self::$absPathErrMsgs = $val;}
	public static function setAbsPathLogs($val) {self::$absPathLogs = $val;}
	public static function setAbsPathSessions($val) {self::$absPathSessions = $val;}

	public static function setAbsPathVideo()
	{
		if (RAW_SERVER_NAME === SysMain::LOCALHOST && 
					SysMain::getClientOs() === SysMain::CLIENT_OS_WIN) {
			self::$absPathVideo = $_SERVER['DOCUMENT_ROOT'] . self::DN_VIDEO . 
					AuthUser::userSubPathPrependDs();
		} else {
			self::$absPathVideo = $_SERVER['DOCUMENT_ROOT'] . self::DS . self::DN_VIDEO . 
					AuthUser::userSubPathPrependDs();
		}
	}

	public static function setAbsPathImages()
	{
		self::$absPathImages = self::getAbsPathDocRoot() . self::DS . 
					self::DN_IMAGES . AuthUser::userSubPathPrependDs();
	}

	public static function setRelPathGallery($val) {self::$relPathGallery = $val;}
	public static function setRelPathImages($val) {self::$relPathImages = $val;}
	public static function setRelPathJs($val) {self::$relPathJs = $val;}
	public static function setRelPathEntry($val) {self::$relPathEntry = $val;}
	public static function setRelPathPages($val) {self::$relPathPages = $val;}
	public static function setRelPathCss($val) {self::$relPathCss = $val;}
	public static function setRelPathTasks($val) {self::$relPathTasks = $val;}
	public static function setRelPathVideo($val) {self::$relPathVideo = $val;}

	public static function setErrorLogFnam($val)
	{
		if (FileSystem::validatSrvrFname($val, $echo = false, $abort = true)) {
			self::$errorLogFnam = $val;
		}
	}

	public static function setErrorMsgFnam($val) {self::$errorMsgFnam = $val;}

	public static function setLogFileFnam($val)
	{
		if (FileSystem::validatSrvrFname($val, $echo = false, $abort = true)) {
			self::$logFileFnam = $val;
		}
	}

	public static function setActualFullUrl()
	{
		self::$actualFullUrl = 'http://' . RAW_SERVER_NAME . self::getRelPathRoot();
	}

}
