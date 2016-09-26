<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Db;

use AppVvp\General\BusDefs;
use AppVvp\General\SysMain;
/**
 * Database connection properties
 * -----------------------------------------------------------------------------
 * *WARNING* This file must be EXcluded when using a source code/version mgr
 * -----------------------------------------------------------------------------
 */
class DbConnect
{
	// ---------------------------------------------------------
	const TEST_USER_LOGIN_PW1 = 'password'; // DESENSITIZED
	const TEST_USER_LOGIN_PW2 = 'password'; // DESENSITIZED
	// ---------------------------------------------------------
	const PROD1_HOST	= SysMain::LOCALHOST;
	const PROD1_USER	= 'user';		// DESENSITIZED
	const PROD1_PW		= 'password';	// DESENSITIZED
	const PROD1_DBNAME	= 'dbname';		// DESENSITIZED

	const DEV1_HOST		= SysMain::LOCALHOST;
	const DEV1_USER		= 'user';		// DESENSITIZED
	const DEV1_PW		= 'password';	// DESENSITIZED
	const DEV1_DBNAME	= 'dbname';		// DESENSITIZED

	const DB_ID_MAIN	= 'vvpDbMain';
	const DB_ID_TEST	= 'vvpDbTest';

	private static $dbId;


	private static $connAry = array(
		self::DB_ID_MAIN => array(
			SysMain::LOCALHOST => array(
				self::DEV1_HOST,
				self::DEV1_USER,
				self::DEV1_PW,
				self::DEV1_DBNAME
			),
			BusDefs::WEBNAME1_F1 => array(
				self::PROD1_HOST,
				self::PROD1_USER,
				self::PROD1_PW,
				self::PROD1_DBNAME
			)
		),
		self::DB_ID_TEST => array(  // for LOCALHOST test purposes
			SysMain::LOCALHOST => array(
				self::DEV1_HOST,
				self::DEV1_USER,
				self::DEV1_PW,
				'dbname'  // can use any DB name here  // DESENSITIZED
			),
			BusDefs::WEBNAME1_F1 => array(
				self::PROD1_HOST,
				self::PROD1_USER,
				self::PROD1_PW,
				'dbname'  // can use any DB name here  // DESENSITIZED
			)
		)
	);


	public static function init($dbId)
	{
		self::$dbId = $dbId;
	}


	public static function getHost()
	{
		return self::getConnAryVal(0);
	}

	public static function getUser()
	{
		return self::getConnAryVal(1);
	}

	public static function getPw()
	{
		return self::getConnAryVal(2);
	}

	public static function getDbName()
	{
		return self::getConnAryVal(3);
	}

	private static function getConnAryVal($i)
	{
		if (array_key_exists(self::$dbId, self::$connAry)) {
			if (array_key_exists(RAW_SERVER_NAME, self::$connAry[self::$dbId])) {
				if (array_key_exists($i, self::$connAry[self::$dbId][RAW_SERVER_NAME])) {
					return self::$connAry[self::$dbId][RAW_SERVER_NAME][$i];
				}
			}
		}
		trigger_error('[' . 'E0DB999' . '] === DB CONNECT array key NOT FOUND === >> ' .
				'[' . self::$dbId . '][' . RAW_SERVER_NAME . '][' . $i . ']', E_USER_ERROR);
	}

}
