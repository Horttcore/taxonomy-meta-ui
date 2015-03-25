jQuery(document).ready(function(){

	var Plugin = {

		init:function(){

			// Cache
			Plugin.body = jQuery('body');
			Plugin.checkbox = jQuery('#taxonomy-meta-ui-delete-tables');

			// Go
			Plugin.bindings();

		},

		bindings:function(){

			Plugin.checkbox.change(function(){
				jQuery.post(ajaxurl,{
					action: 'taxonomy-meta-ui-delete-tables',
					nonce: taxonomyMetaUI.nonce,
					value: Plugin.checkbox.prop('checked'),
				});

			});

		},

	};

	Plugin.init();

});
