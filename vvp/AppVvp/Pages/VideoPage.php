<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\App;
use AppVvp\Db\DbAccess;
use AppVvp\FilNams;
use AppVvp\Forms\Form;
use AppVvp\General\AuthUser;
use AppVvp\General\BusDefs;
use AppVvp\General\PgLinkFactory;
use AppVvp\General\PgMsgs;
use AppVvp\General\SysMain;
use AppVvp\Validators\Validator;

/**
 * Parent class for all VIDEO PAGE objects
 */
class VideoPage extends Page
{
	const SV_VID_OPTNUM 	   = 'vidoptnum';
	const SV_VID_OPT_DBTBL_NAM = 'vidopt_dbtblnam';
	const SV_VID_AUTOPLAY 	   = 'vid_autoplay';
	const SV_LOCAL_VIDEO 	   = 'local_video';

	// SD format - 4:3 apsect ratio
	const VI_LNK_TN_WW = 175;
	const VI_LNK_TN_HH = 132;
	const VI_LNK_TN_WW_SEL = 159;
	const VI_LNK_TN_HH_SEL = 120;

	const VI_AUTOPLAY_DFLT = 'false';

	private $currVidOptNum;
	private $hideVidUpdTbl = true;

	private $vidFullUrl;
	private $vidInfo = array();
	private $vidNoTnImg = array();
	private $vidNamsKey;
	private $vidSrc = '';

	private $vidCnt = 0;
	private $vidLinksPerRow;

	private $vidFormat;
	private $vidAutoplay;
	private $vidClassId;

	private $vidHt = 0;
	private $vidWd;



	public function __construct($pgName)
	{
		//echo 'In [self::__construct]<br />';

		$this->videoPage = true;
		$this->sessionVars();
		if (SysMain::isMsiePreVers9()) {
			// Older vers of MSIE - use 'wmv' format & old html object format
			$this->vidFormat = 'wmv';
			$this->vidClassId = 'clsid:6BF52A52-394A-11D3-B153-00C04F79FAA6';
		} else {
			$this->vidFormat = 'html5';
			if ($_SESSION[self::SV_VID_AUTOPLAY] === 'true') {
				$this->vidAutoplay = 'autoplay';
			} else {
				$this->vidAutoplay = '';
			}
		}
		// Dirname [FilNams::DN_VIDEO] is also a 'localhost' virtual dir name.
		// Forces 'video' to 'http://localhost/video'  OR  'http://www.voilavideo.com/video'
		//   regardless of launch path.
		if ($_SESSION[self::SV_LOCAL_VIDEO] == 'n') {
			$this->vidFullUrl = 'http://' . BusDefs::WEBNAME1_F1 . '/' . FilNams::DN_VIDEO;
		} else {
			// Use 'HTTP_HOST' here instead of SysMain::LOCALHOST because of
			//  possibility of localhost WampServer w/ port# [e.g. localhost:81]
			$this->vidFullUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . FilNams::DN_VIDEO;
		}
		// Do not abort page script if DB/server is down
		if (App::$dbMain->connect1($reportErr = false)) {
			DbAccess::getVidpageVideos($this);
		}
		if ($this->vidCnt > 0) {
			if ($this->currVidOptNum > $this->vidCnt) {
				$this->currVidOptNum = $this->vidCnt;
			}
			if ($this->currVidOptNum == 0 &&
					PgLinkFactory::isCurrentPage(FilNams::PN_VIDSMY)) {
				$this->currVidOptNum = 1;
			}
			if ($this->currVidOptNum > 0) {
				if ($this->vidInfo[$this->currVidOptNum]['aspect'] === 'HD') {
					if (SysMain::isMsiePreVers9()) {
						$this->vidWd = 600;
					} else {
						$this->vidWd = 650; // 520;
					}
				} else {
					$this->vidWd = 410;
				}
				if (SysMain::isMsiePreVers9()) {
					// Height used for older html WMV / Windows Media Player objects ONLY
					$this->vidHt = 372; // Increase ht for MP object w/ controls
				}
				$this->vidSrc = $this->vidInfo[$this->currVidOptNum]['url_fullname'];
				$_SESSION[self::SV_VID_OPT_DBTBL_NAM] = 
						$this->vidInfo[$this->currVidOptNum]['dbtbl_vidnam'];
			}
		} else {
			$this->currVidOptNum = 0;
		}
		if ($this->currVidOptNum == 0) {
			$this->setVideoPlayPage(false);
		} else {
			$this->setVideoPlayPage(true);
		}
		if (PgLinkFactory::isCurrentPage(FilNams::PN_VIDSMY) && 
					$this->isVideoPlayPage()) {
			$this->vidLinksPerRow = 4;
		} else {
			$this->vidLinksPerRow = 2;
		}
		$_SESSION[self::SV_VID_OPTNUM] = $this->currVidOptNum;
		parent::__construct($pgName);
	}


