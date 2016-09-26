<?php

/**~******************************************************************
 * Copyright (c) 2016 Voila! Video Productions
 *********************************************************************
 */

namespace AppVvp;

use AppVvp\General\PgLinkFactory;
use AppVvp\General\PgMsgs;
use AppVvp\Pages\Page;
use AppVvp\Pages\VideoPage;

/**
 * Dynamic CSS for main/public pages - controlled by php variables/properties.
 * THE VALUES FOR THESE ARE SET IN THE 'set_page_attribs()' METHOD OF THE 'Page' CLASS.
 */
?>
<style type="text/css">
@charset "UTF-8";

html *, a:hover, a.linkNormalWeb:hover, a.linkSiteTheme, a.stillFrBtn, a.stillFrBtnSel {
	color: <?php echo cc(App::$page->getMainTxtColr()); ?>;
}
body {
	width: <?php echo cc(App::$page->getBodyWidth()); ?>;
	background-image: url("<?php echo cc(App::$page->getBgImg1()); ?>");
}
.vidLnkTNimg {border: 0.4em solid <?php echo cc(App::$page->getViTnLinkMsOutColor()); ?>;}
.vidLnkTNimg:hover {border-color: <?php echo cc(App::$page->getViTnLinkMsOverColor()); ?>;}
.vidLnkTNsel {border: 0.6em double <?php echo cc(App::$page->getBgColor1()); ?>;}
.vidLnkTNimg, .vidLnkTNsel, .vidLnkTNblank {
	width: <?php  echo VideoPage::VI_LNK_TN_WW; ?>px;
	height: <?php echo VideoPage::VI_LNK_TN_HH; ?>px;
}
.vidLnkTNimgSel {
	width: <?php  echo VideoPage::VI_LNK_TN_WW_SEL; ?>px;
	height: <?php echo VideoPage::VI_LNK_TN_HH_SEL; ?>px;
}

a.linkSiteTheme:hover, a.stillFrBtn:hover, a.stillFrBtnSel 
        {background-color: <?php echo cc(App::$page->getBgColor1()); ?>;}
select#stillFrPagSelLst {background-color: <?php echo cc(App::$page->getBgColor2()); ?>;}
a.stillFrBtn {background-color: <?php echo cc(App::$page->getBgColor3()); ?>;}

div.borderOuter {border-color: <?php echo cc(App::$page->getDivBorderOutrColor()); ?>;}

div.borderInner {border-color: <?php echo cc(App::$page->getDivBorderInnrColor()); ?>;}

table.border01 {border-color: <?php echo cc(App::$page->getTblBrdrColr()); ?>;}

.mnuLnk, a {color: <?php echo cc(App::$page->getMenuLnkColr()); ?>;}

a.linkNormalWeb {color: <?php echo cc(App::$page->getLinkColr()); ?>;}

.alertColor, .reqFldMsg1, .vidLocOrWebMsg, #pagErrMsgTxt, .formFldErrDsp {color: <?php echo Page::ALERTCOLOR1; ?>;}
.vidUpdBtn:hover {color: <?php echo cc(App::$page->getPageMsgErrorColor()); ?>;}

.<?php echo PgMsgs::PGMSG_CSS_CLASS_ERROR; ?> {color: <?php echo cc(App::$page->getPageMsgErrorColor()); ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_INFO; ?> {color: <?php echo cc(App::$page->getPageMsgInfoColor()); ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_SUCCESS; ?> {color: <?php echo cc(App::$page->getPageMsgSuccessColor()); ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_TEST; ?> {color: <?php echo Page::TESTCOLOR1; ?>;}
.<?php echo PgMsgs::PGMSG_CSS_CLASS_ERROR; ?>, .<?php echo PgMsgs::PGMSG_CSS_CLASS_INFO; ?>, 
   .<?php echo PgMsgs::PGMSG_CSS_CLASS_SUCCESS; ?>, .<?php echo PgMsgs::PGMSG_CSS_CLASS_TEST; ?> {
	text-align: center;
}
.formFldStyle {color: <?php echo cc(App::$page->getFormFldColr()); ?>;}
<?php if (is_object(App::$page->getForm1())): ?>
.formFldInput {height: <?php echo cc(App::$page->getForm1()->getRowsHt1()); ?>;}
<?php endif; ?>
<?php if (PgLinkFactory::isCurrentPage(FilNams::PN_VIDSMY) && 
				App::$page->isVideoPlayPage()): ?>
section#videoLinks {margin: 0 auto; text-align: center; width: 847px;}
main#vvpWelcome {margin: 0 auto; text-align: center; width: 700px; margin-top: 1.75em;}
<?php else: ?>
section#videoLinks {float: left; clear: left; width: 350px;}
main#vvpWelcome {float: right; width: 450px; margin-top: 0.75em;}
<?php endif; ?>
</style>
