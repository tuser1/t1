<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

/**
 * TODO: [document]
 */
class Email
{
	/*
	 * Send email using PHP mail() ftn - returns TRUE if successful
	 * Error-handling will catch if it fails
	 * $textType: 'html', 'plain', ...
	 * 
	 * NOTE: NO sanitizing is done here as all text is generated internally and not 
	 * from $_POST.
	 */
	public static function send($emailTo, $subject, $messageBody, $textType)
	{
		$emailFromActual = BusDefs::EMAIL1_F2;
		$emailFromDflt = 'noreply@domain.com';
		$nameFrom = BusDefs::BUSNAME1_F1;
		$hdrs = 'Return-Path: ' . $emailFromDflt . "\r\n" .
				'From: ' . $nameFrom . ' <' . $emailFromDflt . '>' . "\r\n" .
				'X-Priority: 3' . "\r\n" .
				'X-Mailer: PHP ' . phpversion() . "\r\n" .
//				'Reply-To: ' . $nameFrom . ' <' . $emailFromActual . '>' . "\r\n" .
				'MIME-Version: 1.0' . "\r\n" .
				'Content-Transfer-Encoding: 8bit' . "\r\n" .
				'Content-type: text/' . $textType. '; charset=iso-8859-1' . "\r\n";
		$params = '-f ' . $emailFromDflt;

		// Send email using PHP mail() ftn:
		return mail($emailTo, $subject, $messageBody, $hdrs, $params);
	}

}