	public function renderVideoTnLinks($indent)
	{
		echo "\n";
		if ($this->vidCnt > 0) {
			$viVidSpac = round($this->vidCnt / 2) * 2;  // round up to an even #
			for ($rowStartIdx = 1; $rowStartIdx <= $viVidSpac; 
						$rowStartIdx+=$this->vidLinksPerRow) {
				echo $indent, '<tr>', "\n";
				for ($viColCnt = 1, $i = $rowStartIdx; 
					 $viColCnt <= $this->vidLinksPerRow; $viColCnt++, $i++) {
					if ($i <= $this->vidCnt) {

						$this->renderVideoTnLink($indent, $i);

//					} elseif ($this->vidCnt > 1) {
					} elseif ($this->vidCnt > $this->vidLinksPerRow) {
						// CREATE BLANK 'TD' CELL
						echo $indent, '    <td class="vidLnkTNblank"></td>', "\n";
					}
				}
				echo $indent, '</tr>', "\n";
			}
		} else {
			echo $indent, '<tr>', "\n";
			echo $indent, '    <td>', "\n";
			echo $indent, '        <p>* NO VIDEO LINKS FOR THIS PAGE *</p>', "\n";
			echo $indent, '    </td>', "\n";
			echo $indent, '</tr>', "\n";
		}
	}

	private function renderVideoTnLink($indent, $i)
	{
		$absVidTnLinkFilename = rawurldecode(FilNams::getAbsPathImages() . FilNams::DS . 
					$this->vidInfo[$i]['url_name'] . '.jpg');
		$relVidTnLinkFilename = FilNams::getRelPathImages() . 
					AuthUser::userSubPathPrependDs() . '/' . 
					$this->vidInfo[$i]['url_name'] . '.jpg';
		// NOTE! For <canvas> it is better to declare width/height here rather than
		// using javascript - in Chrome browser it 'flashes' when using js to 
		// change values dynamically.
		if ($i != $this->currVidOptNum) {
			// ------------ CREATE VIDEO-LINK & THUMBNAIL ------------
			echo $indent, '    <td> ', "\n";
			echo $indent, '        <a href="' . 
				uu( SAFE_RU_CURR_PAGE_URL . QSDLM1 . self::SV_VID_OPTNUM . '=' . 
						ur(sprintf("%03d", $i))
				   ) . '">', "\n";
			if (is_file($absVidTnLinkFilename) || 
					SysMain::isMsiePreVers9() || 
					isset($_SESSION[SysMain::SV_JS_DISABLED])) {
				echo $indent, '        <img class="vidLnkTNimg" src="', 
						hh($relVidTnLinkFilename), '" ', "\n";
				echo $indent, '        alt="', hh($this->vidInfo[$i]['vidtn_alttxt1']), 
						'"></a>', "\n";
			} else {
				echo $indent, '        <canvas id="', hh($this->vidInfo[$i]['url_name']), 
						'" class="vidLnkTNimg" width="', self::VI_LNK_TN_WW, 
						'px" height="', self::VI_LNK_TN_HH, 'px"></canvas>', '</a>', "\n";
				$this->videoNoTnImgAdd($i);
			}
		} else {
			// ------- CURRENTLY SELECTED VIDEO - THUMBNAIL ONLY, NO LINK -------
			echo $indent, '    <td class="vidLnkTNsel">', "\n";
			if (is_file($absVidTnLinkFilename) || 
					SysMain::isMsiePreVers9() || 
					isset($_SESSION[SysMain::SV_JS_DISABLED])) {
				echo $indent, '        <img class="vidLnkTNimgSel" src="', 
						hh($relVidTnLinkFilename), '" ', "\n";
				echo $indent, '        alt="', hh($this->vidInfo[$i]['vidtn_alttxt1']), '">', "\n";
			} else {
				echo $indent, '        <canvas id="', hh($this->vidInfo[$i]['url_name']), 
						'" width="', self::VI_LNK_TN_WW_SEL, 'px" height="', 
						self::VI_LNK_TN_HH_SEL, 'px"></canvas>', "\n";
				$this->videoNoTnImgAdd($i);
			}
		}
		echo $indent, '    </td>', "\n";
	}

