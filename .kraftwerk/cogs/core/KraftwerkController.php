<?php
/* 
###########################################################

  KRAFTWERK CONTROLLER CLASS 
	
  This is the control class for the kraftwerk controllers
	
###########################################################
*/
class KraftwerkController {
	
	public $view = NULL; // placeholder for view
	public $template = NULL; // template to use
	
	/* 
		CONSTRUTOR
	*/
	public function __construct() { }
	
	/*
		USE TEMPLATE
		Tells the renderer what template to use
		@param $template, name of template
	*/
	public function use_template($template) {
		$this->template = $template;
	}
	
	/*
		REDIRECT TO
		Redirects to specified action, will automatically find controller
		@param $action = action to redirect to
	*/	
	public function redirect_to($action) {
		
		// get current controller
		$controller_name = $this->instance_of();
		
		// remove the controller label from the end of the class, this will be the folder
		$controller_slug = strtolower(preg_replace('~Controller(?!.*Controller)~', '', $controller_name)); 
		
		// clean slug
		$action = preg_replace("/[^a-zA-Z0-9\s]/", "_", $action);
		
		// redirect
		header("location:  /" . $controller_slug . "/" . $action);
	}
	
	/*
		REDIRECT
		Alias of redirect_to
		@param $action = action to redirect to
	*/	
	public function redirect($action) {
		$this->redirect_to($action);
	}
	
	/*
		REDIRECT TO URL
		Redirect to specified url location
		@param $url = url to redirect to
	*/	
	
	public function redirect_to_url($url) {
		header("location:  " . $url);
	}
	
	/*
		RENDER
		Renders the current template
		@param $view = name of view to render, kraftwerk will look for view file
		@param $options = variables to be sent to view for display/manipulation
	*/
	public function render($view="",$options=array()) {
		global $kraftwerk;
		global $kw_config;

		// REGISTER GLOBALS
		if($options != NULL && $options != "" && count($options) > 0) {
			foreach($options as $key => $value) {
				if($GLOBALS[$key] == "" || $GLOBALS[$key] == NULL) {
					$GLOBALS[$key] = $value;
					eval("global $$key;"); //register global
				}
			}
		}
		
		// RENDER
		if($view != NULL && $view != "") {
			$path =  $kw_config->hosted_dir . $kraftwerk->VIEWS_DIR . "/" . $this->extrapolate_view($view);
		} else {
			$path =  $kw_config->hosted_dir . $kraftwerk->VIEWS_DIR . "/" . $this->extrapolate_view();
		}
		
		// OUTPUT TO BUFFER FOR LATER INSERTION INTO TEMPLATE
		ob_start();
		if(file_exists(realpath($_SERVER['DOCUMENT_ROOT']) . $path)) {
			if(!include_once(realpath($_SERVER['DOCUMENT_ROOT']) . $path)) {
				die("Kraftwerk cannot open view file [" . $this->extrapolate_view($view) . "]");
			}
			$GLOBALS["$yield"] = ob_get_clean(); // send result to globals
		} else {
			die("Kraftwerk cannot find view file [" . $this->extrapolate_view($view) . "]");
		}


		// YIELD FUNCTION / Yields the content from within the template
		function yield() {
			print $GLOBALS["$yield"];
		}
		
		// alias
		function kw_yield() {
			print $GLOBALS["$yield"];
		}
		
		// SNIPPET FUNCTION / Include a snippet from the snippet directory
		function snippet($snippet="") {
			global $kraftwerk;
			global $kw_config;
			$snippet_path = realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $kraftwerk->VIEWS_DIR . "/_layouts/_snippets/" . $snippet . ".php";
			if(file_exists($snippet_path)) {
				include_once($snippet_path);
			} else {
				if($snippet == "") {
					die("Kraftwerk expects a snippet name and it was not found.");
				} else {
					die("Kraftwerk cannot find the specified snippet file [" . $snippet . "]");
				}
			}
		}
		
		// alias
		function kw_snippet($snippet="") {
			snippet($snippet);
		}

		// RENDER TEMPLATE
		$template_path = realpath($_SERVER['DOCUMENT_ROOT']) . $kw_config->hosted_dir . $kraftwerk->VIEWS_DIR . "/_layouts/_templates/" . $this->template . ".php";
		if(file_exists($template_path)) {
			include_once($template_path);
		} else {
			if($this->template == "") {
				die("Kraftwerk cannot find a template associated with this view. Please check your controller to verify a template has been specified.");
			} else {
				die("Kraftwerk cannot find the specified template file [" . $this->template . "]");
			}
		}
		
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