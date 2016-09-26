<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Db;

use AppVvp\App;
use AppVvp\Forms\Form;
use AppVvp\General\AuthUser;
use AppVvp\General\CsrfToken;
use AppVvp\General\ErrHandler;
use AppVvp\General\SysMain;
use AppVvp\Validators\PassChgValidator;
use AppVvp\Validators\PwResetValidator;
use AppVvp\Validators\Validator;
use PDO;

/**
 * PDO MySQL database custom methods
 * 
 */
class DbAccess
{
	const SV_CUST_MODDTE		= 'cust_moddte';
	const SV_CUST_MODDTE_DSP1	= 'cust_moddte_dsp1';

	const USR_ACT_NEWUSER = 'NU';
	const USR_ACT_LOGIN	  = 'LG';
	const USR_ACT_CHGINFO = 'CI';
	const USR_ACT_CHGPASS = 'CP';
	const USR_ACT_PWTOKEN = 'PTKN';
	const USR_ACT_THROTTLELOG = 'THLG';
	const USR_ACT_UL_VID  = 'ULVI';
	const USR_ACT_VID_UPD = 'VUPD';
	const USR_ACT_VID_DEL = 'VDEL';
	const USR_ACT_VID_DEL_ALL = 'VDLA';

	const USR_ACT_PAGE_SIZE = 30;

	private static $custNbr;
	private static $sqlRowChgd = false;



	public static function getDbTest()
	{
		$query = 'SELECT t1 FROM test1;';
		App::$dbTest->stmtPrepAndExec($query, array(), 'E0DBTST1');
		while ($row = App::$dbTest->getStmt()->fetch(PDO::FETCH_ASSOC)) {
			var_dump($row['t1']);
		}
	}


	public static function addNextVideoToVidpage($vidpageId, $vidFlnam, $altText, 
			$altTxt2, $vidType, $aspect)
	{
		// Call custom database procedure to prevent dupe filenames
		$query = 'CALL add_next_video_to_vidpage(?, ?, ?, ?, ?, ?, ?);';
		App::$dbMain->stmtPrepAndExec($query, array($vidpageId, $vidFlnam, $altText, $altTxt2, 
				$vidType, $aspect, $_SERVER["REQUEST_TIME_FLOAT"]), 'E0DB22');
	}

	public static function getVidpageVideos($pagObj)
	{
		$pagId = $pagObj->getVidNamsKey();
		$query = 'SELECT vp.vid_flnam, vp.alt_text, vp.alt_txt2, vp.vid_type , vp.aspect ' . 
				 '  FROM vidpage_videos vp ' . 
				 ' WHERE vp.vidpage_id = ? ' . 
				 ' ORDER BY vp.time_created;';
		App::$dbMain->stmtPrepAndExec($query, array($pagId), 'E0DB04');
		while ($row = App::$dbMain->getStmt()->fetch(PDO::FETCH_ASSOC)) {
			$pagObj->addVidNam($row['vid_flnam'], $row['alt_text'], $row['alt_txt2'], 
					$row['vid_type'], $row['aspect']);
		}
	}

	public static function delCustomerVideo($pagId, $vidFlNam)
	{
		if ($vidFlNam === '') {
			// delete ALL videos
			$query = 'DELETE FROM vidpage_videos WHERE vidpage_id = ?;';
			$parmsAry = array($pagId);
		} else {
			// delete SELECTED video
			$query = 'DELETE FROM vidpage_videos WHERE vidpage_id = ? AND vid_flnam = ?;';
			$parmsAry = array($pagId, $vidFlNam);
		}
		App::$dbMain->stmtPrepAndExec($query, $parmsAry, 'E0DB25');
		if (App::$dbMain->getStmt()->rowCount() === 0) {
			ErrHandler::triggerDbErr1('E0DB25N', 
				'NO videos were found to delete for cust# [' . $pagId . ']', 
				E_USER_ERROR, App::$dbMain);
		}
	}

