<?php
/* 

##############################################################################

	KRAFTWERK DATABASE CLASS 
	
	This class is used as a abstraction layer in establishing database
	connections. Everytime we run a query, we do it through this
	class so we can open a connection ONLY when it is needed.
	
	Future plans to extend this class using MySQLi
	
##############################################################################
*/
class MySQLConnector {
	
	// CONTROL VARIABLES FOR THIS CONNECTOR
	protected $DB_HOST	 = "";
	protected $DB_USERNAME = "";
	protected $DB_PASSWORD = "";
	protected $DB_SCHEMA	 = "";

	// ERROR CODE HANDLING
	protected $status 		= 0;
	protected $statusCodes 	= array();
	
	// STORE
	var $LAST_QUERY = ""; // stores last query so it can be referenced later.

	/* 
		CONSTRUTOR
		@param $host = host address for mysql server
		@param $user = database username for login
		@param $pass = database password for login
		@param $schema = (optional) default scheme for database.
	*/
	public function __construct($host='',$user='',$pass='',$schema='') { // schema is optional
	
		global $kw_config;

		// SET LOGIN STATUS
		$this->DB_HOST	 	= $host;
		$this->DB_USERNAME 	= $user;
		$this->DB_PASSWORD 	= $pass;
		$this->DB_SCHEMA 	= $schema;
		
		if($this->DB_HOST == "") 		{ $this->DB_HOST = $kw_config->site_database_server; }
		if($this->DB_USERNAME == "") 	{ $this->DB_USERNAME = $kw_config->site_database_username; }
		if($this->DB_PASSWORD == "") 	{ $this->DB_PASSWORD = $kw_config->site_database_password; }
		if($this->DB_SCHEMA == "") 		{ $this->DB_SCHEMA = $kw_config->site_database_schema; }

		// SET ERROR CODES
		$this->statusCodes[0] = "No Errors, Database Connector is Idle";
		$this->statusCodes[1] = "Database Failed to Connect, Check Login Credentials";
		$this->statusCodes[2] = "Query Result is Blank";
		$this->statusCodes[3] = "Query Result Failed to Parse";
		$this->statusCodes[4] = "Query Failed";
		$this->statusCodes[5] = "Query Successfully Run and Parsed";

	}

	/*
		BASIC RUN QUERY FUNCTION
		@param $query = Query to run
		@returns Query result
	*/
	public function runQuery($query) {
		
		// clear status
		$this->status = 0;

		// create connection
		$innerConn = new mysqli($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD);
		
		// check connection
		if(!mysqli_connect_errno()) {

			$innerConn->select_db($this->DB_SCHEMA); // select database
			$queryResult = $innerConn->query($query);

			if($queryResult) { // run query
				if($parsedResult = $this->parseResult($queryResult)) {
					$this->status = 5; // query successful
				} else {
					$this->status = 3; // query failed to parse
				}
				$queryResult->close(); // close result*/
			} else {
				$this->status = 4; // query failed
				$this->statusCodes[4] .= ": " . $innerConn->error; // append error
			} 
			
		} else {
			$this->status = 1; // connection failed
			$this->statusCodes[1] .= ": " . $innerConn->connect_error; // append error
		}
		
		// close database connection
		$innerConn->close();
		
		// STORE LAST QUERY
		$this->LAST_QUERY = $query;

		// RETURN RESULT
		return $parsedResult; // return the result
	}
	
	/*
		GET FIELD TYPES OF A TABLE
		@param $table = Table to get fields for
		@returns Query result
	*/
	protected function getFields($table) { 
		$result = $this->runQuery("DESCRIBE " . $table . ";"); // describe table query
		foreach($result as $field) { // extract field info from result
			$type = $field->Type; // field type
			$name = $field->Field; // field name
			$this->fields[$name] = array();
			if(strpos($type,"(")) {
				$this->fields[$name]["type"] = substr($type,0,strpos($type,"(")); // strip the size off
				$this->fields[$name]["size"] = intval(substr($type,strpos($type,"(")+1,strpos($type,")"))); // strip the type off, convert to int
			} else {
				$this->fields[$name]["type"] = $type; // strip the size off
			}
		}
		return $this->fields; // return field info
	}

	/*
		CONVERT A QUERY RESULT INTO AN ARRAY
		@param $result = Result to convert
		@returns $Array with result;
	*/
	private function parseResult($result) {

		// SET RETURN TYPE TO ARRAY
        $parsedOut = array();

		// CHECK VALIDITY OF RESULT
        if(!$result){ // test to see if result exists
			$this->status = 2; // query is blank
        } else { 
			$row = 0;
			while ($obj = $result->fetch_object()) {
				$parsedOut[$row] = $obj; // push result
				$row++;
			}
		}

		// RETURN PARSED QUERY RESULT
		return $parsedOut;
	}

	/* 
		UTILITY FUNCTION, RETURNS LAST STATUS CODE DESCRIPTION
	*/
	public function getStatus() {
		return $this->statusCodes[$this->status];
	}

	/* 
		UTILITY FUNCTION, RETURNS LAST STATUS CODE
	*/
	public function getStatusCode() {
		return $this->status;
	}

}

?>