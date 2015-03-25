<?php
/**
 * Plugin Name: Taxonomy Meta (UI)
 * Plugin URI: http://horttcore.de
 * Description: Taxnonomy Meta with UI
 * Version: 1.2.0
 * Author: Ralf Hortt
 * Author URI: http://horttcore.de
 * Text Domain: taxonomy-meta-ui
 * Domain Path: /languages/
 * License: GPL2
 */

require( 'includes/functions.php' );
require( 'classes/class.taxonomy-meta-ui.php' );

if ( is_admin() ) :
	require( 'classes/class.taxonomy-meta-ui.admin.php' );
	Taxonomy_Meta_UI_Admin::plugin_basename( plugin_basename( __FILE__) );
endif;

register_activation_hook( __FILE__, 'Taxonomy_Meta_UI_Admin::activation' );

