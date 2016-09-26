<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\General\AuthUser;
use AppVvp\General\CsrfToken;
use AppVvp\General\PhpIniCfg;
use AppVvp\Validators\Validator;

/**
 *
 */
class FfoFactory
{

	/** -------------------------------------------
	 *  -------------------------------------------
	 * 	  FORMFIELD OBJECT [FFO] FACTORY METHODS
	 *  -------------------------------------------
	 *  -------------------------------------------
	 */

	public static function addFfoHiddenCsrfToken1($ffo)
	{
		// Hidden-type - FOR CSRF TOKEN ONLY - USED ON ALL FORMS
		$ffo->setDescr('{ Csrf Token }');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::CSRF_TOKEN_SIZE);
		$ffo->setMaxSiz(Validator::CSRF_TOKEN_SIZE);
//		if (TEST_RUN) {
		if (PhpIniCfg::getPiSrvrDef() === PhpIniCfg::DEV1 || 
				AuthUser::isAdminUser()) {
			$ffo->setAuditFtn('cmnValidRegex1');
			$ffo->setReAuditVal(Validator::RE_VALID_CSRF_TOKEN);
			$ffo->setReAuditErrTxt(Validator::RE_VALID_CSRF_TOKEN_ERR1);
		} else {
			$ffo->setAuditFtn('');
			$ffo->setFieldType('hidden');
		}
		$ffo->setValue($_SESSION[CsrfToken::getSvToken()]);
	}

	// -------------------------------------------

	public static function addFfoUsrNam1($ffo)
	{
		$ffo->setDescr('Email-ID/Username');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::FF_USRNAM_MINLEN);
		$ffo->setMaxSiz(Validator::FF_USRNAM_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_EMAIL1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_EMAIL1_ERR1);
	}

	public static function addFfoLoginPw1($ffo)
	{
		$ffo->setDescr('Password');
		$ffo->setRequired(true);
		$ffo->setMinSiz(1);
		$ffo->setMaxSiz(Validator::getFfPwMaxlen());
		$ffo->setFieldType('password');
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::getReValidPw2());
		$ffo->setReAuditErrTxt(Validator::RE_VALID_PW2_ERR1);
	}


	public static function addFfoNewPw1($ffo)
	{
		$ffo->setDescr('NEW Password');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::getFfPwMinlen());
		$ffo->setMaxSiz(Validator::getFfPwMaxlen());
		$ffo->setFieldType('password');
		$ffo->setAuditFtn('cmnValidPw1');
	}

	public static function addPwResetPin1($ffo)
	{
		$ffo->setDescr('Password Reset PIN');
		$ffo->setRequired(true);
		$ffo->setMaxSiz(10);
	}

	// -------------------------------------------

	public static function addFfoTitle1($ffo)
	{
		$ffo->setSqlNam('title');
		$ffo->setDescr('Title');
		$ffo->setRequired(false);
		$ffo->setMinSiz(3);
		$ffo->setMaxSiz(5);
		$ffo->setAuditFtn('validTitle1');

		$ffo->setSelectAry(Validator::getValidTitlesAry());
		$ffo->setSelectInit('-Select Title-');
	}

	public static function addFfoFirstName1($ffo)
	{
		$ffo->setSqlNam('firstname');
		$ffo->setDescr('First Name');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::FF_FIRSTNAM_MINLEN);
		$ffo->setMaxSiz(Validator::FF_FIRSTNAM_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_NAME1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_NAME1_ERR1);
	}

	public static function addFfoInitial1($ffo)
	{
		$ffo->setSqlNam('initial');
		$ffo->setDescr('Middle Initial');
		$ffo->setRequired(false);
		$ffo->setMinSiz(1);
		$ffo->setMaxSiz(1);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_INITIAL1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_INITIAL1_ERR1);
	}

	public static function addFfoLastName1($ffo)
	{
		$ffo->setSqlNam('lastname');
		$ffo->setDescr('Last Name');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::FF_FIRSTNAM_MINLEN);
		$ffo->setMaxSiz(Validator::FF_LASTNAM_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_NAME1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_NAME1_ERR1);
	}

	public static function addFfoAddr1($ffo)
	{
		$ffo->setSqlNam('address');
		$ffo->setDescr('Address');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::FF_ADDR_MINLEN);
		$ffo->setMaxSiz(Validator::FF_ADDR_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_ADDR1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_ADDR1_ERR1);
	}

	public static function addFfoCity1($ffo)
	{
		$ffo->setSqlNam('city');
		$ffo->setDescr('City');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::FF_FIRSTNAM_MINLEN);
		$ffo->setMaxSiz(Validator::FF_CITY_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_NAME1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_NAME1_ERR1);
	}

	public static function addFfoStateCode1($ffo)
	{
		$ffo->setSqlNam('statecode');
		$ffo->setDescr('State');
		$ffo->setRequired(true);
		$ffo->setMinSiz(2);
		$ffo->setMaxSiz(2);
		$ffo->setAuditFtn('validStateCode1');

		$ffo->setSelectAry(Validator::getValidStatesAry());
		$ffo->setSelectInit('-Select a state-');
//		$ffo->setDfltVal('CA');
	}

	public static function addFfoZipCode1($ffo)
	{
		$ffo->setSqlNam('zipcode');
		$ffo->setDescr('Zip Code');
		$ffo->setRequired(true);
		$ffo->setMinSiz(Validator::FF_USZIPCODE_MINLEN);
		$ffo->setMaxSiz(Validator::FF_USZIPCODE_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_USZIPCODE1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_USZIPCODE1_ERR1);
	}

	public static function addFfoPhone1($ffo)
	{
		$ffo->setSqlNam('phone');
		$ffo->setDescr('Phone# [ ___-___-____ ]');
		$ffo->setRequired(false);
		$ffo->setMinSiz(Validator::FF_PHONE_MINLEN);
		$ffo->setMaxSiz(Validator::FF_PHONE_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_PHONE1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_PHONE1_ERR1);
	}

	public static function addFfoTextAddr1($ffo)
	{
		$ffo->setSqlNam('textmsg_addr');
		$ffo->setDescr('Text Msg Address'); // ##########@providername.com
		$ffo->setRequired(false);
		$ffo->setMinSiz(Validator::FF_TEXTMSG_ADDR_MINLEN);
		$ffo->setMaxSiz(Validator::FF_TEXTMSG_ADDR_MAXLEN);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_TEXTMSG_ADDR1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_TEXTMSG_ADDR1_ERR1);
	}

	// -------------------------------------------

	// !!!! 2015-08: DO NOT USE - CHECKS SIZE ONLY AFTER FILE HAS BEEN UPLOADED !!!!
