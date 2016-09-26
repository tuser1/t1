<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp\Forms;

use AppVvp\App;
use AppVvp\FilNams;
use AppVvp\FormFields\InputFormField;
use AppVvp\General\AuthUser;
use AppVvp\General\CsrfToken;
use AppVvp\General\ErrHandler;
use AppVvp\General\PgLinkFactory;
use AppVvp\General\PgMsgs;
use AppVvp\General\SysMain;
use AppVvp\Pages\Page;
use AppVvp\Validators\Validator;

/**
 * Parent class for all FORM objects
 */
abstract class Form extends App
{
	const SV_NEWUSER_RECORD = 'newuser_record';

	const FFN_LOGIN_USRNAM = 'login_username';
	const FFN_LOGIN_PW = 'login_pw';
	const FFN_NEW_PW = 'new_pw';
	const FFN_NEW_PW_RETYP = 'new_pw_retyped';
	const FFN_PW_RESET_PIN = 'pw_reset_pin';
	const FFN_TEXTMSG_ADDR = 'textmsg_addr';

	const FFN_FIRSTNAME1 = 'first_name';
	const FFN_LASTNAME1 = 'last_name';
	const FFN_INITIAL1 = 'middle_initial';
	const FFN_TITLE1 = 'title';
	const FFN_ADDR1 = 'address';
	const FFN_CITY1 = 'city';
	const FFN_STATECODE1 = 'state_code';
	const FFN_ZIPCODE1 = 'zip_code';
	const FFN_PHONE1 = 'phone';

//	const FFN_UPLOAD_VIDEO_MAXSIZ_F1 = 'MAX_FILE_SIZE';
	const FFN_UPLOAD_VIDEO_F1 = 'ul_video_file1';
	const FFN_UPLOAD_VIDEO_DESCR1 = 'ul_video_descr1';
	const FFN_UPLOAD_VIDEO_DESCR2 = 'ul_video_descr2';
	const FFN_UPLOAD_VIDEO_ASPECT = 'ul_video_aspect';

	const FFN_VID_UPD_TBL = 'vid_upd_tbl';
	const FFN_UPD_SEL_VID = 'upd_sel_vid';
	const FFN_CANCEL_UPD_VID = 'cancel_upd_vid';
	const FFN_DEL_SEL_VID = 'del_sel_vid';
	const FFN_DEL_ALL_VID = 'del_all_vid';

	const FFN_ERR_SUFX = '_ERRDISP';

	const FBN_SUBMIT = 'btnFormSubmit';

	private $requiredFldMsg = array(
		false => '&nbsp;',
		true =>  '<span class="reqFldMsg1">&nbsp;*</span>',
	);

	private $ffoList = array();

	private $jsFocusElementId = '';
	private $jsFocus1stElement = '';
	private $errCnt1 = 0;
	private $rowsHt1 = '1.5em';

	private $parentPage;
	private $formName;



	protected function __construct($parentPage, $formName = '')
	{
		if (!empty($formName)) {
			$this->formName = $formName;
		} else {
			$this->formName = FilNams::removeNs(get_class($this));
		}
		CsrfToken::init($this->formName);
		if (Validator::getServerRequestMethod() === 'POST') {
			if (!AuthUser::requestFromSameDomain()) {
				$this->formSubmitError('E013', 
					'INVALID POST - req from diff domain / ' . 
					'REFERER HOST: [' . SysMain::getRefererHost() . '] / ' . 
					'HTTP_HOST: [' . $_SERVER['HTTP_HOST'] . ']');
			} elseif (CsrfToken::tokenInvalid()) {
				PgMsgs::set(CsrfToken::getTokenErrKey(), 
					'INVALID SUBMIT -OR- ' . 
					'FORM WAS ALREADY OPENED IN SAME BROWSER. ' . 
					'PLEASE RE-ENTER ...');
				if (ini_get('log_errors')) {
					error_log('[' . CsrfToken::getTokenErrKey() . '] ' . 
						'Form: ' . $this->formName . ' - sessID: ' . session_id() . ' - ' . 
							CsrfToken::getTokenErrMsg());
				}
				App::redirect(SAFE_RU_CURR_PAGE_URL);
			} elseif (CsrfToken::tokenExpired()) {
				$this->formSubmitError(CsrfToken::getTokenErrKey(), CsrfToken::getTokenErrMsg());
			}
		}
		$this->setParentPage($parentPage);
		if (Validator::getServerRequestMethod() !== 'POST') {
			// Create a NEW token with each FIRST load of formpage
			// Creating one for each REQUEST causing problems w/ browser RESENDs
			CsrfToken::createToken();
		}
		FfoFactory::addFfoHiddenCsrfToken1(new InputFormField($this, CsrfToken::getSvToken()));

		$this->addFormFieldObjs();
	}


