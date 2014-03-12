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
	var $CURRENT_CONTROLLER     = NULL;
	var $CURRENT_ACTION		    = NULL;
	
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
		
		// load libs
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->LIB_DIR);
		
		// load cogs/dependencies
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->COGS_DIR . "/connectors");
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->COGS_DIR . "/core");
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->COGS_DIR . "/custom");
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->COGS_DIR . "/extensions");
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->COGS_DIR . "/utility");
		
		// load the models
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->MODELS_DIR);
		
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
	
	/* 
		LOAD CONTROLLER 
		@param $controller = Controller name
		@return true/false
	*/
	public function loadController($controller) {
		global $kw_config; // grab global settings
		
		// include controller class file and then initialize it
		(@include_once(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir .  $this->CONTROLLERS_DIR . "/" . $controller . "_controller.php") )
			or die("Kraftwerk received a request that it does not have a controller for. [" . $controller . "];");
		
		@eval('$this->CURRENT_CONTROLLER = new ' . $this->controllerToClassName($controller) . '();'); // load into ENV
		
		// return true/false on whether controller successfully loaded
		if($this->CURRENT_CONTROLLER != NULL) {
			return true;
		} else {
			return false;	
		}
	}
	
	/* 
		LOAD ACTION
		Perform the provided action on the currently loaded controller
		@param $action = Action Name
		@return true/false
	*/
	public function loadAction($action) {
		$this->CURRENT_ACTION = $action;
		$function = '$this->CURRENT_CONTROLLER->' . $this->CURRENT_ACTION;
		if(method_exists($this->CURRENT_CONTROLLER->instance_of(),$action)) {
			eval($function . '();');
		} else {
			die("Kraftwerk received a request on controller:[" . $this->CURRENT_CONTROLLER->instance_of() . "] that it does not have an action for. [" . $action . "];");
		}
	}
	
	/*
		Return extrapolated Class name from loaded controller name
		@param $controller = Controller name
		@return properly formatted Controller Class definition for use in the app
	*/
	private function controllerToClassName($controller) {
    	return preg_replace('/(?:^|_)([a-z])/e', 'strtoupper($1)', $controller) . "Controller";
	}
	
}
?>