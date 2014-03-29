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
	protected $fields= array(); // fields used by this table, will be used for checking
	
	// If set in a child class that extends KraftwerkModel, Kraftwerk will attempt to write to this table instead.
	protected $use_table = NULL; 
	
	/*
		CONSTRUCTOR
	*/
	public function __construct() { // schema is optional
		parent::__construct($host,$user,$pass,$schema);
	}
	
	/*
		SEARCH FUNCTIONS
	*/
	public function find($id, $conditions = array()) {
		
		// SET TABLE
		$table = $this->get_table(); // get table
		$result = NULL;
		
		// construct query
		if($id != NULL && $id != "" && $id != 0) {

			// needed for mysql_real_escape_string
			$innerConn = new mysqli($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD);
			
			// make sure connection valid
			if(!mysqli_connect_errno()) {
				$query = "SELECT * FROM " . $table . " WHERE id=" . intval($id); 
				if((count($conditions) > 0) && $this->validate_data_types($conditions,$innerConn)) {
					$query .= " AND" . $this->generate_params_clause($conditions,$innerConn);
				}
				$query .= ";";
				$result = $this->runQuery($query);
				$this->data = $result[0]; // save result internally
				
				// close connection, we'll use another to execute
				$innerConn->close();
			}
		}
		
		return $result;
	}
	
	/*
		SEARCH FUNCTIONS, FIND BY CONDTIONS
	*/
	public function find_by($conditions = array()) {
		
		// SET TABLE
		$table = $this->get_table(); // get table
		$result = NULL;
		
		// construct query
		if($conditions != NULL && $conditions != "") {

			// needed for mysql_real_escape_string
			$innerConn = new mysqli($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD);
			
			// make sure connection valid
			if(!mysqli_connect_errno()) {
				$query = "SELECT * FROM " . $table . " WHERE 1=1 "; 
				if((count($conditions) > 0) && $this->validate_data_types($conditions,$innerConn)) {
					$query .= " AND" . $this->generate_params_clause($conditions,$innerConn);
				}
				$query .= ";";
				$result = $this->runQuery($query);
				$this->data = $result[0]; // save result internally
				
				// close connection, we'll use another to execute
				$innerConn->close();
			}
		}
		
		return $result;
	}
	
	/*
		FIND ALL
		Returns all pets that meet the criteria specified in $opts
		@param $conditions parameters of query
		@param $filters MIN/MAX/ORDER_BY/DESC
	*/
	public function find_all($conditions = array(),$filters = array()) {
		
		// SET TABLE
		$table = $this->get_table(); // get table
		$result = NULL;
		
		if($table != NULL && $table != "") {
			
			// needed for mysql_real_escape_string
			$innerConn = new mysqli($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD);
			
			// make sure connection valid
			if(!mysqli_connect_errno()) {
			
				$query = "SELECT * FROM " . $table;
				
				// WHERE CLAUSE
				if($this->validate_data_types($conditions)) {	
					$query .= " WHERE" . $this->generate_params_clause($conditions,$innerConn);
				}
				
				// ORDER BY CLAUSE
				if(isset($filters["order_by"]) && $filters["order_by"] != NULL && $filters["order_by"] != "") {	
					if(kw_isalphanum($filters["order_by"]) && $this->field_exists($filters["order_by"])) { // if field exists and is alpha numeric
						$query .= " ORDER_BY " . $filters["order_by"];
					}
				}
				
				// DESC ORDER?
				if(isset($filters["desc"]) && $filters["desc"] == "true") {	
					$query .= " DESC";
				}
				
				// MAX/MIN -> LIMIT X,XX
				if(isset($filters["max"]) && $filters["max"] != NULL && $filters["max"] != "") { // check if max min exist
					$max = $filters["max"];
					if(!isset($filters["min"]) || $filters["min"] == "" || $filters["min"] == NULL) { // min is optional
						$min = $filters["min"];
					} else {
						$min = intval(0); // default to zero
					}
					if(is_int($min) && is_int($max)) { // only valid integers 
						$query .= " LIMIT " . intval($min) . "," . intval($max); // force integers to be safe
					}
				}
				
				// close connection, we'll use another to execute
				$innerConn->close();
			}
				
			$result = $this->runQuery($query . ";");
		}

		return $result;
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
		
		// SET TABLE
		$table = $this->get_table(); // get table
		$result = NULL;
		
		// CHECK IF ID EXISTS AND  NUMERIC
		$existing_records = 0;
		if($data["id"] && is_numeric($data["id"])) {
			$existing_records = $this->find($data["id"]);
		}
		
		// VALIDATE DATATYPES
		$datatypes_valid = $this->validate_data_types($data);
		
		if($datatypes_valid["status"] == true) {	
		
			// needed for mysql_real_escape_string
			$innerConn = new mysqli($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD);
			
			// make sure connection valid
			if(!mysqli_connect_errno()) {
				
				// generate save query
				if(count($existing_records) > 0 && $data["id"] != "" && $data["id"] != NULL) {
					$id = $data["id"];
					unset($data["id"]); // unset the id, we are not changing it.
					$query = "UPDATE " . $table . " SET " . $this->generate_update_clause($data,$innerConn) . " WHERE id=" . intval($id) . ";"; // update existing record
				} else {
					$query = "INSERT INTO " . $table . $this->generate_insert_clause($data,$innerConn) . ";"; // insert new record
				}
				
				// close connection, we're using a different one to run the query
				$innerConn->close();

			}
				
			//return $query;
			$result = $this->runQuery($query);

		} else { 
			die($datatypes_valid["error"]);
		}
		
		// return result
		return $result;
	}
	
	/*
		DELETE
		Deletes the model from the database based on id
		@param $id id of model to remove
		@param $data additional data to match entry to delete
		@returns whether or not entry successfully deleted
	*/
	public function delete($id,$conditions = array()) {
		
		// SET TABLE
		$table = $this->get_table();
		
		if(is_numeric($id) && $id != "" && $id != NULL) { 
		
			// needed for mysql_real_escape_string
			$innerConn = new mysqli($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD);
			
			// delete record
			if(!mysqli_connect_errno()) {
				$query = "DELETE FROM " . $table . " WHERE id=" . intval($id);
				if(count($conditions) > 0) {
					 $query .= " AND " . $this->generate_params_clause($conditions,$innerConn);
				}
				$query .= ";";
				
			}
			
			// close connection, we'll use a different connection to run the query
			$innerConn->close();
			
		}
		return $this->runQuery($query);
	}
	
	/*
		DESTROY
		Alias for delete();
		@param $id id of model to remove
		@param $data additional data to match entry to delete
		@returns whether or not entry successfully deleted
	*/
	public function destroy($id,$conditions = array()) {
		$this->delete($id,$conditions);
	}
	
	/*
		ALIAS FOR runQuery() MYSQLIN CONNECTOR, RUN DIRECT MYSQL QUERY
		@param $query = Query to run
		@returns Query result
	*/
	public function sql($query) {
		return $this->runQuery($query);	
	}

	/*
		RETURNS THE NAME OF THE CLASS THAT IS EXTENDING THIS CONNECTOR
		@returns $String class name of current object extending this connector
	*/
	public function instance_of() {
		return get_class($this);
	}
	
	/*
		RETURNS THE FIELDS OF THE CURRENT MODEL
		@returns array of the model's fields and their types
	*/
	public function fields() {
		// execute only if fields not explicitly stated in the child model
		if(count($this->fields) <= 0 || $this->fields == NULL || $this->fields == "") { 
			$table = $this->get_table();
			$this->getFields($table);
		}
		return $this->fields;
	}
	
	/*
		###########################################################
							PRIVATE FUNCTIONS
		###########################################################
	*/
	
	private function validate_data_types($conditions) {
		$output = array();
		$output["status"] = true;
		foreach($conditions as $field => $value) {
			if($this->is_field_type_datetime($field) && !$this->is_valid_mysql_datetime($value)) {
				$output["status"] = false;
				$output["error"] = "Type mismatch: The value of `" . $field . "` is not a correctly formatted MySQL datetime value: " . $value;
			} elseif($this->is_field_type_date($field) && !$this->is_valid_mysql_date($value)) {
				$output["status"] = false;	
				$output["error"] = "Type mismatch: The value of `" . $field . "` is not a correctly formatted MySQL date value: " . $value;
			} elseif($this->is_field_type_number($field) && !is_numeric($value)) {
				$output["status"] = false;	
				$output["error"] = "Type mismatch: The value of `" . $field . "` is not a correctly formatted numeric value: " . $value;
			}
			// assume it is a string if none of these are false
		}
		return $output;
	}
	
	/*
		VALIDATES WHETHER THE INCOMING DATA IS PART OF THE MODEL
		@returns true/false
 	*/
	public function valid($data) {
		$fields = $this->fields();
		foreach($data as $data_field) {
			if(!array_key_exists($data_field,$fields)) {
				return false;
			}
		}
		return true;
	}
	
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
		GENERATES A PARAMETER CLAUSE FOR PREDEFIED QUERIES BASED ON $conditions
		@param $conditions = Conditions as Array to convert to SQL conditonal clause
		@returns SQL formatted query list
	*/
	private function generate_params_clause($conditions,$innerConn) {
		$params = "";
		if(count($conditions) > 0) {
			
			 $first_param = false; // first parameter
			 foreach($conditions as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ' AND '; 
				} else { 
					$and = " "; 
					$first_param = true; // set this so the next param includes AND
				}
				if($this->is_field_type_number($key)) { // if numeric
					$params .= $and . $key . '=' . $value;
				} else {
					$params .= $and . $key . '="' . mysqli_real_escape_string($innerConn,$value) . '"';
				}
			 }
			 
		}
		return $params;
	}
	
	/* 
		GENERATES A UPDATE PARAMETER CLAUSE FOR PREDEFIED QUERIES BASED ON $conditions
		@param $conditions = Conditions as Array to convert to SQL conditonal clause
		@returns SQL formatted query param list for update
	*/
	private function generate_update_clause($conditions,$innerConn) {
		$params = "";
		if(count($conditions) > 0) {
				
			$first_param = false; // first parameter
			foreach($conditions as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ', '; 
				} else { 
					$and = " "; 
					$first_param = true; // set this so the next param includes a comma
				}
				if(is_numeric($value)) { // if numeric
					$params .= $and . $key . '=' . $value;
				} else {
					$params .= $and . $key . '="' . mysqli_real_escape_string($innerConn,$value) . '"';
				}
			}
				
		}
		return $params;
	}
	
	/* 
		GENERATES A UPDATE PARAMETER CLAUSE FOR PREDEFIED QUERIES BASED ON $conditions
		@param $conditions = Conditions as Array to convert to SQL conditonal clause
		@returns SQL formatted query param list for update
	*/
	private function generate_insert_clause($conditions,$innerConn) {
		$params = $params .= "("; // open bracket for keys
		if(count($conditions) > 0) {
				
			// go through query now
			$first_param = false; // first parameter
			foreach($conditions as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ','; 
				} else { 
					$and = ""; 
					$first_param = true; // set this so the next param includes a comma
				}
				$params .= $and . $key;
			}
			 
			$params .= ") VALUES ("; // add values clause
			
			$first_param = false; // first parameter			 
			foreach($conditions as $key => $value) { // assemble the query
				if($first_param != false) { 
					$and = ','; 
				} else { 
					$and = ""; 
					$first_param = true; // set this so the next param includes a comma
				}
				if(is_numeric($value)) { // if numeric and field type is a number (int/float)
					$params .= $and . $value;
				} else { // treat as string
					$params .= $and .  '"' . mysqli_real_escape_string($innerConn,$value) . '"';
				}
			 }
			$params .= ")";

		}
		return $params;
	}
	
	/*
		RETURNS WHETHER OR NOT THE SELECT FIELD EXISTS AS PART OF THE MODEL
		@param $field_name = Field to check for in model
		@returns true/false
	*/
	private function field_exists($field_name) {
		$output = false;
		$the_fields = $this->fields();
		if(isset($the_fields[$field_name])) { // see if field exists in field list
			$output = true;
		}
		return $output;
	}
	
	/*
		###########################################################
							MODEL FUNCTIONS
		###########################################################
	*/
	
	/*
		RETURNS THE TABLE USED BY THIS MODEL
		return $string table name
	*/
	protected function get_table() {
		if($this->use_table != "" && $use_table != NULL && kw_isalphanum($use_table)) {
			$table = $this->use_table; // use declared table
		} else {
			$table = $this->extrapolate_table(); // extrapolate table name based on model name
		}	
		return $table;
	}
	
	/* 
		RETURNS THE FIELD TYPE OF THE SELECTED FIELD NAME
		@returns field type as a string
	*/
	protected function field_type($field_name) {
		$the_fields = $this->fields();
		return $the_fields[$field_name]["type"]; // get field type for selected field
	}
	
	/*
		RETURNS WHETHER VALUE IS VALID MYSQL DATE
	*/
	protected function is_valid_mysql_date($strIn) {
		if (preg_match("/^(\d{4})-(\d{2})-(\d{2})$/", $strIn, $matches)) { 
        	if (checkdate($matches[2], $matches[3], $matches[1])) { 
           		return true; 
        	} 
    	} 
    	return false; 
	}

	/*
		RETURNS WHETHER VALUE IS VALID MYSQL DATETIME
	*/
	protected function is_valid_mysql_datetime($strIn) {
		if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $strIn, $matches)) { 
        	if (checkdate($matches[2], $matches[3], $matches[1])) { 
           		return true; 
        	} 
    	} 
    	return false; 
	}
	
	/*
		RETURNS WHETHER OR NOT THE SELECTED FIELD IS NUMERIC
		@returns true/false
	*/
	protected function is_field_type_number($field_name) {
		$type = $this->field_type($field_name);
		$output = false;
		if(strtoupper($type) == "INTEGER" || strtoupper($type) == "INT" || strtoupper($type) == "SMALLINT" 
			|| strtoupper($type) == "TINYINT" || strtoupper($type) == "MEDIUMINT" || strtoupper($type) == "BIGINT"
			|| strtoupper($type) == "DECIMAL" || strtoupper($type) == "NUMERIC" || strtoupper($type) == "FLOAT"
			|| strtoupper($type) == "DOUBLE"
		) {
			$output = true;
		}
		return $output;
	}
	
	/*
		RETURNS WHETHER OR NOT THE SELECTED FIELD IS A STRING
		@returns true/false
	*/
	protected function is_field_type_string($field_name) {
		$type = $this->field_type($field_name);
		$output = false;
		if(strtoupper($type) == "CHAR" || strtoupper($type) == "VARCHAR" || strtoupper($type) == "BINARY" 
			|| strtoupper($type) == "VARBINARY" || strtoupper($type) == "BLOB" || strtoupper($type) == "TEXT"
			|| strtoupper($type) == "ENUM" || strtoupper($type) == "SET" 
		) {
			$output = true;
		}
		return $output;
	}
	
	/*
		RETURNS WHETHER OR NOT THE SELECTED FIELD IS A TEXT FIELD (255-4096 chars)
		@returns true/false
	*/
	protected function is_field_type_text($field_name) {
		$type = $this->field_type($field_name);
		$output = false;
		if(strtoupper($type) == "TEXT") {
			$output = true;
		}
		return $output;
	}
	
	/*
		RETURNS WHETHER OR NOT THE SELECTED FIELD IS A DATETIME
		@returns true/false
	*/
	protected function is_field_type_datetime($field_name) {
		$type = $this->field_type($field_name);
		$output = false;
		if(strtoupper($type) == "TIME" || strtoupper($type) == "DATETIME" 
			|| strtoupper($type) == "TIMESTAMP" || strtoupper($type) == "YEAR"
		) {
			$output = true;
		}
		return $output;
	}
	
	/*
		RETURNS WHETHER OR NOT THE SELECTED FIELD IS A DATE
		@returns true/false
	*/
	protected function is_field_type_date($field_name) {
		$type = $this->field_type($field_name);
		$output = false;
		if(strtoupper($type) == "DATE") {
			$output = true;
		}
		return $output;
	}

}
?>