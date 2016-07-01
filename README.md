# wp.template-for-php
PHP parser for wp.template underscore templates. Reproduces the functionality from [`wp.template()`](https://github.com/WordPress/WordPress/blob/4.5.3/wp-includes/js/wp-util.js#L8-L36), [`_.template()`](https://github.com/jashkenas/underscore/blob/master/underscore.js#L1487-L1546), and [`_.memoize()`](https://github.com/jashkenas/underscore/blob/master/underscore.js#L781-L791) in PHP.

**Done:**

* Import/read tmpl script tags, or pass html directly
* Convert variables, interpolated and escaped
* Convert logic
* Remove wrapping script tags
* [Memoize](http://underscorejs.org/#memoize) output

**Todo:**

* Replace `eval` with something less `evil`.

Huge thanks to [daggerhart](https://github.com/jtsternberg/wp.template-for-php/pull/1) for knocking this out. As promised, [he now has my ♥️](https://twitter.com/Jtsternberg/status/748548298332577792).

Still open to PRs if you're willing to take a stab at replacing the `eval` stuff with a logic parser.
___

Takes this:

```html
<script type="text/html" id="tmpl-gc-item-status">
	<span class="gc-status-column" data-id="{{ data.id }}" data-item="{{ data.item }}" data-mapping="{{ data.mapping }}">
	<# if ( data.status.name ) { #>
		<div class="gc-item-status">
			<span class="gc-status-color" style="background-color:{{ data.status.color }};"></span>
			{{{ data.open }}}
				{{ data.status.name }}
			{{{ data.close }}}
		</div>
	<# } else { #>
		&mdash;
	<# } #>
	</span>
</script>
```

And makes it this:

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

From this:

```php
// Hey, let's output a template!
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
```
	
**Pull requests welcome!**
