/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * JavaScript for AJAX form-field auditing
 */

window.onload = function() {
	"use strict";

	/**
	 * @param formObj -> document.getElementById(<form ID>);
	 */
	var auditOnSubmit = function(formObj)
	{
		var submit = ajaxCkFfe.ffldsValid();
		if (submit) {
			lockForm(formObj, true); // no input allowed
			if (gJsonForm.uploadPage) {
				gUploadMonitor.init();
			}
		}
		return submit;
	};

	/**
	 * @param {object} formObj -> document.getElementById(<form ID>);
	 * @param {boolean} bool   -> toggles disabled/readOnly to true/false
	 **/
	var lockForm = function(formObj, bool)
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

	// ------------------------------------------------
	//            -------- INIT --------
	// ------------------------------------------------
	var ajaxCkFfe = new AjaxChkFFE();
	// Create the Ajax onchange events for each form element validated
	for (var i = 0; i < gJsonForm.frmFldObjs.length; i++) {
		if (!gJsonForm.frmFldObjs[i].isHidden && 
				gJsonForm.frmFldObjs[i].ffoName !== gJsonForm.formFldNamCsrfToken) {
			document.getElementById(gJsonForm.frmFldObjs[i].ffoName).onchange = 
						function() {ajaxCkFfe.ajaxValidation(this);};
		}
	}
	// ---- Prep event handlers ----
	if (gJsonForm.formCfvMethod !== gJsonForm.cfvNone) {
		var oForm = document.getElementById(gJsonForm.currFormName);
		oForm.onsubmit = function() {
			return auditOnSubmit(oForm);
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



var AjaxChkFFE = function()
{
	"use strict";
	var thisPageUri = gJsonForm.currPageUrl;
//	var thisPageUri = window.location.pathname;
	var validFflds = {};

	this.ajaxValidation = function(elemnt)
	{
//		alert('['+elemnt.name+']');
//		elemnt.disabled = true;  => field gets blanked out
		elemnt.readonly = true;
		document.getElementById(elemnt.name + gJsonForm.ffnErrSufx).innerHTML = '';
		validFflds[elemnt.name] = true;

		var xmlHttp = (window.ActiveXObject) ? new ActiveXObject('Microsoft.XMLHTTP') : 
					new XMLHttpRequest();
		xmlHttp.open('POST', thisPageUri, true);
		xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xmlHttp.send(gJsonForm.cfvAjax + '=&' + gJsonForm.csrfTokenSV + '=' + 
			document.getElementById(gJsonForm.csrfTokenSV).value + '&' + 
			elemnt.name + '=' + elemnt.value);

		xmlHttp.onreadystatechange = function()
		{
			var respErrText;
			switch (xmlHttp.readyState) {
			case 0: break; // 0 = UNSENT - object has been constructed
			case 1: break; // 1 = OPENED - open() method invoked (ready for SEND)
			case 2: break; // 2 = HEADERS_RECEIVED
			case 3: break; // 3 = LOADING - The response entity body is being received
			case 4:        // 4 = DONE
				elemnt.readonly = false;
				respErrText = xmlHttp.responseText;
				// Check for formfield's error msg from the server:
				if (respErrText !== '') {
					document.getElementById(elemnt.id).focus();
					document.getElementById(
							elemnt.name + gJsonForm.ffnErrSufx).innerHTML = '@' + respErrText;
//					document.getElementById(gJsonForm.formBtnSubmit).disabled = true;
					validFflds[elemnt.name] = false;
					if (respErrText.search(gJsonForm.unauthPost) > -1) {
						window.location.replace(gJsonForm.logoutPageUrl);
					}
				}
//				if (ajaxCkFfe.ffldsValid()) {
//					// ENable submit btn
//					document.getElementById(gJsonForm.formBtnSubmit).disabled = false;
//				}
				break;
			}
		};
	};

	this.ffldsValid = function()
	{
		var ffldValid = true;
		for (var key in validFflds) {
			if (!validFflds[key]) {
				ffldValid = false;
				break;
			}
		}
		return ffldValid;
	};
};
