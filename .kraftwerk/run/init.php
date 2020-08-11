<?php
/*

##########################################################################
	KRAFTWERK ROOT LOADER
	This file loads before all other when the kraftwerk application runs.

	developer: Dom Price (info@domprice.com)
	created: 04/19/2010
	last:	 04/05/2014

	(c) 2010-2014 - Dom Price - http://www.domprice.com
	This software is open source and licensed under the standard MIT license.
    Creative Commons license also applies. All developers must be listed in this file.

##########################################################################
*/

// LOAD HEADERS

//#################################################################################

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(E_ERROR | E_PARSE);

// LOAD CONSTANTS
include_once(dirname(__FILE__) . '/dep/constants.php');

// LOAD KRAFTWERK APPLICATION
include_once(dirname(__FILE__) . '/kernel/KraftwerkEnv.php');
include_once(dirname(__FILE__) . '/kernel/KraftwerkConfig.php');
include_once(dirname(__FILE__) . '/kernel/KraftwerkRoutes.php');
$GLOBALS['kw_env'] = new KraftwerkEnv();
$GLOBALS['kw_config'] = new KraftwerkConfig();
$GLOBALS['kw_routes'] = new KraftwerkRoutes();

// ERRORS
if ($kw_config->display_errors == true) {
	ini_set('display_errors', '1');
	ini_set('error_reporting', E_ERROR | E_WARNING | E_PARSE);
}

include_once(dirname(__FILE__) . '/kernel/Kraftwerk.php');
$GLOBALS['kraftwerk'] = new Kraftwerk(); // run kraftwerk kernal

// convert env to struct, since we don't need it for special purposes anymore
$GLOBALS['KW_ENV'] = $kw_env->to_struct();
unset($kw_env); // destroy env object;

// generate log file
$GLOBALS['kraftwerk']->logger->generate($GLOBALS['kraftwerk']->get_log_dir() . "/kraftwerk.log");

?>
