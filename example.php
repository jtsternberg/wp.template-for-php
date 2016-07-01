<?php

require_once 'class-wp-underscore-template.php';

// Hey, let's get a template output!
$get_html = WP_Underscore_Template::get( 'tmpl-gc-item-status.html', array(
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

ob_start();
// Do output
WP_Underscore_Template::output( 'tmpl-gc-item-status.html', array(
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

$output_html = ob_get_clean();

echo '<xmp>$output_html === $get_html: '. ( $output_html === $get_html ? 'true' : 'false' ) .'</xmp>';

die( '<xmp>'. print_r( $get_html, true ) .'</xmp>' );

/* Alternatively:

$html = '<div id=""{{ data.id }}">{{ data.name }}</div>';

$html = template( $html, array(
	'id'   => 1,
	'name' => 'Name',
), true );

*/
