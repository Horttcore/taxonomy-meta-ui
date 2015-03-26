<?php
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}



/**
 *  Taxonomy Meta UI
 */
final class Taxonomy_Meta_UI_Admin
{



	/**
	 * Version number
	 *
	 * @var string
	 **/
	protected $version = '1.2.0';



	/**
	 * Plugin basename
	 *
	 * @var string
	 **/
	static protected $plugin_basename = '';



	/**
	 *
	 * Constructor
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 */
	public function __construct()
	{

		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'admin_print_scripts-plugins.php', array( $this, 'admin_enqueue_option_script' ) );
		add_action( 'wp_ajax_taxonomy-meta-ui-delete-tables', array( $this, 'set_delete_tables_option' ) );
		add_action( 'delete_term', array( $this, 'delete_term' ), 10, 4 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'switch_blog', array($this, 'wpdb_table') );
		add_action( 'wp_loaded', array( $this, 'register_tax_hooks' ) );
		add_action( 'wpmu_new_blog', 'Taxonomy_Meta_UI_Admin::setup_new_blog', 10, 6);

	} // END __construct



	/**
	 * Plugin activation
	 *
	 * @static
	 * @access public
	 * @param bool $network_wide Network wide activation
	 * @author Ralf Hortt <me@horttcore.de>
	 **/
	static public function activation( $network_wide )
	{

		// Network
		if ( $network_wide ) :

			$blogs = wp_get_sites();

			foreach ( $blogs as $blog ) :

				Taxonomy_Meta_UI_Admin::setup_blog( $blog['blog_id'] );

			endforeach;

			restore_current_blog();

		// Single
		else :

			Taxonomy_Meta_UI_Admin::setup_blog();

		endif;

	} // END activation



	/**
	 * Register javascripts
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 **/
	public function admin_enqueue_scripts()
	{

		wp_register_script( 'taxonomy-meta-ui', plugins_url( '../scripts/scripts.js', __FILE__ ), array(), $this->version, TRUE );
		wp_localize_script( 'taxonomy-meta-ui', 'taxonomyMetaUI', array(
			'name' => __( 'Name' ),
			'value' => __( 'Value' ),
			'delete' => __( 'Delete' ),
		) );
		wp_enqueue_script( 'taxonomy-meta-ui' );

	} // END admin_enqueue_scripts



	/**
	 * Register javascripts
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.2.0
	 **/
	public function admin_enqueue_option_script()
	{

		wp_register_script( 'taxonomy-meta-ui-options', plugins_url( '../scripts/options.js', __FILE__ ), array(), $this->version, TRUE );
		wp_localize_script( 'taxonomy-meta-ui-options', 'taxonomyMetaUI', array(
			'nonce' => wp_create_nonce( 'taxonomy-meta-ui-options' ),
		) );
		wp_enqueue_script( 'taxonomy-meta-ui-options' );

	} // admin_enqueue_option_script



	/**
	 * Register javascripts
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 **/
	public function admin_enqueue_styles()
	{

		wp_register_style( 'taxonomy-meta-ui', plugins_url( '../styles/styles.css', __FILE__ ), array(), $this->version );
		wp_enqueue_style( 'taxonomy-meta-ui' );

	} // END admin_enqueue_scripts



	/**
	 * Term meta on add tag screen
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 **/
	public function add_form_fields()
	{

		$screen = get_current_screen();

		$this->add_static_form_fields( get_taxonomy( $screen->taxonomy ) );

		if ( FALSE === apply_filters( $screen->taxonomy . '_has_custom_fields', TRUE ) )
			return;

		if ( FALSE === apply_filters( 'has_custom_fields', TRUE ) )
			return;

		?>

		<div class="form-field term-custom-fields term-custom-fields-new">

			<label><?php _e( 'Custom Fields' ); ?></label>

			<div id="meta-list"></div>

			<div id="new-meta">
				<?php $this->dropdown_meta_fields() ?>
				<input name="meta_key[]" class="meta_key" id="meta_key" type="text" placeholder="<?php _e( 'Name' ) ?>">
				<a id="enternew" href="#"><?php _e( 'Enter new' ) ?></a>
				<a id="cancelnew" href="#"><?php _e( 'Cancel' ) ?></a>

				<textarea name="meta_value[]" class="meta_value" id="meta_value" rows="2" placeholder="<?php _e( 'Value' ) ?>"></textarea>

				<a class="button" href="#" id="add-meta"><?php _e( 'Add Custom Field' ) ?></a>
			</div>

		</div>

		<?php

	} // END add_form_fields



	/**
	 * Term meta on add tag screen
	 *
	 * @access public
	 * @param obj $taxonomy Taxonomy object
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.1.0
	 **/
	public function add_static_form_fields( $taxonomy )
	{

		$fields = $this->get_static_fields();

		if ( empty( $fields ) )
			return;

		foreach ( $fields as $field ) :

			?>

			<div class="form-field term-custom-fields term-custom-fields-new">

				<label><?php echo $field['label'] ?></label>
				<input name="meta_key[]" class="meta_key" id="meta_key" type="hidden" value="<?php echo $field['name'] ?>">
				<textarea name="meta_value[]" class="meta_value" id="meta_value" rows="2" placeholder="<?php if ( isset( $field['placeholder'] ) ) echo esc_attr( $field['placeholder'] ) ?>"></textarea>

				<?php if ( isset( $field['description'] ) && '' !== $field['description'] ) : ?>

					<?php echo apply_filters( 'the_content', $field['description'] ) ?>

				<?php endif; ?>

			</div>

			<?php

		endforeach;

	} // END add_static_form_fields



	/**
	 * Get all static fields
	 *
	 * @access public
	 * @return array Term meta fields
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.1.0
	 **/
	private function get_static_fields()
	{

		$screen = get_current_screen();
		$taxonomy = ( isset( $screen->taxonomy ) ) ? $screen->taxonomy : FALSE;
		$term_id = ( isset( $_GET['tag_ID'] ) ) ? sanitize_text_field( $_GET['tag_ID'] ) : FALSE;
		$fields = apply_filters( 'term_fields_' . $taxonomy, array(), $term_id );

		return apply_filters( 'term_fields', $fields, $taxonomy, $term_id );

	} // END get_static_fields



	/**
	 * Cleanup after term is deleted
	 *
	 * @access public
	 * @param int $term_id Term ID
	 * @param int $tt_id Taxonomy term ID
	 * @param str $taxonomy Taxonomy slug
	 * @param mixed $deleted_term Copy of the already-deleted term, in the form specified by the parent function. WP_Error otherwise.
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 **/
	public function delete_term( $term_id, $tt_id, $taxonomy, $deleted_term )
	{

		global $wpdb;

		$sql = "SELECT * FROM $wpdb->taxonomymeta WHERE taxonomy_id = %d";
		$meta = $wpdb->get_results( $wpdb->prepare( $sql, intval( $term_id  ) ) );

		if ( !$meta )
			return;

		foreach ( $meta as $m ) :

			delete_term_meta( $term_id, $m->meta_key );

		endforeach;

	} // END delete_term



	/**
	 * Dropdown for term meta
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 **/
	public function dropdown_meta_fields()
	{

		global $wpdb;

		$limit = apply_filters( 'postmeta_form_limit', 30 );
		$sql = "SELECT meta_key
			FROM $wpdb->taxonomymeta
			GROUP BY meta_key
			HAVING meta_key NOT LIKE %s
			ORDER BY meta_key
			LIMIT %d";
		$keys = $wpdb->get_col( $wpdb->prepare( $sql, $wpdb->esc_like( '_' ) . '%', $limit ) );

		?>
		<select name="meta_keys" id="selectnew">
			<option value=""><?php _e( '&mdash; Select &mdash;' ) ?></option>
			<?php foreach ( $keys as $key ) : ?>
				<option><?php echo $key ?></option>
			<?php endforeach; ?>
		</select>
		<?php

	} // END dropdown_meta_fields



	/**
	 * Tag Color input field on edit tag screen
	 *
	 * @access public
	 * @param obj $tag Tag object
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 **/
	public function edit_form_fields( $tag )
	{

		$this->edit_static_form_fields( $tag );

		?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term-meta"><?php _e( 'Custom Fields' ); ?></label>
			</th>
			<td>

				<div id="meta-list"><?php $this->list_meta( $tag->term_id ) ?></div>

				<div id="new-meta">

					<?php $this->dropdown_meta_fields() ?>

					<input name="meta_key[]" class="meta_key" id="meta_key" type="text" placeholder="<?php _e( 'Name' ) ?>">
					<a id="enternew" href="#"><?php _e( 'Enter new' ) ?></a>
					<a id="cancelnew" href="#"><?php _e( 'Cancel' ) ?></a>

					<textarea name="meta_value[]" class="meta_value" id="meta_value" rows="2" placeholder="<?php _e( 'Value' ) ?>"></textarea>

					<a class="button" href="#" id="add-meta"><?php _e( 'Add Custom Field' ) ?></a>

				</div>

			</td>
		</tr>

		<?php

	} // END edit_form_fields



	/**
	 * Static fields on edit term screen
	 *
	 * @access private
	 * @param obj $tag Tag object
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.1.0
	 **/
	private function edit_static_form_fields( $tag )
	{

		$fields = $this->get_static_fields();

		if ( empty( $fields ) )
			return;

		foreach ( $fields as $field ) :

			if ( !isset( $field['name'] ) || '' === $field['name'] )
				continue;

			?>

			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="<?php echo esc_attr( $field['name'] ) ?>"><?php echo $field['label'] ?></label>
				</th>
				<td>
					<input name="meta_key[]" class="meta_key" type="hidden" value="<?php echo esc_attr( $field['name'] ) ?>">
					<textarea name="meta_value[]" class="meta_value" id="<?php echo esc_attr( $field['name'] ) ?>" rows="2" placeholder="<?php if ( isset( $field['placeholder'] ) ) echo esc_attr( $field['placeholder'] ) ?>"><?php echo wp_kses_post( get_term_meta( $tag->term_id, $field['name'], TRUE ) ) ?></textarea>

					<?php if ( isset( $field['description'] ) && '' !== $field['description'] ) : ?>

						<div class="field-description">
							<?php echo apply_filters( 'the_content', $field['description'] ) ?>
						</div><!-- .field-description -->

					<?php endif; ?>

				</td>
			</tr>

			<?php

		endforeach;

	} // END edit_static_form_fields



	/**
	 * list meta
	 *
	 * @access private
	 * @param obj $tag Tag object
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 * @todo Update meta button
	 **/
	private function list_meta( $term_id )
	{

		global $wpdb;

		$screen = get_current_screen();

		$sql = "SELECT * FROM $wpdb->taxonomymeta WHERE taxonomy_id = %d";
		$meta = $wpdb->get_results( $wpdb->prepare( $sql, intval( $term_id  ) ) );

		if ( !$meta )
			return;

		foreach ( $meta as $m ) :

				if ( isset( $this->get_static_fields()[$m->meta_key] ) )
					continue;

				?>

				<div class="meta-field">

					<input name="meta_key[]" class="meta_key" type="text" value="<?php echo esc_attr( $m->meta_key ) ?>" placeholder="<?php _e( 'Name' ) ?>">

					<?php if ( isset( $m->label ) ) : ?>
						<label>
							<span class="meta-label"><?php echo $m->label ?></span><br>
					<?php endif; ?>

						<textarea name="meta_value[]" class="meta_value" rows="2" placeholder="<?php _e( 'Value' ) ?>"><?php echo esc_attr( $m->meta_value ) ?></textarea><br>

					<?php if ( isset( $m->label ) ) : ?>
						</label>
					<?php endif; ?>

					<a class="button delete-meta-button" href="#"><?php _e( 'Delete' ) ?></a> <!-- <a class="button update-meta-button" href="#"><?php _e( 'Update' ) ?></a> -->

				</div><!-- .meta-field -->

				<?php

		endforeach;

	} // END list_meta



	/**
	 * Load plugin textdomain
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain( 'taxonomy-meta-ui', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );

	} // END load_plugin_textdomain



	/**
	 * Plugin action links
	 *
	 * @access public
	 * @param array $links Links
	 * @return array Links
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since  1.2.0
	 **/
	static public function plugin_action_links( $links )
	{

		$links[] = '<a href="https://github.com/Horttcore/taxonomy-meta-ui" target="_blank">' . __( 'Documentation' ) . '</a>';

		// Remove database tables only for delete plugins
		if ( !current_user_can( 'delete_plugins' ) )
			return $links;

		$links[] = '<label style="color:#0074a2" title="' . __( 'Remove database tables on plugin deletion', 'taxonomy-meta-ui' ) . '">
						<input id="taxonomy-meta-ui-delete-tables" ' . checked( 'true', get_option( 'taxonomy-meta-ui-delete-tables') ) . ' type="checkbox"> ' . __( 'Delete data', 'taxonomy-meta-ui' ) . '
						<span class="dashicons dashicons-editor-help"></span>
					</label>';

		return $links;

	} // END plugin_action_links



	/**
	 * Set plugin basename
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since  1.2.0
	 **/
	static public function plugin_basename( $basename )
	{

		self::$plugin_basename = $basename;
		add_filter( 'plugin_action_links_' . self::$plugin_basename, 'Taxonomy_Meta_UI_Admin::plugin_action_links' );

	} // END plugin_basename



	/**
	 * Register hooks
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since  1.0.0
	 **/
	public function register_tax_hooks()
	{

		$taxonomies = get_taxonomies();

		foreach ( $taxonomies as $taxonomy ) :

			if ( FALSE === apply_filters( $taxonomy . '_has_meta', TRUE ) )
				continue;

			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_form_fields' ) );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
			add_action( 'edited_' . $taxonomy, array( $this , 'save_term_meta' ), 10, 2 );
			add_action( 'created_' . $taxonomy, array( $this , 'save_term_meta' ), 10, 2 );

		endforeach;

	} // END register_tax_hooks



	/**
	 * Save new term meta
	 *
	 * @access public
	 * @param int $term_id Term ID
	 * @param int $tt_id Taxonomy term ID
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since 1.0.0
	 **/
	public function save_term_meta( $term_id = FALSE, $tt_id = FALSE )
	{

		global $wpdb;

		if ( !$_POST['meta_key'] )
			return;

		$meta_keys = array();

		$i = 0;

		// Add/Update meta
		foreach ( $_POST['meta_key'] as $meta_key ) :

			update_term_meta( $term_id, $meta_key, $_POST['meta_value'][$i] );
			$meta_keys[] = $meta_key;

			$i++;

		endforeach;

		// Remove unused meta data
		$sql = "SELECT * FROM $wpdb->taxonomymeta WHERE taxonomy_id = %d";
		$meta = $wpdb->get_results( $wpdb->prepare( $sql, intval( $term_id  ) ) );

		foreach ( $meta as $m ) :

			if ( !in_array( $m->meta_key, $meta_keys ) )
				delete_term_meta( $term_id, $m->meta_key );

		endforeach;


	} // END save_term_meta



	/**
	 * Setup blog
	 *
	 * @static
	 * @access public
	 * @param int $blog_id Blog ID
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since  1.0.0
	 **/
	static public function setup_blog( $blog_id = FALSE )
	{

		global $wpdb;


		if ( $blog_id !== FALSE )
			switch_to_blog( $blog_id );

		$charset_collate = '';

		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";

		$tables = $wpdb->get_results( "show tables like '{$wpdb->prefix}term_meta'" );

		if ( !count( $tables ) )
			$wpdb->query( "CREATE TABLE {$wpdb->prefix}term_meta (
				meta_id bigint(20) unsigned NOT NULL auto_increment,
				taxonomy_id bigint(20) unsigned NOT NULL default '0',
				meta_key varchar(255) default NULL,
				meta_value longtext,
				PRIMARY KEY	(meta_id),
				KEY taxonomy_id (taxonomy_id),
				KEY meta_key (meta_key)
			) $charset_collate;" );

	} // END setup_blog



	/**
	 * Ajax: Set delete table options
	 *
	 * @access public
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since  1.2.0
	 **/
	public function set_delete_tables_option()
	{

		if ( !current_user_can( 'manage_options' ) )
			return;

		if ( !wp_verify_nonce( $_POST['nonce'], 'taxonomy-meta-ui-options' ) )
			return;

		update_option( 'taxonomy-meta-ui-delete-tables', $_POST['value'] );
		die('1');

	} // END set_delete_tables_option



	/**
	 * Setup new blog
	 *
	 * @static
	 * @access public
	 * @param int $blog_id Blog ID
	 * @author Ralf Hortt <me@horttcore.de>
	 * @since  1.0.0
	 **/
	static public function setup_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta )
	{

		if ( !is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
			return;

		$this->setup_blog( $blog_id );

	} // END setup_new_blog



} // END final class Taxonomy_Meta_UI_Admin

new Taxonomy_Meta_UI_Admin;
