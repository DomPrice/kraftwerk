<?php
/* 
###########################################################

  KRAFTWERK MODEL CLASS
	
  This is the control class for the kraftwerk models
	
###########################################################
*/
class KraftwerkModel extends MySQLConnector {
	
	/*
		SEARCH FUNCTIONS
	*/
	public function find($id, $opts = array()) {
		$table = $this->extrapolate_table(); // extrapolate table name based on model name
		$query = "SELECT * FROM " . $table . " WHERE id=" . $id . ";";
		return $this->runQuery($query);
	}
	
	/*
		FIND ALL
		Returns all pets that meet the criteria specified in $opts
		@param $opts parameters of query
	*/
	public function find_all($opts = array()) {
		$table = $this->extrapolate_table(); // extrapolate table name based on model name
		$params = "";
		if(count($opts) > 0) {
			 $params .= " WHERE 1=1";
		}
		$params .= ";";
		$query = "SELECT * FROM " . $table . $params;
		return $this->runQuery($query);
	}
	
	/*
		###########################################################
							SELF FUNCTIONS
		###########################################################
	*/
	
	/*
		RETURNS THE NAME OF THE CLASS THAT IS EXTENDING THIS CONNECTOR
		@returns $String class name of current object extending this connector
	*/
	public function instance_of() {
		return get_class($this);
	}
	
	/*
		PRIVATE FUNCTIONS
	*/
	
	/* 
		RETURN THE EXTRAPOLATED TABLE NAME THAT KRAFTWERK WILL ATTEMPT TO ACCESS WHEN DOING SELF QUERIES ON A MODEL EXTENDING THIS CLASS
		returns the extrapolated table name
		@returns $String returns the extrapolated table name
	*/
	private function extrapolate_table() {
		
		// get class name
		$name = $this->instance_of($this);
		
		// split string based on camel case
		foreach(str_split($name) as $char) {
       		strtoupper($char) == $char and $output and $output .= "_";
            $output .= $char;
        }
		$name = $output; // save as name
		
		// set plurality
		if(substr($name, -2) == "sh" || substr($name, -2) == "ch" || substr($name, -1) == "s") {
			$name .= "es";
		} else {
			$name .= "s";
		}
		$table = preg_replace("/[^a-zA-Z0-9\s]/", "_", $name);
		
		// return name
		return strtolower($table);
	}

}
?>