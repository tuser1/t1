<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\Validators\Validator;

/**
 *
 */
class ShowLogFilePage extends Page
{
	private $filter;


	public function __construct($pgName)
	{
		if (Validator::issetGetAddAry1('filter')) {
			$filter = strtolower(Validator::cleanInput1($_GET['filter']));
			if ($filter != 'y' && $filter != 'n') {
				echo "Invalid (y/n) value passed in 'filter' parm: was set to 'n' ...";
				$filter = 'n';
			}
		} else {
			$filter = 'y';
		}
		$this->filter = Validator::ynToBool($filter);
		$this->htmTitle = 'Show Logfile';
		parent::__construct($pgName);
	}


	// GETTERS / SETTERS

	public function getFilter()
	{
		return $this->filter;
	}

}
