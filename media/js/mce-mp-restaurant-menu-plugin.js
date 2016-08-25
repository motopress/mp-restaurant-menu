/*global tinymce:false, wp:false, console: false, md5:false, jBox:false, _:false, CommonManager:false, PopupEvents:false,MP_RM_Registry:false*/
(function($) {
	"use strict";
	tinymce.PluginManager.add('mp_restaurant_menu', function(editor, url) {
		var mprmTitle = 'Insert Restaurant Menu Shortcode';

		/**
		 * init change shortcode type
		 */
		function init_change_shortcode_type() {
			$('[name=shortcode_name]').off('change').on('change', function() {
				MP_RM_Registry._get('MP_RM_Functions').showBlocks($(this).val());
			});
		}

		function change_shortcode_view() {
			var shortCode = $('[name="shortcode_name"]').val();
			if (shortCode === 'mprm_items') {
				var elementsMenuItems = $('[name="item_ids"],[name="feat_img"],select[name="categ_name"]');
				var elementsCategoryItems = $('input[name="categ_name"],[name="price_pos"]');
				switch ($('*[data-display="mprm_items"] select[name="view"]').val()) {
					case"simple-list" :
						elementsMenuItems.parents('.mprm-line').addClass('hidden');
						elementsCategoryItems.parents('.mprm-line').removeClass('hidden');
						break;
					case"grid" :
					case"list" :
					default:
						elementsMenuItems.parents('.mprm-line').removeClass('hidden');
						elementsCategoryItems.parents('.mprm-line').addClass('hidden');
						break;
				}
			}
		}

		/**
		 * init shortcode button
		 * @param callBack
		 */
		function inti_insert_button(callBack) {
			$('[data-selector=insert_shortcode]').off("click").on('click', function() {
				var params = parse_form($('[data-selector=shortcode-form]'));
				if (_.isFunction(callBack)) {
					callBack(params);
				}
			});
		}

		/**
		 * init Checkbox change
		 */
		function init_checkbox() {
			$('[data-selector=shortcode-form]').find('input[type=checkbox]').off('change').on('change', function() {
				if ($(this).attr('checked')) {
					$(this).val('1');
				} else {
					$(this).val('');
				}
			});
		}

		/**
		 * Parse form function
		 * @param form
		 * @returns {{}}
		 */
		function parse_form(form) {
			var params = {
				attrs: {},
				name: ''
			};
			form.find('[data-selector=data-line]').each(function(key, value) {
				if ($(value).is(':visible')) {
					var data_item = $(value).find('[data-selector=form_data]');
					if (data_item.length && !_.isNull(data_item.val()) && data_item.val() !== "") {
						if (_.isArray(data_item.val())) {
							params.attrs[data_item.attr('name')] = data_item.val().join(',');
						} else {
							params.attrs[data_item.attr('name')] = data_item.val();
						}
					}
				}
			});
			params.name = $('[data-selector=shortcode_name]').val();
			return params;
		}


		//Gallery Button
		editor.addButton('mp_add_menu', {
			title: mprmTitle,
			image: url + '/../img/shortcode-icon.png',
			//icon: 'dashicons-carrot',
			onclick: function() {
				MP_RM_Registry._get("MP_RM_Functions").callModal('', function() {
						//callback open
						var jbox = this;
						MP_RM_Registry._get("MP_RM_Functions").wpAjax(
							{
								controller: "popup",
								action: "get_shortcode_builder"
							},
							function(data) {
								jbox.setContent(data);
								MP_RM_Registry._get('MP_RM_Functions').showBlocks('mprm_categories');
								init_change_shortcode_type();
								init_checkbox();
								change_shortcode_view();
								$('#mprm-shortcode-form').on('change', '*[data-display="mprm_items"] select[name="view"]', change_shortcode_view);
								$('#mprm-shortcode-form').on('change', 'select[name="shortcode_name"]', change_shortcode_view);
								inti_insert_button(function(params) {
									var shortcode = wp.shortcode.string({
										tag: params.name,
										attrs: params.attrs,
										type: "single"
									});
									editor.insertContent(shortcode);
									jbox.close();
								});
							},
							function(data) {
								console.warn(data);
							}
						);
					}, {
						title: mprmTitle,
						width: 500,
						height: 600
					}
				);
			}
		});
	});
})(window.jQuery);
