<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\FormFields;

use AppVvp\App;

/**
 * For all <input> FORMFIELD objects
 */
class InputFormField extends FormField
{
	protected function initFieldType()
	{
		$this->fieldType = 'text';
		$this->inputField = true;
	}

	public function renderHiddenFfo1($indent)
	{
		echo "\n";
		echo $indent, '<input type="hidden" name="', 
						hh($this->getFldNam()), '"  id="', hh($this->getFldNam()), '" ', 
						'value="', hh($this->getValue()), '" size=', 
						hh($this->getHtmlInputfldSiz($this->getMaxSiz())), '>', "\n";
	}

	public function renderFfo1($indent)
	{
		echo $indent, '<tr>', "\n";
		echo $indent, '    <td class="formFldDescr">', hh($this->getDescr()), 
						App::$page->getForm1()->getRequiredFldMsg($this->getRequired()), 
						'</td>', "\n";
		echo $indent, '    <td class="formFldInput"><input class="formFldStyle" type="', 
						hh($this->getFieldType()), '" name="', 
						hh($this->getFldNam()), '"  id="', hh($this->getFldNam()), '" ', 
						'value="', hh($this->getValue()), '" size=', 
						hh($this->getHtmlInputfldSiz($this->getMaxSiz())), '></td>', "\n";
		echo $indent, '</tr>', "\n";
	}


	// GETTERS / SETTERS

	// Restrict DISPLAY size of html input fields on forms
	// Does not restrict number of chars entered
	private function getHtmlInputfldSiz($siz)
	{
		if ($siz > self::HTML_INPUT_FLD_MAXDISP) {
			return self::HTML_INPUT_FLD_MAXDISP;
		} else {
			return $siz;
		}
	}
}
