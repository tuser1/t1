<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * Main initialization for each public url page.
 * Is exec'd BEFORE creation of PAGE object [App::$page].
 */

namespace AppVvp;

use AppVvp\Db\DbAccess;
use AppVvp\Db\DbConnect;
use AppVvp\Db\Dbo;
use AppVvp\General\AuthUser;
use AppVvp\General\ErrHandler;
use AppVvp\General\PgLinkFactory;
use AppVvp\General\PgMsgs;
use AppVvp\General\PhpIniCfg;
use AppVvp\General\SysMain;
use AppVvp\Validators\Validator;


// Used with 'register_shutdown_function' to catch ERROR not handled by custom error_handler
function atShutdown()
{
	ErrHandler::checkLastError();
	DbAccess::close_conn();
}

// Save the top-level/vendor namespace that is declared above
define('_VENDOR_NS_', __NAMESPACE__);


define('TEST_RUN', true);
if (TEST_RUN) {
	// ====================================================
	// Default errlog file: /public_html/error_log
	ini_set('error_reporting', E_ALL | E_STRICT);
	ini_set('display_errors', 		  1);
	ini_set('display_startup_errors', 1);
	ini_set('log_errors', 			  1);
	ini_set('track_errors', 		  1);
	ini_set('html_errors', 			  1);
	// ====================================================
}

mb_internal_encoding('UTF-8');

ob_start(); // Begin output buffering

require __DIR__ . '/globals.php'; // also performs spl_autoload_register

// ==================================================================
// Compatibility library: pw-hashing ftns for PHP >= 5.3.7, < 5.5-DEV
require dirname(__DIR__) . '/' . DN_VENDOR . '/pw-hash-compat-lib/password.php';
// ==================================================================

FilNams::init();

PgLinkFactory::create(); // Create URL-page objects/attribs - validate page name

  App::$dbMain = new Dbo(DbConnect::DB_ID_MAIN);
//App::$dbTest = new Dbo(DbConnect::DB_ID_TEST);

//==============
//PhpIniCfg::setPhpIni(PhpIniCfg::PROD1, $displayIniVals = false);
  PhpIniCfg::setPhpIni('', $displayRuntimeIniVals = false);
//==============

register_shutdown_function('AppVvp\atShutdown');
register_shutdown_function('ob_end_flush'); // display output

set_error_handler('AppVvp\General\ErrHandler::customErrHandler');

session_start();
if (!isset($_SESSION[SysMain::SV_SESSION_TIMESTAMP])) {
//			if (ini_get('log_errors')) {
//				error_log('==== NEW SESSION: ' . session_id() . ' == ' . 
//					SAFE_RU_CURR_PAGE_URL . ' ====');
//			}
	$_SESSION[SysMain::SV_SESSION_TIMESTAMP] = time();
}

Validator::init();

SysMain::init();

AuthUser::init(); // Authentication

if (PhpIniCfg::getPiSrvrDef() === PhpIniCfg::DEV1 || 
		AuthUser::isAdminUser()) {
	PgMsgs::set('I000', 'SESS: ' . session_id() . 
		' / client: ' . $_SERVER['REMOTE_ADDR'] . 
		' / server: ' . $_SERVER['SERVER_ADDR'] . 
		' / cfv: ' . Validator::getCfvMethod() . 
		' / PSWR: ' . $_SESSION[Validator::SV_ENFORCE_PW_RULES] . 
		' / php.ini: ' . PhpIniCfg::getPiSrvrDef(), 
		PgMsgs::PGMSG_CSS_CLASS_TEST, $strong = false, $dupeKeysOk = true);
}

// -------------------------------------------------
App::renderCurrentPage(PgLinkFactory::getNsPageObjCls());
// -------------------------------------------------
