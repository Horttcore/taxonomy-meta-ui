<?php
/**
 * Plugin Name: Taxonomy Meta (UI)
 * Plugin URI: http://horttcore.de
 * Description: Taxonomy meta with admin UI, REST API, and block binding support.
 * Version: 1.5.0
 * Author: Ralf Hortt
 * Author URI: http://horttcore.de
 * Text Domain: taxonomy-meta-ui
 * Domain Path: /languages/
 * License: GPL2
 */

require_once __DIR__ . '/classes/class.taxonomy-meta-ui.php';

Taxonomy_Meta_UI::instance();
