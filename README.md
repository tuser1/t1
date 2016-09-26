# -----------------------------------------------
# THE BASIC LOGIC FLOW FOR THE PUBLIC URLs:
# -----------------------------------------------

- URL rewrite should be enabled for this site - SSL is required

- Each URL is passed to a SINGLE MAIN ENTRY-POINT: [public_html]/entry-pt/index-pages.php

- In [index-pages.php] => [bootstrap.php] is executed:
	- Misc system initialization
	- URL is REDIRECTED IF INVALID
	- Authentication is checked and page redirected if necessary [e.g. not authorized or logged-in]
	- App::renderCurrentPage({PHP_SELF}) is called to create the page object:
		- App::createPage(new {pageCls}(''))
			- creates/sets the page object App::$page
				- parent object executes [Validator::validatePassedGetKeys1] to 
					  check for/validate any parms in $_GET
				- if page has a form, creates/sets the page's form object $PageObj->form1:
						$PageObj->setForm1(new {formCls}($this))
			- executes $PageObj->runPage() method to load the main page template: {page-layout}.phtml
				- page-template executes $PageObj->getPageContent() to include page's 
						content template; IN MOST CASES this is {url-name}.phtml

-----------------------------------------------

Class CONSTANT-name prefixes:

CFV_* = [C]lient-side [F]ormfield [V]alidation method
DN_*  = dir/folder name
FBN_* = Formbutton name/key
FF_*  = Formfield attrib
FFN_* = Formfield name/key
FN_*  = filename
PI_*  = PHP ini
PN_*  = pagename
RE_*  = regexpression
SI_*  = still-image
SV_*  = $_SESSION index name
VI_*  = video

NOTE: for simplicity, URL/GET parms and their
    corresponding SESSIONvars USE THE SAME NAME

-----------------------------------------------
