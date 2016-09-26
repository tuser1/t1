<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Forms\NewUserForm;

/**
	 * For new user PAGE object
 */
class NewUserPage extends Page
{
	public function __construct($pgName)
	{
		//echo 'In [NewUserPage::__construct]<br />';

		$this->htmTitle = 'Los Angeles wedding video by Los Angeles wedding videographer';
		$this->metaDesc = 'L.A. wedding videography';

		parent::__construct($pgName);

		$this->setForm1(new NewUserForm($this));
	}

}
