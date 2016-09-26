<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\FormFields;

use AppVvp\App;

/**
 * For all <input> type="file" FORMFIELD objects
 */
class InputFileFormField extends InputFormField
{
	private $acceptFilter = ''; // e.g.: [accept="video/*"]


	protected function initFieldType()
	{
		$this->fieldType = 'file';
		$this->inputField = true;
	}

	public function renderHiddenFfo1($indent)
	{
		// This ftn is for the hidden <input> for[ini_get('session.upload_progress.name')]
		// It MUST precede the type="file" <input>s in the form
		// You MUST have the name="" attrib for this to work
		// If value="123", then session data is in $_SESSION["upload_progress_123"]
		echo "\n";
		echo $indent, '<input type="hidden" name="', hh($this->getFldNam()), '"  ', 
						'value="', hh($this->getValue()), '">', "\n";
	}

	public function renderFfo1($indent)
	{
		// This ftn is for the <input> type="file"
		// You MUST have the name="" attrib for this to work
		echo $indent, '<tr>', "\n";
		echo $indent, '    <td class="formFldDescr">', hh($this->getDescr()), 
						App::$page->getForm1()->getRequiredFldMsg($this->getRequired()), 
						'</td>', "\n";
		echo $indent, '    <td class="formFldInput"><input class="formFldStyle" type="', 
						hh($this->getFieldType()), 
						'" ', hh($this->getAcceptFilter()), ' name="', hh($this->getFldNam()), 
						'"  id="', hh($this->getFldNam()), '"></td>', "\n";
		echo $indent, '</tr>', "\n";
	}


	// GETTERS / SETTERS

	public function getAcceptFilter()
	{
		return $this->acceptFilter;
	}

	public function setAcceptFilter($acceptFilter)
	{
		$this->acceptFilter = $acceptFilter;
	}

}
