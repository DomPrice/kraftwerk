<?php
/* 

#####################################################
	KRAFTWERK UTILITY CLASS 
	
	This is the control class for the kraftwerk utility class, it provides
	common functions that other classes use.


#####################################################
*/
class KraftwerkUtility {

	// DATA CONNECTOR
	protected $databaseConnector = "";

	/* 
		CONSTRUTOR
	*/
	public function __construct() { 

		// ESTABLISH USER CONNECTOR, WE NEED TO GRAB THE MASTER DATABASE DATA FROM THE $GLOBAL ENVIRONMENT
		$login_user 	= $GLOBALS['kraftwerk']->CONFIG["content_database_username"];
		$login_pass 	= $GLOBALS['kraftwerk']->CONFIG["content_database_password"]; 
		$login_host 	= $GLOBALS['kraftwerk']->CONFIG["content_database_server"];
		$login_schema 	= $GLOBALS['kraftwerk']->CONFIG["content_database_schema"];
		$this->databaseConnector = new DatabaseConnector($login_host, $login_user, $login_pass, $login_schema);

	}

	/*
		UTILITY FUNCTION: MAKE SURE TEXT IS ALPHA NUMERIC
	*/
	public function safeAlphaNum($strIn) {
		$output = false;
		if(!preg_match('/[^a-zA-Z0-9_\-]/i',$strIn)) {
			$output = true;
		}	
		return $output;
	}

	/*
		UTILITY FUNCTION: MAKE SURE USERNAME IS ALPHA NUMERIC
	*/
	public function safeUsername($strIn) {
		$output = false;
		if(!preg_match('/[^a-zA-Z0-9_\-]/i',$strIn)) {
			$output = true;
		}	
		return $output;
	}
	
	/*
		UTILITY FUNCTION: MAKE SURE PASSWORD IS ALPHA NUMERIC + !@#$%^&*()
	*/
	public function safePassword($strIn) {
		$output = false;
		if(!preg_match('/[^a-zA-Z0-9_\-\!\@\#\$\%\^\&\*\(\)]/i',$strIn)) {
			$output = true;
		}	
		return $output;
	}

	/*
		UTILITY FUNCTION: MAKE SURE PASSWORD IS ALPHA NUMERIC + !@#$%^&*()
	*/
	public function safeEmail($strIn) {
		$output = false;
		$strIn = strtolower($strIn);
		if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $strIn)) {
			$output = true;
		}	
		return $output;
	}

	/*
		UTILITY FUNCTION: STRIP ALL HTML FROM FIELDS
	*/
	public function stripTagsFromFields($fields) {
		$fields_out = array();
		foreach ($fields as $key => $value) {
			$fields_out[$key] = strip_tags($value);
		}
		return $fields_out;
	}

	/*
		UTILITY FUNCTION: FILTER OUT ALL NON ALPHA NUMERIC/SPACE CHARACTERS
	*/
	public function filterAlphaNumSpaces($strIn) {
		$strIn = preg_replace('/[^a-zA-Z0-9 \-]/i',"",$strIn);
		return $strIn;
	}

	/*
		GENERATE INTERFACE LIST
		This is useful in generating options for a select list
	*/
	public function generateOptionList($itemArray,$valueVar,$optionVar,$default="") {
		$SPECIAL_CHARS = get_html_translation_table(HTML_ENTITIES); // translate special characters
		$optionsHTML = "<option value=\"\">--</options>\n";
		for($i=0; $i<count($itemArray); $i++) {
			if($itemArray[$i][$valueVar] == $default) {
				$selected = " selected";
			} else {
				$selected = "";
			}
			$optionsHTML .= "<option value=\"" . $itemArray[$i][$valueVar] . "\"" . $selected . ">" . strtr($itemArray[$i][$optionVar],$SPECIAL_CHARS) . "</options>\n";
		}
		return $optionsHTML;
	}

	/*
		GENERATE YEAR LIST
		This is useful in generating options for a years list
	*/
	public function generateYearOptionList($start,$end,$default) {
		$optionsHTML = "<option value=\"\">--</options>\n";
		for($i=intval($start); $i<=intval($end); $i++) {
			if(intval($i) == intval($default)) {
				$selected = " selected";
			} else {
				$selected = "";
			}
			$optionsHTML .= "<option value=\"" . $i . "\"" . $selected . ">" . $i . "</options>\n";	
		}
		return $optionsHTML;
	}

	/*
		GENERATE MONTH LIST
		This is useful in generating options for a month list
	*/
	public function generateMonthOptionList($year,$month,$default) {
		$monthNames		= array("January","February","March","April","May","June","July","August","September","October","November","December");
		$optionsHTML 	= "<option value=\"\">--</options>\n";
		for($i=0; $i<intval(12); $i++) {
			if(intval($i+1) == intval($default)) {
				$selected = " selected";
			} else {
				$selected = "";
			}
			$optionsHTML .= "<option value=\"" . intval($i+1) . "\"" . $selected . ">" . $monthNames[$i] . "</options>\n";	
		}
		return $optionsHTML;
	}

	/*
		GENERATE DAY LIST
		This is useful in generating options for a monthly day list
	*/
	public function generateDayOptionList($year,$month,$default) {
		$monthDays		= array(31,28,31,30,31,30,31,31,30,31,30,31);
		$getDayCount	= $monthDays[intval($month)];
		$optionsHTML 	= "<option value=\"\">--</options>\n";
		for($i=1; $i<=intval($getDayCount); $i++) {
			if($i == $default) {
				$selected = " selected";
			} else {
				$selected = "";
			}
			$optionsHTML .= "<option value=\"" . $i . "\"" . $selected . ">" . $i . "</options>\n";	
		}
		return $optionsHTML;
	}


}