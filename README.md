# wp.template-for-php
PHP parser for wp.template underscore templates.

Definitely a work in progress.

**Done:**

* Import/read tmpl script tags, or pass html directly
* Convert variables, interpolated and escaped

**Todo:**

* Convert logic
* Remove wrapping script tags
* [Memoize](http://underscorejs.org/#memoize) output

___

So far this is what [`example.php`](https://github.com/jtsternberg/wp.template-for-php/blob/master/example.php) will output:

```html
<script type="text/html" id="tmpl-gc-item-status">
	<span class="gc-status-column" data-id="1" data-item="2" data-mapping="3">
	<# if ( data.status.name ) { #>
		<div class="gc-item-status">
			<span class="gc-status-color" style="background-color:red;"></span>
			<div class="status-name">
				Hello World
			</div>
		</div>
	<# } else { #>
		&mdash;
	<# } #>
	</span>
</script>
```

But the output should be:

```html
<span class="gc-status-column" data-id="1" data-item="2" data-mapping="3">
	<div class="gc-item-status">
		<span class="gc-status-color" style="background-color:red;"></span>
		<div class="status-name">
			Hello World
		</div>
	</div>
</span>

```
	
**Pull requests welcome!**

___

For reference, this is what [wp.template](https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-util.js#L8-L36) looks like:

```js
/**
 * wp.template( id )
 *
 * Fetch a JavaScript template for an id, and return a templating function for it.
 *
 * @param  {string} id   A string that corresponds to a DOM element with an id prefixed with "tmpl-".
 *                       For example, "attachment" maps to "tmpl-attachment".
 * @return {function}    A function that lazily-compiles the template requested.
 */
wp.template = _.memoize(function ( id ) {
	var compiled,
		/*
		 * Underscore's default ERB-style templates are incompatible with PHP
		 * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
		 *
		 * @see trac ticket #22344.
		 */
		options = {
			evaluate:    /<#([\s\S]+?)#>/g,
			interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
			escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
			variable:    'data'
		};

	return function ( data ) {
		compiled = compiled || _.template( $( '#tmpl-' + id ).html(), null, options );
		return compiled( data );
	};
});
```
