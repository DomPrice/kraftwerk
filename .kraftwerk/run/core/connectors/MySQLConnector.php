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

	// ERROR CODE HANDLING
	protected $status 		= 0;
	protected $statusCodes 	= array();
	
	// STORED DB USERNAME AND PASS
	private $db_server   = NULL;
	private $db_username = NULL;
	private $db_password = NULL;
	private $db_schema   = NULL;
	
	// STORE
	var $LAST_QUERY = ""; // stores last query so it can be referenced later.

	/* 
		CONSTRUTOR
		@param $host = host address for mysql server
		@param $user = database username for login
		@param $pass = database password for login
		@param $schema = (optional) default scheme for database.
	*/
	public function __construct($db_server="", $db_username="", $db_password="", $db_schema="") { // schema is optional

		// SET ERROR CODES
		$this->statusCodes[0] = "No Errors, Database Connector is Idle";
		$this->statusCodes[1] = "Database Failed to Connect, Check Login Credentials";
		$this->statusCodes[2] = "Query Result is Blank";
		$this->statusCodes[3] = "Query Result Failed to Parse";
		$this->statusCodes[4] = "Query Failed";
		$this->statusCodes[5] = "Query Successfully Run and Parsed";
		
		// SAVE CREDENTIALS
		$db_server 		!= "" ? $this->db_server 	= $db_server 	: $this->db_server = "" ;
		$db_username 	!= "" ? $this->db_username 	= $db_username 	: $this->db_username = "" ;
		$db_password 	!= "" ? $this->db_password 	= $db_password 	: $this->db_password = "" ;
		$db_schema 		!= "" ? $this->db_schema	= $db_schema 	: $this->db_schema = "" ;

	}

	/*
		BASIC RUN QUERY FUNCTION
		@param $query = Query to run
		@returns Query result
	*/
	public function runQuery($query) {
		
		// globals
		global $kw_config;
		
		// clear status
		$this->status = 0;

		// create connection
		$credentials = $this->getCredentials();
		$innerConn = new mysqli($credentials->db_server, $credentials->db_username, $credentials->db_password);
		
		// check connection
		if(!mysqli_connect_errno()) {

			$innerConn->select_db($credentials->db_schema); // select database
			$queryResult = $innerConn->query($query);

			if($queryResult) { // run query
				
				// parse result
				if(method_exists($queryResult,"fetch_object")) {
					if($parsedResult = $this->parseResult($queryResult)) {
						$this->status = 5; // query successful
					} else {
						$this->status = 3; // query failed to parse
					}
					if(method_exists($queryResult,"close")) {
						$queryResult->close(); // close result
					}
				} else {
					$this->status = 2; // query returned empty recordset
					$this->statusCodes[2] .= ": Query executed but returned no results. Continuing normally.";
				}
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
		} else if(!method_exists($result,"fetch_object")) {  // check if he fetch object method exists
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
		UTILITY FUNCTION, RETURNS EITHER KRAFTWERK'S DB CREDENTIALS SETTINGS OR THE ONES USED AT CONSTRUCT
		This allows the MySQLConnector to be used standalone.
	*/
	private function getCredentials() {
		global $kw_config;
		$creds = new StdClass();
		
		// use native credentials, or attempt to use site login.
		$this->db_server 	!= "" ? $creds->db_server 	= $this->db_server 		: $creds->db_server = $kw_config->site_database_server;
		$this->db_username 	!= "" ? $creds->db_username = $this->db_username 	: $creds->db_username = $kw_config->site_database_username;
		$this->db_password 	!= "" ? $creds->db_password = $this->db_password 	: $creds->db_password = $kw_config->site_database_password;
		$this->db_schema 	!= "" ? $creds->db_schema	= $this->db_schema 		: $creds->db_schema = $kw_config->site_database_schema;

		return $creds;
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