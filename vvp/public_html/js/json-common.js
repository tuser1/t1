/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 * Common javascript routines for json
 */


function jsonParse(jsonCode) {
	"use strict";
	try {
		var obj = JSON.parse(jsonCode);
	}
	catch(e) {
		console.log('*E* JavaScript ERROR in cmd [JSON.parse]\n\n' + 
					'JSON error message:\n' + e + '\n\n' + 
					'JSON text:\n' + jsonCode);
	}
	return obj;
}
