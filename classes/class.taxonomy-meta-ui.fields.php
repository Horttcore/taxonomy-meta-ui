<?php
/**
 * Field registry and helpers for Taxonomy Meta UI.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Taxonomy_Meta_UI_Fields {

	/**
	 * Normalize a field definition.
	 *
	 * @param string|int $key   Array key from the filter.
	 * @param mixed      $field Field definition, label string, or invalid value.
	 * @return array<string, mixed>
	 */
	public static function normalize( $key, $field ): array {
		if ( is_string( $field ) ) {
			$field = array(
				'label' => $field,
			);
		}

		if ( ! is_array( $field ) ) {
			$field = array();
		}

		if ( empty( $field['name'] ) ) {
			$field['name'] = is_string( $key ) ? $key : '';
		}

		$defaults = array(
			'name'                 => '',
			'label'                => '',
			'type'                 => 'textarea',
			'placeholder'          => '',
			'description'          => '',
			'options'              => array(),
			'render_callback'      => null,
			'sanitize_callback'    => null,
			'escape_callback'      => null,
			'auth_callback'        => null,
			'block_binding'        => true,
			'block_binding_render' => null,
			'show_in_rest'         => true,
			'rest_type'            => 'string',
			'single'               => true,
		);

		$field = array_merge( $defaults, $field );

		if ( '' === $field['label'] ) {
			$field['label'] = $field['name'];
		}

		return $field;
	}

	/**
	 * Get static fields for a taxonomy.
	 *
	 * @param string|null $taxonomy Taxonomy slug.
	 * @param int|false   $term_id  Term ID.
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_fields( ?string $taxonomy, $term_id = 0 ): array {
		if ( ! $taxonomy ) {
			return array();
		}

		$term_id = $term_id ? (int) $term_id : 0;
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public API.
		$fields = apply_filters( 'term_fields_' . $taxonomy, array(), $term_id );
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public API.
		$fields = apply_filters( 'term_fields', $fields, $taxonomy, $term_id );

		if ( ! is_array( $fields ) ) {
			return array();
		}

		$normalized = array();

		foreach ( $fields as $key => $field ) {
			$field = self::normalize( $key, $field );

			if ( '' === $field['name'] ) {
				continue;
			}

			$normalized[ $field['name'] ] = $field;
		}

		return $normalized;
	}

	/**
	 * Whether meta support is enabled for a taxonomy.
	 */
	public static function is_taxonomy_enabled( string $taxonomy ): bool {
		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Public API.
		if ( false === apply_filters( $taxonomy . '_has_meta', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether the free-form custom fields UI is enabled.
	 */
	public static function has_custom_fields_ui( string $taxonomy ): bool {
		if ( ! self::is_taxonomy_enabled( $taxonomy ) ) {
			return false;
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- Public API.
		if ( false === apply_filters( $taxonomy . '_has_custom_fields', true ) ) {
			return false;
		}

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Public API.
		if ( false === apply_filters( 'has_custom_fields', true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitize a submitted value.
	 *
	 * @param mixed              $value   Raw value.
	 * @param array<string,mixed>|null $field   Field definition.
	 * @param int                $term_id Term ID.
	 * @return mixed
	 */
	public static function sanitize_value( $value, ?array $field, int $term_id ) {
		if ( is_callable( $field['sanitize_callback'] ?? null ) ) {
			return call_user_func( $field['sanitize_callback'], $value, $field, $term_id );
		}

		$type = $field['type'] ?? 'textarea';

		switch ( $type ) {
			case 'select':
				$options = array_keys( (array) ( $field['options'] ?? array() ) );
				$value   = sanitize_text_field( (string) $value );

				if ( ! empty( $options ) && ! in_array( $value, $options, true ) ) {
					return '';
				}

				return $value;

			case 'text':
				return sanitize_text_field( (string) $value );

			case 'textarea':
			default:
				return sanitize_textarea_field( (string) $value );
		}
	}

	/**
	 * Escape a value for admin display.
	 *
	 * @param mixed              $value Raw value.
	 * @param array<string,mixed> $field Field definition.
	 */
	public static function escape_value( $value, array $field ): string {
		if ( is_callable( $field['escape_callback'] ?? null ) ) {
			return (string) call_user_func( $field['escape_callback'], $value, $field );
		}

		$type = $field['type'] ?? 'textarea';

		switch ( $type ) {
			case 'select':
			case 'text':
				return esc_attr( (string) $value );

			case 'textarea':
			default:
				return esc_textarea( (string) $value );
		}
	}

	/**
	 * Default REST auth callback for term meta.
	 */
	public static function default_auth_callback( bool $allowed, string $context, int $term_id, string $meta_key ): bool { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WordPress auth_callback signature.
		if ( 'edit' === $context ) {
			return current_user_can( 'edit_term', $term_id );
		}

		return $allowed;
	}

	/**
	 * Build register_term_meta() args for a field.
	 *
	 * @param array<string,mixed> $field    Field definition.
	 * @param string              $taxonomy Taxonomy slug.
	 * @return array<string, mixed>
	 */
	public static function get_registration_args( array $field, string $taxonomy ): array {
		$meta_key = $field['name'];

		$args = array(
			'type'              => $field['rest_type'] ?? 'string',
			'single'            => (bool) ( $field['single'] ?? true ),
			'description'       => $field['label'],
			'sanitize_callback' => function ( $value ) use ( $field ) {
				return self::sanitize_value( $value, $field, 0 );
			},
			'auth_callback'     => is_callable( $field['auth_callback'] ?? null )
				? $field['auth_callback']
				: array( self::class, 'default_auth_callback' ),
			'show_in_rest'      => array(
				'schema' => array(
					'type'        => $field['rest_type'] ?? 'string',
					'description' => $field['label'],
				),
			),
		);

		/**
		 * Filter term meta registration args for a static field.
		 *
		 * @param array<string,mixed> $args     Registration args.
		 * @param string              $meta_key Meta key.
		 * @param string              $taxonomy Taxonomy slug.
		 * @param array<string,mixed> $field    Field definition.
		 */
		return apply_filters( 'taxonomy_meta_ui_rest_meta_registration_args', $args, $meta_key, $taxonomy, $field );
	}
}
