<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Forms\MyInfoForm;

/**
 * For 'my info' PAGE object
 */
class MyInfoPage extends Page
{
	public function __construct($pgName)
	{
		$this->htmTitle = 'Los Angeles wedding video by Los Angeles wedding videographer';
		$this->metaDesc = 'L.A. wedding videography';

		parent::__construct($pgName);

		$this->setForm1(new MyInfoForm($this));
	}

}
