<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\FormFields\InputFileFormField;
use AppVvp\FormFields\InputFormField;
use AppVvp\FormFields\SelectFormField;
use AppVvp\Validators\UploadValidator;
use AppVvp\Validators\Validator;

/**
 * For UPLOAD-page FORM
 */
class UploadForm extends Form
{
	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		$this->setRowsHt1('2.0em');
		Validator::setJsCompOrigFfVals(false);
		$this->setJsFocus1stElement(Form::FFN_UPLOAD_VIDEO_F1);

		if (Validator::getServerRequestMethod() !== 'POST') {
			// Set js focus to 1st field in form
			$this->setJsFocusElementId($this->getJsFocus1stElement());
		} else {
			UploadValidator::auditAndUpload($this);
		}
	}

	protected function addFormFieldObjs()
	{
//		FfoFactory::addFfoVideoMaxSizeF1(new InputFormField($this, Form::FFN_UPLOAD_VIDEO_MAXSIZ_F1));
		FfoFactory::addFfoUploadProgress(new InputFileFormField($this, ini_get('session.upload_progress.name')));
		FfoFactory::addFfoVideoUploadF1 (new InputFileFormField($this, Form::FFN_UPLOAD_VIDEO_F1));
		FfoFactory::addFfoVideoDescr1F1 (new InputFormField    ($this, Form::FFN_UPLOAD_VIDEO_DESCR1));
		FfoFactory::addFfoVideoDescr2F1 (new InputFormField    ($this, Form::FFN_UPLOAD_VIDEO_DESCR2));
		FfoFactory::addFfoVideoAspect   (new SelectFormField   ($this, Form::FFN_UPLOAD_VIDEO_ASPECT));
	}

}
