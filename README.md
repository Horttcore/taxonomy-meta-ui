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

## Changelog

### v1.0

* Initial release
