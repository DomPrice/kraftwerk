<?php

class Example extends KraftwerkModel {

	function get_something() {
		$this->find(1);
		print "<br /><br />TEST IF CORE LIBS CAN BE ACCESSED BY INTERNAL CLASSES";
		
		$name = "wilford brimley";
		print "<br />Name: " . $name;
		print "<br />Name Cleaned: " . kw_sanitize_str($name); // passed
	}
	
}

?>