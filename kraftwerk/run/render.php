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

// GRAB VARS FROM URL	
//Structure Example /controller/action/id
//print $_GET["controller"] . "<br />"; 	// get controller
//print $_GET["action"] . "<br />"; 		// get action
//print $_GET["id"] . "<br />"; 			// get idd
//print $_GET["query"] . "<br />";			// get query
var_dump($_GET);
		
// LOAD CONTROLLER FIRST
//$GLOBALS['kraftwerk']->loadController($_GET["controller"]);


?>