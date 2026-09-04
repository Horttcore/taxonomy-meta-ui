jQuery( document ).ready( function( $ ) {
	const Plugin = {

		init() {
			Plugin.$body = $( 'body' );
			Plugin.$newMeta = $( '#new-meta' );
			Plugin.$metaList = $( '#meta-list' );
			Plugin.$deleteList = $( '#meta-delete-list' );
			Plugin.$keySelect = Plugin.$newMeta.find( 'select[name="meta_keys"]' );
			Plugin.$newKey = Plugin.$newMeta.find( '.taxonomy-meta-ui-new-key' );
			Plugin.$newValue = Plugin.$newMeta.find( '.taxonomy-meta-ui-new-value' );
			Plugin.$enterNew = Plugin.$newMeta.find( '.taxonomy-meta-ui-enter-new' );
			Plugin.$cancelNew = Plugin.$newMeta.find( '.taxonomy-meta-ui-cancel-new' );
			Plugin.$addMeta = $( '#add-meta' );

			Plugin.bindings();
		},

		bindings() {
			Plugin.$addMeta.on( 'click', function( event ) {
				event.preventDefault();
				Plugin.addTermMeta();
			} );

			Plugin.$cancelNew.on( 'click', function( event ) {
				event.preventDefault();
				Plugin.cancelNew();
			} );

			Plugin.$enterNew.on( 'click', function( event ) {
				event.preventDefault();
				Plugin.enterNew();
			} );

			Plugin.$keySelect.on( 'change', function() {
				Plugin.$newKey.val( Plugin.$keySelect.val() );
			} );

			Plugin.$body.on( 'click', '.delete-meta-button', function( event ) {
				event.preventDefault();

				const $field = $( this ).closest( '.meta-field' );
				const metaKey = $field.data( 'metaKey' ) || $field.find( '.meta_key' ).val();

				if ( metaKey ) {
					Plugin.$deleteList.append(
						$( '<input>', {
							type: 'hidden',
							name: 'meta_delete[]',
							value: metaKey,
						} ),
					);
				}

				$field.remove();
			} );
		},

		addTermMeta() {
			const metaKey = Plugin.$newKey.val();

			if ( '' === metaKey ) {
				return;
			}

			const $field = $( '<div>', { class: 'meta-field', 'data-meta-key': metaKey } );

			$field.append(
				$( '<input>', {
					name: 'meta_key[]',
					class: 'meta_key',
					type: 'text',
					value: metaKey,
					placeholder: taxonomyMetaUI.name,
				} ),
			);

			$field.append(
				$( '<textarea>', {
					name: 'meta_value[]',
					class: 'meta_value',
					rows: 2,
					placeholder: taxonomyMetaUI.value,
					text: Plugin.$newValue.val(),
				} ),
			);

			$field.append(
				$( '<a>', {
					class: 'button delete-meta-button',
					href: '#',
					text: taxonomyMetaUI.delete,
				} ),
			);

			Plugin.$metaList.append( $field );
			Plugin.cleanMetaFields();
		},

		cancelNew() {
			Plugin.$keySelect.show();
			Plugin.$newKey.hide().val( '' );
			Plugin.$enterNew.show();
			Plugin.$cancelNew.hide();
		},

		cleanMetaFields() {
			Plugin.$keySelect.val( '' );
			Plugin.$newKey.val( '' );
			Plugin.$newValue.val( '' );
		},

		enterNew() {
			Plugin.$keySelect.hide();
			Plugin.$newKey.show().trigger( 'focus' );
			Plugin.$enterNew.hide();
			Plugin.$cancelNew.show();
		},
	};

	if ( $( '#new-meta' ).length ) {
		Plugin.init();
	}
} );
