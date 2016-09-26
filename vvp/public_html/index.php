<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp;

use AppVvp\General\SysMain;

/**
 * Main / document root [index.php] script - redirects to default start page
 */
require '../AppVvp/bootstrap.php';

App::redirect(FilNams::getPgUrl(FilNams::getPagNamStartCalc()), SysMain::AUTODIRECT_MAIN);
