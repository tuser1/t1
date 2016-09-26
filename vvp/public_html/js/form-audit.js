/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * JavaScript for client-side formfield validation
 */

// 2014-04-22: JavaScript 'trim' ftn (and others) were not implemented in MSIE 8:
if (typeof String.prototype.trim !== 'function') {
	String.prototype.trim = function() // Implement missing "trim" function
	{
		return this.replace(/^\s+|\s+$/g, '');
	};
}

var gCommon = new function()
{
	"use strict";
	this.g1MB = Math.pow(1024, 2);

	this.bytesToMBs = function(bytes) {
		return (bytes / this.g1MB).toFixed(2);
	};
};


(function() {

"use strict";
var gSubmitErrs, gFocusSet, gRegexSC, gRegexValPw2, gRegexUC, gRegexLC, gRegexNM;


var gUploadFileExists = new function()
{
	this.exists;
	this.exec = function(elemnt, oForm)
	{
		var sendText, respText = 0, eleErrDisp, eleFormBtnSubmit;
		gUploadFileExists.exists = -1;
		eleErrDisp = document.getElementById(elemnt.name + gJsonForm.ffnErrSufx);
		eleFormBtnSubmit = document.getElementById(gJsonForm.formBtnSubmit);
		eleFormBtnSubmit.disabled = true; // DISallow form submit until file check
		var xmlHttp = (window.ActiveXObject) ? new ActiveXObject('Microsoft.XMLHTTP') : 
					new XMLHttpRequest();
		xmlHttp.open('POST', gJsonForm.ulfFileExistsUrl, true);
		xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		sendText = gJsonForm.cfvAjax + '=&' + gJsonForm.csrfTokenSV + '=' + 
				document.getElementById(gJsonForm.csrfTokenSV).value + '&' + 
				gJsonForm.ffnUlVidFile + '=' + elemnt.fnamOnly;
		xmlHttp.send(sendText);
		xmlHttp.onreadystatechange = function ()
		{
			if (xmlHttp.readyState === 4) {
				respText = xmlHttp.responseText;
				if (respText.search(gJsonForm.unauthPost) > -1) {
					window.location.replace(gJsonForm.logoutPageUrl);
				} else if (respText == 0) {
					gUploadFileExists.exists = 0;
					if (oForm !== null) {
						if (auditOnSubmit(oForm)) {
							// ============== SUBMIT FORM ==================
							document.forms[gJsonForm.currFormName].submit();
							// =============================================
						}
					}
				} else if (respText == 1) {
					gUploadFileExists.exists = 1;
					alert('CANNOT UPLOAD - FILE ALREADY EXISTS ON SERVER');
				} else {
					alert('**** INVALID POST -OR- UNABLE TO OBTAIN SERVER FILE INFO ****');
				}
				eleFormBtnSubmit.disabled = false; // Allow form submit
			}
		};
	};

};


window.onload = function() {
	auditInit();
	// ---- Prep event handlers ----
	if (gJsonForm.formCfvMethod !== gJsonForm.cfvNone) {
		var oForm = document.getElementById(gJsonForm.currFormName);
		if (document.getElementById(gJsonForm.ffnUlVidFile) !== null) {
			// FILE UPLOAD - Conditional FORM SUBMIT using javascript
			var btnSubmit = document.getElementById(gJsonForm.formBtnSubmit);
//			btnSubmit.setAttribute('type', 'button'); // No good way to determine if supported
			btnSubmit.onclick = function() {
				// Check if file exists; if NOT, audit form & SUBMIT if no audit errs
				gUploadFileExists.exec(
						document.getElementById(gJsonForm.ffnUlVidFile), oForm);
			};
		} else { // NORMAL FORM SUBMIT using input type="submit"
			oForm.onsubmit = function() {
				return auditOnSubmit(oForm);
			};
		}
	}

	if (document.getElementById(gJsonForm.ffnUlVidFile) !== null) {
		if (!window.File) { // files[0] not supported in older MSIE
			alert('WARNING: Will be unable to determine if upload file size exceeds max ' + 
					'prior to upload');
		}
		document.getElementById(gJsonForm.ffnUlVidFile).onchange = function() {
			// For <input> type="file" / this.value, some browsers supply a full 
			//   and/or fake path, e.g. if filename is "F1.mp4":
			//   FFox: "F1.mp4" -- Chrome: "C:\fakepath\F1.mp4" -- MSIE: "C:\REALPATH\F1.mp4"
			// Create new element property [.fnamOnly] with FULL PATH REMOVED:
			//  [the replace works on both '/' or '\' OS paths]:
			this.fnamOnly = this.value.replace(/^.*[\\\/]/, '');
			gUploadFileExists.exec(this, null);
			var fSizDsp;
			if (window.File) {
				var file = this.files[0];
				fSizDsp = ' - ' + gCommon.bytesToMBs(file.size) + ' MBs / ' + 
						file.size.toLocaleString('en') + ' bytes';
			} else {
				fSizDsp = '';
			}
			document.getElementById(
					gJsonForm.ffnUlVidFile + gJsonForm.ffnErrSufx).innerHTML =
					this.fnamOnly + fSizDsp;
			var fnmOnly = this.fnamOnly.replace(/\.[^/.]+$/, ''); // Remove file extension
			document.getElementById('ul_video_descr1').value = fnmOnly.substr(0, this.size);
			document.getElementById('ul_video_descr2').value = fnmOnly.substr(this.size);
			document.getElementById('ul_video_descr1' + gJsonForm.ffnErrSufx).innerHTML = '';
		};
	}
	if (document.getElementById('btnCancelUpload') !== null) {
		document.getElementById('btnCancelUpload').onclick = function() {
			gUploadMonitor.cancelUpload();
		};
	}
	if (document.getElementById('btnFormClearChgs') !== null) {
		document.getElementById('btnFormClearChgs').onclick = function() {
			clearChgs();
		};
	}
	// -----------------------------
	document.getElementById(gJsonForm.formFocusElementId).focus();
};


function auditInit()
{
	if (document.getElementById('btnCancelUpload') !== null) {
		document.getElementById('btnCancelUpload').hidden = true;
		document.getElementById('btnCancelUpload').disabled = true;
	}
	// REGEXP: 'g' suffix = replace ALL occurences
	gRegexSC = new RegExp('[' + gJsonForm.regexValPw1Sc + ']', 'g');
	// Remove leading/trailing forward-slashes
	gRegexValPw2 = new RegExp(gJsonForm.regexValPw2.substring(
					1, gJsonForm.regexValPw2.length - 1));
	gRegexUC = new RegExp(gJsonForm.regexValPw1Uc.substring(
					1, gJsonForm.regexValPw1Uc.length - 1), 'g');
	gRegexLC = new RegExp(gJsonForm.regexValPw1Lc.substring(
					1, gJsonForm.regexValPw1Lc.length - 1), 'g');
	gRegexNM = new RegExp(gJsonForm.regexValPw1Nm.substring(
					1, gJsonForm.regexValPw1Nm.length - 1), 'g');
	for (var i in gJsonForm.frmFldObjs) {
		// NOTE: New JSON element 'origValue' does not need to be previously declared 
		if (!gJsonForm.frmFldObjs[i].isHidden) {
			if (gJsonForm.compOrigFfVals) {
				gJsonForm.frmFldObjs[i].origValue = 
						document.getElementById(gJsonForm.frmFldObjs[i].ffoName).value.trim();
			} else {
				gJsonForm.frmFldObjs[i].origValue = '';
			}
			if (gJsonForm.frmFldObjs[i].inputFld) {
				gJsonForm.frmFldObjs[i].auditFtnArg = gJsonForm.frmFldObjs[i].auditFtn;
			} else {
				// Do not audit non-input form elements, e.g. <select>
				gJsonForm.frmFldObjs[i].auditFtnArg = "''"; // pass blank arg [pair of quotes]
			}
			if (gJsonForm.frmFldObjs[i].regExVal !== '') {
				// Restore any backslashes that had been detected & replaced
				gJsonForm.frmFldObjs[i].regExValArg = 
					gJsonForm.frmFldObjs[i].regExVal.replace(gJsonForm.regExBackSlashRepl, '\\');
			} else {
				gJsonForm.frmFldObjs[i].regExValArg = "''"; // pass blank arg [pair of quotes]
			}
		} else {
			gJsonForm.frmFldObjs[i].origValue = '';
		}
	}
}


function auditOnSubmit(formObj)
{
//	alert('IN JS FTN "auditOnSubmit"');
	lockForm(formObj, true); // no input allowed
	gSubmitErrs = 0;
	gFocusSet = false;
	var submit;
	for (var i in gJsonForm.frmFldObjs) {
		if (!gJsonForm.frmFldObjs[i].isHidden) {
			// Clear errMsg
			document.getElementById(gJsonForm.frmFldObjs[i].ffoName + 
						gJsonForm.ffnErrSufx).innerHTML = '';
			// VALIDATE the element's value
			// Use eval() to pass auditFtn & regExVal args as literals with no quotes
			validateFFE(document.getElementById(gJsonForm.frmFldObjs[i].ffoName), 
				gJsonForm.frmFldObjs[i].descr, 
				gJsonForm.frmFldObjs[i].required, 
				gJsonForm.frmFldObjs[i].minSiz, gJsonForm.frmFldObjs[i].maxSiz, 
				eval(gJsonForm.frmFldObjs[i].auditFtnArg), 
				eval(gJsonForm.frmFldObjs[i].regExValArg), 
				gJsonForm.frmFldObjs[i].regExAuditErrTxt);
		}
	}
	if (gSubmitErrs > 0) {
		submit = false;
	} else {
		if (formObj.name === 'PassChgForm' || formObj.name === 'NewUserForm' ||
			formObj.name === 'PwResetForm') {
			passChgValidateFlds1(gJsonForm.ffnLoginPw, gJsonForm.ffnNewPw, 
					gJsonForm.ffnNewPwRetyp);
		}
		if (gSubmitErrs > 0) {
			submit = false;
		} else {
			if (gJsonForm.compOrigFfVals) {
				submit = false;
				for (var i in gJsonForm.frmFldObjs) {
					if (!gJsonForm.frmFldObjs[i].isHidden) {
						if (document.getElementById(gJsonForm.frmFldObjs[i].ffoName).value !==
								gJsonForm.frmFldObjs[i].origValue) {
							submit = true;
							break;
						}
					}
				}
				if (!submit) {
					alert('NO changes were made to the original form data.\n\n' + 
							'NO update was done.');
					document.getElementById(gJsonForm.focus1stFfeNnam).focus();
				}
			} else {
				submit = true;
			}
		}
	}
	if (!submit) {
		lockForm(formObj, false); // if errs, unlock form, else NO user input while updating
	} else {
		if (gJsonForm.uploadPage) {
			// Form audits passed - file upload started
			// Use both disabled/hidden to account for browser differences
			document.getElementById('btnCancelUpload').hidden = false;
			document.getElementById('btnCancelUpload').disabled = false;

			document.getElementById(gJsonForm.formBtnSubmit).hidden = true;
			document.getElementById(gJsonForm.formBtnSubmit).disabled = true;
			if (document.getElementById('btnFormClearChgs') !== null) {
				document.getElementById('btnFormClearChgs').hidden = true;
				document.getElementById('btnFormClearChgs').disabled = true;
			}
			if (document.getElementById('btnFormReload') !== null) {
				document.getElementById('btnFormReload').hidden = true;
				document.getElementById('btnFormReload').disabled = true;
			}
			gUploadMonitor.init(); // Start monitoring the upload
		}
	}
	return submit;
}


function passChgValidateFlds1(ffeNamPw, ffeNamPwNew, ffeNamPwRetyp)
{
	var ffePwNew = document.getElementById(ffeNamPwNew);
	if (document.getElementById(ffeNamPw) !== null) {  // if element EXISTS ...
		var ffePw = document.getElementById(ffeNamPw);
		// NEW PW must not be the same as CURRENT PW
		if (ffePwNew.value === ffePw.value) {
			setErrMsg(ffePwNew, 'Password is the same as the CURRENT password.');
		}
	}
	// RE-TYPED new PW must match NEW pw
	var ffePwRetyp = document.getElementById(ffeNamPwRetyp);
	if (ffePwNew.value !== ffePwRetyp.value) {
		setErrMsg(ffePwRetyp, 'Does not match NEW password.');
	}
}

function clearChgs()
{
	var ffoNam;
	for (var i in gJsonForm.frmFldObjs) {
		if (!gJsonForm.frmFldObjs[i].isHidden) {
			ffoNam = gJsonForm.frmFldObjs[i].ffoName;
			document.getElementById(ffoNam).value = gJsonForm.frmFldObjs[i].origValue;
			document.getElementById(ffoNam + gJsonForm.ffnErrSufx).innerHTML = '';
		}
	}
	document.getElementById(gJsonForm.focus1stFfeNnam).focus();
}

function validateFFE(ffe, descr, reqd, minSiz, maxSiz, auditFtn, regExVal, reErr)
{
	ffe.descr = descr;
	var callAuditFtn   = function(ftnNam, arg1) {
		return ftnNam(arg1);
	};
	var callAuditFtnRe = function(ftnNam, arg1, arg2, arg3) {
		return ftnNam(arg1, arg2, arg3);
	};
	var valid = false;
	if (!fieldEmpty(ffe, reqd)) {
		if (validStrLen(ffe, minSiz, maxSiz)) {
			if (auditFtn !== '') {
				if (regExVal === '') {
					valid = callAuditFtn  (auditFtn, ffe);
				} else {
					valid = callAuditFtnRe(auditFtn, ffe, regExVal, reErr);
				}
			} else {
				valid = true;
			}
			if (valid && ffe.type === 'file') {
				if (validFilSiz(ffe, gJsonForm.ulMaxFilSiz)) {
						validFilExt(ffe, ['.mp4', '.wmv', '.webm']);
				}
			}
		}
	}
}

function validFilExt(ffe, fexts) {
	var valid, fnam = ffe.value.toLowerCase();
    valid = (new RegExp('(' + fexts.join('|').replace(/\./g, '\\.') + ')$')).test(fnam);
	if (!valid) {
		setErrMsg(ffe, 'File-extension invalid');
	}
	return valid;
}

function validFilSiz(ffe, max)
{
	if (window.File) {
		var file = ffe.files[0];
		if (file.size > max) {
			// Use toLocaleString('en') to format number with commas
			setErrMsg(ffe, 'Size [' + file.size.toLocaleString('en') +
					'] exceeds max [' + max.toLocaleString('en') + ']');
			return false;
		}
	}
	return true;
}

function cmnValidRegex1(ffe, regExVal, reErr)
{
	var val;
	if (ffe.type === 'file') {
		val = ffe.fnamOnly;
	} else {
		val = ffe.value;
	}
	if (! regExVal.test(val)) {
		setErrMsg(ffe, reErr);
		return false;
	}
	return true;
}

function cmnValidPw1(ffe)
{
	if (!validPw1Chars(ffe.value)) {
		setErrMsg(ffe, 'INVALID (see rules)');
		return false;
	}
	return true;
}

function validStrLen(ffe, minSiz, maxSiz)
{
	var ffeVal;
	if (ffe.type === 'file') {
		ffeVal = ffe.fnamOnly;
	} else {
		ffeVal = ffe.value;
	}
	if  (ffeVal.length < minSiz || 
		 ffeVal.length > maxSiz) {
		if (minSiz !== maxSiz) {
			setErrMsg(ffe, '[' + ffeVal.length + ' chars] must be between ' + 
				minSiz + ' and ' + maxSiz + ' chars in length.');
		} else {
			setErrMsg(ffe, '[' + ffeVal.length + ' chars] must be ' + 
				maxSiz + ' character(s) in length.');
		}
		return false;
	}
	return true;
}

function fieldEmpty(ffe, reqd)
{
	// This is done to avoid "operation is insecure" error [Firebug] on
	//   objects that cannot modify ffe.value, e.g. <input> type 'file'.
	//   So only trim value if it NEEDS to be trimmed:
	var ffeVal = ffe.value.trim();
	if (ffeVal !== ffe.value) {
		ffe.value = ffeVal;
	}
	if (ffeVal === '') {
		//==== If NOT a required field, return 'field empty = TRUE'
		//====   but do NOT register as an error
		if (reqd) {
			setErrMsg(ffe, 'cannot be BLANK.');
		}
		return true;
	}
	return false;
}

function setErrMsg(ffe, msg)
{
	gSubmitErrs++;
	document.getElementById(ffe.name + gJsonForm.ffnErrSufx).innerHTML = 
		'{' + ffe.descr + '}: ' + msg;
	if (!gFocusSet) {
		document.getElementById(ffe.name).focus();
		gFocusSet = true;
	}
}

function validPw1Chars(val)
{
	var len1 = val.length;
	if (! gRegexValPw2.test(val)) return false;
	if (len1 - val.replace(gRegexUC, '').length < gJsonForm.regexValPw1UcCnt) return false;
	if (len1 - val.replace(gRegexLC, '').length < gJsonForm.regexValPw1LcCnt) return false;
	if (len1 - val.replace(gRegexNM, '').length < gJsonForm.regexValPw1NmCnt) return false;
	if (len1 - val.replace(gRegexSC, '').length < gJsonForm.regexValPw1ScCnt) return false;
	return true;
}


/**
 * @param {object} formObj -> document.getElementById(<form ID>);
 * @param {boolean} bool   -> toggles disabled/readOnly to true/false
 **/
function lockForm(formObj, bool)
{
	var ele = formObj.elements;
	for (var i = 0, len = ele.length; i < len; ++i) {
		// Buttons set to READONLY can still be clicked: must be DISABLED instead
		// All other types must be READONLY or values will not be posted in submit
		switch (ele[i].type) {
		case 'submit':
			ele[i].disabled = bool;
			break;
		case 'button':
			ele[i].disabled = bool;
			break;
		default:
			ele[i].readOnly = bool;
			break;
		}
	}
};


})();
