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
function kw_form_select($itemArray,$valueVar,$optionVar,$default="") {
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
function kw_form_select_years($start,$end,$default) {
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
function kw_form_select_months($year,$month,$default) {
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
function kw_form_select_monthdays($year,$month,$default) {
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