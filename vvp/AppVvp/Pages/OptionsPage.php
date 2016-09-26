<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\General\BusDefs;

/**
 * For service/pricing/options PAGE object
 */
class OptionsPage extends Page
{
	public function __construct($pgName)
	{
		//echo 'In [OptionsPage::__construct]<br />

		$this->htmTitle = 'Los Angeles Weddings Mitzvahs Special Events Videos by ' . 
			BusDefs::BUSNAME1_F2 . ' - Los Angeles Videography';
		$this->metaDesc = 'Los Angeles Wedding Birthday Mitzvah Video by ' . BusDefs::BUSNAME1_F2 . 
			' - Los Angeles Videographer. Affordable videography of your once-in-a-lifetime event.';

		parent::__construct($pgName);
	}
}

