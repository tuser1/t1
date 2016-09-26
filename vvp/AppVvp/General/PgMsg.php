<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\App;

/**
 * For page/flash MESSAGE objects
 */
class PgMsg extends App
{
	private $msg;
	private $pageMsgCssClass;
	private $strongOpen = '';  // html opening tag
	private $strongClose = ''; // html closing tag


	public function __construct($msg, $pageMsgCssClass, $strong)
	{
		$this->msg = $msg;
		$this->pageMsgCssClass = $pageMsgCssClass;
		if ($strong) {
			$this->strongOpen  = '<strong>';
			$this->strongClose = '</strong>';
		}
	}

	// GETTERS / SETTERS

	public function getMsg()
	{
		return $this->msg;
	}

	public function getPageMsgCssClass()
	{
		return $this->pageMsgCssClass;
	}

	public function getStrongOpen()
	{
		return $this->strongOpen;
	}

	public function getStrongClose()
	{
		return $this->strongClose;
	}

}
