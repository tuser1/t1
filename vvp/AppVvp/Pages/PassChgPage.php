<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Forms\PassChgForm;

/**
 * For password change PAGE object
 */
class PassChgPage extends Page
{
	public function __construct($pgName)
	{
		$this->htmTitle = 'Los Angeles wedding video by Los Angeles wedding videographer';
		$this->metaDesc = 'L.A. wedding videography';

		parent::__construct($pgName);

		$this->setForm1(new PassChgForm($this));
	}

}
