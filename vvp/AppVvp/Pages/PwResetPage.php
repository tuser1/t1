<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Forms\PwResetForm;
use AppVvp\Validators\PwResetValidator;
use AppVvp\Validators\Validator;

/**
 * For PW reset PAGE object
 */
class PwResetPage extends Page
{
	const GETV_PW_RESET_TOKEN = 'token';


	public function __construct($pgName)
	{
		// Retrieve $_GET pwResetToken
		if (Validator::issetGetAddAry1(self::GETV_PW_RESET_TOKEN)) {
			PwResetValidator::setPwResetToken(
					Validator::cleanInput1($_GET[self::GETV_PW_RESET_TOKEN]));
		} else {
			PwResetValidator::setPwResetToken('');
		}

		$this->htmTitle = 'Reset password';
		$this->metaDesc = 'L.A. wedding videography';

		parent::__construct($pgName);

		// Validate pwResetToken - retrieve userNam from users table via pwResetToken
		PwResetValidator::auditPwResetToken();

		$this->setForm1(new PwResetForm($this));
	}

}
