<?php

// Hey, let's get a template output!
$html = template( 'tmpl-gc-item-status.html', array(
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