//	public static function addFfoVideoMaxSizeF1($ffo)
//	{
		// From PHP manual - MAX_FILE_SIZE hidden field:
		//  "The MAX_FILE_SIZE hidden field (measured in bytes) must precede the 
		//   file input field, and its value is the maximum filesize accepted by PHP. 
		//   This form element should always be used as it saves users the trouble 
		//   of waiting for a big file being transferred only to find that it was 
		//   too large and the transfer failed."

//		$ffo->setFieldType('hidden');
//		$ffo->setValue(50000000); // maxSize in bytes
//	}

	public static function addFfoUploadProgress($ffo)
	{
		$ffo->setFieldType('hidden');
		$ffo->setValue('Video~File~Upload');
	}

	public static function addFfoVideoUploadF1($ffo)
	{
		$ffo->setDescr('Video Upload File');
		$ffo->setRequired(true);
		$ffo->setMinSiz(5); // e.g. 'x.mp4'
		$ffo->setMaxSiz(60); // should not exceed sql def
		$ffo->setAcceptFilter('accept="video/*"');
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_FNAME1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_FNAME1_ERR1);
	}

	public static function addFfoVideoDescr1F1($ffo)
	{
		$ffo->setDescr('Video Description 1');
		$ffo->setRequired(false);
		$ffo->setMinSiz(0);
		$ffo->setMaxSiz(25); // should not exceed sql def
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_FNAME1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_FNAME1_ERR1);
	}

	public static function addFfoVideoDescr2F1($ffo)
	{
		$ffo->setDescr('Video Description 2');
		$ffo->setRequired(false);
		$ffo->setMinSiz(0);
		$ffo->setMaxSiz(25); // should not exceed sql def
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_FNAME1);
		$ffo->setReAuditErrTxt(Validator::RE_VALID_FNAME1_ERR1);
	}

	public static function addFfoVideoAspect($ffo)
	{
		$ffo->setDescr('Video Aspect Ratio');
		$ffo->setRequired(true);
		$ffo->setMinSiz(2);
		$ffo->setMaxSiz(2);
		$ffo->setAuditFtn('cmnValidRegex1');
		$ffo->setReAuditVal(Validator::RE_VALID_VID_ASPECT);
		$ffo->setReAuditErrTxt('Select HD -or- SD');
		$ffo->setSelectAry(['HD'=>'HD', 'SD'=>'SD',]);
	}

}
