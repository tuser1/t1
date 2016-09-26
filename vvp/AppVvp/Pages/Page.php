<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\App;
use AppVvp\FilNams;
use AppVvp\General\AuthUser;
use AppVvp\General\BusDefs;
use AppVvp\General\PgLinkFactory;
use AppVvp\General\SysMain;
use AppVvp\Validators\Validator;

/**
 * Parent class for all PAGE objects
 */
class Page extends App
{
	// For indenting html output using spaces instead of tabs
	const INDENT_0 = '';
	const INDENT_1 = '    ';
	const INDENT_2 = '        ';
	const INDENT_3 = '            ';
	const INDENT_4 = '                ';
	const INDENT_5 = '                    ';
	const INDENT_6 = '                        ';

	const SV_ADMINPAG = 'admin_pag';
	const SV_USERPAG = 'user_pag';

	const ALERTCOLOR1 = '#ff0000';
	const TESTCOLOR1 = '#bbbbbb';

	const DIVBORDR_COLR1A = '#eeddee';
	const DIVBORDR_COLR1B = '#a087a0';
	const DIVBORDR_COLR2A = '#e8e8ff';
	const DIVBORDR_COLR2B = '#9999bb';

	protected $htmTitle;
	protected $metaDesc;
	protected $videoPage = false;
	private $videoPlayPage; // = false;

	protected $form1 = NULL; // Main object for page-form 1

	private $pagNamNoExt;
	private $menuLinks = '';
	private $menuLinkAry1 = array();

	const MNULNK_DLM = '|';
	const MNULNK_USE_DLM_SFX = 1;
	const MNULNK_NO_DLM_SFX = 0;

	private $bodyWidth;
	private $bgColor1;
	private $bgColor2;
	private $bgColor3;
	private $divBorderOutrColor;
	private $divBorderInnrColor;
	private $viTnLinkMsOutColor;
	private $viTnLinkMsOverColor;
	private $mainTxtColr;
	private $menuLnkColr;
	private $logo1;
	private $logo1Alt;
	private $bgImg1;
	private $tblBrdrColr;
	private $linkColr;
	private $formFldColr;

	private $pageMsgErrorColor;
	private $pageMsgSuccessColor;
	private $pageMsgInfoColor;




	public function __construct($pgName)
	{
		//echo 'In [Page::__construct]<br />';
		if (empty($pgName)) {
			$this->pagNamNoExt = SAFE_UU_PHP_SELF_FNAM_NO_EXT;
		} else {
			$this->pagNamNoExt = $pgName;
		}
		if (!PgLinkFactory::isAdminPage()) {
			$this->setPageAttribs();
			$this->bldMenuLnks(self::INDENT_2);
		}
		//=================================
		Validator::validatePassedGetKeys1();
		//=================================
	}

	protected function setPageAttribs()
	{
		if (ini_get('display_errors')) {
			if ($this->htmTitle == '') {
				echo '<strong>*******************************************************',
				'<br />* WARNING: NO title defined for this page.',
				'<br />*******************************************************</strong>';
			}
			if ($this->metaDesc == '') {
				echo '<strong>*******************************************************',
				'<br />* WARNING: NO description defined for this page.',
				'<br />*******************************************************</strong>';
			}
		}

		// =======================================================
		//		BEGIN: Dynamic CSS stylesheet properties
		// =======================================================
		if ($this->isVideoPage()) {
			$this->setBodyWidth('885px');
		} else {
			$this->setBodyWidth('1100px');
		}
		if (!AuthUser::isAdminUser()) {
			$this->setBgColor1('#ffccff');  // ffccff - light pink / ff00ff - bright pink
			$this->tblBrdrColr = '#ddbbdd';  // msie: '#ffddff'
			$this->setDivBorderOutrColor(self::DIVBORDR_COLR1A . ' ' . self::DIVBORDR_COLR1B . ' ' .
					self::DIVBORDR_COLR1A . ' ' . self::DIVBORDR_COLR1B);
			$this->setDivBorderInnrColor(self::DIVBORDR_COLR1B . ' ' . self::DIVBORDR_COLR1A . ' ' .
					self::DIVBORDR_COLR1B . ' ' . self::DIVBORDR_COLR1A);
		} else {
			$this->setBgColor1('#aaaaff'); // light blue
			$this->tblBrdrColr = '#ccccff';
			$this->setDivBorderOutrColor(self::DIVBORDR_COLR2A . ' ' . self::DIVBORDR_COLR2B . ' ' .
					self::DIVBORDR_COLR2A . ' ' . self::DIVBORDR_COLR2B);
			$this->setDivBorderInnrColor(self::DIVBORDR_COLR2B . ' ' . self::DIVBORDR_COLR2A . ' ' .
					self::DIVBORDR_COLR2B . ' ' . self::DIVBORDR_COLR2A);
		}
		$this->setBgColor2('#fff0ff');
		$this->setBgColor3('#ffeeff');
		if (!$this->isVideoPlayPage()) {
			$this->pageMsgErrorColor = self::ALERTCOLOR1;
			$this->pageMsgSuccessColor = '#0000ff';
			$this->pageMsgInfoColor = '#000000';
			if (!AuthUser::isAdminUser()) {
				$this->mainTxtColr = '#670067';
				$this->menuLnkColr = '#aa88aa';
				$this->viTnLinkMsOutColor = '#d7b7d7'; //#eeccee';
				$this->logo1 = FilNams::getRelPathImages() . '/' . FilNams::CO_BANNER1;
				$this->bgImg1 = FilNams::getRelPathImages() . '/' . FilNams::PAGE_BG1;
			} else {
				$this->mainTxtColr = '#0000cc';
				$this->menuLnkColr = '#7777dd';
				$this->viTnLinkMsOutColor = '#ccccff';
				$this->logo1 = FilNams::getRelPathImages() . '/' . FilNams::CO_BANNER3;
				$this->bgImg1 = FilNams::getRelPathImages() . '/' . FilNams::PAGE_BG3;
			}
			$this->logo1Alt = 'Los Angeles Wedding Video';
			$this->viTnLinkMsOverColor = $this->menuLnkColr;
			$this->formFldColr = $this->mainTxtColr;
		} else {
			$this->pageMsgErrorColor = '#ff7777';
			$this->pageMsgSuccessColor = $this->getBgColor1();
			$this->pageMsgInfoColor = '#bbbbbb';
			$this->mainTxtColr = '#ffffff';
			$this->menuLnkColr = $this->getBgColor1();
			$this->logo1 = FilNams::getRelPathImages() . '/' . FilNams::CO_BANNER2;
			$this->logo1Alt = 'Los Angeles Videography';
			$this->bgImg1 = FilNams::getRelPathImages() . '/' . FilNams::PAGE_BG2;
			$this->viTnLinkMsOutColor = '#999999';
			$this->viTnLinkMsOverColor = '#ffffff';
			$this->formFldColr = '#555555';
		}
		$this->linkColr = '#0000ff';  // dark blue
		// =======================================================
		//		END: Dynamic CSS stylesheet properties
		// =======================================================
	}


