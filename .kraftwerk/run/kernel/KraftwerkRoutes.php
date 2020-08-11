<?php
/*

###################################################################
	KRAFTWERK ROUTES CLASS

	This handles the routes for Kraftwerk and parses the routes YAML
	file found under config

###################################################################
*/
class KraftwerkRoutes {

	// DATABASE CONFIG
	public $routes   = array(); // storage for routes
	public $mappings = array(); // storage for mappings

	// Constructor
	public function __construct() {}

	// Use to set values
	public function __get($name) {
		if(array_key_exists($name, $this->routes)) {
			return $this->routes[$name];
		}
	}

	// Parse configuration file
	public function parse_config_file($routes_file) {
		$routes =  kw_parse_yaml($routes_file);
		foreach($routes as $key => $val) {
			$this->routes[$key] = $val;
		}
	}

	// Map routes
	public function map_routes() {
		foreach($this->routes as $from => $to) {
			$from_exploded = explode("/",$from);
			$to_exploded   = explode("/",$to);
			array_push($this->mappings, array(
				$from_exploded[0] => $to_exploded[0],
				$from_exploded[1] => $to_exploded[1]
			));
		}
	}

	// Check controller mapping
	public function override_controller_mapping($controller) {
		$controller_new = $controller;
		foreach($this->mappings as $mapping) {
			if($mapping["$controller"] != NULL) {
				$controller_new = $mapping["$controller"]; // Found Mapping
			}
		}
		return $controller_new;
	}

	// Check action mapping
	public function override_action_mapping($action) {
		$action = $action;
		foreach($this->mappings as $mapping) {
			if($mapping["$action"] != NULL) {
				$action_new = $mapping["$action"]; // Found Mapping
			}
		}
		return $action_new;
	}

}
?>
