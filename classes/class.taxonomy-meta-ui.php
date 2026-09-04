<?php
/**
 * Core bootstrap: REST API and block bindings for Taxonomy Meta UI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Taxonomy_Meta_UI {

	const VERSION = '1.5.0';

	const BLOCK_BINDING_SOURCE = 'taxonomy-meta-ui/term-meta';

	/**
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * @var array<string, bool>
	 */
	private static $registered_binding_sources = array();

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		require_once __DIR__ . '/class.taxonomy-meta-ui.fields.php';
		require_once __DIR__ . '/class.taxonomy-meta-ui.admin.php';

		add_action( 'init', array( $this, 'register_rest_term_meta' ), 15 );
		add_action( 'init', array( $this, 'register_block_bindings' ), 20 );

		new Taxonomy_Meta_UI_Admin( self::VERSION );
	}

	/**
	 * Register term meta for REST API.
	 */
	public function register_rest_term_meta(): void {
		foreach ( get_taxonomies() as $taxonomy ) {
			if ( ! Taxonomy_Meta_UI_Fields::is_taxonomy_enabled( $taxonomy ) ) {
				continue;
			}

			foreach ( $this->get_rest_meta_keys_for_taxonomy( $taxonomy ) as $meta_key ) {
				$fields = Taxonomy_Meta_UI_Fields::get_fields( $taxonomy, 0 );
				$field  = $fields[ $meta_key ] ?? null;

				if ( $field && empty( $field['show_in_rest'] ) ) {
					continue;
				}

				if ( $field ) {
					$args = Taxonomy_Meta_UI_Fields::get_registration_args( $field, $taxonomy );
				} else {
					$args = array(
						'type'              => 'string',
						'single'            => true,
						'sanitize_callback' => 'sanitize_textarea_field',
						'auth_callback'     => array( Taxonomy_Meta_UI_Fields::class, 'default_auth_callback' ),
						'show_in_rest'      => true,
					);

					/**
					 * Filter term meta registration args for extra REST keys.
					 *
					 * @param array<string,mixed>      $args     Registration args.
					 * @param string                   $meta_key Meta key.
					 * @param string                   $taxonomy Taxonomy slug.
					 * @param array<string,mixed>|null $field    Field definition, if any.
					 */
					$args = apply_filters( 'taxonomy_meta_ui_rest_meta_registration_args', $args, $meta_key, $taxonomy, null );
				}

				register_term_meta( $taxonomy, $meta_key, $args );
			}
		}
	}

	/**
	 * Register block binding source for term meta.
	 */
	public function register_block_bindings(): void {
		if ( ! function_exists( 'register_block_bindings_source' ) ) {
			return;
		}

		register_block_bindings_source(
			self::BLOCK_BINDING_SOURCE,
			array(
				'label'              => __( 'Term Meta', 'taxonomy-meta-ui' ),
				'get_value_callback' => array( $this, 'get_block_binding_value' ),
				'uses_context'       => array( 'termId', 'taxonomy' ),
			)
		);

		foreach ( get_taxonomies() as $taxonomy ) {
			if ( ! Taxonomy_Meta_UI_Fields::is_taxonomy_enabled( $taxonomy ) ) {
				continue;
			}

			foreach ( Taxonomy_Meta_UI_Fields::get_fields( $taxonomy, 0 ) as $field ) {
				if ( empty( $field['block_binding'] ) || empty( $field['name'] ) ) {
					continue;
				}

				$this->register_field_block_binding_source( $field );
			}
		}
	}

	/**
	 * Register a dedicated block binding source for one field.
	 *
	 * @param array<string,mixed> $field Field definition.
	 */
	private function register_field_block_binding_source( array $field ): void {
		$source_name = 'taxonomy-meta-ui/' . sanitize_key( str_replace( '_', '-', $field['name'] ) );

		if ( isset( self::$registered_binding_sources[ $source_name ] ) ) {
			return;
		}

		register_block_bindings_source(
			$source_name,
			array(
				'label'              => $field['label'],
				'get_value_callback' => function ( array $source_args, $block_instance, string $attribute_name ) use ( $field ) {
					return $this->get_block_binding_value(
						array( 'key' => $field['name'] ),
						$block_instance,
						$attribute_name
					);
				},
				'uses_context'       => array( 'termId', 'taxonomy' ),
			)
		);

		self::$registered_binding_sources[ $source_name ] = true;
	}

	/**
	 * Resolve a block binding value for term meta.
	 *
	 * @param array<string,mixed> $source_args     Binding args (expects `key`).
	 * @param WP_Block|null       $block_instance  Block instance.
	 * @param string              $attribute_name  Bound attribute name.
	 */
	public function get_block_binding_value( array $source_args, $block_instance, string $attribute_name ): ?string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WordPress get_value_callback signature.
		$meta_key = isset( $source_args['key'] ) ? sanitize_text_field( (string) $source_args['key'] ) : '';

		if ( '' === $meta_key ) {
			return null;
		}

		$term_id  = 0;
		$taxonomy = '';

		if ( is_object( $block_instance ) && isset( $block_instance->context['termId'] ) ) {
			$term_id  = (int) $block_instance->context['termId'];
			$taxonomy = isset( $block_instance->context['taxonomy'] ) ? (string) $block_instance->context['taxonomy'] : '';
		}

		if ( $term_id <= 0 ) {
			return null;
		}

		if ( '' === $taxonomy ) {
			$term = get_term( $term_id );
			if ( $term && ! is_wp_error( $term ) ) {
				$taxonomy = $term->taxonomy;
			}
		}

		$fields = Taxonomy_Meta_UI_Fields::get_fields( $taxonomy, $term_id );
		$field  = $fields[ $meta_key ] ?? null;

		if ( $field && empty( $field['block_binding'] ) ) {
			return null;
		}

		$value = get_term_meta( $term_id, $meta_key, true );

		if ( '' === $value || false === $value || null === $value ) {
			return null;
		}

		if ( $field && is_callable( $field['block_binding_render'] ?? null ) ) {
			$value = call_user_func( $field['block_binding_render'], $value, $term_id, $taxonomy );
		} elseif ( $field ) {
			$value = Taxonomy_Meta_UI_Fields::escape_value( $value, $field );
		} else {
			$value = esc_html( (string) $value );
		}

		if ( null === $value || '' === $value || false === $value ) {
			return null;
		}

		return is_scalar( $value ) ? (string) $value : null;
	}

	/**
	 * Meta keys to expose in REST for one taxonomy.
	 *
	 * @return array<int, string>
	 */
	private function get_rest_meta_keys_for_taxonomy( string $taxonomy ): array {
		$keys = array();

		foreach ( Taxonomy_Meta_UI_Fields::get_fields( $taxonomy, 0 ) as $field ) {
			if ( ! empty( $field['show_in_rest'] ) && ! empty( $field['name'] ) ) {
				$keys[] = $field['name'];
			}
		}

		$extra = apply_filters( 'taxonomy_meta_ui_rest_meta_keys', array(), $taxonomy );

		if ( is_array( $extra ) ) {
			foreach ( $extra as $key ) {
				if ( is_string( $key ) && '' !== $key ) {
					$keys[] = $key;
				}
			}
		}

		return array_values( array_unique( $keys ) );
	}
}
