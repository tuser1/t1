/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * JavaScript for stillframes page
 */

window.onload = function() {
	"use strict";
	var i, oStillFrPagSelLst = document.getElementById('stillFrPagSelLst');
	// ---- Load page selections list ----
	for (i = 1; i <= gJsonStillframes.totlPagCnt; i++) {
		oStillFrPagSelLst.options[oStillFrPagSelLst.length] = new Option("Pg " + i + 
				" of " + gJsonStillframes.totlPagCnt);
		oStillFrPagSelLst.options[oStillFrPagSelLst.length - 1].value = i;
	}
	oStillFrPagSelLst.selectedIndex = gJsonStillframes.currentPagNum - 1;
	// ---- Prep event handlers ----
	oStillFrPagSelLst.onchange = function() {
		window.location.replace(gJsonStillframes.currPageUrl + gJsonStillframes.QsDlm1 + 
			gJsonStillframes.PagOptNumParmName + '=' + 
			oStillFrPagSelLst.options[oStillFrPagSelLst.selectedIndex].value);
	};
	// -----------------------------
	document.getElementById(gJsonStillframes.btnNextPrevFocus).focus();
};
