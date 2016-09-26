<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Db;

use AppVvp\General\ErrHandler;
use AppVvp\General\PgMsgs;
use AppVvp\General\PhpIniCfg;
use PDO;
use PDOException;

/**
 * PDO MySQL database custom methods
 * 
 * NOTE: For Prepared Statements, placeholders [?] OR named parameters [:val] do
 *		NOT need to be surrounded by quotes.
 * 
 * $PDO->execute($ary): ALL vals in array passed as datatype PDO::PARAM_STR
 * $PDO->bindParam()->execute(): bindParam() can define the DATATYPE passed
 * 
 *  When using POSITIONAL PLACEHOLDERS [?, ?] w/ prepare() + execute(), use [array_values] to 
 *   re-key the array numerically IF ARRAY KEYS ARE NOT NUMERICALLY SEQUENTIAL.
 * 
 * After MySQL table UPDATE stmt->rowCount() will be > 0 ONLY IF at least 1 column-value 
 *   in the row has changed.
 * 
 * $_SERVER["REQUEST_TIME_FLOAT"] = sysdatetime as secs.ms
 * 
 * [ ->prepare() from PHP manual:] "Emulated prepared statements do not communicate with the 
 *   database server so PDO::prepare() does not check the statement."
 *   BY DFLT emulation is ON, so TECHNICALLY there is NO NEED to check the 
 *	 status of a prepare() call.
 */
class Dbo
{
	private $dbId;
	private $pdoConn = NULL;
	private $stmt;
	private $queryResult;


	public function __construct($dbId)
	{
		$this->dbId = $dbId;
	}

	public function connect1($reportErr = true)
	{
		// --- EXIT and return [true] if ALREADY CONNECTED ---
		if ($this->isDbConn()) {
			return true;
		}
		// ------------- Establish new DB connection -------------
		if (!$reportErr) {
			$errRepSav = ini_set('error_reporting', PhpIniCfg::PI_OFF);
		}
		// !NOTE! Invalid/unknown HOSTNAME w/ this config will generate a WARNING
		//    and will NOT be trapped by 'catch'/ErrHandler::triggerDbErr1().
		// Generate PgMsg ['E0DB00'] to display on errpage to account for this possibility.
		PgMsgs::set('E0DB00', 'DATABASE ERROR - PLEASE CONTACT SITE ADMINISTRATOR.');
		DbConnect::init($this->dbId); // placed here so 'E0DB00' is displayed if problem
		try {
			// ===========================================
			$this->pdoConn = new PDO(
					'mysql:host=' . DbConnect::getHost() . 
					'; dbname=' . DbConnect::getDbName(),
					DbConnect::getUser(),
					DbConnect::getPw());
			// ===========================================
//			// PDO setAttribute dflt: PDO::ERRMODE_SILENT
//			$this->pdoConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$dbConn = true;
		} catch (PDOException $e) {
			if ($reportErr) {
				unset($_SESSION[PgMsgs::SV_ERRMSGS1]['E0DB00']); // DEL UNNEEDED PgMsg
				ErrHandler::triggerDbErr1('E0DB01', $e->getMessage(), E_USER_ERROR, $this);
			}
			$dbConn = false;
		}
		if (!$reportErr) {
			ini_set('error_reporting', $errRepSav);
		}
		unset($_SESSION[PgMsgs::SV_ERRMSGS1]['E0DB00']); // NO ERR TRIGGERED - DEL UNNEEDED MSG
		return $dbConn;
	}


	public function close()
	{
		if ($this->isDbConn()) { // Close ONLY if connection exists
			$this->pdoConn = NULL; // NO ->close() method in PDO
		}
	}

	public function query($query, $parm1, $errMsgkey)
	{
		if (!$this->queryResult = $this->pdoConn->query($query, $parm1)) {
			ErrHandler::triggerDbErr1($errMsgkey, $this->pdoConn->errorCode(), 
					E_USER_ERROR, $this);
		}
	}

	public function stmtPrepAndExec($query, $parmsAry, $errMsgkey)
	{
		$this->stmtPrep($query, $errMsgkey);
		if (!$this->stmt->execute($parmsAry)) {
			ErrHandler::triggerDbErr1($errMsgkey, $this->stmt->errorCode(), 
					E_USER_ERROR, $this);
		}
	}

	public function stmtPrep($query, $errMsgkey)
	{
		if (!$this->stmt = $this->pdoConn->prepare($query)) {
			ErrHandler::triggerDbErr1($errMsgkey, $this->pdoConn->errorCode(), 
					E_USER_ERROR, $this);
		}
	}

	public function stmtExecNoParm($errMsgkey)
	{
		if (!$this->stmt->execute()) {
			ErrHandler::triggerDbErr1($errMsgkey, $this->stmt->errorCode(), 
					E_USER_ERROR, $this);
		}
	}

	public function getDbErrorInfo()
	{
		if (is_object($this->pdoConn)) {
			return $this->pdoConn->errorInfo();
		}
		return NULL;
	}

	public function getStmtErrorInfo()
	{
		if (is_object($this->stmt)) {
			return $this->stmt->errorInfo();
		}
		return NULL;
	}

	public function isDbConn()
	{
		return is_object($this->pdoConn);
	}


	// GETTERS / SETTERS

	public function getStmt()
	{
		return $this->stmt;
	}

	public function getQueryResult()
	{
		return $this->queryResult;
	}

	public function getLastInsertId()
	{
		return $this->pdoConn->lastInsertId();
	}

}
