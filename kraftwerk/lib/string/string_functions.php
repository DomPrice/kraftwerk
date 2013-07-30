<?php

/* 
###############################################################################

	KRAFTWERK STRING LIB
	
	String manipulation functions that can be used in the framework.

###############################################################################
*/

/*
	UTILITY FUNCTION: Sanitizes a string into plain text, alpha numeric string
*/
function kw_sanitize_str($strIn) {
	return kw_alphanum_str(strip_tags($strIn));
}

/*
	UTILITY FUNCTION: Makes string alpha numeric
*/ 
function kw_isalphanum($strIn) {
	$output = false;
	if(!preg_match('/[^a-zA-Z0-9_\-]/i',$strIn)) {
		$output = true;
	}	
	return $output;
}

/*
	UTILITY FUNCTION: Makes string alpha numeric
*/ 
function kw_alphanum_str($strIn) {
	$strIn = preg_replace('/[^a-zA-Z0-9\-]/i',"",$strIn);
	return $output;
}

/*
	UTILITY FUNCTION: FILTER OUT ALL NON ALPHA NUMERIC/SPACE CHARACTERS
*/
function kw_alphanumspaces_str($strIn) {
	$strIn = preg_replace('/[^a-zA-Z0-9 \-]/i',"",$strIn);
	return $strIn;
}

/*
	UTILITY FUNCTION: MAKE SURE PASSWORD IS ALPHA NUMERIC + !@#$%^&*()
*/
function kw_isemail($strIn) {
	$output = false;
	$strIn = strtolower($strIn);
	if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $strIn)) {
		$output = true;
	}	
	return $output;
}


?>