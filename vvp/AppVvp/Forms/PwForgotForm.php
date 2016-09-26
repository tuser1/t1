<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\FormFields\InputFormField;
use AppVvp\Validators\PwForgotValidator;
use AppVvp\Validators\Validator;

/**
 *
 */
class PwForgotForm extends Form
{
	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		Validator::setJsCompOrigFfVals(false);
		$this->setJsFocus1stElement(Form::FFN_LOGIN_USRNAM);

		if (Validator::getServerRequestMethod() !== 'POST') {
			if (TEST_RUN) {
				$this->getFfo(Form::FFN_LOGIN_USRNAM)->setValue('b@a.a');
			}

			// Set js focus to 1st field in form
			$this->setJsFocusElementId($this->getJsFocus1stElement());
		} else {
			PwForgotValidator::auditAndGenResetToken($this);
		}
	}

	protected function addFormFieldObjs()
	{
		FfoFactory::addFfoUsrNam1 (new InputFormField($this, Form::FFN_LOGIN_USRNAM));
	}

}
