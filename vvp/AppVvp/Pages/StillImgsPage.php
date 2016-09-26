<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Pages;

use AppVvp\App;
use AppVvp\General\BusDefs;
use AppVvp\Validators\Validator;

/**
 * For event still-frames/photos PAGE object
 * 'SI_' => still-image
 */
class StillImgsPage extends Page
{
	const  SI_COLS = 3;
	const  SI_ROWS = 2;

	const  SI_GETV_IMGPGOPT = 'imgpgopt';
	const  SV_SI_PGNUM 	= 'pgnum';

	private $siBtnNextPrevFocus;
	private $siBtnNextSelected;
	private $siBtnPrevSelected;
	private $siPagNext;

	private $siPgImgCnt;
	private $siTtlImgCnt;
	private $siImgNumOffset;

	private $siPagNum;
	private $siPagPrev;
	private $siTtlPagCnt;

	private $siGalleryAry = array();



	public function __construct($pgName)
	{
		//echo 'In [StillImgsPage::__construct]<br />';

		$this->htmTitle = 'Los Angeles Wedding Birthday Mitzvah Video by ' . 
				BusDefs::BUSNAME1_F2 . ' - Los Angeles Videographer';
		$this->metaDesc = 
			'Los Angeles Videographers, Los Angeles Weddings Birthdays Mitzvahs Videos by ' . 
			BusDefs::BUSNAME1_F2 . 
			'. We will create a lovely, heart touching video of your once-in-a-lifetime event.';

		$this->sessionVars();

		parent::__construct($pgName);

		if ($this->siPagNum < 1) {
			$this->siBtnNextPrevFocus = 'btn_prev';
			$this->siBtnPrevSelected = 'stillFrBtnSel';
			$this->siBtnNextSelected = 'stillFrBtn';
		} else {
			$this->siBtnNextPrevFocus = 'btn_next';
			$this->siBtnPrevSelected = 'stillFrBtn';
			$this->siBtnNextSelected = 'stillFrBtnSel';
		}
		$this->siPagNum = abs($this->siPagNum);
		$this->siTtlImgCnt = 90;
		$this->siTtlPagCnt = (int)(($this->siTtlImgCnt - 1) / 6) + 1;
		$this->siPagNum  = $this->ckpagnum($this->siPagNum);
		$this->siPagPrev = $this->ckpagnum($this->siPagNum - 1);
		$this->siPagNext = $this->ckpagnum($this->siPagNum + 1);

		$this->siPgImgCnt = self::SI_COLS * self::SI_ROWS;

		self::createSiGallery();
	}

	private function sessionVars()
	{
		if (Validator::issetGetAddAry1(self::SI_GETV_IMGPGOPT)) {
			$this->siPagNum = trim($_GET[self::SI_GETV_IMGPGOPT]);
			if (!Validator::isDigits2($this->siPagNum, 2)) {
				$this->siPagNum = 0;
			} else {
				$this->siPagNum+= 0; // convert to numeric, remove leading '0's;
			}
			if ($this->siPagNum == 0) {
				$this->siPagNum = 1;
			}				
			$_SESSION[self::SV_SI_PGNUM] = $this->siPagNum;
		} else {
			if (isset($_SESSION[self::SV_SI_PGNUM])) {
				$this->siPagNum = $_SESSION[self::SV_SI_PGNUM];
			} else {
				$this->siPagNum = 1;
				$_SESSION[self::SV_SI_PGNUM] = $this->siPagNum;
			}
		}
	}

	private function ckpagnum($pagvar)
	{
		if ($pagvar > $this->siTtlPagCnt) {
			$pagvar = 1;
		}
		if ($pagvar < 1) {
			$pagvar = $this->siTtlPagCnt;
		}
		return $pagvar;
	}

	private function createSiGallery()
	{
		$siActualImgNbr = ($this->siPgImgCnt * ($this->siPagNum - 1)) + 1;
		$this->siImgNumOffset = $siActualImgNbr - 1;
		$siRow = -1;
		for ($siPgimgNbr = 1; $siPgimgNbr < $this->siPgImgCnt;) {
			$siRow++;
			$this->siGalleryAry[$siRow] = array();
			for (	$siColCnt = 0; $siColCnt < self::SI_COLS && 
					$siActualImgNbr <= $this->siTtlImgCnt; 
					$siColCnt++, $siPgimgNbr++, $siActualImgNbr++) {
				$this->siGalleryAry[$siRow][$siColCnt] = $siActualImgNbr;
			}
		}
	}

	public function renderJson()
	{
		$ary = array(
			'totlPagCnt' => App::$page->getSiTtlPagCnt(),
			'currentPagNum' => App::$page->getSiPagNum(),
			'PagOptNumParmName' => StillImgsPage::SI_GETV_IMGPGOPT,
			'btnNextPrevFocus' => App::$page->getSiBtnNextPrevFocus(),
			'QsDlm1' => QSDLM1,
			'currPageUrl' => SAFE_RU_CURR_PAGE_URL
		);
		echo Page::INDENT_1, 'var gJsonStillframes = jsonParse(\'', jj($ary), '\');', "\n";
	}


	// GETTERS / SETTERS

	public function getSiBtnNextPrevFocus()
	{
		return $this->siBtnNextPrevFocus;
	}

	public function getSiBtnNextSelected()
	{
		return $this->siBtnNextSelected;
	}

	public function getSiBtnPrevSelected()
	{
		return $this->siBtnPrevSelected;
	}

	public function getSiPagNext()
	{
		return $this->siPagNext;
	}

	public function getSiImgNumOffset()
	{
		return $this->siImgNumOffset;
	}

	public function getSiPagNum()
	{
		return $this->siPagNum;
	}

	public function getSiPagPrev()
	{
		return $this->siPagPrev;
	}

	public function getSiTtlPagCnt()
	{
		return $this->siTtlPagCnt;
	}

	public function getSiGalleryAry()
	{
		return $this->siGalleryAry; // array object
	}

}
