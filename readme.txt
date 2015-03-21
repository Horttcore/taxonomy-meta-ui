=== Taxonomy Meta UI ===
Contributors: Horttcore
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=3X7QY649K36X4
Tags: Taxonomy, Term, Meta, UI
Requires at least: 4.2
Tested up to: 4.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Custom field support for terms

== Description ==

Custom field support for terms

== Installation ==

* Put the plugin in your plugin directory and activate it in your WordPress backend.

== Frequently Asked Questions ==

= Filters =

`term_fields - Add static fields`

`add_filter( 'term_fields', function( $taxonomy, $term ){
	return array(
		'test' => array(
			'name' => 'test', // Meta name
			'label' => 'Test', // Meta value
			'description' => 'This is a test' // Meta description
		)
	);
} );`

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Upgrade Notice ==

None yet

== Changelog ==

= 1.1 =

* Added: `term_fields` filter
* Enhancement: Create predefined meta fields
* Changed: Term meta table renamed

= 1.0 =

* Initial release

== Arbitrary section ==

= add_term_meta =

`add_term_meta( $term_id, $meta_key, $meta_value, $unique = FALSE )`

= update_term_meta =

`update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' )`

= delete_term_meta =

`delete_term_meta( $term_id, $meta_key, $meta_value = '' )`

= get_term_meta =

`get_term_meta( $term_id, $key, $single = FALSE)`
