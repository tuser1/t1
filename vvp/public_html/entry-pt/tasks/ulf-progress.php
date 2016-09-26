<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp;

/**
 * PUBLIC script - valid URL: Repsond to Ajax request for file-upload progress
 */

session_start();

$key = ini_get('session.upload_progress.prefix') . 'Video~File~Upload';
if (isset($_SESSION[$key])) {
	echo json_encode($_SESSION[$key]);
} else {
	echo '';
}
