<?php
/*

###################################################################
	KRAFTWERK  ENVIRONMENT CLASS

	This is the main environment class for the kraftwerk application

###################################################################
*/
class KraftwerkEnv {

	var $VARS = array();

	// Constructor
	public function __construct() { }

	// Use to set values
	public function __get($name) {
		if(array_key_exists(strtolower($name), $this->VARS)) {
			return $this->VARS[strtolower($name)];
		}
	}

	// Parse environment file
	public function parse_settings_file($config_file) {
		$this->VARS = kw_parse_yaml($config_file);
	}

	// Converts to ENV struct
	public function to_struct() {
		$struct_out = array();
		foreach($this->VARS as $key => $value) {
			$struct_out[$key] = $value;
		}
		return $struct_out;
	}

}
?>
