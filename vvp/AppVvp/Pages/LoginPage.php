<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Forms\LoginForm;

/**
 * For login PAGE object
 */
class LoginPage extends Page
{
	public function __construct($pgName)
	{
		$this->htmTitle = 'Los Angeles wedding video by Los Angeles wedding videographer';
		$this->metaDesc = 'L.A. wedding videography';

		parent::__construct($pgName);

		$this->setForm1(new LoginForm($this));
	}

}
