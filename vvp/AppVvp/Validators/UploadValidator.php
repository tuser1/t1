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
use AppVvp\General\ErrHandler;
use AppVvp\General\PgMsgs;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class UploadValidator extends Validator
{
	public static function auditAndUpload($formObj)
	{
		/** ! NOTE: This function does NOT exec UNTIL THE FILE
		 *    UPLOAD HAS COMPLETED !
		 */
		if (empty($_FILES[Form::FFN_UPLOAD_VIDEO_F1])) {
			PgMsgs::set('E0202', 'UPLOAD file error');
			trigger_error('[' . 'E0202' . '] - UPLOAD file error - ' . 
				'$_FILES was empty: invalid upload-file attribs?', E_USER_ERROR);
		}
		$uploadFile = $_FILES[Form::FFN_UPLOAD_VIDEO_F1];
		if (TEST_RUN) {
			PgMsgs::set('I003', 'Upload temp filename: [' . $uploadFile['tmp_name'] . ']');
		}
		$ulFnamOnly = $uploadFile['name'];
		$ulFileFullDestination = FilNams::getAbsPathVideo() . 
					FilNams::DS . $ulFnamOnly;
		$pathInfo = pathinfo($ulFnamOnly);
		$ulFnamOnlyNoExt = $pathInfo['filename'];
		$ffoUlFile = $formObj->getFfo(Form::FFN_UPLOAD_VIDEO_F1);
		// ! NOTE: filename not in $_POST - add from $_FILES array:
		$ffoUlFile->setValue($ulFnamOnly);
		// ===================================
		if ($uploadFile['error'] === UPLOAD_ERR_EXTENSION) {
			PgMsgs::set('E0205', 'File UPLOAD was STOPPED/CANCELLED');
			$ffoUlFile->setErrMsg('File UPLOAD was STOPPED/CANCELLED');
			return; // NOTICE - exit function
		}
		if (is_file($ulFileFullDestination)) {
			$ffoUlFile->setErrMsg('File ALREADY EXISTS on server');
			return; // ERROR - exit function
		}
		// ===================================
		// Validate FORM fields from $_POST
		// ===================================
		self::formPostFfosSetValMysqlClean($formObj);
		self::validateFfoVals1($formObj);
		if ($formObj->getErrCnt1()) {
			return; // ERROR - exit function
		}
		// ===================================
		if (!isset($pathInfo['extension'])) {
			$ffoUlFile->setErrMsg('File EXTENSION missing');
			return; // ERROR - exit function
		} else {
			$fext = strtolower($pathInfo['extension']);
			if (!self::isValidVidFext($fext)) {
				$ffoUlFile->setErrMsg('Invalid video file EXTENSION: [' . $fext . ']');
				return; // ERROR - exit function
			}
		}
		// ===================================
		if ($_FILES[Form::FFN_UPLOAD_VIDEO_F1]['size'] > 
					Validator::getPhpUlMaxFilSizBytes()) {
			$ffoUlFile->setErrMsg(
				'Size [' . number_format($_FILES[Form::FFN_UPLOAD_VIDEO_F1]['size']) . 
				'] exceeds max [' . 
				number_format(Validator::getPhpUlMaxFilSizBytes()) . ']');
			return; // ERROR - exit function
		}
		// ===================================
		// Check for other file-upload system errors
		// ===================================
		if ($uploadFile['error'] !== UPLOAD_ERR_OK) {
			PgMsgs::set('E0201', 'UPLOAD file error');
			trigger_error('[' . 'E0201' . '] - UPLOAD file error: [' . 
					$uploadFile['error'] . '] - ' . 
					ErrHandler::getUploadErrMsg($uploadFile['error']), E_USER_ERROR);
		}
		// ===================================
		// Place this check LAST as $uploadFile['tmp_name'] will be empty 
		//   if there was a problem in the upload
		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mimeType = finfo_file($finfo, $uploadFile['tmp_name']);
		finfo_close($finfo);
		if (!self::isValidVidMime($mimeType)) {
			$ffoUlFile->setErrMsg('Invalid video MIME type: [' . $mimeType . ']');
			return; // ERROR - exit function
		}
		// ===================================
		// Move the uploaded file from temp dir to upload dir
		// ===================================
		move_uploaded_file($uploadFile['tmp_name'], $ulFileFullDestination);

		// 0644 => owner R/W, group R, other R
		chmod($ulFileFullDestination, 0644);

		// ----------------------------------------------------------
		// 'addNextVideoToVidpage()' allows multiple files with diff extensions, 
		//  e.g. f2.mp4, f2.mov, stores in DB as a single entry 'f2':
		$altText = $formObj->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR1)->getValue();
		$altTxt2 = $formObj->getFfo(Form::FFN_UPLOAD_VIDEO_DESCR2)->getValue();
		DbAccess::addNextVideoToVidpage(DbAccess::getCustNbr(), 
				$ulFnamOnlyNoExt, $altText, $altTxt2, '', 
				$formObj->getFfo(Form::FFN_UPLOAD_VIDEO_ASPECT)->getValue());
		// ----------------------------------------------------------
		DbAccess::logUserActivity(DbAccess::USR_ACT_UL_VID);
		// ----------------------------------------------------------
		// Redirect to uploaded video on customer's video page:
		$bytesSent = number_format(
				($_FILES[Form::FFN_UPLOAD_VIDEO_F1]['size'] / Validator::ONE_MB), 
				2, '.', ',');
		PgMsgs::set('S0100', 'UPLOAD SUCCESSFUL [' . $ulFnamOnly. '] - ' . 
				$bytesSent . ' MBs bytes SENT');
//		App::redirect(FilNams::getPgUrl(FilNams::PN_VIDSMY) . 
//					QSDLM1 . VideoPage::SV_VID_OPTNUM . '=999');
		App::redirect(SAFE_RU_CURR_PAGE_URL);
	}

}
