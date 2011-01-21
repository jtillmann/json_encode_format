<?php


function json_encode_format( $array )
{
	$json = json_encode($array);


	// Newline before and after all closing brackets
	$json = preg_replace('/([\]\}]\,?)/', "\n$1\n", $json);

	// Newline after all opening brackets
	$json = preg_replace('/([\[\{])/', "$1\n", $json);

	// Newline after all commata which are not following a bracket closing
	$json = preg_replace('/([^\}\]]\,)/', "$1\n", $json);

	// Whitespace removed between empty brackets
	$json = preg_replace('/\[[ \t\n]*\]/m', '[]', $json);




	// Find the longest attribute in the entire json element
	preg_match_all('/\".*?\"/i', $json, $matches);

	// To achieve unified value indentation we calculate the distance
	// between the beginning of an attribute and the beginning of the value
	// by making sure it has enough space for even the longest attribute
	$tab_width = ceil(max(array_map('strlen', $matches[ 0 ]))/4)*4 + 4;




	$json_lines 	= explode("\n", $json);
	$depth 			= 0;

	foreach( $json_lines as $index => $line ){

		$trim = ltrim( rtrim( $line, ", \t\n\r\0" ) );

		// Any closing bracket, that's not an empty bracket element reduces the depth
		if( in_array(substr($trim, -1), array('}', ']', '},', '],') ) && substr($trim, -2) != '[]'){
			$depth--;
		}
	
		// Find any line that contains a "key": "value" combination
		if( preg_match('/([ \t]*)(\"(.*?)\"\:)(.*)/i', $line, $matches) ){
		
			// Determine the necessary number of tabs to insert
			$variable 	= $matches[2];
			$tabs 		= ceil( max(1, $tab_width - strlen($variable) )/4);
		
			// Recompose the line by adding the tabs inbetween
			$line =  
				$matches[ 2 ] . 
				str_repeat('	', $tabs) .
				$matches[ 4 ];
		}
	
		// The line is suffixed by the necessary number of tabs
		$json_lines[ $index ] = str_repeat('	', $depth) . $line;
	
		// Every opening bracket increases the depth for the following lines
		if( in_array(substr($trim, -1), array('{', '[') ) ){
			$depth++;
		}
	
	}

	$json = implode("\n", $json_lines);
	
	return $json;
}

