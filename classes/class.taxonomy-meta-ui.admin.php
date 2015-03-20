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
	protected $version = '1.0.0';



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

		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'delete_term', array( $this, 'delete_term' ), 10, 4 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'switch_blog', array($this, 'wpdb_table') );
		add_action( 'wp_loaded', array( $this, 'register_tax_hooks' ) );
		add_action( 'wpmu_new_blog', 'Taxonomy_Meta_UI_Admin::setup_new_blog', 10, 6);


	} // END __construct



	/**
	 * Plugin activation
	 *
	 * @return void
	 * @author Ralf Hortt
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
	 * @author Ralf Hortt
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
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function admin_enqueue_styles()
	{

		wp_register_style( 'taxonomy-meta-ui', plugins_url( '../styles/styles.css', __FILE__ ), array(), $this->version );
		wp_enqueue_style( 'taxonomy-meta-ui' );

	} // END admin_enqueue_scripts



	/**
	 * Term thumbnail on add tag screen
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function add_form_fields()
	{

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
	 * Cleanup after term is deleted
	 *
	 * @access public
	 * @return void
	 * @author Ralf Hortt
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
	 * @author Ralf Hortt
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
	 * Delete term thumbnail
	 *
	 * @access protected
	 * @param int $term_id Term ID
	 * @param int $attachment_id Attachment ID
	 * @author Ralf Hortt
	 * @since  1.0.0
	 **/
	protected function delete_term_thumbnail( $term_id )
	{

		$term_id = intval( $term_id );

		if ( 0 == $term_id )
			return;

		$options = get_option( 'taxonomy-meta-ui' );
		unset( $options[$term_id] );
		update_option( 'taxonomy-meta-ui', $options );

	} // END delete_term_thumbnail



	/**
	 * Tag Color input field on edit tag screen
	 *
	 * @access public
	 * @param obj $tag Tag object
	 * @author Ralf Hortt
	 * @since 1.0.0
	 **/
	public function edit_form_fields( $tag )
	{

		$term_id = $tag->term_id;
		?>

		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term-thumbnail"><?php _e( 'Custom Fields' ); ?></label>
			</th>
			<td>

				<div id="meta-list"><?php $this->list_meta( $term_id ) ?></div>

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
	 * list meta
	 *
	 * @access private
	 * @param obj $tag Tag object
	 * @author Ralf Hortt
	 * @since 1.0.0
	 * @todo Update meta button
	 **/
	private function list_meta( $term_id )
	{

		global $wpdb;

		$sql = "SELECT * FROM $wpdb->taxonomymeta WHERE taxonomy_id = %d";
		$meta = $wpdb->get_results( $wpdb->prepare( $sql, intval( $term_id  ) ) );

		if ( !$meta )
			return;

		foreach ( $meta as $m ) :

			?>

			<div class="meta-field">
				<input name="meta_key[]" class="meta_key" type="text" value="<?php echo esc_attr( $m->meta_key ) ?>" placeholder="<?php _e( 'Name' ) ?>">
				<textarea name="meta_value[]" class="meta_value" rows="2" placeholder="<?php _e( 'Value' ) ?>"><?php echo esc_attr( $m->meta_value ) ?></textarea>
				<a class="button delete-meta-button" href="#"><?php _e( 'Delete' ) ?></a> <!-- <a class="button update-meta-button" href="#"><?php _e( 'Update' ) ?></a> -->
			</div>

			<?php

		endforeach;

	} // END list_meta



	/**
	 * Load plugin textdomain
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain( 'taxonomy-meta-ui', false, dirname( plugin_basename( __FILE__ ) ) . '/../languages/' );

	} // END load_plugin_textdomain



	/**
	 * Register hooks
	 *
	 * @access public
	 * @author Ralf Hortt
	 * @since  1.0.0
	 **/
	public function register_tax_hooks()
	{

		$taxonomies = apply_filters( 'taxonomy-meta-taxonomies', get_taxonomies() );

		foreach ( $taxonomies as $taxonomy ) :

			if ( FALSE === apply_filters( $taxonomy . '-has-meta', TRUE ) )
				continue;

			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_form_fields' ) );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
			add_action( 'edited_' . $taxonomy, array( $this , 'save_term_meta' ), 10, 2 );
			add_action( 'created_' . $taxonomy, array( $this , 'save_term_meta' ), 10, 2 );

		endforeach;

	} // END register_tax_hooks



	/**
	 * Save new term thumbnail
	 *
	 * @access public
	 * @param int $term_id Term ID
	 * @param int $tt_id Taxonomy term ID
	 * @author Ralf Hortt
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


	} // END created_term_thumbnail



	/**
	 * Setup blog
	 *
	 * @static
	 * @access public
	 * @param int $blog_id Blog ID
	 * @author Ralf Hortt
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

		$tables = $wpdb->get_results( "show tables like '{$wpdb->prefix}taxonomymeta'" );

		if ( !count( $tables ) )
			$wpdb->query( "CREATE TABLE {$wpdb->prefix}taxonomymeta (
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
	 * Setup new blog
	 *
	 * @static
	 * @access public
	 * @param int $blog_id Blog ID
	 * @author Ralf Hortt
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
