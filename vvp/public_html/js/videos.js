/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * JavaScript for videos page
 */

(function() {
	"use strict";
	// This code must self-execute upon scriptfile load BEFORE window.onload event triggers
	// The loading of the current/selected video causes a visible delay
	for (var i in gJsonVideo.vidLnkCanvasObjs) {
		var vidCanvas = document.getElementById(gJsonVideo.vidLnkCanvasObjs[i].vidTnId);
		var x = vidCanvas.width / 2;
		var y = vidCanvas.height / 2;
		var ctx = vidCanvas.getContext("2d");

//		ctx.font = 'bold 1.1em Times New Roman';
		ctx.font = '0.95em Times New Roman'; // '1.0em Times New Roman';
		ctx.fillStyle = gJsonVideo.textColor;
		ctx.textAlign = 'center';
		ctx.fillText(gJsonVideo.vidLnkCanvasObjs[i].altText, x, y);
		ctx.fillText(gJsonVideo.vidLnkCanvasObjs[i].altTxt2, x, y + 20);
	}

	if (gJsonVideo.isMyVidPlayPage) {
		document.getElementById(gJsonVideo.ffnVidUpdTbl).hidden = gJsonVideo.hideVidUpdTbl;
		document.getElementById(gJsonVideo.ffnCancelUpdVid).hidden = true;
	}
})();


window.onload = function () {
	"use strict";
	var oForm, delVidLabel, btnClicked;
	// ---- Prep event handlers ----
	oForm = document.getElementById(gJsonVideo.currFormName);
	if (oForm !== null) {
//		document.getElementById(gJsonVideo.ffnVidUpdTbl).hidden = gJsonVideo.hideVidUpdTbl;
//		document.getElementById(gJsonVideo.ffnCancelUpdVid).hidden = true;

		document.getElementById(gJsonVideo.ffnUpdSelVid).onclick = function () {
			btnClicked = 0;
		};

		document.getElementById(gJsonVideo.ffnCancelUpdVid).onclick = function () {
			btnClicked = -1;
		};

		document.getElementById(gJsonVideo.ffnDelSelVid).onclick = function () {
			delVidLabel = 'Delete SELECTED video';
			btnClicked = 1;
		};

		document.getElementById(gJsonVideo.ffnDelAllVid).onclick = function () {
			delVidLabel = '!!  D-E-L-E-T-E   A-L-L   V-I-D-E-O-S  !!';
			btnClicked = 2;
		};

		oForm.onsubmit = function () {
			return auditOnSubmit();
		};
	}

	function auditOnSubmit() {
		var submit = true;
		if (btnClicked > 0) {
			submit = (confirm(
					'*****************************************************\n' +
					'*****************************************************\n' +
					'*****************************************************\n\n' +
					delVidLabel + '\n\n' +
					'****************** W A R N I N G ********************\n\n' +
					'This will permanently remove your video(s) from the server!\n\n' +
					'ARE YOU SURE ? (click OK to continue)\n\n' +
					'*****************************************************\n' +
					'*****************************************************\n' +
					'*****************************************************'));
			if (submit && btnClicked === 2) {
				submit = (confirm('RE-CONFIRM DELETE OF =ALL= VIDEOS'));
			}
		} else {
			if (btnClicked === -1) {
				document.getElementById(gJsonVideo.ffnVidUpdTbl).hidden = true;
				document.getElementById(gJsonVideo.ffnCancelUpdVid).hidden = true;
				submit = false;
			} else {
				if (document.getElementById(gJsonVideo.ffnVidUpdTbl).hidden === true) {
					document.getElementById(gJsonVideo.ffnVidUpdTbl).hidden = false;
					document.getElementById(gJsonVideo.ffnCancelUpdVid).hidden = false;
					submit = false;
				}
			}
		}
		return submit;
	}
};
