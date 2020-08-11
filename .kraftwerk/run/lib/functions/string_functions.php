<?php

/* 
###############################################################################

	KRAFTWERK STRING LIB
	
	String manipulation functions that can be used in the framework.
	Core functions use these, so do not delete or change this file.

###############################################################################
*/

/*
	UTILITY FUNCTION: Sanitizes a string into plain text, alpha numeric string
*/
function kw_sanitize_str($strIn) {
	return kw_alphanum_str(strip_tags($strIn));
}

/*
	UTILITY FUNCTION: Check to see if String is Alpha-Numeric
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
	$output = str_replace(" ","_",$strIn);
	$output = preg_replace('/[^a-zA-Z0-9_\-]/i',"",$output);
	return $output;
}

/*
	UTILITY FUNCTION: FILTER OUT ALL NON ALPHA NUMERIC/SPACE CHARACTERS
*/
function kw_alphanumspaces_str($strIn) {
	$output = preg_replace('/[^a-zA-Z0-9 \-]/i',"",$strIn);
	return $output;
}

/*
	UTILITY FUNCTION: FORMAT PHONE NUMBER
*/
function kw_usphonenumber_str($strIn) {
	$num = preg_replace('/[^0-9]/', '', $num);
	$len = strlen($num);
	if($len == 7) {
		$num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
	} elseif($len == 10) {
		$num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);
	}
	return $num;
}

/*
	UTILITY FUNCTION: MAKE SURE PASSWORD IS ALPHA NUMERIC + !@#$%^&*()
*/
function kw_isemail($strIn) {
	$strIn = strtolower($strIn);
	return kw_validate_format($strIn,"^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$");
}

/*
	UTILITY FUNCTION: VALIDATE FORMAT
*/
function kw_validate_format($strIn,$format) {
	$output = false;
	if(preg_match($format, $strIn)) {
		$output = true;
	}	
	return $output;
}

/*
	NOTE: kw_pluralize is modified from code by Bermi Ferrer Martinez 
*/
function kw_pluralize($word){
	$plural = array(
		'/(quiz)$/i'               => '\1zes',
		'/^(ox)$/i'                => '\1en',
		'/([m|l])ouse$/i'		   => '\1ice',
		'/(matr|vert|ind)ix|ex$/i' => '\1ices',
		'/(x|ch|ss|sh)$/i'		   => '\1es',
		'/([^aeiouy]|qu)ies$/i'	   => '\1y',
		'/([^aeiouy]|qu)y$/i'	   => '\1ies',
		'/(hive)$/i'			   => '\1s',
		'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
		'/sis$/i'				   => 'ses',
		'/([ti])um$/i'			   => '\1a',
		'/(buffal|tomat)o$/i'	   => '\1oes',
		'/(bu)s$/i'				   => '\1ses',
		'/(alias|status)/i'		   => '\1es',
		'/(octop|vir)us$/i'		   => '\1i',
		'/(ax|test)is$/i'		   => '\1es',
		'/s$/i'					   => 's',
		'/$/'					   => 's');

	$uncountable = array('equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep');

	$irregular = array(
		'person' => 'people',
		'man'	=> 'men',
		'child'  => 'children',
		'sex'	=> 'sexes',
		'move'   => 'moves');

	$lowercased_word = strtolower($word);

	foreach($uncountable as $_uncountable){
		if(substr($lowercased_word, (-1 * strlen($_uncountable))) == $_uncountable){
			return $word;
		}
	}

	foreach($irregular as $_plural => $_singular){
		if(preg_match('/(' . $_plural . ')$/i', $word, $arr)){
			return preg_replace('/(' . $_plural . ')$/i', substr($arr[0], 0, 1) . substr($_singular, 1), $word);
		}
	}

	foreach($plural as $rule => $replacement){
		if(preg_match($rule, $word)){
			return preg_replace($rule, $replacement, $word);
		}
	}
	
	return false;
} 

function kw_singularize($params) {
	
    if (is_string($params)) {
        $word = $params;
    } else if (!$word = $params['word']) {
        return false;
    }

    $singular = array (
        '/(quiz)zes$/i' => '\\1',
        '/(matr)ices$/i' => '\\1ix',
        '/(vert|ind)ices$/i' => '\\1ex',
        '/^(ox)en/i' => '\\1',
        '/(alias|status)es$/i' => '\\1',
        '/([octop|vir])i$/i' => '\\1us',
        '/(cris|ax|test)es$/i' => '\\1is',
        '/(shoe)s$/i' => '\\1',
        '/(o)es$/i' => '\\1',
        '/(bus)es$/i' => '\\1',
        '/([m|l])ice$/i' => '\\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\\1',
        '/(m)ovies$/i' => '\\1ovie',
        '/(s)eries$/i' => '\\1eries',
        '/([^aeiouy]|qu)ies$/i' => '\\1y',
        '/([lr])ves$/i' => '\\1f',
        '/(tive)s$/i' => '\\1',
        '/(hive)s$/i' => '\\1',
        '/([^f])ves$/i' => '\\1fe',
        '/(^analy)ses$/i' => '\\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
        '/([ti])a$/i' => '\\1um',
        '/(n)ews$/i' => '\\1ews',
        '/s$/i' => ''
    );

    $irregular = array(
        'person' => 'people',
        'man' => 'men',
        'child' => 'children',
        'sex' => 'sexes',
        'move' => 'moves'
    );	

    $ignore = array(
        'equipment',
        'information',
        'rice',
        'money',
        'species',
        'series',
        'fish',
        'sheep',
        'press',
        'sms',
    );

    $lower_word = strtolower($word);
    foreach($ignore as $ignore_word) {
        if(substr($lower_word, (-1 * strlen($ignore_word))) == $ignore_word) {
            return $word;
        }
    }

    foreach($irregular as $singular_word => $plural_word) {
        if(preg_match('/('.$plural_word.')$/i', $word, $arr)) {
            return preg_replace('/('.$plural_word.')$/i', substr($arr[0],0,1).substr($singular_word,1), $word);
        }
    }

    foreach($singular as $rule => $replacement) {
        if(preg_match($rule, $word)) {
            return preg_replace($rule, $replacement, $word);
        }
    }

    return $word;
}

/*
	UTILITY FUNCTION: PARSE YAML
	This uses the third party SPYC dependency
	@param $str = string, can be filename or string: NOTE: Kraftwerk will always assume file is from website root unless specified.
*/
function kw_parse_yaml($str) {
	if(is_file($str)) {
		$filename = $str;
		if(file_exists($filename)) {
			return Spyc::YAMLLoad($filename);
		}
	} else {
		return Spyc::YAMLLoadString($str);
	}
}

?>