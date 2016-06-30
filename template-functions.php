<?php 
function template_magic_converter( $content, $vars, $basekey = '' ) {
	// 1) Parse if/else/_.each stuff
	// ?

	// 2) Parse all underscore stuff and replace w/ $vars array values.
	// Rudimentary example:
	foreach ( $vars as $varkey => $varvalue ) {
		if ( is_scalar( $varvalue ) ) {

			if ( $basekey ) {
				$varkey = $basekey . $varkey;
			}

			$content = str_replace( '{{ data.'. $varkey .' }}', $varvalue, $content );

		} elseif ( is_object( $varvalue ) || is_array( $varvalue ) ) {
			$content = template_magic_converter( $content, $varvalue, $varkey . '.' );
		}
	}

	return $content;
}

function get_template_content( $file ) {
	static $files = array();

	if ( ! isset( $files[ $file ] ) ) {
		ob_start();
		include_once $file;

		$files[ $file ] = ob_get_clean();
	}


	return $files[ $file ];
}

function template( $file, $vars ) {
	static $files = array();

	$content = get_template_content( $file );

	// @todo memoize output/vars combo.
	return template_magic_converter( $content, $vars );
}

$html = template( 'tmpl-gc-item-status.html', array(
	'id'           => 1,
	'item'         => 2,
	'mapping'      => 3,
	'status'      => array(
		'name'  => 'Status Name',
		'color' => 'red',
	),
) );

die( '<xmp>'. print_r( $html, true ) .'</xmp>' );