	private function videoNoTnImgAdd($i)
	{
		$this->vidNoTnImg[$i] = '';
	}


	private function sessionVars()
	{
		if (Validator::issetGetAddAry1(self::SV_VID_OPTNUM)) {
			// !! leading '0's returns FALSE on FILTER_VALIDATE_INT
//			if (filter_var($_GET[self::SV_VID_OPTNUM], FILTER_VALIDATE_INT))
			$tmp1 = trim($_GET[self::SV_VID_OPTNUM]);
			if (!Validator::isDigits1($tmp1, 3)) {
				$tmp1 = 0;
			} else {
				$tmp1+= 0;	//convert to numeric, remove leading '0's;
			}
			$this->currVidOptNum = $tmp1;
		} else {
			if (!isset($_SESSION[self::SV_VID_OPTNUM]) || 
					!PgLinkFactory::isCurrentPage(FilNams::PN_VIDSMY)) {
				$this->currVidOptNum = 0;
			} else {
				$this->currVidOptNum = $_SESSION[self::SV_VID_OPTNUM];
			}
		}
		if (Validator::issetGetAddAry1(self::SV_VID_AUTOPLAY)) {
			$tmp1 = strtolower(Validator::cleanInput1($_GET[self::SV_VID_AUTOPLAY]));
			if ($tmp1 != 'true' && $tmp1 != 'false') {
				PgMsgs::set('E006', 'Invalid boolean value passed in [' . 
					self::SV_VID_AUTOPLAY . '] parm: was set to [' . 
						self::VI_AUTOPLAY_DFLT . '] ...');
				$_SESSION[self::SV_VID_AUTOPLAY] = self::VI_AUTOPLAY_DFLT;
			} else {
				$_SESSION[self::SV_VID_AUTOPLAY] = $tmp1;
			}
		} else {
			if (!isset($_SESSION[self::SV_VID_AUTOPLAY])) {
				$_SESSION[self::SV_VID_AUTOPLAY] = self::VI_AUTOPLAY_DFLT;
			}
		}
		//----------------------------------------------
		if (Validator::issetGetAddAry1(self::SV_LOCAL_VIDEO)) {
			$tmpget1 = Validator::cleanInput1($_GET[self::SV_LOCAL_VIDEO]);
			if ($tmpget1 != '') {
				$_SESSION[self::SV_LOCAL_VIDEO] = strtolower($tmpget1);
			} else {
				$_SESSION[self::SV_LOCAL_VIDEO] = '';
			}
		}
		if (!isset($_SESSION[self::SV_LOCAL_VIDEO])) {
			$_SESSION[self::SV_LOCAL_VIDEO] = '';
		}
	}

