Taxonomy Meta UI
===================

## Description

Custom field support for taxonomy terms with admin UI, REST API, and block binding support.

## Installation

* Put the plugin in your plugin directory and activate it in your WordPress backend.

## Usage

Visit any term add/edit screen to manage term meta.

### Static fields

Register predefined fields via filters:

```php
add_filter( 'term_fields_division', function ( array $fields ): array {
    $fields['mailing_address'] = [
        'label' => __( 'Mailing address', 'my-theme' ),
        'name'  => 'mailing_address',
        'type'  => 'textarea',
    ];

    $fields['status'] = [
        'label'   => __( 'Status', 'my-theme' ),
        'name'    => 'status',
        'type'    => 'select',
        'options' => [
            'open'   => __( 'Open', 'my-theme' ),
            'closed' => __( 'Closed', 'my-theme' ),
        ],
    ];

    return $fields;
} );
```

#### Field options

| Key | Description |
|-----|-------------|
| `name` | Meta key (required) |
| `label` | Admin label |
| `type` | `textarea` (default), `text`, or `select` |
| `placeholder` | Input placeholder |
| `description` | Help text below the field |
| `options` | For `select`: `value => label` pairs |
| `render_callback` | `fn( string $name, mixed $value, array $field, ?WP_Term $term, string $context ): void` |
| `sanitize_callback` | `fn( mixed $value, array $field, int $term_id ): mixed` |
| `escape_callback` | `fn( mixed $value, array $field ): string` |
| `auth_callback` | `fn( bool $allowed, string $context, int $term_id, string $meta_key ): bool` |
| `block_binding` | Expose in block bindings (default: `true`) |
| `block_binding_render` | `fn( mixed $value, int $term_id, string $taxonomy ): ?string` |
| `show_in_rest` | Register for REST API (default: `true`) |
| `rest_type` | REST schema type (default: `string`) |

### Block bindings

Registered static fields with `show_in_rest` and `block_binding` are available in the block editor.

**Generic source** (like `core/post-meta`):

```html
<!-- wp:paragraph {
    "metadata":{
        "bindings":{
            "content":{
                "source":"taxonomy-meta-ui/term-meta",
                "args":{"key":"mailing_address"}
            }
        }
    }
} -->
<p></p>
<!-- /wp:paragraph -->
```

**Per-field source** (e.g. `taxonomy-meta-ui/mailing-address`) is also registered automatically.

Requires term context (`termId`, `taxonomy`) from blocks such as `core/term-template`.

### WordPress functions

* `add_term_meta( $term_id, $meta_key, $meta_value, $unique = false )`
* `update_term_meta( $term_id, $meta_key, $meta_value, $prev_value = '' )`
* `delete_term_meta( $term_id, $meta_key, $meta_value = '' )`
* `get_term_meta( $term_id, $key, $single = false )`

## Hooks

### Filters

| Filter | Description |
|--------|-------------|
| `term_fields` | Static fields for all taxonomies |
| `term_fields_{$taxonomy}` | Static fields for a taxonomy |
| `has_custom_fields` | Disable free-form custom fields UI globally |
| `{taxonomy}_has_custom_fields` | Disable free-form custom fields UI per taxonomy |
| `{taxonomy}_has_meta` | Disable all meta support per taxonomy |
| `taxonomy_meta_ui_rest_meta_keys` | Extra REST meta keys per taxonomy |
| `taxonomy_meta_ui_rest_meta_registration_args` | REST registration args per meta key |

## Changelog

### 1.5.0

* Fixed: security (nonce, capability checks, sanitization, escaping)
* Fixed: custom fields lost on new term submit
* Fixed: delete now removes meta from database
* Fixed: meta key dropdown labels
* Fixed: early return when `meta_key` is absent (no `$_POST` shim needed)
* Added: `select` field type
* Added: `render_callback`, `sanitize_callback`, `escape_callback`, `auth_callback`
* Added: block binding support (`taxonomy-meta-ui/term-meta`)
* Added: per-field block binding sources
* Removed: broken multisite hook

### 1.4.0

* Added: REST API registration for static fields

### 1.3.0

* Fixing things…

### 1.2

* Added: `term_fields_$taxonomy` filter
* Added: `$taxonomy_has_custom_fields` filter
* Added: `has_custom_fields` filter
* Added: Placeholder parameter for static fields

### 1.1

* Added: `term_fields` filter
* Enhancement: Create predefined meta fields

### 1.0

* Initial release