	abstract protected function addFormFieldObjs();


	public function formSubmitError($postErrKey, $errMsg)
	{
		PgMsgs::set($postErrKey, 'FORM SUBMIT WAS INVALID.');
		if (!isset($_POST[Validator::CFV_AJAX])) {
			$errMsg = '[' . $postErrKey . '] ' . $errMsg;
			trigger_error($errMsg, E_USER_ERROR);
		} else {
			//-------------- AJAX post ------------------------
			$errMsg = '[' . $postErrKey . ']{AJAX} ' . $errMsg;
			//send form-submit error to AJAX/XMLHttpRequest:
			echo Validator::UNAUTH_POST_ID;
			/* ErrHandler::setShowError(0) prior to trigger_error:
			 *  1) Write error to logfile
			 *  2) destroy/regen SESSION [save pageMsgs]
			 *  3) DB cleanup
			 *  4) NO echo -or- header/exit to show error
			 */
			ErrHandler::setShowError(0);
			trigger_error($errMsg, E_USER_ERROR);
			// AJAX/XMLHttpRequest send complete - EXIT SCRIPT
			exit();
		}
	}

	//-------------------------------------------

	public function renderHiddenFfos1($indent)
	{
		echo "\n";
		foreach ($this->getFfoList() as $ffo) {
			if ($ffo->isHidden()) {
				$ffo->renderHiddenFfo1($indent);
			}
		}
		unset($ffo); // per PHP doc
	}

	public function renderFfos1($indent)
	{
		echo "\n";
		foreach ($this->getFfoList() as $ffo) {
			if (!$ffo->isHidden()) {
				$ffo->renderFfoChkErr($indent);
				$ffo->renderFfo1($indent);
			}
		}
		unset($ffo); // per PHP doc
	}

	public function renderPwRules1($indent)
	{
		echo "\n";
		echo $indent, '<p class="txtNotice2"><strong>', 
						'Password rules:</strong><br />', "\n";
		echo $indent, '    ', Validator::getFfPwMinlen(), ' to ', Validator::getFfPwMaxlen(), 
						' characters in length<br />', "\n";
		echo $indent, '    Have at least: ', Validator::getFfPwUcCnt(), ' UPPERcase / ', 
						Validator::getFfPwLcCnt(), ' lowercase / ', Validator::getFfPwNmCnt(), 
						' numbers<br />', "\n";
		echo $indent, '    Have at least ', Validator::getFfPwScCnt(), ' of any of these ', 
						'special characters:&nbsp;&nbsp;', Validator::RE_VALID_PW_SC_SET, 
						' <br />', "\n";
		echo $indent, '    NO SPACES or OTHER special characters allowed<br />', "\n";
		echo $indent, '</p>', "\n";
	}

	public function renderJson()
	{
		if (!Validator::isCfvMethod(Validator::CFV_NONE)) {
			$this->renderJsonFormCommon();
		} else {
			$this->renderJsonFormNoCfv();
		}
	}

