<?php
/**
 * Admin UI for Taxonomy Meta UI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Taxonomy_Meta_UI_Admin {

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var array<string, array<string, array<string, mixed>>>
	 */
	private $field_cache = array();

	public function __construct( string $version ) {
		$this->version = $version;

		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_scripts-edit-tags.php', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'admin_print_scripts-term.php', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'admin_print_scripts-term.php', array( $this, 'admin_enqueue_styles' ) );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'wp_loaded', array( $this, 'register_tax_hooks' ) );
	}

	public function admin_enqueue_scripts(): void {
		wp_register_script(
			'taxonomy-meta-ui',
			plugins_url( '../scripts/scripts.js', __FILE__ ),
			array( 'jquery' ),
			$this->version,
			true
		);

		wp_localize_script(
			'taxonomy-meta-ui',
			'taxonomyMetaUI',
			array(
				'name'   => __( 'Name', 'taxonomy-meta-ui' ),
				'value'  => __( 'Value', 'taxonomy-meta-ui' ),
				'delete' => __( 'Delete', 'taxonomy-meta-ui' ),
			)
		);

		wp_enqueue_script( 'taxonomy-meta-ui' );
	}

	public function admin_enqueue_styles(): void {
		wp_register_style(
			'taxonomy-meta-ui',
			plugins_url( '../styles/styles.css', __FILE__ ),
			array(),
			$this->version
		);
		wp_enqueue_style( 'taxonomy-meta-ui' );
	}

	public function add_form_fields(): void {
		$screen   = get_current_screen();
		$taxonomy = $screen ? $screen->taxonomy : '';

		if ( ! $taxonomy || ! Taxonomy_Meta_UI_Fields::is_taxonomy_enabled( $taxonomy ) ) {
			return;
		}

		$fields = $this->get_fields( $taxonomy, 0 );

		if ( empty( $fields ) && ! Taxonomy_Meta_UI_Fields::has_custom_fields_ui( $taxonomy ) ) {
			return;
		}

		wp_nonce_field( 'taxonomy_meta_ui_save', 'taxonomy_meta_ui_nonce' );
		$this->render_static_form_fields( $taxonomy, null, 'add' );

		if ( ! Taxonomy_Meta_UI_Fields::has_custom_fields_ui( $taxonomy ) ) {
			return;
		}
		?>
		<div class="form-field term-custom-fields term-custom-fields-new">
			<label><?php esc_html_e( 'Custom Fields', 'taxonomy-meta-ui' ); ?></label>
			<div id="meta-list" class="taxonomy-meta-ui-meta-list"></div>
			<div id="new-meta" class="taxonomy-meta-ui-new-meta">
				<?php $this->dropdown_meta_fields( $taxonomy ); ?>
				<input name="meta_key[]" class="meta_key taxonomy-meta-ui-new-key" type="text" placeholder="<?php esc_attr_e( 'Name', 'taxonomy-meta-ui' ); ?>">
				<a id="enternew" class="taxonomy-meta-ui-enter-new" href="#"><?php esc_html_e( 'Enter new', 'taxonomy-meta-ui' ); ?></a>
				<a id="cancelnew" class="taxonomy-meta-ui-cancel-new" href="#"><?php esc_html_e( 'Cancel', 'taxonomy-meta-ui' ); ?></a>
				<textarea name="meta_value[]" class="meta_value taxonomy-meta-ui-new-value" rows="2" placeholder="<?php esc_attr_e( 'Value', 'taxonomy-meta-ui' ); ?>"></textarea>
				<a class="button" href="#" id="add-meta"><?php esc_html_e( 'Add Custom Field', 'taxonomy-meta-ui' ); ?></a>
			</div>
			<div id="meta-delete-list" class="taxonomy-meta-ui-delete-list" aria-hidden="true"></div>
		</div>
		<?php
	}

	public function edit_form_fields( $tag ): void {
		$taxonomy = $tag->taxonomy ?? '';

		if ( ! $taxonomy || ! Taxonomy_Meta_UI_Fields::is_taxonomy_enabled( $taxonomy ) ) {
			return;
		}

		$fields = $this->get_fields( $taxonomy, (int) $tag->term_id );

		if ( empty( $fields ) && ! Taxonomy_Meta_UI_Fields::has_custom_fields_ui( $taxonomy ) ) {
			return;
		}

		wp_nonce_field( 'taxonomy_meta_ui_save', 'taxonomy_meta_ui_nonce' );
		$this->render_static_form_fields( $taxonomy, $tag, 'edit' );

		if ( ! Taxonomy_Meta_UI_Fields::has_custom_fields_ui( $taxonomy ) ) {
			return;
		}
		?>
		<tr class="form-field taxonomy-meta-ui-custom-fields">
			<th scope="row" valign="top">
				<label for="term-meta"><?php esc_html_e( 'Custom Fields', 'taxonomy-meta-ui' ); ?></label>
			</th>
			<td>
				<div id="meta-list" class="taxonomy-meta-ui-meta-list"><?php $this->list_meta( (int) $tag->term_id, $taxonomy ); ?></div>
				<div id="new-meta" class="taxonomy-meta-ui-new-meta">
					<?php $this->dropdown_meta_fields( $taxonomy ); ?>
					<input name="meta_key[]" class="meta_key taxonomy-meta-ui-new-key" type="text" placeholder="<?php esc_attr_e( 'Name', 'taxonomy-meta-ui' ); ?>">
					<a id="enternew" class="taxonomy-meta-ui-enter-new" href="#"><?php esc_html_e( 'Enter new', 'taxonomy-meta-ui' ); ?></a>
					<a id="cancelnew" class="taxonomy-meta-ui-cancel-new" href="#"><?php esc_html_e( 'Cancel', 'taxonomy-meta-ui' ); ?></a>
					<textarea name="meta_value[]" class="meta_value taxonomy-meta-ui-new-value" rows="2" placeholder="<?php esc_attr_e( 'Value', 'taxonomy-meta-ui' ); ?>"></textarea>
					<a class="button" href="#" id="add-meta"><?php esc_html_e( 'Add Custom Field', 'taxonomy-meta-ui' ); ?></a>
				</div>
				<div id="meta-delete-list" class="taxonomy-meta-ui-delete-list" aria-hidden="true"></div>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param string       $taxonomy Taxonomy slug.
	 * @param WP_Term|null $term     Term object.
	 * @param string       $context  `add` or `edit`.
	 */
	private function render_static_form_fields( string $taxonomy, ?WP_Term $term, string $context ): void {
		$term_id = $term ? (int) $term->term_id : 0;
		$fields  = $this->get_fields( $taxonomy, $term_id );

		if ( empty( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {
			$value = $term_id ? get_term_meta( $term_id, $field['name'], true ) : '';

			if ( 'edit' === $context ) {
				$this->render_static_field_edit_row( $field, $value, $term );
			} else {
				$this->render_static_field_add_row( $field, $value );
			}
		}
	}

	/**
	 * @param array<string,mixed> $field Field definition.
	 * @param mixed             $value Current value.
	 */
	private function render_static_field_add_row( array $field, $value ): void {
		?>
		<div class="form-field term-custom-fields term-custom-fields-new taxonomy-meta-ui-static-field" data-field="<?php echo esc_attr( $field['name'] ); ?>">
			<label for="<?php echo esc_attr( 'taxonomy-meta-ui-' . $field['name'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			<?php $this->render_field_input( $field, $value, null, 'add' ); ?>
			<?php $this->render_field_description( $field ); ?>
		</div>
		<?php
	}

	/**
	 * @param array<string,mixed> $field Field definition.
	 * @param mixed             $value Current value.
	 * @param WP_Term|null      $term  Term object.
	 */
	private function render_static_field_edit_row( array $field, $value, ?WP_Term $term ): void {
		?>
		<tr class="form-field taxonomy-meta-ui-static-field" data-field="<?php echo esc_attr( $field['name'] ); ?>">
			<th scope="row" valign="top">
				<label for="<?php echo esc_attr( 'taxonomy-meta-ui-' . $field['name'] ); ?>"><?php echo esc_html( $field['label'] ); ?></label>
			</th>
			<td>
				<?php $this->render_field_input( $field, $value, $term, 'edit' ); ?>
				<?php $this->render_field_description( $field ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * @param array<string,mixed> $field   Field definition.
	 * @param mixed               $value   Current value.
	 * @param WP_Term|null        $term    Term object.
	 * @param string              $context `add` or `edit`.
	 */
	private function render_field_input( array $field, $value, ?WP_Term $term, string $context ): void {
		if ( is_callable( $field['render_callback'] ?? null ) ) {
			call_user_func( $field['render_callback'], $field['name'], $value, $field, $term, $context );
			return;
		}

		$field_id = 'taxonomy-meta-ui-' . $field['name'];

		echo '<input type="hidden" name="meta_key[]" class="meta_key" value="' . esc_attr( $field['name'] ) . '">';

		switch ( $field['type'] ?? 'textarea' ) {
			case 'select':
				echo '<select name="meta_value[]" class="meta_value" id="' . esc_attr( $field_id ) . '">';
				foreach ( (array) ( $field['options'] ?? array() ) as $option_value => $option_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( (string) $option_value ),
						selected( (string) $value, (string) $option_value, false ),
						esc_html( (string) $option_label )
					);
				}
				echo '</select>';
				break;

			case 'text':
				printf(
					'<input type="text" name="meta_value[]" class="meta_value" id="%1$s" value="%2$s" placeholder="%3$s" />',
					esc_attr( $field_id ),
					esc_attr( (string) $value ),
					esc_attr( $field['placeholder'] ?? '' )
				);
				break;

			case 'textarea':
			default:
				printf(
					'<textarea name="meta_value[]" class="meta_value" id="%1$s" rows="2" placeholder="%2$s">%3$s</textarea>',
					esc_attr( $field_id ),
					esc_attr( $field['placeholder'] ?? '' ),
					Taxonomy_Meta_UI_Fields::escape_value( $value, $field ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped.
				);
				break;
		}
	}

	/**
	 * @param array<string,mixed> $field Field definition.
	 */
	private function render_field_description( array $field ): void {
		if ( empty( $field['description'] ) ) {
			return;
		}

		echo '<div class="field-description">' . wp_kses_post( $field['description'] ) . '</div>';
	}

	public function dropdown_meta_fields( string $taxonomy ): void {
		global $wpdb;

		$limit  = (int) apply_filters( 'postmeta_form_limit', 30 ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core filter.
		$fields = $this->get_fields( $taxonomy, 0 );

		$keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->termmeta} WHERE meta_key NOT LIKE %s ORDER BY meta_key LIMIT %d",
				$wpdb->esc_like( '_' ) . '%',
				$limit
			)
		);

		?>
		<select name="meta_keys" id="selectnew" class="taxonomy-meta-ui-key-select">
			<option value=""><?php esc_html_e( '&mdash; Select &mdash;', 'taxonomy-meta-ui' ); ?></option>
			<?php
			foreach ( (array) $keys as $key ) {
				if ( isset( $fields[ $key ] ) ) {
					continue;
				}

				printf( '<option value="%s">%s</option>', esc_attr( $key ), esc_html( $key ) );
			}
			?>
		</select>
		<?php
	}

	private function list_meta( int $term_id, string $taxonomy ): void {
		$fields       = $this->get_fields( $taxonomy, $term_id );
		$static_names = array_keys( $fields );
		$meta         = get_term_meta( $term_id );

		if ( empty( $meta ) || ! is_array( $meta ) ) {
			return;
		}

		foreach ( $meta as $meta_key => $values ) {
			if ( in_array( $meta_key, $static_names, true ) ) {
				continue;
			}

			$value = is_array( $values ) ? reset( $values ) : $values;
			?>
			<div class="meta-field" data-meta-key="<?php echo esc_attr( $meta_key ); ?>">
				<input type="hidden" name="taxonomy_meta_ui_tracked_keys[]" value="<?php echo esc_attr( $meta_key ); ?>">
				<input name="meta_key[]" class="meta_key" type="text" value="<?php echo esc_attr( $meta_key ); ?>" placeholder="<?php esc_attr_e( 'Name', 'taxonomy-meta-ui' ); ?>">
				<textarea name="meta_value[]" class="meta_value" rows="2" placeholder="<?php esc_attr_e( 'Value', 'taxonomy-meta-ui' ); ?>"><?php echo esc_textarea( (string) $value ); ?></textarea>
				<a class="button delete-meta-button" href="#"><?php esc_html_e( 'Delete', 'taxonomy-meta-ui' ); ?></a>
			</div>
			<?php
		}
	}

	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'taxonomy-meta-ui',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/../languages/'
		);
	}

	public function register_tax_hooks(): void {
		foreach ( get_taxonomies() as $taxonomy ) {
			if ( ! Taxonomy_Meta_UI_Fields::is_taxonomy_enabled( $taxonomy ) ) {
				continue;
			}

			add_action( $taxonomy . '_add_form_fields', array( $this, 'add_form_fields' ) );
			add_action( $taxonomy . '_edit_form_fields', array( $this, 'edit_form_fields' ) );
			add_action( 'edited_' . $taxonomy, array( $this, 'save_term_meta' ), 10, 2 );
			add_action( 'created_' . $taxonomy, array( $this, 'save_term_meta' ), 10, 2 );
		}
	}

	public function save_term_meta( $term_id = false, $tt_id = false ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WordPress created_/edited_{taxonomy} signature.
		$term_id = (int) $term_id;

		if ( $term_id <= 0 ) {
			return;
		}

		if ( empty( $_POST['taxonomy_meta_ui_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['taxonomy_meta_ui_nonce'] ) ), 'taxonomy_meta_ui_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		$term = get_term( $term_id );

		if ( ! $term || is_wp_error( $term ) ) {
			return;
		}

		$taxonomy = $term->taxonomy;
		$fields   = $this->get_fields( $taxonomy, $term_id );

		$meta_keys   = isset( $_POST['meta_key'] ) && is_array( $_POST['meta_key'] ) ? wp_unslash( $_POST['meta_key'] ) : array();
		$meta_values = isset( $_POST['meta_value'] ) && is_array( $_POST['meta_value'] ) ? wp_unslash( $_POST['meta_value'] ) : array();
		$deleted     = isset( $_POST['meta_delete'] ) && is_array( $_POST['meta_delete'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['meta_delete'] ) ) : array();
		$tracked     = isset( $_POST['taxonomy_meta_ui_tracked_keys'] ) && is_array( $_POST['taxonomy_meta_ui_tracked_keys'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['taxonomy_meta_ui_tracked_keys'] ) )
			: array();

		$saved_keys = array();

		foreach ( $meta_keys as $index => $meta_key ) {
			$meta_key = sanitize_text_field( (string) $meta_key );

			if ( '' === $meta_key ) {
				continue;
			}

			$raw_value = isset( $meta_values[ $index ] ) ? $meta_values[ $index ] : '';
			$field     = $fields[ $meta_key ] ?? null;
			$value     = Taxonomy_Meta_UI_Fields::sanitize_value( $raw_value, $field, $term_id );

			update_term_meta( $term_id, $meta_key, $value );
			$saved_keys[] = $meta_key;
		}

		$static_names = array_keys( $fields );

		foreach ( $deleted as $delete_key ) {
			if ( in_array( $delete_key, $static_names, true ) ) {
				continue;
			}

			delete_term_meta( $term_id, $delete_key );
		}

		foreach ( $tracked as $tracked_key ) {
			if ( in_array( $tracked_key, $static_names, true ) ) {
				continue;
			}

			if ( in_array( $tracked_key, $saved_keys, true ) || in_array( $tracked_key, $deleted, true ) ) {
				continue;
			}

			delete_term_meta( $term_id, $tracked_key );
		}
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	private function get_fields( string $taxonomy, $term_id = 0 ): array {
		$cache_key = $taxonomy . ':' . (int) $term_id;

		if ( ! isset( $this->field_cache[ $cache_key ] ) ) {
			$this->field_cache[ $cache_key ] = Taxonomy_Meta_UI_Fields::get_fields( $taxonomy, $term_id );
		}

		return $this->field_cache[ $cache_key ];
	}
}
