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
use AppVvp\General\BusDefs;
use AppVvp\General\Email;
use AppVvp\General\FileSystem;
use AppVvp\General\PgMsgs;
use AppVvp\General\SysMain;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class NewUserValidator extends Validator
{
	public static function auditAndUpdate($formObj)
	{
		App::$dbMain->connect1();
		self::formPostFfosSetValMysqlClean($formObj);

		// NOTE: ?? No need to check for prev add from other user ??
		self::validateFfoVals1($formObj);
		if (!$formObj->getErrCnt1()) {
			self::newUserValidateFlds1(
				$formObj->getFfo(Form::FFN_LOGIN_USRNAM),
				$formObj->getFfo(Form::FFN_NEW_PW),
				$formObj->getFfo(Form::FFN_NEW_PW_RETYP));
		}
		if ($formObj->getErrCnt1() === 0) {
			// ===================================
			// NO errors - update the database
			// ===================================
			$custNo = DbAccess::addNewUser(
					$formObj->getFfo(Form::FFN_LOGIN_USRNAM)->getValue(), 
					$formObj->getFfo(Form::FFN_NEW_PW)->getValue());
			DbAccess::addNewCustomer($custNo);
			DbAccess::clearFailedUserLogins(
					$formObj->getFfo(Form::FFN_LOGIN_USRNAM)->getValue());

 			// =================================================================
			// ====== Create the new user's VIDEOS and IMAGES directories ======
			FileSystem::createDir(FilNams::getAbsPathVideo(), 0755, true, 'VIDEOS');
			// Create empty 'index.php' file in new dir
			$fh = fopen(FilNams::getAbsPathVideo() . '/' . FilNams::PN_INDEX . '.' . 
					PG_FILEEXT, 'x+');
			fclose($fh);

			FileSystem::createDir(FilNams::getAbsPathImages(), 0755, true, 'IMAGES');
			// Create empty 'index.php' file in new dir
			$fh = fopen(FilNams::getAbsPathImages() . '/' . FilNams::PN_INDEX . '.' . 
					PG_FILEEXT, 'x+');
			fclose($fh);
 			// =================================================================

			AuthUser::setLoginSessVars($formObj->getFfo(
					Form::FFN_LOGIN_USRNAM)->getValue());
			DbAccess::logUserActivity(DbAccess::USR_ACT_NEWUSER);
			$_SESSION[AuthUser::SV_LOGIN_FIRSTNAM] = 'New user';
			DbAccess::logUserActivity(DbAccess::USR_ACT_LOGIN);
			$_SESSION[Form::SV_NEWUSER_RECORD] = '';

			PgMsgs::set('S003', 'New USER created.  Please enter your info.');

			App::redirect(FilNams::getPgUrl(FilNams::PN_MYINFO));
		}
	}

	private static function newUserValidateFlds1($ffoUsr, $ffoPwNew, $ffoPwRetyp)
	{
		// New user must not ALREADY EXIST in database
		if (DbAccess::userExists($ffoUsr->getValue())) {
			$ffoUsr->setErrMsg('Username ALREADY EXISTS - Please try a NEW username.');

		// RE-TYPED new PW must match NEW pw
		} elseif ($ffoPwNew->getValue() != $ffoPwRetyp->getValue()) {
			$ffoPwRetyp->setErrMsg('Does not match NEW password.');

		} else {
			if (RAW_SERVER_NAME === SysMain::LOCALHOST) {
				PgMsgs::set('I001', '*** You are in DEV/localhost - ' . 
							'EMAIL-ADDRESS [USERNAME] CANNOT BE VALIDATED ***');
			} else {
				// Send welcome-msg to validate email-address
				if (!self::emailWelcomeMsg($ffoUsr->getValue())) {
					$ffoUsr->setErrMsg('**** EMAIL-ADDRESS DOES NOT EXIST ****');
				}
			}
		}
	}

	public static function emailWelcomeMsg($emailTo)
	{
		$subject = 'Welcome to ' . BusDefs::BUSNAME1_F1 . '!';
		$messageBody = '
		<html>
		<head>
			<title>' . $subject . '</title>
		</head>
		<body>
			<p>Welcome to ' . BusDefs::BUSNAME1_F1 . '!  Your login user has been created.</p>
		</body>
		</html>
		';
		return Email::send($emailTo, $subject, $messageBody, 'html');
	}

}
