<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp;

use AppVvp\General\PgMsgs;
use AppVvp\Pages\Page;

/**
 * Dynamic CSS for admin pages - controlled by php variables/properties.
 */
?>
<style type="text/css">
@charset "UTF-8";

.<?php echo PgMsgs::PGMSG_CSS_CLASS_ERROR; ?> {color: <?php echo cc(App::$page->getPageMsgErrorColor()); ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_INFO; ?> {color: <?php echo cc(App::$page->getPageMsgInfoColor()); ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_SUCCESS; ?> {color: <?php echo cc(App::$page->getPageMsgSuccessColor()); ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_TEST; ?> {color: <?php echo Page::TESTCOLOR1; ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_ERROR; ?>, .<?php echo PgMsgs::PGMSG_CSS_CLASS_INFO; ?>, 
  .<?php echo PgMsgs::PGMSG_CSS_CLASS_SUCCESS; ?>, .<?php echo PgMsgs::PGMSG_CSS_CLASS_TEST; ?> {
	text-align: center; font-family: 'Times New Roman', Times, serif;
}
</style>
