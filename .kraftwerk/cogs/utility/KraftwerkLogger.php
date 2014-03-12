<?php
/* 
###############################################################################

  KRAFTWERK LOGGER CLASS
	
  This class generates the log file for Kraftwerk
	
###############################################################################
*/
class KraftwerkLogger extends FileConnector {
	
	/*
		Writes to the log file
	*/
	public function write($str,$opts=array()) {
		$mode = "append"; // default
		if(strtolower($opts["mode"]) == "append" || strtolower($opts["mode"]) == "overwrite") {
			$mode = strtolower($opts["mode"]);
		}
	}
	
	/*
		Alias for write, appends to the log file
	*/
	public function append($str) {
		$opts = array();
		$opts["mode"] = "append";
		$this->write($str,$opts);
	}
	
	public function delete() {
		
	}
	
	public function generate() {
		
	}
	
}
?>