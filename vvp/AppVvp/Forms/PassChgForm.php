<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\Db\DbConnect;
use AppVvp\FormFields\InputFormField;
use AppVvp\Validators\PassChgValidator;
use AppVvp\Validators\Validator;

/**
 * For PASSWORD-CHANGE-page FORM
 */
class PassChgForm extends Form
{
	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		$this->getFfo(Form::FFN_LOGIN_PW	  )->setDescr('CURRENT Password');
		$this->getFfo(Form::FFN_NEW_PW_RETYP)->setDescr('RE-TYPE NEW Password');

		Validator::setJsCompOrigFfVals(false);
		$this->setJsFocus1stElement(Form::FFN_LOGIN_PW);

		if (Validator::getServerRequestMethod() !== 'POST') {
			if (TEST_RUN) {
				$this->getFfo(Form::FFN_LOGIN_PW)->setValue(DbConnect::TEST_USER_LOGIN_PW1);
				$this->getFfo(Form::FFN_NEW_PW)->setValue(DbConnect::TEST_USER_LOGIN_PW2);
				$this->getFfo(Form::FFN_NEW_PW_RETYP)->setValue(DbConnect::TEST_USER_LOGIN_PW2);
			}

			// Set js focus to 1st field in form
			$this->setJsFocusElementId($this->getJsFocus1stElement());
		} else {
			PassChgValidator::auditAndUpdate($this);
		}
	}

	protected function addFormFieldObjs()
	{
		FfoFactory::addFfoLoginPw1(new InputFormField($this, Form::FFN_LOGIN_PW));
		FfoFactory::addFfoNewPw1  (new InputFormField($this, Form::FFN_NEW_PW));
		FfoFactory::addFfoNewPw1  (new InputFormField($this, Form::FFN_NEW_PW_RETYP));
	}

}
