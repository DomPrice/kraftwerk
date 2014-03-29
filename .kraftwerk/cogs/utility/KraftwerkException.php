<?php
/* 
###############################################################################

  KRAFTWERK EXCEPTION CLASS
	
  Extends PHP's exception class to include kraftwerk framework related 
  error handling.
	
###############################################################################
*/
class KraftwerkException extends Exception {

	/*
		Exception Handler Constructor
	*/
	public function __construct() { }
	
	/*
		Throw Error 
	*/
	public function throw_error($error) {
		die($error);
	}
}
?>