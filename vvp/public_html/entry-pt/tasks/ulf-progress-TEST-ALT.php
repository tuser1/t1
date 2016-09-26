<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp;

/**
 * PUBLIC script - valid URL: Repsond to Ajax request for upload progress
 */

function getUlFileInfoFromTmpDir()
{
	// NON-recursive
	$filCnt = 0;
	$filSiz = 0;
	$files = glob(ini_get('upload_tmp_dir') . '/php*');
	foreach($files as $file) {
		if (is_file($file)) {
			$filCnt++;
			$filSiz = filesize($file);
		}
	}
	if ($filCnt === 1) { // there should be only ONE 'php*' filename found
		return $filSiz;
	} else {
		return 0;
	}
}

session_start();

$key = ini_get('session.upload_progress.prefix') . 'Video~File~Upload';
if (1 === 0) { // TEST to force condition #2
//if (isset($_SESSION[$key])) {
	echo json_encode($_SESSION[$key]);

} elseif ($processedBytes = getUlFileInfoFromTmpDir()) {
	// ===============================================
	$totalBytes = 2000000000; // TODO: Requires value sent via javascript [file.size]
	// ===============================================
	$tmpDirInfoAry = array( // Uses the same element/key names as PHP's sessvar above
		"content_length"  => $totalBytes,
		"bytes_processed" => $processedBytes,
	);
	echo json_encode($tmpDirInfoAry);

} else {
	echo '';
}
