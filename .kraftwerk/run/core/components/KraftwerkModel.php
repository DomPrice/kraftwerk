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
  protected $fields = array(); // field information used by this table, will be used for validation
  private $instance_data = array(); // used to store dynamically declared instance variables
  
  // If set in a child class that extends KraftwerkModel, Kraftwerk will attempt to write to this table instead.
  protected $use_table = NULL; 
  
  // RELATIONSHIPS
  protected $relationships = array();
  
  // VALIDATORS
  public $validators = NULL;
  
  // ERROR MESSAGES
  public $field_errors = array();


  /*
    ###########################################################
         CONSTRUCTOR FUNCTIONS AND MAGIC FUNCTIONS
    ###########################################################
  */

  /*
    CONSTRUCTOR
  */
  public function __construct() { // schema is optional
    $this->validators = new KraftwerkValidator();
    parent::__construct();
  }
  
  /*
    SET DYNAMICALLY CREATED VARIABLES
    This will set variables for this model instance as $model->variable, and
    store them in the appropriate data array depending on what the name of the
    field is.
  */
  public function __set($name, $value) {
    if(array_key_exists($name, $this->fields)) { // check to see if it's a field
      $this->data[$name] = $value;
    } else {
      $this->instance_data[$name] = $value;
    }
  }
  
  /*
    GET DYNAMICALLY CREATED VARIABLES
    This will get variables for this model instance as $model->variable, and
    retrieve them from the appropriate data array depending on what the name of the
    field is.
  */
  public function __get($name) {

    if(array_key_exists($name, $this->fields)) { // check to see if it's a field
      return $this->data[$name];
    } else if(
      (is_array($this->relationships["has_many"]) && in_array($name, $this->relationships["has_many"])) || 
      (is_array($this->relationships["has_one"]) && in_array($name, $this->relationships["has_one"])) || 
      (is_array($this->relationships["belongs_to"]) && in_array($name, $this->relationships["belongs_to"]))
    ) {
      $model = $this->extrapolate_model_class($name);
      if(count($this->data) > 0) {
        
        $conditions = array();
        $new_class = new $model(); // create new instance

        // determine whether to send children or parent model
        if(is_array($this->relationships["has_many"]) && in_array($name, $this->relationships["has_many"])) {
          $table_singular_name = kw_singularize($this->extrapolate_table());
          $conditions[$table_singular_name . "_id"] = $this->data["id"];
          return $new_class->find_all($conditions);
        } else if(is_array($this->relationships["has_one"]) && in_array($name, $this->relationships["has_one"])) {
          $table_singular_name = kw_singularize($this->extrapolate_table());
          $conditions[$table_singular_name . "_id"] = $this->data["id"];
          return $new_class->find_by($conditions);
        } else if(is_array($this->relationships["belongs_to"]) && in_array($name, $this->relationships["belongs_to"])) {
          $parent_singular_name = kw_singularize($name);
          $conditions["id"] = $this->data[$parent_singular_name . "_id"];
          return $new_class->find_by($conditions);
        }
      }
    } else if(array_key_exists($name, $this->instance_data)) { // check instance data
      return $this->instance_data[$name];
    } else {
      return NULL;  
    }
  }
  
  /*
    ###########################################################
              UPDATE FUNCTIONS
    ###########################################################
  */
  
  /*
    RETURNS THE DATA SET FOR THIS OBJECT
  */
  public function data() {
    return $this->data;
  }
  
  /*
    UPDATE THE DATA FIELDS IN THE OBJECT TO SPECIFIED HASH
  */
  public function update($update_hash=array()) {
    foreach ($update_hash as $key => $value) {
      $this->data[$key] = $value;
    }
  }
  
  /*
    ALIAS FOR UPDATE
  */
  public function update_data($update_hash=array()) {
    $this->update($update_hash);
  }
  
  
  /*
    ###########################################################
              SEARCH FUNCTIONS
    ###########################################################
  */
  
  /*
    SEARCH FUNCTIONS, FIND
  */
  public function find($id, $conditions = array()) {
    $conditions["id"] = $id;
    $output = $this->find_by($conditions);
    return $output;
  }
  
  /*
    SEARCH FUNCTIONS, FIND BY CONDTIONS
  */
  public function find_by($conditions = array()) {
    
    // globals
    global $kw_config;
    
    // SET TABLE
    $table = $this->get_table(); // get table
    $result = NULL;
    
    // construct query
    if($conditions != NULL && $conditions != "" && $table != NULL && $table != "") {

      // needed for mysql_real_escape_string
      $innerConn = new mysqli($kw_config->site_database_server, $kw_config->site_database_username, $kw_config->site_database_password);
      
      // make sure connection valid
      if(!mysqli_connect_errno()) {
        $query = "SELECT * FROM " . $table . " WHERE 1=1 "; 
        if((count($conditions) > 0) && $this->validate_data_types($conditions,$innerConn)) {
          $query .= " AND" . $this->generate_params_clause($conditions,$innerConn);
        }
        $query .= ";";
        $result = $this->runQuery($query);
        
        // encapsulate data
        if(count($result) > 0) {
          $output =  $this->encapsulate($result[0]); // save result in new model of same type
        } else {
          $output = array(); // empty set
        }
        
        // close connection, we'll use another to execute
        $innerConn->close();
      }
    }
    
    return $output;
  }

  /*
    FIND ALL
    Returns all that meet the criteria specified in $opts
    @param $conditions parameters of query
    @param $filters MIN/MAX/ORDER_BY/DESC
  */
  public function find_all($conditions = array(),$filters = array()) {
    
    // globals
    global $kw_config;
    
    // SET TABLE
    $table = $this->get_table(); // get table
    $result = NULL;
    
    if($table != NULL && $table != "") {
      
      // needed for mysql_real_escape_string
      $innerConn = new mysqli($kw_config->site_database_server, $kw_config->site_database_username, $kw_config->site_database_password);
      
      // make sure connection valid
      if(!mysqli_connect_errno()) {
      
        $query = "SELECT * FROM " . $table;
        
        // WHERE CLAUSE
        if($this->validate_data_types($conditions) && $conditions != NULL && $conditions != array()) {  
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
      if(count($result) > 0) {
        $output = $this->encapsulate($result); // save result in new model of same type
      } else {
        $output = array(); // empty set
      }

    }

    return $output;
  }
  
  /*
    ALL
    Alias for find_all()
    @param $conditions parameters of query
    @param $filters MIN/MAX/ORDER_BY/DESC
  */
  public function all($condtions=array(),$filters = array()) {
    return $this->find_all($condtions,$filters);
  }
  
  /*
    FIRST
    Returns the first match
    @param $conditions parameters of query
    @param $filters MIN/MAX/ORDER_BY/DESC
  */
  public function first($condtions=array(),$filters = array()) {
    $filters["min"] = 0;
    $filters["max"] = 1;
    return $this->find_all($condtions,$filters);
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

    // globals
    global $kw_config;
    
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
      $innerConn = new mysqli($kw_config->site_database_server, $kw_config->site_database_username, $kw_config->site_database_password);
      
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
      
      // encapsulate data
      $output = $this->encapsulate($result); // save result in new model of same type

    } else { 
      die($datatypes_valid["error"]);
    }
    
    // return result
    return $output;
  }
  
  /*
    DELETE
    Deletes the model from the database based on id
    @param $id id of model to remove
    @param $data additional data to match entry to delete
    @returns whether or not entry successfully deleted
  */
  public function delete($id,$conditions = array()) {
        
    // globals
    global $kw_config;
        
    // SET TABLE
    $table = $this->get_table();
    
    if(is_numeric($id) && $id != "" && $id != NULL) { 
    
      // needed for mysql_real_escape_string
      $innerConn = new mysqli($kw_config->site_database_server, $kw_config->site_database_username, $kw_config->site_database_password);
      
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
    HAS FIELD
    Declares a field for the model at construct, initializes it
    @param field = Field name to initialize, required
    @param properties = Field Options, required
  */
  public function has_field($field,$properties) {
    if($field != "" && $field != NULL && is_array($properties)) {
      $this->fields[$field] = $properties;
    }
  }
  
  
  /*
    ###########################################################
                RELATIONSHIP FUNCTIONS
    ###########################################################
  */
  
  /*
    Pushes array of dependent model names to this model
    @param $models = Array of models to push, if single string, pushes only that model
  */
  public function has_many($models) {
    if(!is_array($this->relationships["has_many"])) {
      $this->relationships["has_many"] = array();  
    }
    if(is_array($models)) {
      foreach($models as $i => $m ) {
        array_push($this->relationships["has_many"],$m);
      }
    } else if(is_string($models)) {
      array_push($this->relationships["has_many"],$models); // push single model
    }
  }
  
  /*
    Pushes of one dependent model names to this model
    @param $models = Array of models to push, if single string, pushes only that model
  */
  public function has_one($model) {
    if(!is_array($this->relationships["has_one"])) {
      $this->relationships["has_one"] = array();  
    }
    if(is_string($model)) {
      array_push($this->relationships["has_one"],$model);
    }
  }
  
  /*
    Pushes array of parent model names to this model
    @param $models = Array of models to push, if single string, pushes only that model
  */
  public function belongs_to($models) {
    if(!is_array($this->relationships["belongs_to"])) {
      $this->relationships["belongs_to"] = array();  
    }
    if(is_array($models)) {
      foreach($models as $i => $m ) {
        array_push($this->relationships["belongs_to"],$m);
      }
    } else if(is_string($models)) {
      $m = $models; // push single model
      array_push($this->relationships["belongs_to"],$m);
    }
  }
  
  
  /*
    ###########################################################
              PRIVATE FUNCTIONS
    ###########################################################
  */
  
  private function validate_data_types($conditions) {
    $output = array();
    $output["status"] = true;
    if($conditions != NULL && $conditions != "" && $conditions != array()) {
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
    } else {
      $output["status"] = false;  
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
    $name = kw_pluralize($name);
    $table = strtolower(preg_replace("/[^a-zA-Z0-9\s]/", "_", $name));
    
    // return name
    return $table;
  }
  
  /* 
    RETURN THE EXTRAPOLATED MODEL CLASS NAME THAT KRAFTWERK WILL ATTEMPT TO ACCESS
    returns the extrapolated model class name
    @param $name = Name of database table or underscore variable to convert
    @returns $String returns the extrapolated model class name
  */
  private function extrapolate_model_class($name) {
    $name = kw_singularize($name);
    $words = explode("_", strtolower($name));
    $model_class = "";
    foreach ($words as $word) {
      $model_class .= ucfirst(trim($word));
    }
    return $model_class;
  }
  
  /*
    ENCAPSULATES THE QUERY RESULT IN A NEW CLASS INSTANCE 
    @param $result = Query result to populate new class with
    @returns $Array returns class or array of classes
  */
  private function encapsulate($result) {
    $model_class_name = get_class($this);
    if(is_object($result)) {
      $model_instance = new $model_class_name(); // get classname
      foreach($result as $field_name => $field_value) {
        $model_instance->$field_name = $field_value;
      }
      return $model_instance;
    } else if(is_array($result)) {
      $results = $result; unset($result); // move to results and unset
      $model_instances_array = array();
      foreach($results as $result) {
        if(is_object($result)) { // must be an object
          $model_instance = new $model_class_name(); // get classname
          foreach($result as $field_name => $field_value) {
            $model_instance->$field_name = $field_value;
          }
          array_push($model_instances_array,$model_instance);
        }
      }
      return $model_instances_array;
    }
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
  
  /*
    ###########################################################
              VAIDATION FUNCTIONS
    ###########################################################
  */

  // VALIDATE FORMAT OF STRING
  public function validate_format($strIn,$format) {
    $output = false;
    if(preg_match($format, $strIn)) {
      $output = true;
    }  
    return $output;
  }
  
  // CHECK THE DATA AGAINST THE SPECIFIED VALIDATORS
  public function validate_data($data) {
    $output = true;
    $this->field_errors = array(); // reset field errors
    $formats = $this->validators->get_formats();
    $failed_count = 0;

    foreach($data as $key => $value) {
      if(isset($formats["$key"]) && $formats["$key"] != "" &&  $formats["$key"] != NULL) {      
        if(!$this->validate_format($value,$formats["$key"])) {
          $this->field_errors[$failed_count] = $key; 
          $output = false;
          $failed_count++;
        }
      }
    }
    return $output;
  }
  
  /// ALIAS FOR validate_data()
  public function validate($data) {
    return $this->validate_data($data);
  }
  
  // RENDER ERROR MESSAGES
  public function validation_errors() {
    $errors = array();
    foreach($this->field_errors as $index => $field) {
      $errors[$index]  = "The format of " . $field . " is not valid.";
    }
    return $errors;
  }

  
}
?>