	public static function userExists($lookupVal, $lookupKey = 'user_name')
	{
		$query = 'SELECT user_name FROM users WHERE ' . $lookupKey . ' = ?;';
		App::$dbMain->stmtPrepAndExec($query, array($lookupVal), 'E0DB18');
		return (App::$dbMain->getStmt()->rowCount() > 0);
	}

	public static function logUserActivity($actCode, $loginUsr = '')
	{
		if ($loginUsr === '') {
			$loginUser = $_SESSION[AuthUser::SV_LOGIN_USRNAM];
		} else {
			$loginUser = $loginUsr;
		}
		$sessId = session_id();
		$query = 'INSERT INTO user_activity (user_name, activity_code, ' . 
							'session_id, time_created) ' . 
				 'VALUES(?, ?, ?, ?);';
		App::$dbMain->stmtPrepAndExec($query, array($loginUser, 
				$actCode, $sessId, $_SERVER['REQUEST_TIME_FLOAT']), 'E0DB21');
	}

	// Record number [$startRow] is zero-relative
	public static function getUserActivity($startRow = 0, 
				$numberOfRows = self::USR_ACT_PAGE_SIZE)
	{
		$query = 'SELECT ua.seq_num, ua.user_name, ua.activity_code, ac.activity_descr, ' . 
						'ua.session_id, ua.moddte, ua.time_created ' . 
				 '  FROM user_activity ua JOIN activity_codes ac ' . 
				 '    ON ua.activity_code = ac.activity_code ' . 
				 ' ORDER BY ua.seq_num DESC ' . 
				 " LIMIT $startRow, $numberOfRows;";
		App::$dbMain->query($query, PDO::FETCH_ASSOC, 'E0DB23'); // NO binding needed
		return App::$dbMain->getQueryResult();
	}

	public static function getUsers()
	{
		$query = 'SELECT * FROM users ORDER BY admin_user_flag DESC, user_name;';
		App::$dbMain->query($query, PDO::FETCH_ASSOC, 'E0DB24'); // NO binding needed
		return App::$dbMain->getQueryResult();
	}

	public static function getFailedUserLogins($loginUsr)
	{
		return self::getUniqueRowByKeyVal('fail_count, fail_time', 'login_failures', 
				'user_name', $loginUsr, 'E0DB28', $abortIfMissing = false);
	}

	public static function logFailedUserLogin($loginUsr, $failedUserLogin)
	{
		if (isset($failedUserLogin)) {
			$query = 'UPDATE login_failures SET fail_count = ?, fail_time = ? ' . 
					 'WHERE user_name = ? LIMIT 1;';
			$failCount = $failedUserLogin['fail_count'] + 1;
			App::$dbMain->stmtPrepAndExec(
					$query, array($failCount, time(), $loginUsr), 'E0DB29U');
		} else {
			$query = 'INSERT INTO login_failures (user_name, fail_count, fail_time) ' . 
					 'VALUES(?, ?, ?);';
			$failCount = 1;
			App::$dbMain->stmtPrepAndExec(
					$query, array($loginUsr, $failCount, time()), 'E0DB29I');
		}
		if ($failCount === AuthUser::FAILED_LOGINS_ALLOWED) {
			DbAccess::logUserActivity(DbAccess::USR_ACT_THROTTLELOG, $loginUsr);
		}
	}

	public static function clearFailedUserLogins($loginUsr)
	{
		$query = 'UPDATE login_failures SET fail_count = ?, fail_time = ? ' . 
				 'WHERE user_name = ? LIMIT 1;';
		App::$dbMain->stmtPrepAndExec($query, array(0, time(), $loginUsr), 'E0DB30');
	}

	public static function addNewUser($loginUsr, $loginPw)
	{
		$storedPw = password_hash($loginPw, PASSWORD_BCRYPT);
		$userDirname = CsrfToken::genToken();
		$query = 'INSERT INTO users (user_name, password, admin_user_flag, user_dirname) ' . 
					'VALUES(?, ?, 0, ?);';
		App::$dbMain->stmtPrep($query, 'E0DB19P');
		/** Instead of calling [App::$dbMain->stmtExec('E0DB???');] with default
		 *  errMsg, use conditional call and then call 'trigger_db_err1'
		 *  (if error) to avoid writing PW to error_log file.
		 */
		if (!App::$dbMain->getStmt()->execute(array($loginUsr, $storedPw, $userDirname))) {
			ErrHandler::triggerDbErr1('E0DB19E', 'SQL error in [INSERT INTO users]', 
				E_USER_ERROR, App::$dbMain);
		}
		AuthUser::setUserSubPath($userDirname);
		return App::$dbMain->getLastInsertId(); // cust# gen'd by AUTO_INCREMENT
	}

