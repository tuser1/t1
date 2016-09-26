<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\Db\DbAccess;
use AppVvp\FormFields\InputFormField;
use AppVvp\FormFields\SelectFormField;
use AppVvp\General\CsrfToken;
use AppVvp\Validators\MyInfoValidator;
use AppVvp\Validators\Validator;

/**
 * For MYINFO-page FORM
 */
class MyInfoForm extends Form
{
	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		if (!isset($_POST[Validator::CFV_AJAX])) {
			Validator::setJsCompOrigFfVals(true);
			$this->setJsFocus1stElement(Form::FFN_FIRSTNAME1);
			if (Validator::getServerRequestMethod() !== 'POST') {
				DbAccess::getCustInfo($this);

				if (isset($_SESSION[self::SV_NEWUSER_RECORD])) { // New user/customer
					if (TEST_RUN) {
						$this->getFfo(Form::FFN_FIRSTNAME1)->setValue('Test');
						$this->getFfo(Form::FFN_LASTNAME1)->setValue('Test');
						$this->getFfo(Form::FFN_ADDR1)->setValue('1111 Test St.');
						$this->getFfo(Form::FFN_CITY1)->setValue('Test');
					//	$this->getFfo(Form::FFN_STATECODE1)->setValue('CA');
						$this->getFfo(Form::FFN_ZIPCODE1)->setValue('11111');
					}
				} else {
					Validator::validateFfoVals1($this);
				}
				if (!$this->getErrCnt1()) {
					// If NO errors set js focus to 1st field in form
					$this->setJsFocusElementId($this->getJsFocus1stElement());
				}
			} else {
				MyInfoValidator::auditAndUpdate($this);
			}
		} else {
			//-------------- AJAX post ------------------------
			foreach ($_POST as $key => $val) {
				if ($key != Validator::CFV_AJAX && $key != CsrfToken::getSvToken()) {
					$ajaxFfoName = $key;
					$this->getFfo($ajaxFfoName)->setValue(Validator::cleanInput1($val));
				}
			}
			unset($key); unset($val); // per PHP doc
			Validator::validateFfoVal1($this->getFfo($ajaxFfoName));
			// Send/echo errMsg (blank if no error) to AJAX/XMLHttpRequest:
			echo hh($this->getFfo($ajaxFfoName)->getErrMsg());
			exit();  // AJAX/XMLHttpRequest send complete - EXIT SCRIPT
			//-------------------------------------------------
		}
	}

	protected function addFormFieldObjs()
	{
		FfoFactory::addFfoTitle1    (new SelectFormField($this, Form::FFN_TITLE1));
		FfoFactory::addFfoFirstName1(new InputFormField ($this, Form::FFN_FIRSTNAME1));
		FfoFactory::addFfoInitial1  (new InputFormField ($this, Form::FFN_INITIAL1));
		FfoFactory::addFfoLastName1 (new InputFormField ($this, Form::FFN_LASTNAME1));
		FfoFactory::addFfoAddr1     (new InputFormField ($this, Form::FFN_ADDR1));
		FfoFactory::addFfoCity1     (new InputFormField ($this, Form::FFN_CITY1));
		FfoFactory::addFfoStateCode1(new SelectFormField($this, Form::FFN_STATECODE1));
		FfoFactory::addFfoZipCode1  (new InputFormField ($this, Form::FFN_ZIPCODE1));
		FfoFactory::addFfoPhone1    (new InputFormField ($this, Form::FFN_PHONE1));
		FfoFactory::addFfoTextAddr1 (new InputFormField ($this, Form::FFN_TEXTMSG_ADDR));
	}

}
