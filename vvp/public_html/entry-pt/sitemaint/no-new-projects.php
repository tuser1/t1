<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

/**
 *  Public page - valid URL: "Not accepting new projcets at this time" message
 */
?>
<!doctype html>

<html lang="en">
<head>
<meta charset="utf-8">
<title>Currently not accepting new work</title>

<style type="text/css">
html * 
{
/*	====== GLOBAL DEFINITIONS ====== */
	font-family: 'Times New Roman', Times, serif;
	color: #670067;
/*	================================ */
}
body {
	/* -------------------------------------------------------------
	NOTE: IF USING [table] TAGs instead of [div]:
		- global 'font-size' def must be placed here
		- !! DO NOT place global 'font-size' def in CSS selector 'html *' !!
	-------------
  	GLOBAL def: controls relative size of ALL object attribs
        */
	font-size: 95%;
	/* ------------------------------------------------------------- */
	background-color: #ffeeff;
}
h1 {
	font-size: 1.5em;
}
a, a.linkNormalWeb, .p1, .p2 {
	font-weight: bold;
}
.p1 {
	font-size: 1.3em;
}
.p3 {
	font-size: 0.9em;
}
a {
	color: #aa88aa; 
}
a.linkNormalWeb { 	/* dark blue */
	color: #0000ff;
}
/* ----------------------------------------------------- */
div.borderInner, div.borderOuter {
	margin:0 auto;  /* 'auto' centers it */
	border-style: solid;
    border-width: 1.0em; /* 0.36em; */
}
div.borderOuter {
	margin-top: 4.0em;
	height: auto; 
	border-color: #eeddee #a087a0 #eeddee #a087a0;
	width: 60.0%;
}
div.borderInner {
	border-color: #a087a0 #eeddee #a087a0 #eeddee;
	text-align: center;
	padding: 2.0em;
}
/* ----------------------------------------------------- */
</style>

</head>
<body>

<div class="borderOuter">
  <div class="borderInner">
	<h1>Thank you for your interest in Voila! Video Productions</h1>
	<p class="p1">
		Currently we are not accepting any new work or projects ...
	</p>
	<p class="p2">
		email: &nbsp;
		<a class="linkNormalWeb" href="mailto:mail@VoilaVideo.com">mail@VoilaVideo.com</a>
	</p>
	<p class="p2">
		phone: &nbsp;310.305.2406
	</p>
	<p class="p2">
		Contact: Kris
	</p>
	<p class="p2">
		<a class="linkNormalWeb" 
		href="http://www.voilavideo.com/">www.voilavideo.com</a>
	</p>
	<p class="p3">
		All information provided is strictly confidential.&nbsp;&nbsp;It will not be 
		sold or given to anyone.
	</p>
	<p class="p3">
	Copyright &copy; 2006-2016 Voila! Video Productions. All rights reserved.
	</p>
  </div>
</div>

</body>
</html>
