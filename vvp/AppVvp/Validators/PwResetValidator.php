<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Validators;

use AppVvp\App;
use AppVvp\Db\DbAccess;
use AppVvp\FilNams;
use AppVvp\Forms\Form;
use AppVvp\General\AuthUser;
use AppVvp\General\PgMsgs;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class PwResetValidator extends Validator
{
	const PW_RESET_TOKEN_LIMIT_SECS = 420; // 7 min

	private static $pwResetToken = '';
	private static $userNam = '';
	private static $pwResetPin = '';
	private static $pwResetTime = 0;


	public static function auditPwResetToken()
	{
		$tokenInvalid = false;
		App::$dbMain->connect1();
		self::$userNam = '';
		if (self::$pwResetToken !== '') {
			DbAccess::getUserViaPwResetToken(self::$pwResetToken);
		}
		if (self::$userNam === '') {
			// Missing PARMNAME or BLANK token passed -OR- token not found in users table
			// Redirect to 'forgot my password':
			$tokenInvalid = true;
		} elseif ( time() > (self::$pwResetTime + self::PW_RESET_TOKEN_LIMIT_SECS) ) {
			// PW reset token EXPIRED
			$tokenInvalid = true;
		}
		if ($tokenInvalid) {
			PgMsgs::set('E019', ' *************** PW RESET TOKEN INVALID ***************');
			App::redirect(FilNams::getPgUrl(FilNams::PN_PW_FORGOT));
		}
	}

	public static function auditAndResetPw($formObj)
	{
		self::formPostFfosSetValMysqlClean($formObj);
		// ? TODO ? - Check for prev upd from other user before updating?
		self::validateFfoVals1($formObj);
		if (!$formObj->getErrCnt1()) {
			self::pwResetValidateFlds1(
				$formObj->getFfo(Form::FFN_PW_RESET_PIN),
				$formObj->getFfo(Form::FFN_NEW_PW),
				$formObj->getFfo(Form::FFN_NEW_PW_RETYP));
		}
		if ($formObj->getErrCnt1() === 0) {
			// ===================================
			// NO errors - update the database
			// ===================================
			$userNewPw = $formObj->getFfo(Form::FFN_NEW_PW)->getValue();

			// Update user's password with NEW pw value
			DbAccess::updLoginUsrPw(self::$userNam, $userNewPw);
			DbAccess::clearFailedUserLogins(self::$userNam);

			// Login the user with the NEW pw
			if (DbAccess::chkLoginUsrPw(self::$userNam, $userNewPw)) {
				AuthUser::setLoginSessVars(self::$userNam);
				DbAccess::logUserActivity(DbAccess::USR_ACT_LOGIN);
				PgMsgs::set('S008', 'Your PASSWORD has been successfully RESET.');
				App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY));
			} else {
				PgMsgs::set('E017', 'PASSWORD RESET FAILED - PLEASE RETRY ...');
				App::redirect(FilNams::getPgUrl(FilNams::PN_PW_FORGOT));
			}
		}
	}

	private static function pwResetValidateFlds1($ffoPwResetPin, $ffoPwNew, $ffoPwRetyp)
	{
		// VALIDATE PIN#
		if ($ffoPwResetPin->getValue() !== self::$pwResetPin) {
			DbAccess::updUsrPwReset(self::$userNam, '', ''); // Invalid pin# / cancel PW reset
			PgMsgs::set('E021', 
					'INVALID PIN# WAS ENTERED - PLEASE REQUEST ANOTHER PASSWORD RESET');
			App::redirect(FilNams::getPgUrl(FilNams::PN_PW_FORGOT));

		// RE-TYPED new PW must match NEW pw
		} elseif ($ffoPwNew->getValue() !== $ffoPwRetyp->getValue()) {
			$ffoPwRetyp->setErrMsg('Does not match NEW password.');
		}
	}


	// GETTERS / SETTERS

	public static function getPwResetToken()
	{
		return self::$pwResetToken;
	}

	public static function setPwResetToken($pwResetToken)
	{
		self::$pwResetToken = $pwResetToken;
	}

	public static function getUserNam()
	{
		return self::$userNam;
	}

	public static function setUserNam($userNam)
	{
		self::$userNam = $userNam;
	}

	public static function getPwResetPin()
	{
		return self::$pwResetPin;
	}

	public static function setPwResetPin($pwResetPin)
	{
		self::$pwResetPin = $pwResetPin;
	}

	public static function getPwResetTime()
	{
		return self::$pwResetTime;
	}

	public static function setPwResetTime($pwResetTime)
	{
		self::$pwResetTime = $pwResetTime;
	}

}
