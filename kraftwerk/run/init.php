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

// CREATE CONFIG OBJECT<br />


// LOAD KRAFTWERK APPLICATION
include_once(dirname(__FILE__) . '/../config/kwconfig.php');
$kw_config = new KWConfig();

if(
	$kw_config->error_reporting != 0 && 
	$kw_config->error_reporting != "" && 
	strtolower($kw_config->error_reporting) != "off" && 
	$kw_config->error_reporting != false
) {
	ini_set('error_reporting', $kw_config->error_reporting);
}
include_once(dirname(__FILE__) . '/core/Kraftwerk.php');
$GLOBALS['kraftwerk'] = new Kraftwerk();

?>