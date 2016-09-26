<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\General;

use AppVvp\Pages\Page;

/**
 * For page/flash MESSAGE objects
 */
class PgMsgs
{
	const SV_ERRMSGS1 = 'err_msgs1';
	const SV_SUCCESSMSGS1 = 'success_msgs1';
	const SV_INFOMSGS1 = 'info_msgs1';

	const PGMSG_CSS_CLASS_INFO = 'pageMsgInfo';
	const PGMSG_CSS_CLASS_ERROR = 'pageMsgError';
	const PGMSG_CSS_CLASS_SUCCESS = 'pageMsgSuccess';
	const PGMSG_CSS_CLASS_TEST = 'pageMsgTest';

	private static $pageMsgKey = '';
	private static $pageMsgSessVarsSave = array();



	public static function set($key, $msg, $pageMsgCssClass = '', 
			$strong = false, $dupeKeysOk = false)
	{
		self::$pageMsgKey = $key;
		switch (strtoupper(substr($key, 0, 1))) {
			case 'E': // error
				$msgAry = self::SV_ERRMSGS1;
				if ($pageMsgCssClass == '') {
					$pageMsgCssClass = self::PGMSG_CSS_CLASS_ERROR;
				}
				break;
			case 'S': // success
				$msgAry = self::SV_SUCCESSMSGS1;
				if ($pageMsgCssClass == '') {
					$pageMsgCssClass = self::PGMSG_CSS_CLASS_SUCCESS;
				}
				break;
			case 'I': // info
				$msgAry = self::SV_INFOMSGS1;
				break;
			default:
				if (ini_get('display_errors')) {
					echo '<strong>*E* PageMsg1 key-type invalid: [', hh($key), ']</strong><br />';
				}
		}
		if ($pageMsgCssClass == '') {
			$pageMsgCssClass = self::PGMSG_CSS_CLASS_INFO;
		}
		if (!$dupeKeysOk && isset($_SESSION[$msgAry][$key])) {
			if (ini_get('display_errors')) {
				echo '<strong>*E* Duplicate [', hh($msgAry), '] key: [', hh($key), ']</strong><br />';
			}
		}
		$_SESSION[$msgAry][$key] = new PgMsg($msg, $pageMsgCssClass, $strong);
	}


	public static function savePageMsgSessVars()
	{
		self::savePageMsgSessVar(self::SV_INFOMSGS1);
		self::savePageMsgSessVar(self::SV_SUCCESSMSGS1);
		self::savePageMsgSessVar(self::SV_ERRMSGS1);
	}

	private static function savePageMsgSessVar($msgAry)
	{
		if (isset($_SESSION[$msgAry])) {
			foreach ($_SESSION[$msgAry] as $key => $obj) {
//				echo "SAVing pageMsg sessvar: $msgAry - $key" . '<br />';
				self::$pageMsgSessVarsSave[$msgAry][$key] = clone($obj);
			}
		}
	}

	public static function restorPageMsgSessVars()
	{
		foreach (self::$pageMsgSessVarsSave as $msgAry => $val) {
			foreach (self::$pageMsgSessVarsSave[$msgAry] as $key => $obj) {
//				echo "RESTORing pagemsg sessvar: $msgAry - $key" . '<br />';
				$_SESSION[$msgAry][$key] = clone($obj);
			}
		}
	}

	public static function renderPageMsgs()
	{
		echo "\n";
		self::renderPageMsg(self::SV_INFOMSGS1);
		self::renderPageMsg(self::SV_SUCCESSMSGS1);
		self::renderPageMsg(self::SV_ERRMSGS1);
	}

	private static function renderPageMsg($msgAry)
	{
		if (isset($_SESSION[$msgAry])) {
			foreach ($_SESSION[$msgAry] as $key => $obj) {
				echo Page::INDENT_1, '<div class="', hh($obj->getPageMsgCssClass()), 
						'">', "\n";
				echo Page::INDENT_2, hh($obj->getStrongOpen()), '[', hh($key), '] ', 
					hh($obj->getMsg()), hh($obj->getStrongClose()), "\n";
				echo Page::INDENT_1, '</div>', "\n";
			}
		}
		unset($key); unset($obj); // per PHP doc
		// Clear msgs array after rendering to page
		unset($_SESSION[$msgAry]);
	}

	public static function unsetPageMsgs()
	{
		unset($_SESSION[self::SV_INFOMSGS1]);
		unset($_SESSION[self::SV_SUCCESSMSGS1]);
		unset($_SESSION[self::SV_ERRMSGS1]);
	}

	// GETTERS / SETTERS

	public static function getPageMsgKey()
	{
		return self::$pageMsgKey;
	}

}
