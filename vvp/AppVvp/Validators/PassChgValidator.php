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
use AppVvp\General\Email;
use AppVvp\General\PgMsgs;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class PassChgValidator extends Validator
{
	public static function auditAndUpdate($formObj)
	{
		self::formPostFfosSetValMysqlClean($formObj);

		// NOTE: No need to check for prev upd from other user before updating
		//  as the login-validation will fail anyway if this happens.
		self::validateFfoVals1($formObj);
		if (!$formObj->getErrCnt1()) {
			self::passChgValidateFlds1(
				$formObj->getFfo(Form::FFN_LOGIN_PW),
				$formObj->getFfo(Form::FFN_NEW_PW),
				$formObj->getFfo(Form::FFN_NEW_PW_RETYP));
		}
		if ($formObj->getErrCnt1() === 0) {
			// ===================================
			// NO errors - update the database
			// ===================================
			DbAccess::updLoginUsrPw($_SESSION[AuthUser::SV_LOGIN_USRNAM], 
					$formObj->getFfo(Form::FFN_NEW_PW)->getValue());

			PgMsgs::set('S002', 'PASSWORD has been successfully UPDATED.');

			App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY));
		}
	}

	private static function passChgValidateFlds1($ffoPw, $ffoPwNew, $ffoPwRetyp)
	{
		// Authenticate CURRENT PW against database
		if (!DbAccess::chkLoginUsrPw($_SESSION[AuthUser::SV_LOGIN_USRNAM], 
				$ffoPw->getValue())) {
			$ffoPw->setErrMsg('Login fail on password.');

		// NEW PW must not be the same as CURRENT PW
		} elseif ($ffoPwNew->getValue() == $ffoPw->getValue()) {
			$ffoPwNew->setErrMsg('Password is the same as the CURRENT password.');

		// RE-TYPED new PW must match NEW pw
		} elseif ($ffoPwNew->getValue() != $ffoPwRetyp->getValue()) {
			$ffoPwRetyp->setErrMsg('Does not match NEW password.');
		}
	}

	public static function emailPwWasChanged($emailTo)
	{
		$subject = ' ** RECENT ACCOUNT ACTIVITY **';
		$messageBody = '
		<html>
		<head>
			<title>' . $subject . '</title>
		</head>
		<body>
			<p>Your password was recently changed.  IF YOU DID NOT MAKE THIS CHANGE:</p>
			<p>**** Please contact us immediately ****</p>
			<p>- OR -</p>
			<p><a href="' . FilNams::getPgFullUrl(FilNams::PN_PW_FORGOT) . 
				'">Click this link to reset your password</a></p>
		</body>
		</html>
		';
		return Email::send($emailTo, $subject, $messageBody, 'html');
	}

}
