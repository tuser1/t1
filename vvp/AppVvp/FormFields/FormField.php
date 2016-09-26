<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\FormFields;

use AppVvp\App;
use AppVvp\Forms\Form;

/**
 * Parent class for all FORMFIELD objects
 */
abstract class FormField extends App
{
	const HTML_INPUT_FLD_MAXDISP = 40;

	protected $parentForm;
	protected $fldNam;
	protected $hidden = false;
	protected $sqlNam = '';
	protected $descr = '';
	protected $required = true;
	protected $minSiz = 1;
	protected $maxSiz = 1;
	protected $auditFtn = '';
	protected $reAuditVal = '';
	protected $reAuditErrTxt = '';
	protected $value = '';
	protected $dfltVal = '';
	protected $errMsg = '';

	protected $fieldType;
	protected $inputField;


	public function __construct($parentForm, $fldNam)
	{
		/* ****************************************
		 * Add form-field object [$this] to its parent form object's FFO list
		 * **************************************** */
		if (!isset($parentForm->getFfoList()[$fldNam])) {
			$parentForm->setFfoList($fldNam, $this);
		} else {
			trigger_error('[' . 'E015' . '] - *ERROR*: Duplicate ffo key: [' . 
					$fldNam . ']', E_USER_ERROR);
		}
		// ****************************************
		$this->fldNam = $fldNam;
		$this->parentForm = $parentForm;
		$this->initFieldType();
	}

	abstract protected function initFieldType();

	abstract public function renderFfo1($indent);


	public function renderFfoChkErr($indent)
	{
		$namid = $this->getFldNam() . Form::FFN_ERR_SUFX;
		echo $indent, '<tr>', "\n", 
			 $indent, '    <td></td>', "\n", 
			 $indent, '    <td class="formFldErrDsp"><span class="alertColor" name="', 
					hh($namid), '" id="', hh($namid), '">', hh($this->getErrMsg()), '</span></td>', "\n", 
			 $indent, '</tr>', "\n";
	}

	// GETTERS / SETTERS

	public function getFldNam()
	{
		return $this->fldNam;
	}

	public function setFldNam($val)
	{
		$this->fldNam = $val;
	}

	public function isHidden()
	{
		return $this->hidden;
	}

	public function setHidden($bool)
	{
		$this->hidden = $bool;
	}

	public function getSqlNam()
	{
		return $this->sqlNam;
	}

	public function setSqlNam($val)
	{
		$this->sqlNam = $val;
	}

	public function getDescr()
	{
		return $this->descr;
	}

	public function setDescr($val)
	{
		$this->descr = $val;
	}

	public function getRequired()
	{
		return $this->required;
	}

	public function setRequired($val)
	{
		$this->required = $val;
	}

	public function getMinSiz()
	{
		return $this->minSiz;
	}

	public function setMinSiz($val)
	{
		$this->minSiz = $val;
	}

	public function getMaxSiz()
	{
		return $this->maxSiz;
	}

	public function setMaxSiz($val)
	{
		$this->maxSiz = $val;
	}

	public function getAuditFtn()
	{
		return $this->auditFtn;
	}

	public function setAuditFtn($val)
	{
		$this->auditFtn = $val;
	}

	public function getReAuditVal()
	{
		return $this->reAuditVal;
	}

	public function setReAuditVal($val)
	{
		$this->reAuditVal = $val;
	}

	public function getReAuditErrTxt()
	{
		return $this->reAuditErrTxt;
	}

	public function setReAuditErrTxt($val)
	{
		$this->reAuditErrTxt = $val;
	}

	public function getDfltVal()
	{
		return $this->dfltVal;
	}

	public function setDfltVal($val)
	{
		$this->dfltVal = $val;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($val)
	{
		if (!empty($val)) {
			$this->value = $val;
		} else {
			$this->value = $this->dfltVal;
		}
	}

	public function getErrMsg()
	{
		return $this->errMsg;
	}

	public function setErrMsg($msg)
	{
		$this->errMsg = '[' . $this->getDescr() . ']: ' . $msg;
		$this->parentForm->setJsFocusElementId($this->getFldNam());
		$this->parentForm->addErrCnt1();
	}

	public function getFieldType()
	{
		return $this->fieldType;
	}

	public function setFieldType($val)
	{
		$this->fieldType = $val;
		if ($val === 'hidden') {
			$this->setHidden(true);
		}
	}

	public function isInputField()
	{
		return $this->inputField;
	}

	public function getParentForm()
	{
		return $this->parentForm;
	}
}
