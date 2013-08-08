<?php
/* 
###########################################################

  KRAFTWERK CONTROLLER CLASS 
	
  This is the control class for the kraftwerk controllers
	
###########################################################
*/
class KraftwerkController {
	
	// CLASS VARS
	protected $current_action 	= "index"; // stored action name
	
	/* 
		CONSTRUTOR
	*/
	public function __construct() {
	}
	
	/*
		RENDER
		Renders the current template
		@param 
	*/
	public function render($path="",$vars=array()) {
		global $kraftwerk;
		if($path != "" && $path != NULL) {
			// load explicit view
			include_once($kraftwerk->VIEWS_DIR . "/" . $path);
		} else { 
			// attempt to load a view based on the controller name and action
			include_once($kraftwerk->VIEWS_DIR . "/" . $this->extrapolate_view());
		}
	}
	
	/*
		RUN ACTION
		@param $action Run the specified action
	*/
	public function run_action($action="",$params=array()) {
	
	}
	
	/*
		RETURNS THE NAME OF THE ACTION THAT IS CURRENTLY BEING EXECUTED
		@returns $String class name of current object extending this connector
	*/
	public function current_action() {
		return $this->current_action;
	}
	
	/*
		RETURNS THE NAME OF THE CLASS THAT IS EXTENDING THIS VIEW
		@returns $String class name of current object extending this connector
	*/
	public function instance_of() {
		return get_class($this);
	}
	
	/* 
		RETURN THE EXTRAPOLATED VIEW NAME THAT KRAFTWERK WILL LOAD, must be placed inside the controller class
		@returns $String returns the extrapolated view name based on controller name
	*/
	private function extrapolate_view() {
		
		// get class name
		$controller_name = $this->instance_of();
		
		// remove the controller label from the end of the class, this will be the folder
		$folder = preg_replace('~Controller(?!.*Controller)~', 'bar', $controller_name); 
		
		// split string based on camel case
		foreach(str_split($folder) as $char) {
       		strtoupper($char) == $char and $output and $output .= "_";
            $output .= $char;
        }

		$folder = preg_replace("/[^a-zA-Z0-9\s]/", "_", $output);
		
		// now get the action
		$action_name = $this->current_action();
		$view_file = preg_replace("/[^a-zA-Z0-9\s]/", "_", $action_name);
		
		// assemble string
		$view = $folder . "/" . $view_file;
		
		// return name
		return strtolower($view);
	}

}
?>