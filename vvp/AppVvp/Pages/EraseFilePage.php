<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\FilNams;
use AppVvp\Validators\Validator;

/**
 *
 */
class EraseFilePage extends Page
{
	private $fileType;
	private $fileName;
	private $fileDescr;
	private $erase;


	public function __construct($pgName)
	{
		if (Validator::issetGetAddAry1('ftyp')) {
			$this->fileType = strtoupper(Validator::cleanInput1($_GET['ftyp']));
			switch ($this->fileType) {
				case 'DE':
					$this->fileName = FilNams::getAbsPathErrMsgs();
					$this->fileDescr = 'Error-Msg Files';
					break;
				case 'DS':
					$this->fileName = FilNams::getAbsPathSessions();
					$this->fileDescr = 'Session ID Files';
					break;
				case 'E':
					$this->fileName = FilNams::getErrorLogFnam();
					$this->fileDescr = 'PHP Errors Logfile';
					break;
				case 'L':
					$this->fileName = FilNams::getLogFileFnam();
					$this->fileDescr = 'Site Access Logfile';
					break;
				default:
					echo '*ERROR* INVALID "ftyp" URL parm VALUE<br />';
					exit();
			}
		} else {
			echo '*ERROR* URL parm "ftyp" missing<br />';
			exit();
		}
		if ($this->erase = Validator::issetGetAddAry1('nqa')) {
			$tmp1 = strtolower(Validator::cleanInput1($_GET['nqa']));
		} else {
			$this->erase = false;
		}
		$this->htmTitle = 'Erase File';
		parent::__construct($pgName);
	}


	// GETTERS / SETTERS

	public function getFileType()
	{
		return $this->fileType;
	}

	public function getFileName()
	{
		return $this->fileName;
	}

	public function getFileDescr()
	{
		return $this->fileDescr;
	}

	public function getErase()
	{
		return $this->erase;
	}

}
