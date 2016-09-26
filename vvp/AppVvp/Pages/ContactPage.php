<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\General\BusDefs;

/**
 * For 'Contact Us' PAGE object
 */
class ContactPage extends Page
{
	public function __construct($pgName)
	{
		//echo 'In [ContactPage::__construct]<br />';

		$this->htmTitle = 
			'Wedding Video Photography Special Event Video Photography in Los Angeles - ' . 
			BusDefs::WEBNAME1_F2;
		$this->metaDesc = 
			'Wedding Video Photography Special Event Video Photography in Los Angeles - ' . 
			BusDefs::WEBNAME1_F2 . 
			'. Affordable videography of your once-in-a-lifetime event.';

		parent::__construct($pgName);
	}
}

