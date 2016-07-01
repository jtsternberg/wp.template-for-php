<?php

/**
 * Retrieve the results of a desired template and relevant data.
 *
 * Template format expected as that of the WordPress javascript function
 * wp.template()
 *
 * @param $string - filename to an html template file
 *                  or
 *                  template string
 *
 * @param array $data - key value pairs relative to the template string
 *
 * @return string
 */
function template( $string, $data = array() ){

	$item = template_cache( $string, $data );

	$output = template_execute_source( $item['template'], $item['tokens'] );

	return $output;
}

/**
 * Cache the template and tokens
 *
 * @param $string
 * @param array $data
 *
 * @return mixed
 */
function template_cache( $string, $data = array() ){

	static $cache = array();

	$cache_key = md5( $string ). '--' .md5( serialize( $data ) );

	// store into cache a set of values that can be repeatably executed
	if ( empty( $cache[ $cache_key ] ) )
	{
		$tokens = template_make_tokens( $data );

		$template = template_parse_template( $string, $tokens );

		$cache[ $cache_key ] = array(
				'template' => $template,
				'tokens' => $tokens,
		);
	}

	return $cache[ $cache_key ];
}

/**
 * Convert a wp.template() template (string or file) into a simple php template
 *
 * @param $string - html file name, or html string
 *
 * @param $tokens - single dimensional array of key-value pairs
 *
 * @return string
 */
function template_parse_template( $string, $tokens ){

	$source = template_load_template( $string );

	$source = template_replace_patterns( $source );

	$source = template_replace_tokens( $source, $tokens );

	return $source;
}

/**
 * @param $source
 *
 * @return string
 */
function template_execute_source( $source, $__tokens ){
	ob_start();
	eval( '?>'.$source );
	return ob_get_clean();
}

/**
 * Load the desired template as a string
 *
 * @param $string
 *
 * @return string
 */
function template_load_template( $string ){

	// @todo - improve directory access
	if ( substr($string, -4) == 'html' &&
	     file_exists( $string ) )
	{
		$source = file_get_contents( $string );
	}
	else {
		$source = $string;
	}

	// strip some tags, especially the <script> tag that normally wraps a
	// wp.template() template string
	$source = preg_replace( "#</?(script)[^>]*>#i", '', $source  );

	return $source;
}

/**
 * Flatten a multidimensional array with a given delimiter
 *
 * @param array $array
 * @param string $parent_key
 * @param string $delimiter
 *
 * @return array
 */
function template_make_tokens( array $array, $parent_key = 'data', $delimiter = '.' ) {
	$children = array();

	foreach( $array as $key => $value ){
		$child_key = $parent_key ? $parent_key.$delimiter.$key : $key;

		if ( is_array( $value ) ){
			$value = template_make_tokens( $value, $child_key, $delimiter );
			$children = array_merge( $children, $value );
		}
		else {
			$children[ $child_key ] = $value;
		}
	}

	return $children;
}

/**
 * Replace the tokens given as
 *
 * @param $string - template as a string
 *
 * @param array $tokens - key value pairs of arbitrary date relevant to the
 *                        template
 *
 * @return array
 */
function template_replace_tokens( $string, $tokens = array() ) {

	// need these tokens to sorted from longest key to shortest so we don't
	// break the longer tokens by replacing the shorter ones first
	uksort( $tokens, function( $a, $b ){
		return strlen($a) < strlen($b);
	} );


	// token replacement
	foreach( $tokens as $key => $value ){
		$string = str_replace($key, '$__tokens[\''.$key.'\']', $string );
	}

	return $string;
}

/**
 * Replace statements and blocks in a similar fashion to the javascript function
 *  wp.template(), which uses underscore.js function _.template()
 *
 * @link https://codex.wordpress.org/Javascript_Reference/wp.template
 * @link http://underscorejs.org/docs/underscore.html
 *
 * @param $string
 *
 * @return string
 */
function template_replace_patterns( $string ){
	$source = $string;

	// order matters here. interpolate must be before escape
	$settings = array(
			'evaluate'    => array(
					'pattern' => '<#([\s\S]+?)#>',
					'replace' => '<?php %2$s ?>',
			),
			'interpolate' => array(
					'pattern' => '{{{([\s\S]+?)}}}',
					'replace' => '<?php echo %2$s; ?>',
			),
			'escape'      => array(
					'pattern' => '{{([\s\S]+?)}}',
					'replace' => '<?php echo esc_html( %2$s ); ?>',
			),
	);

	// replace each of our settings patterns with settings replacements
	foreach( $settings as $process => $setting )
	{
		$source = preg_replace_callback(

				'/'.$setting['pattern'] .'/',

				function( $matches ) use ( $setting ) {
					return vsprintf($setting['replace'], $matches );
				},

				$source
		);
	}

	return $source;
}
