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
	
	var $CONFIG_GLOBAL_DIR		= "/config";
	var $CONTROLLERS_DIR		= "/application/controllers";
	var $MODELS_DIR				= "/application/models";
	var $VIEWS_DIR				= "/application/views";
	var $APP_LIBS_DIR			= "/application/libraries";
	var $COGS_DIR				= "/cogs";
	var $LIBS_DIR				= "/run/lib";
	var $RUN_DIR 				= "/run";
	var $ASSETS_DIR				= "/assets";
	var $LOGS_DIR				= "/logs";
	
	// OTHER STRUCTS
	var $CORE_LIB_LOADED 			= array();
	var $CONFIG_LOADED 				= array();
	var $COGS_LOADED 				= array();
	var $MODELS_LOADED 				= array();
	var $CONTROLLERS_LOADED 		= array();
	var $KERNAL_COMPONENTS_LOADED 	= array();
	var $CURRENT_CONTROLLER     	= NULL;
	var $CURRENT_ACTION		   		= NULL;
	
	// CONFIG VAR
	var $CONFIG = "";
	
	// PUBLIC VARIABLES AND OBJECTS
	public $logger				= "";
	
	/* 
		CONSTRUTOR
	*/
	public function __construct() {
		$this->pathNames();
		$this->loadComponents();
		$this->loadLogger();
		$this->loadExceptionHandler();
	}
	
	/*
		CONFIGURE PATH NAMES
	*/
	public function pathNames() {
		global $kw_config; 
		if(isset($kw_config->kw_root) && ($kw_config->kw_root != "")) {
			$kw_root = $kw_config->kw_root;
		} else {
			$kw_root = "/.kraftwerk"; // default to . syntax
		}

		$this->CONFIG_GLOBAL_DIR	= $kw_root . $this->CONFIG_GLOBAL_DIR;
		$this->CONTROLLERS_DIR		= $kw_root . $this->CONTROLLERS_DIR;
		$this->MODELS_DIR			= $kw_root . $this->MODELS_DIR;
		$this->VIEWS_DIR			= $kw_root . $this->VIEWS_DIR;
		$this->APP_LIBS_DIR			= $kw_root . $this->APP_LIBS_DIR;
		$this->COGS_DIR				= $kw_root . $this->COGS_DIR;
		$this->LOGS_DIR				= $kw_root . $this->LOGS_DIR;
		$this->RUN_DIR				= $kw_root . $this->RUN_DIR;
		$this->LIBS_DIR				= $kw_root . $this->LIBS_DIR;
		$this->ASSETS_DIR			= $kw_root . $this->ASSETS_DIR;
		
	}
	
	/* 
		LOAD CONFIG FILES
		Loads the global config files
		VOID
	*/
	public function loadComponents() {
		global $kw_config;
		
		// load libs
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->LIBS_DIR);
		
		// load cogs/dependencies
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->RUN_DIR . "/core/connectors");
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->RUN_DIR . "/core/components");
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->RUN_DIR . "/core/utility");
		
		// load cogs (extensions)
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->COGS_DIR);
		
		// load the models
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->MODELS_DIR);
		
		// load app specific libraries
		$this->loadComponentDirectory(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->APP_LIBS_DIR);

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
		global $kw_config;
		$this->logger = new KraftwerkLogger();
	}
	
	/* INITLIALIZE LOGGER */
	public function loadExceptionHandler() {
		$this->exception = new KraftwerkException();	
	}
	
	/* 
		LOAD CONTROLLER 
		@param $controller = Controller name
		@return true/false
	*/
	public function loadController($controller) {
		global $kw_config; // grab global settings
		
		// include controller class file and then initialize it
		if(file_exists(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir .  $this->CONTROLLERS_DIR . "/" . $controller . "_controller.php")) {
			@include_once(realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir .  $this->CONTROLLERS_DIR . "/" . $controller . "_controller.php");
		} else {
			$error = "Kraftwerk received a request that it does not have a controller for. [" . $controller . "];";
			$this->logger->log_error($error);
			$this->exception->throw_error($error);
		}
		
		// load the controller class
		try {
			@eval('$this->CURRENT_CONTROLLER = new ' . $this->controllerToClassName($controller) . '();'); // load into ENV
		} catch (Exception $e) {
			$error = "Kraftwerk failed to load controller: [" . $controller . "];";	
			$this->logger->log_error($error . " | details: " . $e->getMessage());
			$this->exception->throw_error($error);	
		}
		
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
		try {
			$this->CURRENT_ACTION = $action;
			$function = '$this->CURRENT_CONTROLLER->' . $this->CURRENT_ACTION;
			if(method_exists($this->CURRENT_CONTROLLER->instance_of(),$action)) {
				$reflection = new ReflectionMethod($this->CURRENT_CONTROLLER, $this->CURRENT_ACTION);
				if($reflection->isPublic()) {
					eval($function . '();');
				} else {
					$error = "Kraftwerk cannot call action:[" . $action . "] on controller:[" . $this->CURRENT_CONTROLLER->instance_of() . "]; Action is not public.";	
					$this->logger->log_error($error);
					$this->exception->throw_error($error);
				}
			} else {
				$error = "Kraftwerk received a request on controller:[" . $this->CURRENT_CONTROLLER->instance_of() . "] that it does not have an action for. [" . $action . "];";	
				$this->logger->log_error($error);
				$this->exception->throw_error($error);
			}
		} catch(Exception $e) {
			$error = "Kraftwerk cannot call action:[" . $action . "] on controller:[" . $this->CURRENT_CONTROLLER->instance_of() . "];";	
			$this->logger->log_error($error . " | details: " . $e->getMessage());
			$this->exception->throw_error($error);
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
	
	/*
		Returns full log directory path
	*/
	public function get_log_dir() {
		global $kw_config;
		return realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $this->LOGS_DIR;	
	}
	
	/*
		Alias for get_log_dir()
	*/
	public function getLogDir() {
		return $this->get_log_dir();
	}
	
}
?>