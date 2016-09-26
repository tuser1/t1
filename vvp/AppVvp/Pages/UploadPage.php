<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Forms\UploadFileExistsForm;
use AppVvp\Forms\UploadForm;
use AppVvp\Validators\Validator;

/**
 * For upload PAGE object
 */
class UploadPage extends Page
{
	public function __construct($pgName)
	{
		//echo 'In [UploadPage::__construct]<br />';

		$this->htmTitle = 'Los Angeles wedding video by Los Angeles wedding videographer';
		$this->metaDesc = 'L.A. wedding videography';

		parent::__construct($pgName);

		if (!isset($_POST[Validator::CFV_AJAX])) {
			$this->setForm1(new UploadForm($this));
		} else {
			// Must use same formname as above for csrf token validation to function
			$this->setForm1(new UploadFileExistsForm($this, $formName = 'UploadForm'));
		}
	}

}
