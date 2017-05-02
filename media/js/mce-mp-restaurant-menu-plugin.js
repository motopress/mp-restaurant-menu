/*global tinymce:false, wp:false, console: false, md5:false, jBox:false, _:false, CommonManager:false, PopupEvents:false,MP_RM_Registry:false*/
(function($) {
	"use strict";
	tinymce.PluginManager.add('mp_restaurant_menu', function(editor, url) {
		var mprmTitle = 'Insert Restaurant Menu Shortcode';

		/**
		 * Init change shortcode type
		 */
		function init_change_shortcode_type() {
			$(document).on('change', '[name=shortcode_name]', function() {
				var type = $(this).val();
				load_shortcode_data(type);
			});
		}

		/**
		 * Load shortcode data(Json)
		 * @param type
		 */
		function load_shortcode_data(type) {

			if (_.isUndefined(type)) {
				type = 'categories';
			}

			var $params = {
				action: 'get_shortcode_by_type',
				controller: 'popup',
				type: type
			};

			MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
				function(data) {
					$('#mprm-shortcode-html-container').html(data.html);
					change_shortcode_view();
				},
				function(data) {
					console.warn('Some error!!!');
					console.warn(data);
				}
			);
		}

		/**
		 * Shortcode view
		 */
		function change_shortcode_view() {
			$(document).on('change', '[name="view"]', function() {
				var view = $(this).val();

				if (view === 'simple-list') {
					$('[name="price_pos"]', '.mprm-line').parents('div.mprm-line').removeClass('mprm-hidden');
					$('[name="buy"]', '.mprm-line').parents('div.mprm-line').addClass('mprm-hidden');
					$('[name="feat_img"]', '.mprm-line').parents('div.mprm-line').addClass('mprm-hidden');
					$('[name="categ_name"] option[value="with_img"]', '.mprm-line').addClass('mprm-hidden');
				} else {
					$('[name="price_pos"]', '.mprm-line').parents('div.mprm-line').addClass('mprm-hidden');
					$('[name="buy"]', '.mprm-line').parents('div.mprm-line').removeClass('mprm-hidden');
					$('[name="feat_img"]', '.mprm-line').parents('div.mprm-line').removeClass('mprm-hidden');
					$('[name="categ_name"] option[value="with_img"]', '.mprm-line').removeClass('mprm-hidden');
				}

			});
		}

		/**
		 * Init shortcode button
		 *
		 * @param callBack
		 */
		function init_insert_button() {
			$(document).on('click.insert', '[data-selector=insert_shortcode]', function() {
				var params = parse_form($('[data-selector=shortcode-form]'));
				var shortcode = wp.shortcode.string({
					tag: params.name,
					attrs: params.attrs,
					type: "single"
				});
				editor.insertContent(shortcode);
				jbox.close();
			});
		}

		/**
		 * init Checkbox change
		 */
		function init_checkbox() {
			$(document).on('click', '[data-selector="shortcode-form"] input[type="checkbox"]', function() {
				if ($(this).attr('checked')) {
					$(this).val('1');
				} else {
					$(this).val('0');
				}
			});
		}

		/**
		 * Parse form function
		 *
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

					if (data_item.length) {
						if (_.isArray(data_item.val())) {
							params.attrs[data_item.attr('name')] = data_item.val().join(',');
						} else {
							params.attrs[data_item.attr('name')] = _.isNull(data_item.val()) ? '' : data_item.val();
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
						window.jbox = this;
						MP_RM_Registry._get("MP_RM_Functions").wpAjax(
							{
								controller: "popup",
								action: "get_shortcode_builder"
							},
							function(data) {
								jbox.setContent(data);
							},
							function(data) {
								console.warn(data);
							}
						);
					},
					{
						title: mprmTitle,
						width: 500,
						height: 600
					}
				);
			}
		});

		/**
		 * Init handlers
		 */
		init_change_shortcode_type();
		init_checkbox();
		change_shortcode_view();
		init_insert_button();

	});
})(window.jQuery);