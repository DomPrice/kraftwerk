<?php
/* 

###################################################################
	KRAFTWERK PARENT CLASS 
	
	This is the main control class for the kraftwerk application

###################################################################
*/
class Kraftwerk {

	// CONFIG STRUCT
	var $CONSTANTS 				= array();	
	
	// COMPOMENT DIRECTORIES
	// These will be appended to by the core if the hosting directory is different than "/" as set in config/config.php
	var $LIB_DIR				= "/kraftwerk/lib";
	var $CONFIG_GLOBAL_DIR		= "/kraftwerk/config";
	var $CONTROLLERS_DIR		= "/kraftwerk/application/controllers";
	var $MODELS_DIR				= "/kraftwerk/application/models";
	var $VIEWS_DIR				= "/kraftwerk/application/views";
	var $COGS_DIR				= "/kraftwerk/cogs";
	
	// OTHER STRUCTS
	var $CORE_LIB_LOADED 		= array();
	var $CONFIG_LOADED 			= array();
	var $COGS_LOADED 			= array();
	var $MODELS_LOADED 			= array();
	var $CONTROLLERS_LOADED 	= array();
	
	var $CONFIG 				= "";
	
	// PUBLIC VARIABLES AND OBJECTS
	public $logger				= "";
	
	/* 
		CONSTRUTOR
	*/
	public function __construct() {
		$this->loadComponents();
		$this->loadLogger();
		$this->loadExceptionHandler();
	}
	
	/* 
		LOAD CONFIG FILES
		Loads the global config files
		VOID
	*/
	public function loadComponents() {
		global $kw_config;
		$kw_config->CORE_LIB_LOADED 		= $this->loadComponentDirectory($_SERVER['DOCUMENT_ROOT'] . $kw_config->hosted_dir . $this->LIB_DIR);
		$kw_config->COGS_LOADED 			= $this->loadComponentDirectory($_SERVER['DOCUMENT_ROOT'] . $kw_config->hosted_dir . $this->COGS_DIR);
		$kw_config->MODELS_LOADED 			= $this->loadComponentDirectory($_SERVER['DOCUMENT_ROOT'] . $kw_config->hosted_dir . $kw_config->MODELS_DIR);
		//$kw_config->CONTROLLERS_LOADED 		= $this->loadComponentDirectory($_SERVER['DOCUMENT_ROOT'] . $kw_config->CONTROLLERS_DIR);
	}
	
	/* LOAD CLASSES */
	public function loadComponentDirectory($comp_dir) {
		$comps = $this->compsToArray($comp_dir);
		for($i=0; $i<count($comps); $i++) {
			include_once($comps[$i]);
		}
		return $comps;
	}

	/* RECURSIVELY LOAD CLASS DIRECTORIES */
	public function compsToArray($directory) {
		$extension = "php";
		$comps = array();
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if (is_dir($directory . "/" . $file)) {
						$comps = array_merge($comps, $this->compsToArray($directory . "/" . $file)); 
					} else { 
						if(!$extension || (ereg("." . $extension, $file))) {
							$comps[] = $directory . "/" . $file;
						}
					}
				}
			}
			closedir($handle);
		}
		return $comps;
	}
	
	/* INITLIALIZE LOGGER */
	public function loadLogger() {
		$this->logger = new KraftwerkLogger();
	}
	
	/* INITLIALIZE LOGGER */
	public function loadExceptionHandler() {
		$this->exception = new KraftwerkException($logger);	
	}

	// ####################### DEPRECATED ####################################
	
	
	/* LOAD DIR 
	private function loadDirectory($dir,$loaded) {
		$open 	= opendir($dir); // Load Library Directory
		while ($read = readdir($open)) {
			if ($read!= "." && $read!= "..") {
				$ext = substr($read,strrpos($read, '.')+1);
				if($ext == "php") { // Only Load PHP Files
					include_once($dir . $read);
					if(count($loaded) == 0) {
						$loaded[0] = $read;
					} else {
						$loaded[count($loaded)] = $read;
					}
				}
			}
		}
		closedir($open);
		return $loaded;
	}*/
	
	/* GATHER COMPONENTS 
	function gatherComponents($component_dir) {
		$open 		= opendir($component_dir);
		$views	= array();
		while ($read = readdir($open)) {
			if ($read!= "." && $read!= "..") {
				$ext 		= substr($read,strrpos($read, '.')+1);
				$fileroot 	= substr($read,0,strrpos($read, '.'));
				if($ext == "php") { // Only Load PHP Files
					$views[$fileroot] = $component_dir . $read;
				}
			}
		}
		closedir($open); 
		return $views;
	}*/

	
}
?>