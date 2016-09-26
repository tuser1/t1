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
use AppVvp\Pages\Page;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class LoginValidator extends Validator
{
	public static function auditAndLogin($formObj)
	{
		App::$dbMain->connect1();
		self::formPostFfosSetValMysqlClean($formObj);

		self::validateFfoVals1($formObj);
		if (!$formObj->getErrCnt1()) {
			self::loginValidateFlds1(
				$formObj->getFfo(Form::FFN_LOGIN_USRNAM), 
				$formObj->getFfo(Form::FFN_LOGIN_PW));
		}
		if ($formObj->getErrCnt1() === 0) {
			// ***********************************
			// ******** LOGIN SUCCESSFUL *********
			// ***********************************
			DbAccess::clearFailedUserLogins(
					$formObj->getFfo(Form::FFN_LOGIN_USRNAM)->getValue());
			AuthUser::setLoginSessVars($formObj->getFfo(
				Form::FFN_LOGIN_USRNAM)->getValue());
			DbAccess::logUserActivity(DbAccess::USR_ACT_LOGIN);
			if (isset($_SESSION[AuthUser::SV_LOGIN_REFERER])) {
				$loginReferer = $_SESSION[AuthUser::SV_LOGIN_REFERER];
				unset($_SESSION[AuthUser::SV_LOGIN_REFERER]);
				App::redirect($loginReferer);
			} else {
				if (!AuthUser::isAdminUser()) {
					App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY));
				} else {
					$_SESSION[Page::SV_USERPAG] = FilNams::PN_VIDSMY;
					App::redirect(FilNams::getPgUrl(FilNams::getAdminPagNamStart()));
				}
			}
		}
	}

	private static function loginValidateFlds1($ffoUsr, $ffoPw)
	{
	if (AuthUser::userIsThrottled($ffoUsr->getValue())) {
		$minutesLeft = ceil(AuthUser::getRemainingThrottleSecs() / 60);
		PgMsgs::set('E022', "*** YOU MUST WAIT {$minutesLeft} MINUTES BEFORE ATTEMPTING " . 
				'ANOTHER LOGIN ***.');
		$ffoUsr->setErrMsg('**** TOO MANY LOGIN FAILURES ****');

	// Check username and password in database
	} elseif (! DbAccess::chkLoginUsrPw($ffoUsr->getValue(), $ffoPw->getValue())) {
			$ffoUsr->setErrMsg('Username/Password combination you entered does not match.');
			 $ffoPw->setErrMsg('Username/Password combination you entered does not match.');
			 // Log each failed login for possibility of throttling username
			 DbAccess::logFailedUserLogin($ffoUsr->getValue(), AuthUser::getFailedUserLogin());
		}
	}

}
