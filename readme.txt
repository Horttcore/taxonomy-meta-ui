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

Add as many custom values as you want, or define static fields which are always visible.

== Installation ==

* Put the plugin in your plugin directory and activate it in your WordPress backend.

== Frequently Asked Questions ==

= Filters =

Add static fields to all taxonomies
`term_fields`

Add static fields for $taxonomy
`term_fields_$taxonomy`

Remove all custom field support
`has_custom_fields`

Remove custom field support for $taxonomy
`$taxonomy_has_custom_fields`

== Screenshots ==

1. New term screen with custom fields
2. Edit term screen with custom fields
3. New term screen with custom fields and static field `foo`
4. Edit term screen with custom fields and static field `foo`

== Upgrade Notice ==

None yet

== Changelog ==

= 1.2 =

* Added: `term_fields_$taxonomy` filter
* Added: `$taxonomy_has_custom_fields` filter
* Added: `has_custom_fields` filter
* Added: `$taxonomy_has_custom_fields` filter
* Added: Deinstall routine
* Added: Placeholder parameter for static fields
* Removed: `taxonomy-meta-taxonomies filter

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
