<?php

/* 
###############################################################################

  KRAFTWERK FORM LIB

  Shortcuts to use for dynamic forms

###############################################################################
*/


/*
  GENERATE INTERFACE LIST
  This is useful in generating options for a select list
*/
function kw_form_select($itemArray,$valueVar,$optionVar,$options=array()) {
  $SPECIAL_CHARS = get_html_translation_table(HTML_ENTITIES); // translate special characters
  $optionsHTML = "<option value=\"\">--</options>\n";
  for($i=0; $i<count($itemArray); $i++) {
    if($itemArray[$i][$valueVar] == $options["default"]) {
      $selected = " selected";
    } else {
      $selected = "";
    }
    $optionsHTML .= "<option value=\"" . $itemArray[$i][$valueVar] . "\"" . $selected . ">" . strtr($itemArray[$i][$optionVar],$SPECIAL_CHARS) . "</option>\n";
  }
  return $optionsHTML;
}

/*
  GENERATE YEAR LIST
  This is useful in generating options for a years list
*/
function kw_form_select_years($start,$end,$options=array()) {
  $optionsHTML = "<option value=\"\">--</options>\n";
  for($i=intval($start); $i<=intval($end); $i++) {
    if(intval($i) == intval($options["default"])) {
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
function kw_form_select_months($options=array()) {
  if($options["long"] == true) {
    $monthNames    = array("January","February","March","April","May","June","July","August","September","October","November","December");
  } else if($options["short"] == true) {
    $monthNames    = array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
  } else {
    $monthNames    = array(1,2,3,4,5,6,7,8,9,10,11,12);
  }
  $optionsHTML   = "<option value=\"\">--</options>\n";
  for($i=0; $i<intval(12); $i++) {
    if(intval($i+1) == intval($options["default"])) {
      $selected = " selected";
    } else {
      $selected = "";
    }
    $optionsHTML .= "<option value=\"" . intval($i+1) . "\"" . $selected . ">" . $monthNames[$i] . "</option>\n";  
  }
  return $optionsHTML;
}

/*
  GENERATE DAY LIST
  This is useful in generating options for a monthly day list
*/
function kw_form_select_monthdays($month="",$options=array()) {
  $monthDays    = array(31,28,31,30,31,30,31,31,30,31,30,31);
  if($month != "") {
    $getDayCount  = $monthDays[intval($month)-1];
  } else {
    $getDayCount  = 31; // default to 31
  }
  $optionsHTML   = "<option value=\"\">--</options>\n";
  for($i=1; $i<=intval($getDayCount); $i++) {
    if($i == intval($options["default"])) {
      $selected = " selected";
    } else {
      $selected = "";
    }
    $optionsHTML .= "<option value=\"" . $i . "\"" . $selected . ">" . $i . "</option>\n";  
  }
  return $optionsHTML;
}

/*
  UTILITY FUNCTION: STRIP ALL HTML FROM FIELDS, WORKS ON $_GET/$_POST
*/
function kw_strip_tags_from_fields($fields) {
  $fields_out = array();
  foreach ($fields as $key => $value) {
    $fields_out[$key] = strip_tags($value);
  }
  return $fields_out;
}

?>