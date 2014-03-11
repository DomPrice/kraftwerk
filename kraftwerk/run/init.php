<?php
/*

##########################################################################
	KRAFTWERK ROOT LOADER
	This file loads before all other when the kraftwerk application runs.
		
	developer: Dom Price (info@domprice.com)
	created: 04/19/2010
	last:	 07/23/2013

	(c) 2010-2013 - Dom Price - http://www.domprice.com
	This software is open source and licensed under the standaed MIT license.
    Creative Commons license also applies. All developers must be listed in this file.

##########################################################################
*/

// LOAD HEADERS

//#################################################################################

// LOAD KRAFTWERK APPLICATION
include_once(dirname(__FILE__) . '/../config/kwconfig.php');
$kw_config = new KWConfig();

// ERRORS
ini_set('display_errors', $kw_config->error_reporting);
ini_set('error_reporting', $kw_config->report_errors);

include_once(dirname(__FILE__) . '/core/Kraftwerk.php');
$GLOBALS['kraftwerk'] = new Kraftwerk(); // run kraftwerk

?>