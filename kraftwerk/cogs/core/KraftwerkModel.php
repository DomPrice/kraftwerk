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
		
		if($this->validate_data_types($conditions)) {	
			// construct query
			$query = "SELECT * FROM " . $table . " WHERE id=" . intval($id);
			if(count($conditions)) { 
				$query .= " AND " . $this->generate_params_clause($conditions);
			}
			$query .= ";";
		
			// run query
			$result = $this->runQuery($query);
		
			$this->data = $result[0]; // save result internally
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
			$query = "SELECT * FROM " . $table;
			
			// WHERE CLAUSE
			if($this->validate_data_types($conditions)) {	
				$query .= " WHERE" . $this->generate_params_clause($conditions);
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
		}
		
		$result = $this->runQuery($query . ";");
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
		
		if($this->validate_data_types($data)) {		
			if(count($existing_records) > 0 && $data["id"] != "" && $data["id"] != NULL) {
				$id = $data["id"];
				unset($data["id"]); // unset the id, we are not changing it.
				$query = "UPDATE " . $table . " SET " . $this->generate_update_clause($data) . " WHERE id=" . intval($id) . ";"; // update existing record
			} else {
				$query = "INSERT INTO " . $table . $this->generate_insert_clause($data) . ";"; // insert new record
			}
			
			//return $query;
			$result = $this->runQuery($query);
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
			$query = "DELETE FROM " . $table . " WHERE id=" . intval($id);
			if(count($conditions) > 0) {
				 $query .= " AND " . $this->generate_params_clause($conditions);
			}
			$query .= ";";
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
		// execute only if not explicitly stated in the child model
		if(count($this->fields) <= 0 || $this->fields == NULL || $this->fields == "") { 
			$table = get_table();
			$this->getFields($table);
		}
		return $this->fields;
	}
	
	/*
		###########################################################
							PRIVATE FUNCTIONS
		###########################################################
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
		GENERATES A PARAMETER CLAUSE FOR PREDEFIED QUERIES BASED ON $conditions
		@param $conditions = Conditions as Array to convert to SQL conditonal clause
		@returns SQL formatted query list
	*/
	private function generate_params_clause($conditions) {
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
				if($this->is_a_number($value)) { // if numeric
					$params .= $and . $key . '=' . $value;
				} else {
					$params .= $and . $key . '="' . $value . '"';
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
	private function generate_update_clause($conditions) {
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
					$params .= $and . $key . '="' . $value . '"';
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
	private function generate_insert_clause($conditions) {
		$params = $params .= "("; // open bracket for keys
		if(count($conditions) > 0) {

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
					$params .= $and .  '"' . $value . '"';
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
		if(strtoupper($type) == "DATE" || strtoupper($type) == "TIME" || strtoupper($type) == "DATETIME" 
			|| strtoupper($type) == "TIMESTAMP" || strtoupper($type) == "YEAR"
		) {
			$output = true;
		}
		return $output;
	}

}
?>