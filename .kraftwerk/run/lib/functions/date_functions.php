<?php

/*
  UTILITY FUNCTION: MAKE SURE IS VALID MYSQL DATE
*/
function kw_ismysqldate($strIn) {
  return preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $strIn);
}

/*
  UTILITY FUNCTION: MAKE SURE IS VALID MYSQL DATETIME
*/
function kw_ismysqldatedime($strIn) {
  return preg_match("/^\d{4}-\d{2}-\d{2} [0-2][0-3]:[0-5][0-9]:[0-5][0-9]$/", $strIn);
}

?>