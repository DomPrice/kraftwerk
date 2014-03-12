<?php
/*
	##########################################################################
		KRAFTWERK RENDERER
		This is the shell script that renders the application
		
		developer: Dom Price (info@domprice.com)
		created: 01/14/2014
		last:	 01/14/2014

		(c) 2010-2014 - Dom Price - http://www.domprice.com
		This software is open source and licensed under the standaed MIT license.
    	Creative Commons license also applies. All developers must be listed in this file.

	#################################################################################
*/

// INITIALIZE
include_once("init.php");

// LOAD CONTROLLER FIRST
$GLOBALS['kraftwerk']->loadController("application"); // load application controller first
$GLOBALS['kraftwerk']->loadController($_GET["controller"]);
$GLOBALS['kraftwerk']->loadAction($_GET["action"]);


?>