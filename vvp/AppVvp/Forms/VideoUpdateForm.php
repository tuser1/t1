<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\FormFields\InputFormField;
use AppVvp\FormFields\SelectFormField;
use AppVvp\Validators\Validator;
use AppVvp\Validators\VideoUpdValidator;

/**
 * For Customer VIDEO-DELETION form
 */
class VideoUpdateForm extends Form
{

	public function __construct($parentPage, $formName = '')
	{
		parent::__construct($parentPage, $formName);

		$this->setRowsHt1('1.5em');
		if (Validator::getServerRequestMethod() !== 'POST') {
			$vidInfo = $parentPage->getVidInfo()[$parentPage->getCurrVidOptNum()];
			$this->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR1)->setValue($vidInfo['vidtn_alttxt1']);
			$this->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR2)->setValue($vidInfo['vidtn_alttxt2']);
			$this->getFfo(Form::FFN_UPLOAD_VIDEO_ASPECT)->setValue($vidInfo['aspect']);
			Validator::validateFfoVals1($this);
		} else {
			VideoUpdValidator::auditAndUpdate($this, $parentPage);
		}
	}

	protected function addFormFieldObjs()
	{
		FfoFactory::addFfoVideoDescr1F1(new InputFormField ($this, Form::FFN_UPLOAD_VIDEO_DESCR1));
		FfoFactory::addFfoVideoDescr2F1(new InputFormField ($this, Form::FFN_UPLOAD_VIDEO_DESCR2));
		FfoFactory::addFfoVideoAspect  (new SelectFormField($this, Form::FFN_UPLOAD_VIDEO_ASPECT));
	}

}
