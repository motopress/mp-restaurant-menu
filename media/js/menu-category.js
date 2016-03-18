/* global console:false,$:false,jQuery:false, _:false, MP_RM_Registry:false, wp:false */
MP_RM_Registry.register("Menu-Category", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			init: function() {
				$('#IconPicker').fontIconPicker({
					source: $.fnt_icons_categorized,
					emptyIcon: true,
					hasSearch: true
				}).on('change', function() {
					//console.log($(this));
				});

				// remove icon
				$('.remove_icon_button').on('click', function() {
					$(this).siblings('.mprm_icon_p').find('input').attr({'value': ''});
				});

				$('.upload_image_button').on('click', function() {
					state.openUploadWindow();
					return false;
				});

				$('.remove_image_button').on('click', function() {
					$('#menu_category_thumbnail img').attr('src', $('#menu_category_thumbnail img').attr('data-placeholder'));
					$('#menu_category_thumbnail_id').val('');
					$('.remove_image_button').hide();
					return false;
				});
			},
			/**
			 * Open upload window
			 * 
			 * @returns {undefined}
			 */
			openUploadWindow: function() {
				if (this.window === undefined) {
					// Create the media frame.
					this.window = wp.media.frames.downloadable_file = wp.media({
						title: window.admin_lang.choose_image,
						button: {
							text: window.admin_lang.use_image
						},
						multiple: false
					});
					var self = this;
					// When an image is selected, run a callback.
					this.window.on('select', function() {
						var attachment = self.window.state().get('selection').first().toJSON();
						$('#menu_category_thumbnail_id').val(attachment.id);
						$('#menu_category_thumbnail img').attr('src', attachment.sizes.thumbnail.url);
						$('.remove_image_button').show();
					});
				}
				// Finally, open the modal.
				this.window.open();
			}
		};
	}

	return {
		getInstance: function() {
			if (!state) {
				state = createInstance();
			}
			return state;
		}
	};
})(jQuery));


