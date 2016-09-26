<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\Validators\Validator;

/**
 * CSRF => Cross-site Request Forgery
 */
class CsrfToken
{
	const SV_CSRF_TOKEN = 'csrf_token';

	const TOKEN_LIMIT_SECS = 600; // 10 min

	private static $svToken;
	private static $svTokenTime;
	private static $tokenErrKey;
	private static $tokenErrMsg;



	public static function init($formName)
	{
		self::$svToken = $formName . '_' . self::SV_CSRF_TOKEN;
		self::$svTokenTime = self::$svToken . '_time';
		}

	public static function genToken()
	{
		// == NOTE: TOKEN WILL ALWAYS BE 32 CHARS LONG ==
		return md5(uniqid(rand(), TRUE));
	}

	public static function createToken()
	{
		$_SESSION[self::$svToken] = self::genToken();
		$_SESSION[self::$svTokenTime] = time();  // current time in SECONDS
		return $_SESSION[self::$svToken];
	}

	private static function setTokenToInvalid()
	{
		$_SESSION[self::$svToken] = '';
		$_SESSION[self::$svTokenTime] = 0;
	}

	public static function tokenInvalid()
	{
		if (!isset($_POST[self::$svToken])) {
			self::$tokenErrMsg = 'Token missing from $_POST';
			self::$tokenErrKey = 'E011C';
			return true;
		}
		if (isset($_SESSION[self::$svToken])) {
			if (!preg_match(Validator::RE_VALID_CSRF_TOKEN, $_POST[self::$svToken]) ||
						strlen($_POST[self::$svToken]) !== Validator::CSRF_TOKEN_SIZE) {
				self::$tokenErrMsg = 'POST token is wrong length or contains ' . 
						'invalid characters';
				self::$tokenErrKey = 'E011D';
				return true;
			} else {
				$postToken = $_POST[self::$svToken];
			}
			// compare token sent from form post to original token
			if ($postToken === $_SESSION[self::$svToken]) {
				return false; // token passed IS VALID
			} else {
				self::$tokenErrMsg = 'POST token [' . $postToken . 
						'] differs from original [' . $_SESSION[self::$svToken] . ']';
				self::$tokenErrKey = 'E011A';
				return true;
			}
		} else {
			self::$tokenErrMsg = 'Original token missing from $_SESSION';
			self::$tokenErrKey = 'E011B';
			return true;
		}
	}

	public static function tokenExpired()
	{
		if (isset($_SESSION[self::$svTokenTime])) {
			if (($_SESSION[self::$svTokenTime] + self::TOKEN_LIMIT_SECS) >= time()) {
				return false; // token still 'fresh'
			} else {
				self::setTokenToInvalid();
				self::$tokenErrMsg = 'Token expired';
				self::$tokenErrKey = 'E012A';
				return true;
			}
		} else {
			self::setTokenToInvalid();
			self::$tokenErrMsg = 'Token TIME missing from $_SESSION';
			self::$tokenErrKey = 'E012B';
			return true;
		}
	}


	// GETTERS / SETTERS

	public static function getSvToken()
	{
		return self::$svToken;
	}

	public static function getTokenErrKey()
	{
		return self::$tokenErrKey;
	}

	public static function getTokenErrMsg()
	{
		return self::$tokenErrMsg;
	}

}
