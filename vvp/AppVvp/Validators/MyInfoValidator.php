<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Validators;

use AppVvp\App;
use AppVvp\Db\DbAccess;
use AppVvp\FilNams;
use AppVvp\General\PgMsgs;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class MyInfoValidator extends Validator
{
	public static function auditAndUpdate($formObj)
	{
		self::formPostFfosSetValMysqlClean($formObj);

		self::validateFfoVals1($formObj);

		if (!$formObj->getErrCnt1()) {
			// ===================================
			// NO errors - check for data changes before updating the database
			// ===================================
			DbAccess::getCustInfo($formObj, $compareOnly = true);
			if (DbAccess::sqlRowChgd()) {
				PgMsgs::set('E001', 'DATA MODIFIED BY ANOTHER USER - PLEASE RE-ENTER.');
				App::redirect(SAFE_RU_CURR_PAGE_URL);
			} else {
				// ===================================
				// Update the database
				// ===================================
				if (DbAccess::updCustInfo($formObj)) {
					PgMsgs::set('S001', 'Your info has been successfully UPDATED.');
					App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY));
				} else {
					PgMsgs::set('E002', 
							'NO CHANGES TO THE INFO WERE ENTERED - NO UPDATE WAS DONE.');
					$formObj->setJsFocusElementId($formObj->getJsFocus1stElement());
				}
			}
		}
	}

}