	private function bldMenuLnks($indent)
	{
		if (!AuthUser::authenticated()) {
			$this->menuLinkAry1[FilNams::PN_VIDSWED] = '';
			$this->menuLinkAry1[FilNams::PN_VIDSMIT] = '';
			$this->menuLinkAry1[FilNams::PN_DEMOSTILLS] = '';
			$this->menuLinkAry1[FilNams::PN_OPTIONS] = '';
			$this->menuLinkAry1[FilNams::PN_CONTACT] = '';
		} else {
			$this->menuLinkAry1[FilNams::PN_VIDSMY] = '';
			$this->menuLinkAry1[FilNams::PN_UPLOAD] = '';
			$this->menuLinkAry1[FilNams::PN_MYINFO] = '';
			$this->menuLinkAry1[FilNams::PN_PASSCHG] = '';
		}
		if (AuthUser::authenticated() && AuthUser::isAdminUser()) {
			if (isset($_SESSION[self::SV_ADMINPAG])) {
				$this->menuLinkAry1[$_SESSION[self::SV_ADMINPAG]] = '';
			} else {
				$this->menuLinkAry1[FilNams::getAdminPagNamStart()] = '';
			}
		}
		$ii = 0;
		foreach ($this->menuLinkAry1 as $lnkNam => $val) {
			if (++$ii < count($this->menuLinkAry1)) {
				$this->bldMenuLnk($lnkNam, self::MNULNK_USE_DLM_SFX, $indent);
			} else {
				$this->bldMenuLnk($lnkNam, self::MNULNK_NO_DLM_SFX, $indent);
			}
		}
	}

	private function bldMenuLnk($lnkNam, $useDlmSuffix, $indent)
	{
		$lnkNam = hh($lnkNam);
		$indent = hh($indent);
		if (PgLinkFactory::isAdminPage($lnkNam)) {
			$mnuLnkDesc = 'ADMIN: ' . PgLinkFactory::getDescr1($lnkNam);
		} else {
			$mnuLnkDesc = PgLinkFactory::getDescr1($lnkNam);
		}
		$mnuLnkDesc = hh($mnuLnkDesc);
		if ($lnkNam === $this->pagNamNoExt && !$this->isVideoPlayPage()) {
			$this->menuLinks.=
					$indent . '<span id="' . $lnkNam . '" class="curLnk">' .
					$mnuLnkDesc . '</span>' . "\n";
		} else {
			$this->menuLinks.=
					$indent . '<a class="mnuLnk" id="' . $lnkNam . '"' . "\n" .
					$indent . '    href="' . uu(FilNams::getPgUrl($lnkNam)) . '">' .
					$mnuLnkDesc . '</a>' . "\n";
		}
		if ($useDlmSuffix) {
			$this->menuLinks.= $indent . '<span class="menuLnkDlm01">' .
					self::MNULNK_DLM . '</span>' . "\n";
		}
	}

	public function renderMenuLinks()
	{
		return  "\n" . self::INDENT_1 . '<nav role="navigation">' . "\n" . $this->menuLinks . 
				self::INDENT_1 . '</nav>' . "\n";
	}

