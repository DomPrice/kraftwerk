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

		// CONNECT
		if($innerConn = mysql_connect($this->DB_HOST,$this->DB_USERNAME,$this->DB_PASSWORD)) { // establish a connection for this query
			mysql_select_db($this->DB_SCHEMA); // select database

			// DO QUERY AND PARSE IT
			if($queryResult = mysql_query($query)) {
				if($parsedResult 	= $this->parseResult($queryResult)) {
					$this->status = 5; // query successful
				} else {
					$this->status = 3; // query failed to parse
				}
			} else {
				$this->status = 4; // query failed
			}

			// CLOSE CONNECTIONS
			@mysql_free_result($queryResult);
			mysql_close($innerConn); // close connection

		} else {
			$this->status = 1; // connection failed
		}
		
		// STORE LAST QUERY
		$this->LAST_QUERY = $query;

		// RETURN RESULT
		return $parsedResult; // return the result
	}

	/*
		CONVERT A QUERY RESULT INTO AN ARRAY
		@param $result = Result to convert
		@returns $Array with result;
	*/
	private function parseResult($result) {

		// SET RETURN TYPE TO ARRAY
        settype($parsedOut,"array");

		// CHECK VALIDITY OF RESULT
        if(!$result){ // test to see if result exists
			$this->status = 2; // query is blank
        } else { 
			// PARSE RESULT
			for($i=0; $i<@mysql_numrows($result); $i++){
				for($j=0;$j<@mysql_num_fields($result);$j++){
					$parsedOut[$i][@mysql_field_name($result,$j)] = @mysql_result($result,$i,@mysql_field_name($result,$j));
				}
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