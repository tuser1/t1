<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Forms\PwForgotForm;

/**
 *
 */
class PwForgotPage extends Page
{
	public function __construct($pgName)
	{
		$this->htmTitle = 'Forgot password?';
		$this->metaDesc = 'L.A. wedding videography';

		parent::__construct($pgName);

		$this->setForm1(new PwForgotForm($this));
	}

}
