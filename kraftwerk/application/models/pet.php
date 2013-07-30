<?php
/*
	This is an example Model file for testing Kraftwerk's MVC system to make sure it operates correctly. 
	In Beta, this will be replaced with a simpler version
*/
class Pet extends KraftwerkModel {

	function get_name() {
		$this_pet = $this->find(1);
		return $this_pet[0]["name"];
	}
	
	function all_pets() {
		return $this->find_all();
	}
	
}

?>