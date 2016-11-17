<?php

class WP_Underscore_Template {

	/**
	 * Cached tokenized instances.
	 *
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * Source template string.
	 *
	 * @var string
	 */
	protected $template = '';

	/**
	 * Source template tokens.
	 *
	 * @var array
	 */
	protected $tokens = array();

	/**
	 * The underscores base variable name.
	 *
	 * @var string
	 */
	protected $parent_key = 'data';

	/**
	 * The variable delimiter (. in JS).
	 *
	 * @var string
	 */
	protected $delimiter = '.';

	/**
	 * Output the results of a desired template and relevant data.
	 *
	 * Template format expected as that of the WordPress javascript function
	 * wp.template().
	 *
	 * @param $file       filename to an html template file or template string
	 *
	 * @param array $data key value pairs relative to the template string
	 * @param array $args Optional arguments for template parser (parent_key and delimiter).
	 *
	 * @return void
	 */
	public static function output( $file, $data = array(), $args = array() ) {
		echo self::get( $file, $data, $args );
	}

	/**
	 * Retrieve the results of a desired template and relevant data.
	 *
	 * Template format expected as that of the WordPress javascript function
	 * wp.template()
	 *
	 * @param $file       filename to an html template file or template string
	 *
	 * @param array $data key value pairs relative to the template string
	 * @param array $args Optional arguments for template parser (parent_key and delimiter).
	 *
	 * @return string
	 */
	public static function get( $file, $data = array(), $args = array() ) {
		return self::template_cache( $file, $data, $args )->execute_source();
	}

	/**
	 * Cache the template and tokens
	 *
	 * @param $file
	 * @param array $data
	 * @param array $args
	 *
	 * @return mixed
	 */
	protected static function template_cache( $file, $data = array(), $args = array() ) {

		$vars = $args;
		$vars['args'] = $args;
		$cache_key = md5( $file ) . '--' . md5( serialize( $vars ) );

		// store into cache a set of values that can be repeatably executed
		if ( ! isset( self::$cache[ $cache_key ] ) ) {
			self::$cache[ $cache_key ] = new self( $file, $data, $args );
		}

		return self::$cache[ $cache_key ];
	}

	/**
	 * Build a template-parser instance.
	 *
	 * @param $file
	 * @param array $data
	 * @param array $args
	 */
	protected function __construct( $file, $data = array(), $args = array() ) {
		if ( isset( $args['parent_key'] ) ) {
			$this->parent_key = $args['parent_key'];
		}

		if ( isset( $args['delimiter'] ) ) {
			$this->delimiter = $args['delimiter'];
		}

		$this->tokens = $this->make_tokens( $data );
		$this->template = $this->parse_template( $file, $this->tokens );
	}

	/**
	 * Convert a wp.template() template (string or file) into a simple php template
	 *
	 * @param $file - Html file name, or html string
	 * @param $tokens - Single dimensional array of key-value pairs
	 *
	 * @return string
	 */
	protected function parse_template( $file, $tokens ) {

		$source = $this->load_template( $file );

		$source = $this->replace_patterns( $source );

		$source = $this->replace_tokens( $source, $tokens );

		return $source;
	}

	/**
	 * `eval`s our template and the tokens.
	 *
	 * @param $source
	 *
	 * @uses   eval USE WITH CAUTION.
	 * @return string
	 */
	protected function execute_source() {
		$__tokens = $this->tokens;
		ob_start();
		eval( '?>'. $this->template );
		return ob_get_clean();
	}

	/**
	 * Load the desired template as a string.
	 *
	 * @param $file
	 *
	 * @return string
	 */
	protected function load_template( $file ) {

		// @todo - Improve directory access
		if ( 'html' === substr( $file, -4 ) && file_exists( $file ) ) {
			$source = file_get_contents( $file );
		}
		else {
			$source = $file;
		}

		// Strip some tags, especially the <script> tag that normally wraps a
		// wp.template() template string.
		// @todo maybe replace other nefarious (in php) strings?
		$source = preg_replace( "#</?(script)[^>]*>#i", '', $source  );

		return $source;
	}

	/**
	 * Flatten a multidimensional array with our delimiter.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	protected function make_tokens( array $array ) {
		$children = array();

		foreach( $array as $key => $value ) {
			$child_key = $this->parent_key
				? $this->parent_key . $this->delimiter . $key
				: $key;

			if ( is_array( $value ) ) {
				$value = $this->make_tokens( $value, $child_key, $this->delimiter );
				$children = array_merge( $children, $value );
			}
			else {
				$children[ $child_key ] = $value;
			}
		}

		return $children;
	}

	/**
	 * Replace the tokens given us.
	 *
	 * @param $string       Template as a string.
	 * @param array $tokens Key value pairs of arbitrary date relevant
	 *                      to the template.
	 *
	 * @return array
	 */
	protected function replace_tokens( $string, $tokens ) {

		// Need these tokens to sorted from longest key to shortest so we don't
		// break the longer tokens by replacing the shorter ones first.
		uksort( $tokens, function( $a, $b ) {
			return strlen($a) < strlen($b);
		} );


		// Token replacement
		foreach( $tokens as $key => $value ) {
			$string = str_replace( $key, '$__tokens[\''.$key.'\']', $string );
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
	protected function replace_patterns( $string ) {
		$source = $string;

		// Mostly so WP is optional.
		$escape_func = function_exists( 'esc_html' ) ? 'esc_html' : 'strip_tags';

		// Order matters here. Interpolate must be before escape.
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
				'replace' => '<?php echo '. $escape_func .'( %2$s ); ?>',
			),
		);

		// Replace each of our settings patterns with settings replacements.
		foreach( $settings as $process => $setting ) {
			$source = preg_replace_callback(

				'/' . $setting['pattern'] . '/',

				function( $matches ) use ( $setting ) {
					return vsprintf( $setting['replace'], $matches );
				},

				$source
			);
		}

		return $source;
	}

}