	public static function addNewCustomer($custNo)
	{
		$query = 'INSERT INTO customers (cust_no, firstname, initial, lastname, ' . 
					'address, city, statecode, zipcode, phone, title) ' . 
					"VALUES(?, '', '', '', '', '', '', '', '', '');";
		App::$dbMain->stmtPrep($query, 'E0DB20P');
		App::$dbMain->getStmt()->bindParam(1, $custNo, PDO::PARAM_INT);
		App::$dbMain->stmtExecNoParm('E0DB20E');
	}

	public static function chkLoginUsrPw($loginUsr, $loginPw)
	{
		$query = 
			'SELECT u.cust_no, u.password, u.admin_user_flag, u.user_dirname, c.firstname ' . 
			  'FROM users u, customers c ' . 
			 'WHERE user_name = ? ' . 
			   'AND c.cust_no = u.cust_no LIMIT 1;';
		App::$dbMain->stmtPrepAndExec($query, array($loginUsr), 'E0DB05');
		if (App::$dbMain->getStmt()->rowCount() === 1) {
			$userRow = App::$dbMain->getStmt()->fetch(PDO::FETCH_ASSOC);
			if (password_verify($loginPw, $userRow['password'])) { // the HASHED pw
				self::$custNbr = $userRow['cust_no'];
				$_SESSION[AuthUser::SV_LOGIN_FIRSTNAM] = $userRow['firstname'];
				AuthUser::setAdminUserFlag($userRow['admin_user_flag']);
				AuthUser::setUserSubPath($userRow['user_dirname']);
				return true;
			}
		}
		return false;
	}

	public static function updLoginUsrPw($loginUsr, $loginPw)
	{
		$storedPw = password_hash($loginPw, PASSWORD_BCRYPT);
		// *WARNING*  !! SQL syntax error MAY display pw !!
		// =====================================================================
		//  For PW RESETs: INIT pw_reset_token/pin => '' and  pw_reset_time => 0
		// =====================================================================
		$query = "UPDATE users SET password = ?, pw_reset_token = '', pw_reset_pin = '', " . 
					'pw_reset_time = 0 WHERE user_name = ? LIMIT 1;';
		App::$dbMain->stmtPrep($query, 'E0DB08P');
		/** Instead of calling [App::$dbMain->stmtExec('E0DB???');] with default
		 *  errMsg, use conditional call and then call 'trigger_db_err1'
		 *  (if error) to avoid writing PW to error_log file.
		 */
		if (!App::$dbMain->getStmt()->execute(array($storedPw, $loginUsr))) {
			ErrHandler::triggerDbErr1('E0DB08E', 'SQL error in [UPDATE users SET password]', 
				E_USER_ERROR, App::$dbMain);
		}
		if (App::$dbMain->getStmt()->rowCount() !== 1) {
			ErrHandler::triggerDbErr1('E0DB09', 
				'NO record was found to UPDATE in table [users] for user_name [' . 
				$loginUsr . ']', E_USER_ERROR, App::$dbMain);
		}
		if (RAW_SERVER_NAME !== SysMain::LOCALHOST) {
			// Email user of recent PW change activity
			if (!PassChgValidator::emailPwWasChanged($loginUsr)) {
//				PgMsgs::set('E023', '*** EMAIL DELIVERY FAILED ***');
			}
		}
		// If PW RESET then NOT logged in, must provide username
		self::logUserActivity(self::USR_ACT_CHGPASS, $loginUsr);
	}

