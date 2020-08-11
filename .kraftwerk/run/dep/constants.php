<?php
/*
	KRAFTWERK GLOBAL CONSTANTS
*/

define('KRAFTWERK_VERSION','2.1.0.1');

define('FORMAT_EMAIL','/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/');
define('FORMAT_US_PHONE','/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/');
define('FORMAT_ALPHA_NUMERIC','/^[a-zA-Z]+[a-zA-Z0-9_-]+$/');
define('FORMAT_ALPHA_NUMERIC_SPACES','/^[a-zA-Z0-9 _-]*$/');
define('FORMAT_MYSQL_DATE','/\d{4}-[01]\d-[0-3]\d$/');
define('FORMAT_MYSQL_DATETIME','/\d{4}-[01]\d-[0-3]\d [0-2]\d:[0-5]\d:[0-5]\d/');
define('FORMAT_ZIPCODE','/^([0-9]{5})(-[0-9]{4})?$/i');

?>
