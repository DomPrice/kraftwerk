<?php
/* 

###################################################################
  KRAFTWERK CONFIGURATION CLASS 
  
  This is the main configuration class for the kraftwerk application

###################################################################
*/
class KraftwerkConfig {
  
  // DATABASE CONFIG
  public $settings = array(); // storage for settings
  
  // constructor
  public function __construct() {}
  
  // use to set values
  public function __get($name) {
    if(array_key_exists($name, $this->settings)) {
      return $this->settings[$name];
    }
  }
  
  // parse configuration file
  public function parse_config_file($config_file) {
    $this->settings =  kw_parse_yaml($config_file);
  }

}
?>