	public function renderJsonFormCommon()
	{
		$frmFldObjsAry = array();
		foreach ($this->getFfoList() as $ffo) {
			$frmFldObjsAry[] = array(
				'ffoName' => $ffo->getFldNam(),
				'descr' => $ffo->getDescr(),
				'isHidden' => $ffo->isHidden(), // bool
				'required' => $ffo->getRequired(), // bool
				'inputFld' => $ffo->isInputField(), // bool
				'auditFtn' => $ffo->getAuditFtn(),
				'auditFtnArg' => '', // init to spaces
				 // Repl any "\"s w/ a unique non-regEx string:
				'regExVal' => str_replace('\\', Validator::RE_BACKSLASH_REPL, $ffo->getReAuditVal()),
				'regExValArg' => '', // init to spaces
				'regExAuditErrTxt' => $ffo->getReAuditErrTxt(),
				'minSiz' => $ffo->getMinSiz(), // number
				'maxSiz' => $ffo->getMaxSiz(), // number
			);
		}
		$ary = array(
			'frmFldObjs' => $frmFldObjsAry,

			'currFormName' => $this->formName,
			'currPageUrl' => SAFE_RU_CURR_PAGE_URL,
			'myVideosUrl' => FilNams::getPgUrl(FilNams::PN_VIDSMY),
			'formFocusElementId' => $this->getJsFocusElementId(),
			'regExBackSlashRepl' => Validator::RE_BACKSLASH_REPL,
			'formCfvMethod' => Validator::getCfvMethod(),
			'cfvNone' => Validator::CFV_NONE,
			'uploadPage' => (PgLinkFactory::isCurrentPage(FilNams::PN_UPLOAD)), // bool
			'ulMaxFilSiz' => Validator::getPhpUlMaxFilSizBytes(), // number

			// All auditing
			'ffnErrSufx' => self::FFN_ERR_SUFX,

			// JS auditing
			'focus1stFfeNnam' => $this->getJsFocus1stElement(),
			'ffnLoginPw' => self::FFN_LOGIN_PW,
			'ffnNewPw' => self::FFN_NEW_PW,
			'ffnNewPwRetyp' => self::FFN_NEW_PW_RETYP,
			'ffnUlVidFile' => self::FFN_UPLOAD_VIDEO_F1,
			'compOrigFfVals' => Validator::jsCompOrigFfVals(), // bool

			// Regex(s): store as string / count-max ['CNT'] store as number [NO quotes]
			'regexValPw2' => Validator::getReValidPw2(),
			'regexValPw1Uc' => Validator::RE_VALID_PW1_UC,
			'regexValPw1UcCnt' => Validator::getFfPwUcCnt(), // number
			'regexValPw1Lc' => Validator::RE_VALID_PW1_LC,
			'regexValPw1LcCnt' => Validator::getFfPwLcCnt(), // number
			'regexValPw1Nm' => Validator::RE_VALID_PW1_NM,
			'regexValPw1NmCnt' => Validator::getFfPwNmCnt(), // number
			'regexValPw1Sc' => Validator::RE_VALID_PW_SC_SET,
			'regexValPw1ScCnt' => Validator::getFfPwScCnt(), // number

			// AJAX auditing
			'formFldNamCsrfToken' => CsrfToken::getSvToken(),
			'csrfTokenSV' => CsrfToken::getSvToken(),
			'formBtnSubmit' => self::FBN_SUBMIT,
			'cfvAjax' => Validator::CFV_AJAX,
			'unauthPost' => Validator::UNAUTH_POST_ID,
			'ulfFileExistsUrl' => FilNams::getPgUrl(FilNams::PN_ULF_EXISTS),
			'ulfProgressUrl' => FilNams::getPgUrl(FilNams::PN_ULF_PROGRS),
			'ulfCancelUrl' => FilNams::getPgUrl(FilNams::PN_ULF_CANCEL),
			'logoutPageUrl' => FilNams::getPgUrl(FilNams::PN_LOGOUT)
		);
		echo Page::INDENT_1, 'var gJsonForm = jsonParse(\'', jj($ary), '\');', "\n";
	}


	public function renderJsonFormNoCfv()
	{
		$ary = array(
			'currFormName' => $this->formName,
			'formFocusElementId' => $this->getJsFocusElementId(),
			'formCfvMethod' => Validator::getCfvMethod(),
			'cfvNone' => Validator::CFV_NONE
		);
		echo Page::INDENT_1, 'var gJsonForm = jsonParse(\'', jj($ary), '\');', "\n";
	}


	public function addErrCnt1($val = 1)
	{
		$this->errCnt1+= $val;
	}


	// GETTERS / SETTERS

	public function getFormName()
	{
		return $this->formName;
	}

	public function getJsFocusElementId()
	{
		return $this->jsFocusElementId;
	}

	public function setJsFocusElementId($fldNam)
	{
  		// If already set then ignore as it has already been set to 
		//  the 1st fieldName containing an error
		if (empty($this->jsFocusElementId)) {
			$this->jsFocusElementId = $fldNam;
		}
	}

	public function getParentPage()
	{
		return $this->parentPage;
	}

	public function setParentPage($obj)
	{
		$this->parentPage = $obj;
	}

	public function getErrCnt1()
	{
		return $this->errCnt1;
	}

	public function setErrCnt1($val)
	{
		$this->errCnt1 = $val;
	}

	public function getJsFocus1stElement()
	{
		return $this->jsFocus1stElement;
	}

	public function setJsFocus1stElement($val)
	{
		$this->jsFocus1stElement = $val;
	}

	public function getRowsHt1()
	{
		return $this->rowsHt1;
	}

	public function setRowsHt1($val)
	{
		$this->rowsHt1 = $val;
	}

	public function getFfoList()
	{
		return $this->ffoList;
	}

	public function setFfoList($fldNam, $ffo)
	{
		$this->ffoList[$fldNam] = $ffo;
	}

	public function getFfo($fldNam)
	{
		return $this->ffoList[$fldNam];
	}

	public function getRequiredFldMsg($bool)
	{
		return $this->requiredFldMsg[$bool];
	}
}
