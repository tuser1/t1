<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

/**
 * Business definitions
 */
class BusDefs
{
	const WEBNAME1_F1	= 'www.voilavideo.com';
	const WEBNAME1_F1B	= 'www.losangelesweddingvideographers.biz';
	const WEBNAME1_F2	= 'www.VoilaVideo.com';
	const BUSNAME1_F1	= 'Voila! Video Productions';
	const BUSNAME1_F2	= 'Voila! Video';
	const BUS_ACRONYM1	= 'VVP';
	const PHONE1_F1  	= '310.305.2406';
	const EMAIL1_F1  	= 'm&#97;il@Voil&#97;Vid&#101;o.&#99;om';
	const EMAIL1_F2  	= 'mail@VoilaVideo.com';
	const AUTHOR_FIRSTNAME		= 'Kris';
	const AUTHOR_LASTNAME		= 'Showman';
	const CONTACT1_FIRSTNAME	= 'Kris';
	const CONTACT1_LASTNAME		= 'Showman';
	const COPYRIGHT_YEAR1		= '2006-2016';


	public static function getMailTo1()
	{
		return self::EMAIL1_F1 . '?subject=' . ru('Inquiry from ' . 
				self::BUSNAME1_F2 . ' website');
	}
}

