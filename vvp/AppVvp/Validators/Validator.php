<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Validators;

use AppVvp\Db\DbAccess;
use AppVvp\General\CsrfToken;
use AppVvp\General\PgMsgs;
use AppVvp\General\SysMain;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class Validator
{
	const CSRF_TOKEN_SIZE = 32;

	const FF_USRNAM_MINLEN = 4;
	const FF_FIRSTNAM_MINLEN = 2;
	const FF_LASTNAM_MINLEN = 2;
	const FF_ADDR_MINLEN = 3;
	const FF_CITY_MINLEN = 2;
	const FF_USZIPCODE_MINLEN = 5;
	const FF_PHONE_MINLEN = 12;
	const FF_TEXTMSG_ADDR_MINLEN = 14;

	const FF_USRNAM_MAXLEN = 60;
	const FF_FIRSTNAM_MAXLEN = 30;
	const FF_LASTNAM_MAXLEN = 30;
	const FF_ADDR_MAXLEN = 70;
	const FF_CITY_MAXLEN = 30;
	const FF_USZIPCODE_MAXLEN = 10;
	const FF_PHONE_MAXLEN = 12;
	const FF_TEXTMSG_ADDR_MAXLEN = 40;

	// PW rules
	const FF_PW_MINLEN = 12;
	const FF_PW_MAXLEN = 40;
	const FF_PW_UC_CNT = 2;	// # of UPPERcase chars required [2]
	const FF_PW_LC_CNT = 2;	// # of lowercase chars required [2]
	const FF_PW_NM_CNT = 2;	//         # of numbers required [2]
	const FF_PW_SC_CNT = 1; //   # of special chars required [1]

	private static $ffPwMinlen = self::FF_PW_MINLEN;
	private static $ffPwMaxlen = self::FF_PW_MAXLEN;
	private static $ffPwUcCnt  = self::FF_PW_UC_CNT;
	private static $ffPwLcCnt  = self::FF_PW_LC_CNT;
	private static $ffPwNmCnt  = self::FF_PW_NM_CNT;
	private static $ffPwScCnt  = self::FF_PW_SC_CNT;
	//

	const SV_ENFORCE_PW_RULES = 'enforce_pw_rules';
	const SV_CFV_METHOD = 'cfv_method_840177';

	/**
	 * NOTE: When including the hyphen char in a regex expr, it is better to place 
	 * it at the end just before the closing ']' since it is a range operator.
	 */
	const RE_BACKSLASH_REPL = '`~`~`~';

	const RE_VALID_PW_SC_SET = '!@#%'; // special chars set

	const RE_VALID_PW1_UC = '/[A-Z]/';
	const RE_VALID_PW1_LC = '/[a-z]/';
	const RE_VALID_PW1_NM = '/[0-9]/';

	const RE_VALID_PW2_PRE   = '/^[a-zA-Z0-9';
	const RE_VALID_PW2_SUFX  = ']*$/';

	const RE_VALID_CSRF_TOKEN = '/^[a-z0-9]*$/';
	const RE_VALID_EMAIL1     = 
		'/^[0-9a-z~!#$%&_-]([.]?[0-9a-z~!#$%&_-])*@[0-9a-z~!#$%&_-]([.]?[0-9a-z~!#$%&_-])*$/';
	const RE_VALID_TEXTMSG_ADDR1 = '/^\d{10}@[0-9a-z~!#$%&_-]([.]?[0-9a-z~!#$%&_-])*$/';
	const RE_VALID_NAME1      = '/^[a-zA-Z -]*$/';
	const RE_VALID_INITIAL1   = '/^[a-zA-Z]$/';
	const RE_VALID_ADDR1      = '/^[0-9a-zA-Z .#-]*$/';
	// ZIP: Matches all US formats ('12345-1234' or '12345')
	const RE_VALID_USZIPCODE1 = '/^\d{5}(-\d{4})?$/';
	const RE_VALID_PHONE1     = '/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/i';

	const RE_VALID_FNAME1     = '/^[0-9a-zA-Z _.#-]*$/';
	const RE_VALID_VID_ASPECT = '/^(HD|SD)$/';

	const RE_VALID_CSRF_TOKEN_ERR1 = 'Only lowercase letters and numbers are allowed.';
	const RE_VALID_PW2_ERR1        = 'Only letters, numbers & certain special chars - NO spaces.';
	const RE_VALID_EMAIL1_ERR1     = '[Email-address] must be in the format (lowercase): name@domain.';
	const RE_VALID_NAME1_ERR1      = 'Only letters/spaces/hyphens are allowed.';
	const RE_VALID_INITIAL1_ERR1   = 'Only a single LETTER is allowed.';
	const RE_VALID_ADDR1_ERR1      = 'Only numbers/letters/periods/hyphens/hashes.';
	const RE_VALID_USZIPCODE1_ERR1 = 'Must be in the format: [#####] -or- [#####-####]';
	const RE_VALID_PHONE1_ERR1     = 'Must be in the format: ###-###-####';
	const RE_VALID_TEXTMSG_ADDR1_ERR1 = '10-digits + @domain(lowercase!), e.g. 3101112222@vtext.com.';

	const RE_VALID_FNAME1_ERR1      = 'Only 0-9/A-Z/a-z/periods/hyphens/#/underscores.';

	const UNAUTH_POST_ID = '~``~unauth-post~``~';

	const CFV_AJAX	= 'AJAX';
	const CFV_JS	= 'JS';
	const CFV_NONE	= 'NONE';

	private static $cfvMethod = self::CFV_JS;
	private static $serverRequestMethod;
	private static $jsCompOrigFfVals = true;

	// 1 MiB [mebibyte] = 2[pwr 20] bytes => 1024 kibibytes => 1,048,576 bytes
	const ONE_KB = 1024;
	const ONE_MB = 1048576; // 1024 squared
	const ONE_GB = 1073741824; // 1024 cubed
	const ONE_TB = 1099511627776; // 1024 to the 4th power

	// PHP.INI settings must not exceed these:
	// NOTE: must always use INTEGER value, e.g. NOT 49.5M
	const APPL_UL_MAX_FILSIZ  = '200M';
	const APPL_UL_MAX_POSTSIZ = '200M';

	private static $phpUlMaxFilSizBytes;
	private static $applUlMaxFilSizBytes;
	private static $phpUlMaxPostSizBytes;
	private static $applUlMaxPostSizBytes;

	private static $validVidMimes = array(  // values are the file-extensions
		'video/mp4'=>'mp4',
		'video/x-ms-asf'=>'wmv',
		'video/webm'=>'webm',
	);

	private static $validStates = array(
        'AL'=>'Alabama',        'AK'=>'Alaska',         'AZ'=>'Arizona',
        'AR'=>'Arkansas',       'CA'=>'California',     'CO'=>'Colorado',
        'CT'=>'Connecticut',    'DE'=>'Delaware',       'DC'=>'District of Columbia',
        'FL'=>'Florida',        'GA'=>'Georgia',        'HI'=>'Hawaii',
        'ID'=>'Idaho',          'IL'=>'Illinois',       'IN'=>'Indiana',
        'IA'=>'Iowa',           'KS'=>'Kansas',         'KY'=>'Kentucky',
        'LA'=>'Louisiana',      'ME'=>'Maine',          'MD'=>'Maryland',
        'MA'=>'Massachusetts',  'MI'=>'Michigan',       'MN'=>'Minnesota',
        'MS'=>'Mississippi',    'MO'=>'Missouri',       'MT'=>'Montana',
        'NE'=>'Nebraska',       'NV'=>'Nevada',         'NH'=>'New Hampshire',
        'NJ'=>'New Jersey',     'NM'=>'New Mexico',     'NY'=>'New York',
        'NC'=>'North Carolina', 'ND'=>'North Dakota',   'OH'=>'Ohio',
        'OK'=>'Oklahoma',       'OR'=>'Oregon',         'PA'=>'Pennsylvania',
        'RI'=>'Rhode Island',   'SC'=>'South Carolina', 'SD'=>'South Dakota',
        'TN'=>'Tennessee',      'TX'=>'Texas',          'UT'=>'Utah',
        'VT'=>'Vermont',        'VA'=>'Virginia',       'WA'=>'Washington',
        'WV'=>'West Virginia',  'WI'=>'Wisconsin',      'WY'=>'Wyoming',
	);

	private static $validTitles = array(
		''=>'(none)', 'Mr.'=>'Mr.', 'Mrs.'=>'Mrs.',   'Miss'=>'Miss', 'Ms.'=>'Ms.', 
		'Dr.'=>'Dr.', 'Prof.'=>'Prof.', 'Rev.'=>'Rev.', 'Other'=>'Other', 
	);

	private static $usrGetKeys = array();





	public static function init()
	{
		if (!isset($_SESSION[self::SV_ENFORCE_PW_RULES])) {
			$_SESSION[self::SV_ENFORCE_PW_RULES] = '1';
		}
		if (!$_SESSION[self::SV_ENFORCE_PW_RULES]) {
			self::$ffPwMinlen = 1;
			self::$ffPwMaxlen = 999;
			self::$ffPwUcCnt = 0;
			self::$ffPwLcCnt = 0;
			self::$ffPwNmCnt = 0;
			self::$ffPwScCnt = 0;
		}
		self::setCfvMethod(self::CFV_JS);
//		self::setCfvMethod(self::CFV_AJAX);
//		self::setCfvMethod(self::CFV_NONE);
		self::setServerRequestMethod();
		self::setPhpUlMaxFilSizBytes();
		$uploadTmpDir = ini_get('upload_tmp_dir');
		if (!empty($uploadTmpDir) && !is_dir($uploadTmpDir)) {
			trigger_error('[' . 'E0105' . '] php INI upload_tmp_dir [' . $uploadTmpDir . 
					'] does not exist', E_USER_ERROR);
		}
	}

	public static function cleanInput1($val)
	// Clean/untaint user input
	// CAN USE THIS WHEN AN INSTANCE OF MYSQLI/CONNECT IS NOT AVAILABLE [real_escape_string]
	{
	  $val1 = trim($val);
	  $val2 = stripslashes($val1);
	  $val3 = htmlspecialchars($val2);
	  return $val3;
	}

	public static function issetGetAddAry1($key)
	{
		// No REAL need to check $usrGetKeys array for dupes
		self::$usrGetKeys[$key] = '';
		return isset($_GET[$key]);
	}

	public static function validatePassedGetKeys1($echo = false)
	{
		$stat = true;
		foreach ($_GET as $key => $val) {
			if (!isset(self::$usrGetKeys[$key]) && $key !== PG_QSKEY) {
				$stat = false;
				PgMsgs::set('E010', 'Invalid URL parm [' . $key . 
						'] was passed to [' . SAFE_UU_PHP_SELF_FNAM_NO_EXT . ']');
				trigger_error('Invalid URL parm [' . $key . 
						'] was passed to [' . SAFE_UU_PHP_SELF_FNAM_NO_EXT . ']', 
						E_USER_ERROR);
			}
		}
		return $stat;
	}


	protected static function formPostFfosSetValMysqlClean($formObj)
	{
		// Get and mysql_clean the $_POST array input
		foreach ($formObj->getFfoList() as $key => $ffo) {
			// Token value has already been checked/reset in 'Form' __construct.
			// Do not overwrite csrf token FFO's NEW value.
			if (isset($_POST[$key]) && $key != CsrfToken::getSvToken()) {
				$ffo->setValue(DbAccess::mysqlClean($_POST, $key));
			}
		}
		unset($key);  unset($ffo); // per PHP doc
	}

	public static function validateFfoVals1($formObj)
	{
		foreach ($formObj->getFfoList() as $ffo) {
			if (!$ffo->isHidden()) {
				self::validateFfoVal1($ffo);
			}
		}
		unset($ffo);  // per PHP doc
	}

	public static function validateFfoVal1($ffo)
	{
		if (!self::fieldEmpty($ffo)) {
			if (self::validStrLen($ffo)) {
				if ($ffo->getAuditFtn() !== '') {
					call_user_func('self::' . $ffo->getAuditFtn(), $ffo);
				}
			}
		}
	}

	private static function fieldEmpty($ffo)
	{
		if ($ffo->getValue() == '') {
			//==== If NOT a required field, return 'field empty = TRUE'
			//====   but do NOT register as an error
			if ($ffo->getRequired()) {
				$ffo->setErrMsg('cannot be BLANK.');
			}
			return true;
		}
		return false;
	}

	private static function validStrLen($ffo)
	{
		if  (strlen($ffo->getValue()) < $ffo->getMinSiz() || 
			 strlen($ffo->getValue()) > $ffo->getMaxSiz()) {
			if ($ffo->getMinSiz() != $ffo->getMaxSiz()) {
				$ffo->setErrMsg('[' . strlen($ffo->getValue()) . ' chars] must be between ' . 
					$ffo->getMinSiz() . ' and ' . $ffo->getMaxSiz() . ' chars in length.');
			} else {
				$ffo->setErrMsg('[' . strlen($ffo->getValue()) . ' chars] must be ' . 
					$ffo->getMaxSiz() . ' character(s) in length.');
			}
			return false;
		}
		return true;
	}

	/*
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			  JAVASCRIPT/PHP COMMON FTN NAMES section
	The ftn names are {camelCased} to conform with JavaScript ftn names
	*WARNING* These ftn names MUST BE IDENTICAL in PHP/JavaScript
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	*/

	public static function cmnValidRegex1($ffo)
	{
		if (!preg_match($ffo->getReAuditVal(), $ffo->getValue())) {
  			$ffo->setErrMsg($ffo->getReAuditErrTxt());
			return false;
		}
		return true;
	}

	public static function cmnValidPw1($ffo)
	{
		if (!self::validPw1Chars($ffo->getValue())) {
  			$ffo->setErrMsg('INVALID (see rules)');
			return false;
		}
		return true;
	}

	/*
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		  End of JAVASCRIPT/PHP COMMON FTN NAMES section
	++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	*/

	public static function isDigits1($val, $range)
	{
		$regExpr = '/^[0-9]{1,' . $range . '}$/';
		if (!preg_match($regExpr, $val)) {
			return false;
		}
		return true;
	}

	public static function isDigits2($val, $range)
	{
  		// allows prepended '-' sign (negative integers)
		$regExpr = '/^-?[0-9]{1,' . $range . '}$/';
		if (!preg_match($regExpr, $val)) {
			return false;
		}
		return true;
	}

	public static function validPw1Chars($val)
	{
		if (!preg_match(self::getReValidPw2(), $val)) {return false;}
		if (preg_match_all(self::RE_VALID_PW1_UC, $val, $matchesAry) 
			< self::getFfPwUcCnt()) {return false;}
		if (preg_match_all(self::RE_VALID_PW1_LC, $val, $matchesAry) 
			< self::getFfPwLcCnt()) {return false;}
		if (preg_match_all(self::RE_VALID_PW1_NM, $val, $matchesAry) 
			< self::getFfPwNmCnt()) {return false;}
		if (preg_match_all('/[' . self::RE_VALID_PW_SC_SET . ']/', $val, $matchesAry) 
			< self::getFfPwScCnt()) {return false;}
		return true;
	}

	public static function validTitle1($ffo)
	{
		if (!self::isValidTitle($ffo->getValue())) {
  			$ffo->setErrMsg('Invalid Title: [' . $ffo->getValue() . '].');
			return false;
		}
		return true;
	}

	public static function validStateCode1($ffo)
	{
		if (!self::isValidStateCd($ffo->getValue())) {
			$ffo->setErrMsg('Invalid code: [' . $ffo->getValue() . '].');
			return false;
		}
		return true;
	}

	// --------------------------------------

	public static function isCfvMethod($arg)
	{
		if (self::getCfvMethod() == $arg) {
			return true;
		} else {
			return false;
		}
	}

	private static function isValidStateCd($val)
	{
		return isset(self::$validStates[$val]);
	}

	private static function isValidTitle($val)
	{
		return isset(self::$validTitles[$val]);
	}

	protected static function isValidVidMime($val)
	{
		return isset(self::$validVidMimes[$val]);
	}

	protected static function isValidVidFext($fext)
	{
		$fext2 = strtolower($fext);
		if (array_search($fext2, self::$validVidMimes) !== false) {
			return true;
		}
		return false;
	}

	public static function getPhpIniBytes($phpIniVal)
	{
		// Expects value in php.ini format, e.g. => 2047 / 120M / 250K / 2GB

		// Numeric portion of value
		$maxSizNum = substr($phpIniVal, 0, -1);

		// Single-letter size/scale flag: [K]Bs / [M]Bs / [G]Bs
		$maxSizInd = strtoupper(substr($phpIniVal, -1));
		switch ($maxSizInd) {
			case 'M':
				$phpIniBytes = $maxSizNum * self::ONE_MB;
				break;
			case 'G':
				$phpIniBytes = $maxSizNum * self::ONE_GB;
				break;
			case 'K':
				$phpIniBytes = $maxSizNum * self::ONE_KB;
				break;
			default:
				if (is_int(0 + $phpIniVal)) {
					$phpIniBytes = $phpIniVal;
				} else {
					$phpIniBytes = -1; // TBs -or- invalid
				}
		}
		return $phpIniBytes;
	}

	public static function ynToBool($ans)
	{
		return (strtolower($ans) === 'y');
	}


	// GETTERS / SETTERS

	public static function getCfvMethod()
	{
		if (isset($_SESSION[self::SV_CFV_METHOD])) {
			self::$cfvMethod = $_SESSION[self::SV_CFV_METHOD];
		}
		return self::$cfvMethod;
	}

	public static function setCfvMethod($val)
	{
		self::$cfvMethod = $val;
	}

	public static function getFfPwMinlen()
	{
		return self::$ffPwMinlen;
	}

	public static function getFfPwMaxlen()
	{
		return self::$ffPwMaxlen;
	}

	public static function getFfPwUcCnt()
	{
		return self::$ffPwUcCnt;
	}

	public static function getFfPwLcCnt()
	{
		return self::$ffPwLcCnt;
	}

	public static function getFfPwNmCnt()
	{
		return self::$ffPwNmCnt;
	}

	public static function getFfPwScCnt()
	{
		return self::$ffPwScCnt;
	}

	public static function getServerRequestMethod()
	{
		return self::$serverRequestMethod;
	}

	public static function setServerRequestMethod($val = '')
	{
		if ($val === '') {
			// Stopped using [filter_input(INPUT_SERVER,] with $_SERVER because of known bug
			// Bug reported Feb 2014
//			self::$serverRequestMethod = filter_input(INPUT_SERVER, $_SERVER['REQUEST_METHOD'], 
//						FILTER_UNSAFE_RAW, FILTER_NULL_ON_FAILURE);
			self::$serverRequestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
		} else {
			self::$serverRequestMethod = $val;
		}
	}

	public static function jsCompOrigFfVals()
	{
		return self::$jsCompOrigFfVals;
	}

	public static function setJsCompOrigFfVals($bool)
	{
		self::$jsCompOrigFfVals = $bool;
	}

	public static function getReValidPw2()
	{
		return self::RE_VALID_PW2_PRE . self::RE_VALID_PW_SC_SET . self::RE_VALID_PW2_SUFX;
	}

	public static function getValidStatesAry()
	{
		return self::$validStates;
	}

	public static function getValidTitlesAry()
	{
		return self::$validTitles;
	}

	public static function getPhpUlMaxFilSizBytes()
	{
		return self::$phpUlMaxFilSizBytes;
	}

	public static function setPhpUlMaxFilSizBytes()
	{
		if (RAW_SERVER_NAME === SysMain::LOCALHOST) {
			self::$applUlMaxFilSizBytes  = self::getPhpIniBytes('5000M');
			self::$applUlMaxPostSizBytes = self::getPhpIniBytes('5000M');
		} else {
			self::$applUlMaxFilSizBytes  = self::getPhpIniBytes(self::APPL_UL_MAX_FILSIZ);
			self::$applUlMaxPostSizBytes = self::getPhpIniBytes(self::APPL_UL_MAX_POSTSIZ);
		}
		self::$phpUlMaxFilSizBytes = self::getPhpIniBytes(ini_get('upload_max_filesize'));
		if (self::$phpUlMaxFilSizBytes > self::$applUlMaxFilSizBytes) {
			trigger_error('[' . 'E0203' . '] - php.ini "upload_max_filesize" value [' . 
					ini_get('upload_max_filesize') . '] exceeds appl max [' . 
					self::APPL_UL_MAX_FILSIZ . ']', E_USER_ERROR);
		}
		self::$phpUlMaxPostSizBytes = self::getPhpIniBytes(ini_get('post_max_size'));
		if (self::$phpUlMaxPostSizBytes > self::$applUlMaxPostSizBytes) {
			trigger_error('[' . 'E0204' . '] - php.ini "post_max_size" value [' . 
					ini_get('post_max_size') . '] exceeds appl max [' . 
					self::APPL_UL_MAX_POSTSIZ . ']', E_USER_ERROR);
		}
	}

}
