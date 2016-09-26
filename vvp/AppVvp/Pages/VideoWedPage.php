<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\General\BusDefs;

/**
 * For Wedding VIDEO PAGE object
 */
class VideoWedPage extends VideoPage
{
	public function __construct($pgName)
	{
		//echo 'In [VideoWedPage::__construct]<br />';

		$this->htmTitle = 'Los Angeles Weddings Birthdays Mitzvahs Videos by ' . 
			BusDefs::BUSNAME1_F2 . ' - Los Angeles Videographers';
		$this->metaDesc = 'Los Angeles wedding video by Los Angeles wedding videographer - ' . 
			BusDefs::BUSNAME1_F2 . 
			' will create a lovely, heart touching video of your once-in-a-lifetime event.';

		$this->setVidNamsKey('wed1');
		parent::__construct($pgName);
	}
}

