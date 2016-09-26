<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\Db\DbConnect;
use AppVvp\FormFields\InputFormField;
use AppVvp\Validators\PwResetValidator;
use AppVvp\Validators\Validator;

/**
 *
 */
class PwResetForm extends Form
{
	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		$this->getFfo(Form::FFN_NEW_PW_RETYP)->setDescr('RE-TYPE NEW Password');

		Validator::setJsCompOrigFfVals(false);
		$this->setJsFocus1stElement(Form::FFN_PW_RESET_PIN);

		if (Validator::getServerRequestMethod() !== 'POST') {
			if (TEST_RUN) {
				$this->getFfo(Form::FFN_NEW_PW)->setValue(DbConnect::TEST_USER_LOGIN_PW2);
				$this->getFfo(Form::FFN_NEW_PW_RETYP)->setValue(DbConnect::TEST_USER_LOGIN_PW2);
			}

			// Set js focus to 1st field in form
			$this->setJsFocusElementId($this->getJsFocus1stElement());
		} else {
			PwResetValidator::auditAndResetPw($this);
		}
	}

	protected function addFormFieldObjs()
	{
		FfoFactory::addPwResetPin1(new InputFormField($this, Form::FFN_PW_RESET_PIN));
		FfoFactory::addFfoNewPw1  (new InputFormField($this, Form::FFN_NEW_PW));
		FfoFactory::addFfoNewPw1  (new InputFormField($this, Form::FFN_NEW_PW_RETYP));
	}

}
