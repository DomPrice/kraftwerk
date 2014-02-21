<?php
/* 
###########################################################

  KRAFTWERK CONTROLLER CLASS 
	
  This is the control class for the kraftwerk controllers
	
###########################################################
*/
class KraftwerkController {
	
	public $view = NULL; // placeholder for view
	
	/* 
		CONSTRUTOR
	*/
	public function __construct() { }
	
	/*
		RENDER
		Renders the current template
		@param 
	*/
	public function render($view="",$options=array()) {
		global $kraftwerk;
		
		// REGISTER GLOBALS
		foreach($options as $key => $value) {
			if($GLOBALS[$key] == "" || $GLOBALS[$key] == NULL) {
				$GLOBALS[$key] = $value;
				eval("global $$key;"); //register global
			}
		}
		
		// RENDER
		if($view != NULL && $view != "") {
			$path =  $kraftwerk->VIEWS_DIR . "/" . $this->extrapolate_view($view);
		} else {
			$path =  $kraftwerk->VIEWS_DIR . "/" . $this->extrapolate_view();
		}
		include_once(realpath($_SERVER['DOCUMENT_ROOT']) . $path);
	}
	
	/*
		RENDER PARTIAL
	*/
	public function render_partial($vars=array(),$path="") {
		
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
		@returns $String returns the extrapolated view name based on controller name, otherwise returns the view name based on
		                 the input variable $view_name
	*/
	private function extrapolate_view($view_name="") {
		global $kraftwerk;
		
		// get class name
		$controller_name = $this->instance_of();
		
		// remove the controller label from the end of the class, this will be the folder
		$folder = preg_replace('~Controller(?!.*Controller)~', '', $controller_name); 
		
		// split string based on camel case
		foreach(str_split($folder) as $char) {
       		strtoupper($char) == $char and $output and $output .= "_";
            $output .= $char;
        }

		$folder = preg_replace("/[^a-zA-Z0-9\s]/", "_", $output);
		
		// now get the action if no view specified
		if($view_name == NULL || $view_name == "") {
			$view_name = $this->kraftwerk->CURRENT_ACTION;			
		}
		
		// make filename friendly
		$view_file = preg_replace("/[^a-zA-Z0-9\s]/", "_", $view_name);
		
		// assemble string
		$view = $folder . "/" . $view_file . ".php";
		
		// return name
		return strtolower($view);
	}

}
?>