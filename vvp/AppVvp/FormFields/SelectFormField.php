<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\FormFields;

use AppVvp\App;

/**
 * For all <select> FORMFIELD objects
 */
class SelectFormField extends FormField
{
	protected $selectAry;
	protected $selectInit = '';


	protected function initFieldType()
	{
		$this->fieldType = 'select';
		$this->inputField = false;
	}

	public function renderFfo1($indent)
	{
		$found = 0;
		echo $indent, '<tr>', "\n", 
			 $indent, '    <td class="formFldDescr">', hh($this->getDescr()), 
						App::$page->getForm1()->getRequiredFldMsg($this->getRequired()), 
						'</td>', "\n";
		echo $indent, '    <td class="formFldInput">', "\n";
		echo $indent, '        <select class="formFldStyle" id="', hh($this->getFldNam()), '" name="', 
					hh($this->getFldNam()), '">', "\n";
		if ($this->getSelectInit() != '') {
			echo $indent, '            <option value="">', hh($this->getSelectInit()), 
					'</option>', "\n";
		}
		foreach ($this->getSelectAry() as $tmpkey => $tmpval) {
			if ($tmpkey === $this->getValue()) {
				echo $indent, '            <option class="formFldStyle" value="', hh($tmpkey), 
					'" selected="selected">', hh($tmpval), '</option>', "\n";
				$found = 1;
			} else {
				echo $indent, '            <option class="formFldStyle" value="', hh($tmpkey), 
					'">', hh($tmpval), '</option>', "\n";
			}
		}
		echo $indent, '        </select>', "\n";
		echo $indent, '    </td>', "\n";
		echo $indent, '</tr>', "\n";
		unset($tmpkey);	unset($tmpval); // per PHP doc
	}


	// GETTERS / SETTERS

	public function getSelectAry()
	{
		return $this->selectAry;
	}

	public function setSelectAry($val)
	{
		$this->selectAry = $val;
	}

	public function getSelectInit()
	{
		return $this->selectInit;
	}

	public function setSelectInit($val)
	{
		$this->selectInit = $val;
	}
}
