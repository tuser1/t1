<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Db\DbAccess;
use AppVvp\Forms\VideoUpdateForm;
use AppVvp\General\BusDefs;
use AppVvp\Validators\Validator;

/**
 * For Customer's (My Videos) VIDEO PAGE object
 */
class VideoMyPage extends VideoPage
{
	public function __construct($pgName)
	{
		$this->htmTitle = 'Los Angeles Wedding Birthday Mitzvah Video by ' . 
				BusDefs::BUSNAME1_F2 . ' - Los Angeles Videographer';
		$this->metaDesc = 
			'Los Angeles Videographers, Los Angeles Weddings Birthdays Mitzvahs Videos by ' . 
			BusDefs::BUSNAME1_F2 . 
			'. We will create a lovely, heart touching video of your once-in-a-lifetime event.';

		$this->setVidNamsKey(DbAccess::getCustNbr());

		parent::__construct($pgName);

		if ($this->isVideoPlayPage()) {
			Validator::setCfvMethod(Validator::CFV_NONE); // form-field validation OFF
			$this->setForm1(new VideoUpdateForm($this));
		}
	}

}
