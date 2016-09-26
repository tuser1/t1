<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\FilNams;

/**
 * Server file-system custom methods
 */
class FileSystem
{
	public static function chkDirFileCnt($dirnam)
	{
		echo '================================================<br />';
		echo 'Getting file-count ...<br /><br />';
		$recCnt1 = self::dirFilcnt($dirnam);
		echo 'File count: ', hh($recCnt1), '<br /><br />';
		if ($recCnt1 == 0) {
			echo '**** Directory is  EMPTY ****<br />';
		}
		echo '================================================<br />';
	}

	public static function eraseDir($dirnam, $fileTyp)
	{
		if ($fileTyp == 'S') {
	//		AuthUser::destroyTheSession();
		}
		$delCnt = self::eraseDirNonRecursive($dirnam);
		echo '<br />Process completed - Files deleted: ', hh($delCnt), '<br />';
	}

	public static function chkFileRecCnt($fnam)
	{
		echo '================================================<br />';
		echo 'Getting file record-count ...<br /><br />';
		$rec_cnt = self::fileReccnt($fnam);
		if ($rec_cnt < 0) {
			exit();
		}
		echo 'Record count: ', hh($rec_cnt), '<br /><br />';
		if ($rec_cnt == 0) {
			echo '**** File is  EMPTY ****<br />';
		}
		echo '================================================<br />';
		return $rec_cnt;
	}

	public static function eraseFile($fnam, $rec_cnt)
	{
		echo '<br />';
		echo '================================================<br />';
		if ($rec_cnt > 0) {
			echo 'ERASING File ...<br />';
			self::fileErase($fnam);
			echo 'ERASURE COMPLETE - ', hh($rec_cnt), ' records deleted<br />';
		} elseif ($rec_cnt == 0) {
			echo '**** File was ALREADY EMPTY ****<br />';
			echo '**** NO erasure performed ****<br />';
		}
		echo '================================================<br />';
	}

	// ------------------------------------------------------------------

	public static function createDir($dirname, $permissions, $recursive, $descr)
	{
		if (!is_dir($dirname)) {
			mkdir($dirname, $permissions, $recursive);
		} else {
			$errKey = 'E0400';
			PgMsgs::set($errKey, $descr . ' DIRECTORY ALREADY EXISTS.');
			trigger_error('[' . $errKey . '] ' . 
					$descr . ' DIRECTORY ALREADY EXISTS: [' . $dirname . ']', E_USER_ERROR);
		}
	}

	public static function validatSrvrFname($filename, $echo = true, $abort = false)
	{
		// Checks if filename exists on server-side.  Fnames must NOT be 'escaped' !!
		// Using the full http:// url-filename will not work.
		if (!is_file($filename)) {
			if (ini_get('display_errors') && $echo) {
				echo '*ERROR* SERVER filename [', hh($filename), '] does not exist.<br />';
			}
			if ($abort) {
				trigger_error('[' . 'E0200' . '] - *ERROR*: Server filename NOT FOUND: ' . 
						$filename, E_USER_ERROR);
			}
			return false;
		}
		return true;
	}

	public static function fileReccnt($fnam, $echo = true)
	{
		if (self::validatSrvrFname($fnam, $echo)) {
			$recCnt = 0;
			$fh = fopen($fnam, 'r');
			while (!feof($fh)) {
				if (fgets($fh) !== false) {
					$recCnt++;
				}
			}
		} else {
			$recCnt = -1;
		}
		return $recCnt;
	}

	public static function fileErase($fnam, $echo = true)
	{
		if (self::validatSrvrFname($fnam, $echo)) {
			$fh = fopen($fnam, 'w');
			fclose($fh);
			return true;
		} else {
			return false;
		}
	}

	public static function dirFilcnt($dir)
	{
		$fileCnt = 0;
		// Filter 'is_file' must be used here to avoid selecting directory names
		$files = array_filter(glob($dir . '/*'), 'is_file');
		if ($files) {
			$fileCnt = count($files);
		}
		return $fileCnt;
	}

	public static function eraseDirNonRecursive($dir, $fset = '*', $echo = true)
	{
		// NON-recursive deletion of ALL files in the folder
		$delCnt = 0;
		$files = glob($dir . "/$fset");
		$currSessidfl = $dir . FilNams::DS . 'sess_' . session_id();
		$indexPhp = $dir . FilNams::DS . FilNams::PN_INDEX . '.' . PG_FILEEXT;
		foreach($files as $file) {
			if (is_file($file)) {
				if ($file != $currSessidfl && $file != $indexPhp) {
					if ($echo) {
						echo '=> DELETING FILE: ', hh($file), '<br />';
					}
					unlink($file); // delete file
					$delCnt++;
				} else {
					if ($echo) {
						echo '==== Bypassing file: ', hh($file), ' ====<br />';
					}
				}
			}
		}
		return $delCnt;
	}
}

