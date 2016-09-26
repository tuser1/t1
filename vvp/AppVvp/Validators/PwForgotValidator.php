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
use AppVvp\General\BusDefs;
use AppVvp\General\CsrfToken;
use AppVvp\General\Email;
use AppVvp\General\PgMsgs;
use AppVvp\General\SysMain;
use AppVvp\Pages\PwResetPage;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class PwForgotValidator extends Validator
{
	public static function auditAndGenResetToken($formObj)
	{
		App::$dbMain->connect1();
		self::formPostFfosSetValMysqlClean($formObj);
		self::validateFfoVals1($formObj);
		if (!$formObj->getErrCnt1()) {
			// == Flash this message EVEN IF THE USERNAME ENTERED WAS INVALID ==
			// ! Do NOT provide information about whether the username was valid or not !
			PgMsgs::set('S007E', 'You have been EMAILED a link for resetting your password.');
			PgMsgs::set('S007T', 'A TEXT MESSAGE was sent containing your RESET PIN#.');
			$userName = $formObj->getFfo(Form::FFN_LOGIN_USRNAM)->getValue();
			if (DbAccess::getUserInfo($userName, $abortIfMissing = false)) {
				// getUserInfo() obtains customer number
				$pwResetToken = CsrfToken::genToken();
				if (!DbAccess::userExists($pwResetToken, 'pw_reset_token')) {
					self::createPwResetRequest($userName, $pwResetToken);
				} else {
					trigger_error('[' . 'E020' . '] - ' . 'PW RESET TOKEN [' . $pwResetToken . 
							'] already exists in a user record', E_USER_ERROR);
				}
			}
		}
	}

	public static function createPwResetRequest($userName, $pwResetToken)
	{
		$custRow = DbAccess::getUniqueRowByKeyVal('textmsg_addr', 'customers', 
					'cust_no', DbAccess::getCustNbr(), 'E0DB33');
		$textmsgAddr = $custRow['textmsg_addr'];
		$pwResetPin = mt_rand(100000, 999999); // gen 6-digit random pin#
		DbAccess::updUsrPwReset($userName, $pwResetToken, $pwResetPin);
		if (RAW_SERVER_NAME === SysMain::LOCALHOST) {
			PgMsgs::set('I002', '*** You are in DEV/localhost - YOU WERE ' . 
					'REDIRECTED HERE - NO EML/TEXT WAS SENT  - PIN# [' . 
					$pwResetPin . '] [' . $textmsgAddr . '] ***');
			App::redirect(FilNams::getPgUrl(FilNams::PN_PW_RESET) .
					QSDLM1 . PwResetPage::GETV_PW_RESET_TOKEN . '=' . $pwResetToken);
		} else {
			if (self::emailPwResetToken($userName, $pwResetToken) && !empty($textmsgAddr)) {
				// Only send text-msg if email was successful
				if (!self::textMsgPwResetPin($textmsgAddr, $pwResetPin)) {
					PgMsgs::set('E018T', '*** TEXT DELIVERY FAILED ***');
				}
			} else {
				PgMsgs::set('E018E', '*** EMAIL DELIVERY FAILED ***');
			}
		}
	}

	public static function emailPwResetToken($emailTo, $pwResetToken)
	{
		$subject = ' ** PASSWORD RESET REQUEST **';
		$messageBody = '
		<html>
		<head>
			<title>' . $subject . '</title>
		</head>
		<body>
			<p>Please use the link below to reset your password. YOUR PASSWORD CANNOT BE 
				CHANGED unless you CLICK THE LINK BELOW to verify the request:</p>
			<p><a href="' . FilNams::getPgFullUrl(FilNams::PN_PW_RESET) . 
				QSDLM1 . PwResetPage::GETV_PW_RESET_TOKEN . '=' . $pwResetToken . '">' . 
				FilNams::getPgFullUrl(FilNams::PN_PW_RESET) . 
				QSDLM1 . PwResetPage::GETV_PW_RESET_TOKEN . '=' . $pwResetToken . '</a></p>
			<p>**** IF YOU DID NOT MAKE THIS REQUEST, YOU NEED NOT TAKE ANY ACTION ****</p>
		</body>
		</html>
		';
		return Email::send($emailTo, $subject, $messageBody, 'html');
	}

	public static function textMsgPwResetPin($emailTo, $pwResetPin)
	{
		$subject = BusDefs::BUSNAME1_F2 . ' ** PASSWORD RESET **';
		$messageBody = 'PIN# for resetting your password: ' . $pwResetPin;
		return Email::send($emailTo, $subject, $messageBody, 'plain');
	}

}
