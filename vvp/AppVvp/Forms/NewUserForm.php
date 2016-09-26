<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\Db\DbConnect;
use AppVvp\FormFields\InputFormField;
use AppVvp\Validators\NewUserValidator;
use AppVvp\Validators\Validator;

/**
 * For LOGIN-page FORM
 */
class NewUserForm extends Form
{
	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		$this->getFfo(Form::FFN_NEW_PW)->setDescr('Password');
		$this->getFfo(Form::FFN_NEW_PW_RETYP)->setDescr('RE-TYPE Password');

		Validator::setJsCompOrigFfVals(false);
		$this->setJsFocus1stElement(Form::FFN_LOGIN_USRNAM);

		if (Validator::getServerRequestMethod() !== 'POST') {
			if (TEST_RUN) {
				$this->getFfo(Form::FFN_LOGIN_USRNAM)->setValue('c@a.a');
				$this->getFfo(Form::FFN_NEW_PW)->setValue(DbConnect::TEST_USER_LOGIN_PW2);
				$this->getFfo(Form::FFN_NEW_PW_RETYP)->setValue(DbConnect::TEST_USER_LOGIN_PW2);
			}

			// Set js focus to 1st field in form
			$this->setJsFocusElementId($this->getJsFocus1stElement());
		} else {
			NewUserValidator::auditAndUpdate($this);
		}
	}

	protected function addFormFieldObjs()
	{
		FfoFactory::addFfoUsrNam1(new InputFormField($this, Form::FFN_LOGIN_USRNAM));
		FfoFactory::addFfoNewPw1 (new InputFormField($this, Form::FFN_NEW_PW));
		FfoFactory::addFfoNewPw1 (new InputFormField($this, Form::FFN_NEW_PW_RETYP));
	}

}
