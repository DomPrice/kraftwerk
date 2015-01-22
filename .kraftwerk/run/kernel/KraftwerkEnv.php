<?php
/* 

###################################################################
	KRAFTWERK  ENVIRONMENT CLASS 
	
	This is the main environment class for the kraftwerk application

###################################################################
*/
class KraftwerkEnv {
	
	var $settings = array();
	var $USE_ENV = NULL;
	
	// constructor
	public function __construct() { }
	
	// parse environment file
	public function parse_env_file($config_file) {
		$this->settings =  kw_parse_yaml($config_file);
		$this->USE_ENV = $this->settings["use_env"];
	}

}
?>
