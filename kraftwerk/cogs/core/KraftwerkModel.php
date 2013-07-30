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
		$query = "SELECT * FROM " . $table . " WHERE id=" . $id;
		if(count($opts)) { 
			$query .= " AND " . $this->generate_params($opts);
		}
		$query .= ";";
		return $this->runQuery($query);
	}
	
	/*
		FIND ALL
		Returns all pets that meet the criteria specified in $opts
		@param $opts parameters of query
	*/
	public function find_all($opts = array(),$limit="") {
		$table = $this->extrapolate_table(); // extrapolate table name based on model name
		$query = "SELECT * FROM " . $table . " WHERE" . $this->generate_params($opts) . ";";
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
	
	/* 
		GENERATES A PARAMETER LIST FOR PREDEFIED QUERIES BASED ON $opts
		@returns SQL formatted query list
	*/
	private function generate_params($opts) {
		$params = "";
		if(count($opts) > 0) {
			 $first_param = false; // first parameter
			 foreach($opts as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ' AND '; 
				} else { 
					$and = " "; 
					$first_param = true; // set this so the next param includes AND
				}
				if(is_numeric($value)) { // if numeric
					$params .= $and . $key . '=' . $value;
				} else {
					$params .= $and . $key . '="' . $value . '"';
				}
			 }
		}
		return $params;
	}

}
?>