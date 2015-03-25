<?php
// If uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

$delete_tables = get_option( 'taxonomy-meta-ui-delete-tables' );

if ( 'true' !== $delete_tables )
	return;

delete_option( 'taxonomy-meta-ui-delete-tables' );

// For site options in multisite
delete_site_option( 'taxonomy-meta-ui-delete-tables' );

//drop a custom db table
global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}term_meta" );

//note in multisite looping through blogs to delete options on each blog does not scale. You'll just have to leave them.
