<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}



/**
 *  Taxonomy Meta UI
 */
final class Taxonomy_Meta_UI
{



	/**
	 * Version number
	 *
	 * @var string
	 **/
	const version = '1.2.0';



	/**
	 *
	 * Constructor
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 */
	public function __construct()
	{

		add_action( 'init', array($this, 'wpdb_table') );

	} // END __construct



	/**
	 * Adding taxonomy meta table to wpdb
	 *
	 * @return void
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function wpdb_table()
	{

		global $wpdb;

		$wpdb->taxonomymeta =  $wpdb->prefix . 'term_meta';

	} // END wpdb_table



} // END final class Taxonomy_Meta_UI

new Taxonomy_Meta_UI;
