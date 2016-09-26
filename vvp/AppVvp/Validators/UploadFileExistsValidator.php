<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Validators;

use AppVvp\FilNams;
use AppVvp\Forms\Form;

/**
 * Validation: e.g. formfields, $_GET parms, etc.
 */
class UploadFileExistsValidator extends Validator
{
	public static function audit($formObj)
	{
		self::formPostFfosSetValMysqlClean($formObj);

		// Some browsers send full and/or fake paths w/ filename; use basename to REMOVE PATH.
		// NOTE: [basename] cmd only works on nix filenames, NOT e.g. C:\fakepath\fl.mp4:
		$filNamOnly = basename($formObj->getFfo(Form::FFN_UPLOAD_VIDEO_F1)->getValue());

		$filNam = FilNams::getAbsPathVideo() . FilNams::DS . $filNamOnly;
		if (is_file($filNam)) {
			echo 1;
		} else {
			echo 0;
		}
	}

}
