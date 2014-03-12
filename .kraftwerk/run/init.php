<?php
/*

##########################################################################
	KRAFTWERK ROOT LOADER
	This file loads before all other when the kraftwerk application runs.
		
	developer: Dom Price (info@domprice.com)
	created: 04/19/2010
	last:	 03/12/2014

	(c) 2010-2014 - Dom Price - http://www.domprice.com
	This software is open source and licensed under the standard MIT license.
    Creative Commons license also applies. All developers must be listed in this file.

##########################################################################
*/

// LOAD HEADERS

//#################################################################################

// LOAD KRAFTWERK APPLICATION
include_once(dirname(__FILE__) . '/../config/kwconfig.php');
$kw_config = new KWConfig();

// ERRORS
if ($kw_config->display_errors == true) {
	ini_set('display_errors', '1');
	ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
}

include_once(dirname(__FILE__) . '/core/Kraftwerk.php');
$GLOBALS['kraftwerk'] = new Kraftwerk(); // run kraftwerk

?>