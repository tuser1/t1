<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Validators\Validator;

/**
 * For error-message PAGE object
 */
class ErrMsgPage extends Page
{
	private $pageErrToken;


	public function __construct($pgName)
	{
		//echo 'In [ErrMsgPage::__construct]<br />';

		if (Validator::issetGetAddAry1('token')) {
			$this->setPageErrToken(strtolower(Validator::cleanInput1($_GET['token'])));
		} else {
			$this->setPageErrToken('');
		}

		$this->htmTitle = '.';
		$this->metaDesc = '.';

		parent::__construct($pgName);
	}


	// GETTERS / SETTERS

	public function setPageErrToken($val)
	{
		$this->pageErrToken = $val;
	}

	public function getPageErrToken()
	{
		return $this->pageErrToken;
	}
}
