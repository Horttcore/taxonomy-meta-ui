Taxonomy Meta UI
===================

## Description

Custom field support for terms

## Installation

* Put the plugin in your plugin directory and activate it in your WordPress backend.

## Screenshots

### New term screen with custom fields
[![New term screen with custom fields](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-1.jpg)](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-1.jpg)

### Edit term screen with custom fields
[![Edit term screen with custom fields](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-2.jpg)](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-2.jpg)

### New term screen with custom fields and static field `foo`
[![New term screen with custom fields and static field `foo`](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-3.jpg)](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-3.jpg)

### Edit term screen with custom fields and static field `foo`
[![Edit term screen with custom fields and static field `foo`](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-4.jpg)](https://github.com/Horttcore/taxonomy-meta-ui/blob/master/screenshot-4.jpg)

## Usage

Visit any term page and add term meta data

### Functions

#### add_term_meta

add_term_meta( $term_id, $meta_key, $meta_value, $unique = FALSE )

#### update_term_meta

update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' )

#### delete_term_meta

delete_term_meta( $term_id, $meta_key, $meta_value = '' )

#### get_term_meta

get_term_meta( $term_id, $key, $single = FALSE)

## Hooks

### Filters

Add static fields to all taxonomies
`term_fields`

Add static fields for $taxonomy
`term_fields_$taxonomy`

Remove all custom field support
`has_custom_fields`

Remove custom field support for $taxonomy
`$taxonomy_has_custom_fields`

## Changelog

### 1.2

* Added: `term_fields_$taxonomy` filter
* Added: `$taxonomy_has_custom_fields` filter
* Added: `has_custom_fields` filter
* Added: `$taxonomy_has_custom_fields` filter
* Added: Deinstall routine
* Added: Placeholder parameter for static fields
* Removed: `taxonomy-meta-taxonomies filter

### 1.1

* Added: `term_fields` filter
* Enhancement: Create predefined meta fields
* Changed: Term meta table renamed

### v1.0

* Initial release
