<?php
class KWConfig {
	
	// DATABASE CONFIG
	var $site_database_server		= '';
	var $site_database_username		= '';
	var $site_database_password		= '';
	var $site_database_schema		= '';
	
	// CORE VARS
	var $hosted_dir					= ""; // set this if you're hosting the kraftwerk app in a subdirectory
	var $default_language 			= "en-us";

	// IMAGE SETTINGS
	var $image_save_dir				= "/files";
	var $image_display_path			= "/images";
	var $image_max_size				= 2097152; // 2 Megabytes
	var $image_accept_files			= array("image/jpeg","image/jpg");
	var $image_default_jpeg_quality	= 99; // in pixels
	var $image_max_width			= 500; // in pixels
	var $image_max_height			= 500; // in pixels
	
	// ERROR HANDLING
	var $report_errors			= "E_ALL";
}
?>
