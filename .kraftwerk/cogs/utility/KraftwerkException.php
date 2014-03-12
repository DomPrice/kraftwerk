<?php
/* 
###############################################################################

  KRAFTWERK EXCEPTION CLASS
	
  Extends PHP's exception class to include kraftwerk framework related 
  error handling.
	
###############################################################################
*/
class KraftwerkException extends Exception {
	
	protected $logger = "";
	
	/*
		Exception Handler Constructor
	*/
	public function KraftwerkException($logger) {
		$this->logger = $logger; // fetch logger from main application
	}
}
?>