	public static function updUsrPwReset($loginUsr, $pwResetToken, $pwResetPin)
	{
		// *WARNING*  !! SQL syntax error MAY display token !!
		$query = 'UPDATE users SET pw_reset_token = ?, pw_reset_pin = ?, pw_reset_time = ? ' . 
				'WHERE user_name = ? LIMIT 1;';
		App::$dbMain->stmtPrep($query, 'E0DB26P');
		/** Instead of calling [App::$dbMain->stmtExec('E0DB???');] with default
		 *  errMsg, use conditional call and then call 'trigger_db_err1'
		 *  (if error) to avoid writing token to error_log file.
		 */
		if (!empty($pwResetToken)) {
			$systime = $_SERVER['REQUEST_TIME_FLOAT'];
		} else {
			$systime = 0; // $pwResetToken empty - clear PW resetting
		}
		if (!App::$dbMain->getStmt()->execute(array($pwResetToken, $pwResetPin,  
					$systime, $loginUsr))) {
			ErrHandler::triggerDbErr1('E0DB26E', 'SQL error in [UPDATE users SET pw_reset_token]', 
				E_USER_ERROR, App::$dbMain);
		}
		if (App::$dbMain->getStmt()->rowCount() !== 1) {
			ErrHandler::triggerDbErr1('E0DB26', 
				'NO record was found to UPDATE in table [users] for user_name [' . 
				$loginUsr . ']', E_USER_ERROR, App::$dbMain);
		}
		if (!empty($pwResetToken)) {
			// NOT logged in, must provide username
			self::logUserActivity(self::USR_ACT_PWTOKEN, $loginUsr);
		}
	}

	public static function getUserViaPwResetToken($pwResetToken)
	{
		$row = self::getUniqueRowByKeyVal('user_name, pw_reset_pin, pw_reset_time', 
			'users', 'pw_reset_token', $pwResetToken, 'E0DB27', $abortIfMissing = false);
		if ($row) {
			PwResetValidator::setUserNam    ($row['user_name']);
			PwResetValidator::setPwResetPin ($row['pw_reset_pin']);
			PwResetValidator::setPwResetTime($row['pw_reset_time']);
		}
	}

	public static function getUserInfo($usrNam, $abortIfMissing = true)
	{
		$row = self::getUniqueRowByKeyVal('cust_no, admin_user_flag, user_dirname', 
				'users', 'user_name', $usrNam, 'E0DB16', $abortIfMissing);
		if ($row) {
			self::$custNbr = $row['cust_no'];
			AuthUser::setAdminUserFlag($row['admin_user_flag']);
			AuthUser::setUserSubPath($row['user_dirname']);
			return true;
		} else {
			return false;
		}
	}

	public static function getCustInfo($formObj, $compareOnly = false)
	{
		// ----- Assemble 'customers' table SELECT query from FFOs in FORM -----
		// e.g. 'SELECT {col1}, {col2}, ... moddte FROM customers WHERE cust_no = ?;'
		$prevfldDlm = '';
		$query = 'SELECT ';
		foreach ($formObj->getFfoList() as $ffo) {
			if ($ffo->getSqlNam()) {  // If FFO 'sqlNam' is NOT BLANK
				$query.= $prevfldDlm . $ffo->getSqlNam();
				$prevfldDlm = ', ';
			}
		}
		$query.= $prevfldDlm . 
				'moddte, date_time_display_01(moddte) AS moddte_dsp1 ' . 
				'FROM customers WHERE cust_no = ? LIMIT 1;';
		// ---------------------------------------------------------------
		App::$dbMain->stmtPrepAndExec($query, array(self::$custNbr), 'E0DB12');
		if (App::$dbMain->getStmt()->rowCount() !== 1) {
			ErrHandler::triggerDbErr1('E0DB13', 
				'NO record was found in table [customers] for cust_no [' . 
				self::$custNbr . ']', E_USER_ERROR, App::$dbMain);
		}
		$row = App::$dbMain->getStmt()->fetch(PDO::FETCH_ASSOC);
		foreach ($formObj->getFfoList() as $ffo) {
			if ($ffo->getSqlNam() != '') {
				if (!$compareOnly) {
					$ffo->setValue($row[$ffo->getSqlNam()]);
				}
			}
		}
		if (!$compareOnly) {
			$_SESSION[self::SV_CUST_MODDTE] = $row['moddte'];
			$_SESSION[self::SV_CUST_MODDTE_DSP1] = $row['moddte_dsp1'];
		} else {
			if ($row['moddte'] != $_SESSION[self::SV_CUST_MODDTE]) {
				self::$sqlRowChgd = true;
			}
		}
		unset($ffo); // per PHP doc
	}


