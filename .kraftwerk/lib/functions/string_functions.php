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

function kw_pluralize($string) {
	$plural = array(
		array( '/(quiz)$/i', "$1zes"),
		array( '/^(ox)$/i', "$1en"),
		array( '/([m|l])ouse$/i', "$1ice"),
		array( '/(matr|vert|ind)ix|ex$/i', "$1ices"),
		array( '/(x|ch|ss|sh)$/i', "$1es"),
		array( '/([^aeiouy]|qu)y$/i', "$1ies"),
		array( '/([^aeiouy]|qu)ies$/i', "$1y"),
		array( '/(hive)$/i', "$1s"),
		array( '/(?:([^f])fe|([lr])f)$/i', "$1$2ves"),
		array( '/sis$/i', "ses"),
		array( '/([ti])um$/i', "$1a"),
		array( '/(buffal|tomat)o$/i', "$1oes"),
		array( '/(bu)s$/i',		"$1ses"),
		array( '/(alias|status)$/i', "$1es"),
		array( '/(octop|vir)us$/i',	"$1i"),
		array( '/(ax|test)is$/i', "$1es"),
		array( '/s$/i', "s"),
		array( '/$/', "s")
	);
	$irregular = array(
		array( 'move',   'moves'    ),
		array( 'sex',    'sexes'    ),
		array( 'child',  'children' ),
		array( 'man',    'men'      ),
		array( 'person', 'people'   )
	);
	
	$uncountable = array('sheep','fish','series','species',
		'money','rice','information','equipment');
						
	// save some time in the case that singular and plural are the same
	if(in_array( strtolower( $string ), $uncountable )) {
		return $string;
	}
	
	// check for irregular singular forms
	foreach($irregular as $noun) {
		if(strtolower( $string ) == $noun[0]) {
			return $noun[1];
		}
	}
	
	// check for matches using regular expressions
	foreach($plural as $pattern) {
		if(preg_match( $pattern[0], $string)) {
			return preg_replace( $pattern[0], $pattern[1], $string );
		}
		return $string;
    }
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

?>