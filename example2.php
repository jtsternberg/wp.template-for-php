<?php
// Output this at the top of the post-edit screen just below the title input.
add_action( 'edit_form_after_title', function() {

	// Enqueue the WP util script.
	wp_enqueue_script( 'wp-util' );

	// Include the lib.
	require_once 'class-wp-underscore-template.php';

	// This is our initial data we fetch/build from the server.
	$data = array(
		'id'      => 1,
		'item'    => 2,
		'mapping' => 3,
		'open'    => '<span class="status-name">',
		'close'   => '</span>',
		'status'  => array(
			'name'  => 'Stop',
			'color' => 'red',
		)
	);

	// Let's get the template object.
	$template = WP_Underscore_Template::get_object( dirname( __FILE__ ) . '/tmpl-gc-item-status.html', $data );

	// Output the parsed results.
	echo $template->execute_source();

	// Output the raw underscore template (for JS).
	echo $template->get_raw();
	?>

	<script type="text/javascript">
		jQuery( function( $ ) {

			var template = wp.template( 'gc-item-status' ); // uses script tag ID minus "tmpl-"
			// Turn out $data into a JS json object.
			var tmplData = <?php echo wp_json_encode( $data ); ?>;

			// Let's wait a tick, so we can see it go from red to green.
			setTimeout( function() {

				// Now update the data from within JS:
				tmplData.mapping = 5;
				tmplData.status.name = 'Go';
				tmplData.status.color = 'green';

				console.log( 'tmplData', tmplData );

				// Now let's replace the contents of the existing markup with our updated template markup.
				$( '.gc-status-column' ).replaceWith( template( tmplData ) );
			}, 500 );
		});

	</script>
	<?php
} );