	public static function getUniqueRowByKeyVal($colList, $tblName, $keyName, $keyVal, 
			$errKey, $abortIfMissing = true)
	{
		$query = "SELECT $colList FROM $tblName WHERE $keyName = ?;";
		App::$dbMain->stmtPrepAndExec($query, array($keyVal), $errKey);
		if (App::$dbMain->getStmt()->rowCount() === 0) {
			if ($abortIfMissing) {
				ErrHandler::triggerDbErr1($errKey, 
					'NO rows found in table [' . $tblName . '] for [' . 
					$keyName . '=' . $keyVal . ']', E_USER_ERROR, App::$dbMain);
			} else {
				/**
				 * App::$dbMain->getStmt()->fetch(PDO::FETCH_ASSOC)
				 * returns [boolean/false] if NO ROWS FOUND, so return [false] here
				 */
				return false;
			}
		}
		if (App::$dbMain->getStmt()->rowCount() > 1) {
			ErrHandler::triggerDbErr1($errKey, 
				'MULTIPLE rows found in table [' . $tblName . '] for [' . 
				$keyName . '=' . $keyVal . ']', E_USER_ERROR, App::$dbMain);
		}
		return App::$dbMain->getStmt()->fetch(PDO::FETCH_ASSOC);
	}


	public static function updCustInfo($formObj)
	{
		// ----- Assemble 'customers' table UPDATE query from FFOs in FORM -----
		$prevfldDlm = '';
		$query = 'UPDATE customers SET ';
		foreach ($formObj->getFfoList() as $ffo) {
			if ($ffo->getSqlNam() != '') {
				$query.= $prevfldDlm . $ffo->getSqlNam() . ' = ?';
				$prevfldDlm = ', ';
			}
		}
		$query.= ' WHERE cust_no = ? LIMIT 1;';
		unset($ffo); // per PHP doc
		// ----------------------------------------------------------------
  		// Bind FFO update-values in array '$parmsAry[]'
		$parmsAry = array();
		foreach ($formObj->getFfoList() as $ffo) {
			if ($ffo->getSqlNam() != '') {
				$parmsAry[] = $ffo->getValue();
			}
		}
		$parmsAry[] = self::$custNbr; // Add last FFO update-value to array
		App::$dbMain->stmtPrepAndExec($query, $parmsAry, 'E0DB14');
		if (App::$dbMain->getStmt()->rowCount() > 0) {
			$_SESSION[AuthUser::SV_LOGIN_FIRSTNAM] = 
					$formObj->getFfo(Form::FFN_FIRSTNAME1)->getValue();
			unset($_SESSION[self::SV_CUST_MODDTE]);
			unset($_SESSION[self::SV_CUST_MODDTE_DSP1]);
			self::logUserActivity(self::USR_ACT_CHGINFO);
			return true;
		} else {
			return false; // NO CHANGES were entered by the user
		}
	}


	/**
	 * Clean/untaint user input from $_GET/$_POST/$_SESSION arrays.
	 * SAMPLE USAGE: $usrID = self::mysqlClean($_GET, 'usrID');
	 */
	public static function mysqlClean($ary, $idx)
	{
		return Validator::cleanInput1($ary["{$idx}"]);
	}


	public static function sqlRowChgd()
	{
		return self::$sqlRowChgd;
	}

	public static function close_conn()
	{
		if (is_object(App::$dbMain)) {
			App::$dbMain->close();
		}
		if (is_object(App::$dbTest)) {
			App::$dbTest->close();
		}
	}


	// GETTERS / SETTERS

	public static function getCustNbr()
	{
		return self::$custNbr;
	}

}
