/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * JavaScript/Ajax for monitoring video file upload
 */

var gUploadMonitor = new function()
{
	"use strict";
	var	gEleDisplay, gTimeOutMs = 1000, gExecCnt = 0;

	this.init = function()
	{
		gEleDisplay = document.getElementById(gJsonForm.ffnUlVidFile + gJsonForm.ffnErrSufx);
		gEleDisplay.innerHTML = 'Starting UPLOAD ...';
		setTimeout('gUploadMonitor.sendMonitorRequest()', gTimeOutMs);
	};

	this.sendMonitorRequest = function() // must be public to be used by setTimeout
	{
		var xmlHttp = (window.ActiveXObject) ? new ActiveXObject('Microsoft.XMLHTTP') : 
						new XMLHttpRequest();
		xmlHttp.open('GET', gJsonForm.ulfProgressUrl);
		xmlHttp.onreadystatechange = function()
		{
			if (xmlHttp.readyState === 4) {
				gExecCnt++;
				var respText = xmlHttp.responseText;
				if (respText === '') {
					// PHP $_SESSION var for upload progress was NOT set/found on server
					gEleDisplay.innerHTML = '[' + gExecCnt + '] PROGRESS INFO not available - ' + 
							'WILL NOT AFFECT UPLOAD';
					setTimeout('gUploadMonitor.sendMonitorRequest()', gTimeOutMs);
				} else {
					// PHP $_SESSION var for upload progress WAS FOUND on server
					var jsonUlProgess = jsonParse(respText);
					var pctComplete = Math.ceil( // ceil converts to integer
							jsonUlProgess.bytes_processed / jsonUlProgess.content_length * 100);
					// Display bytes processed
					gEleDisplay.innerHTML = 'UPLOAD completion: ' + pctComplete + '% ' + 
							'( ' + gCommon.bytesToMBs(jsonUlProgess.bytes_processed) + ' MBs / ' + 
							gCommon.bytesToMBs(jsonUlProgess.content_length) + ' MBs )';
					if (pctComplete < 100) {
						setTimeout('gUploadMonitor.sendMonitorRequest()', gTimeOutMs);
					}
				}
			}
		};
		xmlHttp.send(null); // mandatory
	};

	this.cancelUpload = function()
	{
		window.location.replace(gJsonForm.ulfCancelUrl);
	};

};
