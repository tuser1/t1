<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Validators\Validator;

/**
 * For admin phpinfo PAGE object
 */
class PhpInfoPage extends Page
{
	public function __construct($pgName)
	{
		$phpInfoIframe = 0;
		if (Validator::issetGetAddAry1('phpinfo')) {
			$phpInfoIframe = strtolower(Validator::cleanInput1($_GET['phpinfo']));
		}
		parent::__construct($pgName);
		if (!$phpInfoIframe) { // Create SECURE outer page for phpinfo() iframe container
			$this->htmTitle = 'PHP Info';
		} else {
			// ==========
			phpinfo(); // Run phpinfo() in iframe container
			// ==========
			exit();
		}
	}

}
