<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\General\BusDefs;

/**
 * For Mitzvah VIDEO PAGE object
 */
class VideoMitPage extends VideoPage
{
	public function __construct($pgName)
	{
		//echo 'In [VideoMitPage::__construct]<br />';

		$this->htmTitle = 'Los Angeles Wedding Birthday Mitzvah Video by ' . 
				BusDefs::BUSNAME1_F2 . ' - Los Angeles Videographer';
		$this->metaDesc = 
			'Los Angeles Videographers, Los Angeles Weddings Birthdays Mitzvahs Videos by ' . 
			BusDefs::BUSNAME1_F2 . 
			'. We will create a lovely, heart touching video of your once-in-a-lifetime event.';

		$this->setVidNamsKey('mit1');
		parent::__construct($pgName);
	}
}

