<?php
/* 

###################################################################
	KRAFTWERK  ENVIRONMENT CLASS 
	
	This is the main environment class for the kraftwerk application

###################################################################
*/
class KraftwerkEnv {
	
	var $USE_ENV = NULL;
	
	// constructor
	public function __construct() {
		$this->USE_ENV = "kwconfig";
	}

}
?>
