=== Taxonomy Meta UI ===
Contributors: Horttcore
Tags: Taxonomy, Term, Meta, UI, Block Bindings
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Custom field support for taxonomy terms with REST API and block binding support.

== Description ==

Add custom term meta via a UI, or define static fields with text, textarea, or select inputs. Supports custom render, sanitize, escape, and auth callbacks. Exposes registered fields to REST and the block bindings API.

== Installation ==

* Put the plugin in your plugin directory and activate it in your WordPress backend.

== Frequently Asked Questions ==

= How do I register static fields? =

Use the `term_fields` or `term_fields_{$taxonomy}` filters. See README.md in the plugin directory.

= How do I use block bindings? =

Bind to `taxonomy-meta-ui/term-meta` with `args.key`, or use the per-field source `taxonomy-meta-ui/{field-name}`.

== Changelog ==

= 1.5.0 =

* Security fixes, select fields, callbacks, block bindings, bug fixes

= 1.4.0 =

* REST API registration for static fields

= 1.3.0 =

* Fixing things…

= 1.2 =

* Added taxonomy-specific filters and custom field toggles

= 1.1 =

* Added `term_fields` filter and predefined meta fields

= 1.0 =

* Initial release
