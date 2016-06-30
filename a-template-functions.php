<?php

/**
 * Convert underscore template content.
 *
 * @param  string       $content HTML content
 * @param  array|object $vars    Array or object of variables to parse.
 * @param  string       $basekey Base key string when using recursively (for nested args).
 *
 * @return string                Converted HTML content.
 */
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

			$content = str_replace( array(
				'{{{ data.'. $varkey .' }}}',
				'{{ data.'. $varkey .' }}',
			), array(
				$varvalue,
				esc_html( $varvalue ),
			), $content );

		} elseif ( is_object( $varvalue ) || is_array( $varvalue ) ) {
			$content = template_magic_converter( $content, $varvalue, $varkey . '.' );
		}
	}

	return $content;
}

/**
 * Get a template files contents. Should be a .html file. If $is_html is true, $file can be html.
 *
 * @param  string  $file    File name (or html content).
 * @param  boolean $is_html Whether $file is html content.
 *
 * @return string           HTML content.
 */
function get_template_content( $file, $is_html = false ) {
	static $files = array();

	if ( ! isset( $files[ $file ] ) ) {
		if ( ! $is_html ) {

			ob_start();
			include_once $file;
			$files[ $file ] = ob_get_clean();

		} else {
			$files[ $file ] = $file;
		}

	}

	return $files[ $file ];
}

/**
 * Get and convert an underscore template file using an object/array of variables.
 *
 * @param  string       $file    File name (or html content).
 * @param  array|object $vars    Array or object of variables to parse.
 * @param  boolean      $is_html Whether $file is html content.
 *
 * @return string                HTML content.
 */
function template( $file, $vars, $is_html = false ) {
	$content = get_template_content( $file, $is_html );

	// @todo memoize output/vars combo.
	return template_magic_converter( $content, $vars );
}


// Hey, let's get a template output!
$html = template( 'b-tmpl-gc-item-status.html.html', array(
	'id'      => 1,
	'item'    => 2,
	'mapping' => 3,
	'open'    => '<div class="status-name">',
	'close'   => '</div>',
	'status'  => array(
		'name'  => 'Hello World',
		'color' => 'red',
	),
) );

die( '<xmp>'. print_r( $html, true ) .'</xmp>' );

/* Alternatively:

$html = '<div id=""{{ data.id }}">{{ data.name }}</div>';

$html = template( $html, array(
	'id'   => 1,
	'name' => 'Name',
), true );

*/
