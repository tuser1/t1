<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Validators;

use AppVvp\App;
use AppVvp\Db\DbAccess;
use AppVvp\FilNams;
use AppVvp\Forms\Form;
use AppVvp\General\FileSystem;
use AppVvp\General\PgMsgs;
use AppVvp\Pages\VideoPage;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class VideoUpdValidator extends Validator
{
	public static function auditAndUpdate($formObj, $parentPage)
	{
		self::formPostFfosSetValMysqlClean($formObj);

		if (isset($_POST[Form::FFN_UPD_SEL_VID])) { // upd selected video info
			self::validateFfoVals1($formObj);
			if (!$formObj->getErrCnt1()) {
				$vidInfo = $parentPage->getVidInfo()[$parentPage->getCurrVidOptNum()];
				if (
					$formObj->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR1)->getValue() ===
							$vidInfo['vidtn_alttxt1'] &&
					$formObj->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR2)->getValue() ===
							$vidInfo['vidtn_alttxt2'] &&
					$formObj->getFfo(Form::FFN_UPLOAD_VIDEO_ASPECT)->getValue() ===
							$vidInfo['aspect']) {
					PgMsgs::set('E002', 
							'NO CHANGES TO THE INFO WERE ENTERED - NO UPDATE WAS DONE.');
				} else {
					// ===================================
					// UPDATE the existing database row
					// ===================================
					DbAccess::addNextVideoToVidpage(
						DbAccess::getCustNbr(), 
						$vidInfo['dbtbl_vidnam'], 
						$formObj->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR1)->getValue(),
						$formObj->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR2)->getValue(),
						'', $formObj->getFfo(Form::FFN_UPLOAD_VIDEO_ASPECT)->getValue());
					DbAccess::logUserActivity(DbAccess::USR_ACT_VID_UPD);
					self::pageMsgAndRedirect('S006', 'Video update successful');
				}
			} else {
				$parentPage->setHideVidUpdTbl(false);
			}

		} elseif (isset($_POST[Form::FFN_DEL_SEL_VID])) { // delete by selected video
			DbAccess::delCustomerVideo(DbAccess::getCustNbr(), 
					$_SESSION[VideoPage::SV_VID_OPT_DBTBL_NAM]);
			$delCnt = FileSystem::eraseDirNonRecursive(
					FilNams::getAbsPathVideo(), 
					$_SESSION[VideoPage::SV_VID_OPT_DBTBL_NAM] . '*');
			DbAccess::logUserActivity(DbAccess::USR_ACT_VID_DEL);
			self::pageMsgAndRedirect('S004', 'Video deletion successful');

		} elseif (isset($_POST[Form::FFN_DEL_ALL_VID])) { // delete ==ALL==
			DbAccess::delCustomerVideo(DbAccess::getCustNbr(), '');
			foreach ($parentPage->getVidInfo() as $i => $vidArys) {
				$delCnt = FileSystem::eraseDirNonRecursive(
						FilNams::getAbsPathVideo(), $vidArys['dbtbl_vidnam'] . '*');
			}
			DbAccess::logUserActivity(DbAccess::USR_ACT_VID_DEL_ALL);
			self::pageMsgAndRedirect('S005', 'Video DELETE-ALL successful');

		} else {
			$formObj->formSubmitError('E016', 'Invalid post - NO valid post key was sent');
		}
	}

	private static function pageMsgAndRedirect($key, $msg)
	{
		PgMsgs::set($key, $msg);
		App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY));
	}

}
