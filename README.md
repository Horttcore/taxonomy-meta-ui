Taxonomy Meta UI
===================

## Description

Custom field support for terms

## Installation

* Put the plugin in your plugin directory and activate it in your WordPress backend.

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

#### `term_fields` - Add fields

`add_filter( 'term_fields', function( $taxonomy, $term ){
	return array(
		'test' => array(
			'name' => 'test', // Meta name
			'label' => 'Test', // Meta value
			'description' => 'This is a test' // Meta description
		)
	);
} );`

## Changelog

### 1.1

* Added: `term_fields` filter
* Enhancement: Create predefined meta fields
* Changed: Term meta table renamed

### v1.0

* Initial release