	public function renderMainH1()
	{
		if (!AuthUser::authenticated()) {
			return BusDefs::BUSNAME1_F1 . ' - Los Angeles Videography Services';
		} else {
			return BusDefs::BUSNAME1_F1 . ' - My Account';
		}
	}


	public function runPage()
	{
		if (!PgLinkFactory::isAdminPage()) {
			require FilNams::DN_TEMPLATES . FilNams::DS . 'main-page-layout' . 
					'.' . TMPL_FILEEXT;
		} else {
			require FilNams::DN_TEMPLATES . FilNams::DS . 'main-page-layout-admin' . 
					'.' . TMPL_FILEEXT;
		}
	}


	public function getPageContent()
	{
		if ($this->isVideoPage()) {
			$contentFl = 'videos';
		} else {
			$contentFl = SAFE_UU_PHP_SELF_FNAM_NO_EXT;
		}
		if (!PgLinkFactory::isAdminPage()) {
			require FilNams::DN_TEMPLATES . FilNams::DS . 
					FilNams::DN_PAGES . FilNams::DS . $contentFl . '.' . TMPL_FILEEXT;
		} else {
			require FilNams::DN_TEMPLATES . FilNams::DS . 
					FilNams::DN_ADMIN . '-' . FilNams::DN_PAGES . FilNams::DS . 
					$contentFl . '.' . TMPL_FILEEXT;
		}
	}


	public function getJsFooter() // for javascript at end of page body
	{
		if (!isset($_SESSION[SysMain::SV_JS_DISABLED])) {
			if (is_object($this->form1)) {
				if (!Validator::isCfvMethod(Validator::CFV_NONE)) {
					require FilNams::DN_TEMPLATES . FilNams::DS . 'js-footer-form' . 
							'.' . TMPL_FILEEXT;
				}
			}
			if (PgLinkFactory::isCurrentPage(FilNams::PN_DEMOSTILLS)) {
				require FilNams::DN_TEMPLATES . FilNams::DS . 'js-footer-stillframes' . 
						'.' . TMPL_FILEEXT;

			} elseif ($this->isVideoPage()) {
				require FilNams::DN_TEMPLATES . FilNams::DS . 'js-footer-videos' . 
						'.' . TMPL_FILEEXT;
			}
		}
	}


	// GETTERS / SETTERS

	public function isVideoPage()
	{
		return $this->videoPage;
	}

	private function setVideoPage($bool)
	{
		$this->videoPage = $bool;
	}

	public function isVideoPlayPage()
	{
		return $this->videoPlayPage;
	}

	public function setVideoPlayPage($bool)
	{
		$this->videoPlayPage = $bool;
	}

	public function getForm1()
	{
		return $this->form1;
	}

	public function setForm1($formObj)
	{
		$this->form1 = $formObj;
	}

	public function getHtmTitle()
	{
		return $this->htmTitle;
	}

	public function getMetaDesc()
	{
		return $this->metaDesc;
	}

	public function getBodyWidth()
	{
		return $this->bodyWidth;
	}
	public function setBodyWidth($val)
	{
		$this->bodyWidth = $val;
	}

	public function getBgColor1()
	{
		return $this->bgColor1;
	}
	public function setBgColor1($val)
	{
		$this->bgColor1 = $val;
	}

	public function getBgColor2()
	{
		return $this->bgColor2;
	}
	public function setBgColor2($val)
	{
		$this->bgColor2 = $val;
	}

	public function getBgColor3() {
		return $this->bgColor3;
	}
	public function setBgColor3($val)
	{
		$this->bgColor3 = $val;
	}

	public function getDivBorderOutrColor()
	{
		return $this->divBorderOutrColor;
	}
	public function setDivBorderOutrColor($val)
	{
		$this->divBorderOutrColor = $val;
	}

	public function getDivBorderInnrColor()
	{
		return $this->divBorderInnrColor;
	}
	public function setDivBorderInnrColor($val)
	{
		$this->divBorderInnrColor = $val;
	}

	public function getViTnLinkMsOutColor()
	{
		return $this->viTnLinkMsOutColor;
	}

	public function getViTnLinkMsOverColor()
	{
		return $this->viTnLinkMsOverColor;
	}

	public function getMainTxtColr()
	{
		return $this->mainTxtColr;
	}

	public function getMenuLnkColr()
	{
		return $this->menuLnkColr;
	}

	public function getLogo1()
	{
		return $this->logo1;
	}

	public function getLogo1Alt()
	{
		return $this->logo1Alt;
	}

	public function getBgImg1()
	{
		return $this->bgImg1;
	}

	public function getTblBrdrColr()
	{
		return $this->tblBrdrColr;
	}

	public function getLinkColr()
	{
		return $this->linkColr;
	}

	public function getPageMsgErrorColor()
	{
		return $this->pageMsgErrorColor;
	}

	public function getPageMsgSuccessColor()
	{
		return $this->pageMsgSuccessColor;
	}

	public function getPageMsgInfoColor()
	{
		return $this->pageMsgInfoColor;
	}

	public function getFormFldColr()
	{
		return $this->formFldColr;
	}

}