	public function addVidNam($vidNm, $altTxt, $altTxt2, $viTyp, $aspect)
	{
		$this->vidCnt++;
		$this->vidInfo[$this->vidCnt]['dbtbl_vidnam'] = $vidNm;
		$this->vidInfo[$this->vidCnt]['url_name'] = 
				ru(FilNams::getFnamPrefixVideos($viTyp) . $vidNm);
		$this->vidInfo[$this->vidCnt]['url_fullname'] = $this->vidFullUrl . 
				AuthUser::userSubPathPrependDs() . '/' . 
				$this->vidInfo[$this->vidCnt]['url_name'];
		$this->vidInfo[$this->vidCnt]['vidtn_alttxt1'] = $altTxt;
		$this->vidInfo[$this->vidCnt]['vidtn_alttxt2'] = $altTxt2;
		$this->vidInfo[$this->vidCnt]['aspect'] = $aspect;

//		// NOTE: This logic works locally as well as on the internet ...
//		// TODO: Currently the video's file-extension is missing - 
//		//       use all possible video file-extensions:
//		$srvrvidnam = rawurldecode(FilNams::getAbsPathDocRoot() . 
//			  	FilNams::DS . FilNams::DN_VIDEO . FilNams::DS . 
//				$this->vidInfo[$this->vidCnt]['url_name']);
//		//echo $srvrvidnam, '<br />';
//		FileSystem::validatSrvrFname($srvrvidnam);
	}

	public function renderJson()
	{
		$vidLnkCanvasObjsAry = array();
		foreach ($this->getVidNoTnImg() as $i => $val) {
			$vidLnkCanvasObjsAry[] = array(
				'vidTnId' => $this->vidInfo[$i]['url_name'],
				'altText' => $this->vidInfo[$i]['vidtn_alttxt1'],
				'altTxt2' => $this->vidInfo[$i]['vidtn_alttxt2']
			);
		}
		$ary = array(
			'vidLnkCanvasObjs' => $vidLnkCanvasObjsAry,

			'textColor' => $this->getMainTxtColr(),
			'currFormName' => 'VideoUpdateForm', 
			'isMyVidPlayPage' => ($this->isVideoPlayPage() &&
					PgLinkFactory::isCurrentPage(FilNams::PN_VIDSMY)), // bool
			'hideVidUpdTbl' => $this->hideVidUpdTbl, // bool
			'ffnVidUpdTbl' => Form::FFN_VID_UPD_TBL,
			'ffnUpdSelVid' => Form::FFN_UPD_SEL_VID,
			'ffnCancelUpdVid' => Form::FFN_CANCEL_UPD_VID,
			'ffnDelSelVid' => Form::FFN_DEL_SEL_VID,
			'ffnDelAllVid' => Form::FFN_DEL_ALL_VID
		);
		echo Page::INDENT_1, 'var gJsonVideo = jsonParse(\'', jj($ary), '\');', "\n";
	}


	// GETTERS / SETTERS

	public function getVidNamsKey()
	{
		return $this->vidNamsKey;
	}

	protected function setVidNamsKey($val)
	{
		$this->vidNamsKey = $val;
	}

	public function getVidInfo()
	{
		return $this->vidInfo;
	}

	public function getCurrVidOptNum()
	{
		return $this->currVidOptNum;
	}

	public function getHideVidUpdTbl()
	{
		return $this->hideVidUpdTbl;
	}

	public function setHideVidUpdTbl($hideVidUpdTbl)
	{
		$this->hideVidUpdTbl = $hideVidUpdTbl;
	}

	public function getVidSrc()
	{
		return $this->vidSrc;
	}

	public function getVidFormat()
	{
		return $this->vidFormat;
	}

	public function getVidAutoplay()
	{
		return $this->vidAutoplay;
	}

	public function getVidClassId()
	{
		return $this->vidClassId;
	}

	public function getVidNoTnImg()
	{
		return $this->vidNoTnImg;
	}

	public function getVidHt()
	{
		return $this->vidHt;
	}

	public function getVidWd()
	{
		return $this->vidWd;
	}

}
