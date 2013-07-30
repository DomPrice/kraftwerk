<?php
/* 
###########################################################

  KRAFTWERK MODEL CLASS
	
  This is the control class for the kraftwerk models
	
###########################################################
*/
class KraftwerkModel extends MySQLConnector {
	
	// CLASS VARS
	protected $data = array(); // stored data from singular query result
	
	/*
		SEARCH FUNCTIONS
	*/
	public function find($id, $opts = array()) {
		$table = $this->extrapolate_table(); // extrapolate table name based on model name
		$query = "SELECT * FROM " . $table . " WHERE id=" . $id;
		if(count($opts)) { 
			$query .= " AND " . $this->generate_params_clause($opts);
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
		$query = "SELECT * FROM " . $table . " WHERE" . $this->generate_params_clause($opts) . ";";
		return $this->runQuery($query);
	}
	
	/*
		###########################################################
							SELF FUNCTIONS
		###########################################################
	*/
	
	/*
		SAVE
		Saves the model data to the database
		@param $data data to save to this model's database entry
		@returns whether or not entry successfully saved
	*/
	public function save($data = array()) {
		$table = $this->extrapolate_table(); // extrapolate table name based on model name
		
		// CHECK IF EXISTS
		$existing_records = $this->find_all($data);
		if(count($existing_records) > 0) {
			$query = "UPDATE " . $table . " SET " . generate_update_clause($data) . ";"; // update existing record
		} else {
			$query = "INSERT INTO " . $table . generate_insert_clause($data) . ";"; // insert new record
		}
		//return $this->runQuery($query);
	}
	
	/*
		DELETE
		Deletes the model from the database based on id
		@param $id id of model to remove
		@param $data additional data to match entry to delete
		@returns whether or not entry successfully deleted
	*/
	public function delete($id,$data = array()) {
		$table = $this->extrapolate_table(); // extrapolate table name based on model name
		
		
		//return $this->runQuery($query);
	}
	
	/*
		DESTROY
		Alias for delete();
		@param $id id of model to remove
		@param $data additional data to match entry to delete
		@returns whether or not entry successfully deleted
	*/
	public function destroy($id,$data = array()) {
		$this->delete($id,$data);
	}
	
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
		GENERATES A PARAMETER CLAUSE FOR PREDEFIED QUERIES BASED ON $opts
		@returns SQL formatted query list
	*/
	private function generate_params_clause($opts) {
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
	
	/* 
		GENERATES A UPDATE PARAMETER CLAUSE FOR PREDEFIED QUERIES BASED ON $opts
		@returns SQL formatted query param list for update
	*/
	private function generate_update_clause($opts) {
		$params = "";
		if(count($opts) > 0) {
			 $first_param = false; // first parameter
			 foreach($opts as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ', '; 
				} else { 
					$and = " "; 
					$first_param = true; // set this so the next param includes a comma
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
	
	/* 
		GENERATES A UPDATE PARAMETER CLAUSE FOR PREDEFIED QUERIES BASED ON $opts
		@returns SQL formatted query param list for update
	*/
	private function generate_insert_clause($opts) {
		$params = $params .= "("; // open bracket for keys
		if(count($opts) > 0) {

			$first_param = false; // first parameter
			foreach($opts as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ', '; 
				} else { 
					$and = " "; 
					$first_param = true; // set this so the next param includes a comma
				}
				$params .= $and . $key;
			}
			 
			$params .= ") VALUES ("; // add values clause
			
			$first_param = false; // first parameter			 
			foreach($opts as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ', '; 
				} else { 
					$and = " "; 
					$first_param = true; // set this so the next param includes a comma
				}
				if(is_numeric($value)) { // if numeric
					$params .= $and . $value;
				} else {
					$params .= $and .  '"' . $value . '"';
				}
			 }
			 
			$params .= ");";

		}
		return $params;
	}

}
?>