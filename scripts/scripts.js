jQuery(document).ready(function(){

	var Plugin = {

		init:function(){

			// Cache
			Plugin.body = jQuery('body');
			Plugin.metaKeys = jQuery('select[name="meta_keys"]');
			Plugin.addMetaButton = jQuery('#add-meta');
			Plugin.enterNewButton = jQuery('#enternew');
			Plugin.cancelNewButton = jQuery('#cancelnew');
			Plugin.metaList = jQuery('#meta-list');
			Plugin.metaKey = jQuery('#meta_key');
			Plugin.metaValue = jQuery('#meta_value');
			Plugin.isNewTerm = Plugin.metaKey.parents('.form-field:first').hasClass('term-custom-fields-new');
			Plugin.submit = jQuery('#submit');

			// Go
			Plugin.bindings();

		},

		bindings:function(){

			Plugin.addMetaButton.click(function(e){
				e.preventDefault();
				Plugin.addTermMeta();
			});

			Plugin.cancelNewButton.click(function(e){
				e.preventDefault();
				Plugin.cancelNew();
			});

			Plugin.enterNewButton.click(function(e){
				e.preventDefault();
				Plugin.enterNew();
			});

			Plugin.metaKeys.change(function(e){
				e.preventDefault();
				Plugin.metaKey.val(Plugin.metaKeys.val());
			});

			Plugin.body.on('click', '.delete-meta-button',function(e){
				e.preventDefault();
				jQuery(this).parents('.meta-field:first').remove();
			});

			Plugin.submit.click(function(e){
				if ( Plugin.isNewTerm )
					Plugin.metaList.html('');
			});

		},

		addTermMeta:function(){

			if ( '' === Plugin.metaKey.val() )
				return;

			var output = '<div class="meta-field"><input name="meta_key[]" class="meta_key" type="text" value="' + Plugin.metaKey.val() + '" placeholder="' + taxonomyMetaUI.name + '">' +
						 '<textarea name="meta_value[]" class="meta_value" id="meta_value" rows="2" placeholder="' + taxonomyMetaUI.value + '">' + Plugin.metaValue.val() + '</textarea>' +
						 '<a class="button delete-meta-button" href="#">' + taxonomyMetaUI.delete + '</a></div>';

			Plugin.metaList.append(output);
			Plugin.cleanMetaFields();

		},

		cancelNew:function(){
			Plugin.metaKeys.show();
			Plugin.metaKey.hide();
			Plugin.metaKey.val('');
			Plugin.enterNewButton.show();
			Plugin.cancelNewButton.hide();
		},

		cleanMetaFields:function(){
			Plugin.metaKeys.val('');
			Plugin.metaKey.val('');
			Plugin.metaValue.val('');
		},

		enterNew:function(){
			Plugin.metaKeys.hide();
			Plugin.metaKey.show().focus();
			Plugin.enterNewButton.hide();
			Plugin.cancelNewButton.show();
		},

	};

	Plugin.init();

});
