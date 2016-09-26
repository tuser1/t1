<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\FormFields\InputFileFormField;
use AppVvp\Validators\UploadFileExistsValidator;
use AppVvp\Validators\Validator;

/**
 * For UPLOAD-page FORM
 */
class UploadFileExistsForm extends Form
{
	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		if (Validator::getServerRequestMethod() === 'POST') {
			UploadFileExistsValidator::audit($this);
		}
	}

	protected function addFormFieldObjs()
	{
		FfoFactory::addFfoVideoUploadF1(new InputFileFormField($this, Form::FFN_UPLOAD_VIDEO_F1));
	}

}
