/* global console:false,$:false,jQuery:false, _:false, MP_RM_Registry:false, wp:false */
MP_RM_Registry.register("Menu-Item", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			imgIds: [],
			init: function() {
				state.imagesInit();
				$('a.mp_menu_gallery').on('click', function() {
					state.openMediaWindow();
				});
			},
			/**
			 * Gallery Init
			 */
			imagesInit: function() {
				$('.mp_menu_images a.mprm-delete').off('click').on('click', function() {
					$(this).parents('li.mprm-image').remove();
					state.refreshImages();
					return false;
				});
				state.refreshImages();
				
				// Image ordering
				$('#mprm-menu-item-gallery .mp_menu_images').sortable({
					items: 'li.mprm-image',
					cursor: 'move',
					scrollSensitivity: 40,
					forcePlaceholderSize: true,
					forceHelperSize: false,
					helper: 'clone',
					opacity: 0.65,
					placeholder: 'mp-metabox-sortable-placeholder',
					start: function(event, ui) {
						ui.item.css('background-color', '#f6f6f6');
					},
					stop: function(event, ui) {
						ui.item.removeAttr('style');
					},
					update: function() {
						state.refreshImages();
					}
				});
			},
			/**
			 * Is mew img
			 * 
			 * @param {type} id
			 * @returns {Boolean}
			 */
			isNewImg: function(order) {
				var image = $('.mp_menu_images li.mprm-image[data-key="' + order + '"]');
				if (parseInt(image.attr('new_image'))) {
					return true;
				} else {
					return false;
				}
			},
			/**
			 * Open add media frame
			 * 
			 * @returns {Boolean}
			 */
			openMediaWindow: function() {
				if (this.window === undefined) {
					this.window = wp.media({
						title: window.admin_lang.insert_media,
						library: {type: 'image'},
						multiple: true,
						button: {text: window.admin_lang.insert}
					});

					var self = this;
					// Needed to retrieve our variable in the anonymous function below
					this.window.on('select', function() {
						var $data = self.window.state().get('selection').toJSON();
						// biuld gallery html 
						$('#mprm-menu-item-gallery .mp_menu_images').append(state._buldImages($data));
						state.imagesInit();
					});
				}
				this.window.open();
				return false;
			},
			/**
			 * Set attachments orders
			 * 
			 * @returns {undefined}
			 */
			refreshImages: function() {
				var ids = [];
				$('#mprm-menu-item-gallery .mp_menu_images li.mprm-image').each(function(key, value) {
					ids[key] = $(value).attr('data-attachment_id');
					$(value).attr('data-key', key);
				});
				$('#mprm-menu-item-gallery input[name="mp_menu_gallery"]').val(ids.join(','));
			},
			/**
			 * Build images 
			 * 
			 * @param {type} $data
			 * @returns {unresolved}
			 */
			_buldImages: function($data) {
				var structure = [];
				$.each($data, function(key, value) {
					structure.push({
						tag: "li",
						attrs: {
							"class": "mprm-image",
							"data-attachment_id": value.id,
							"new_image": 1
						},
						content: [
							{
								tag: "img",
								attrs: {
									"src": value.sizes.thumbnail.url
								}
							}, {
								tag: "ul",
								attrs: {
									"class": "mprm-actions"
								},
								content: [
									{
										tag: "li",
										content: [
											{
												tag: "a",
												attrs: {
													"class": "mprm-delete",
													title: window.admin_lang.delete_img,
													href: "#"
												},
												content: window.admin_lang.delete
											}
										]
									}
								]
							}
						]
					});

				});
				try {
					return MP_RM_Registry._get("HtmlBuilder").getHtml(structure);
				} catch (e) {
					console.log(e);
				}
			},
			/**
			 * add_atribute
			 *
			 * @param {type} $params
			 * @param {type} callback
			 */
			removeImg: function($params, callback) {
				var $args = {
					controller: "menu_item",
					action: "delete_gallery_img"
				};
				$.extend($args, $params);
				MP_RM_Registry._get('MP_RM_Functions').wpAjax($args,
						function(data) {
							if (!_.isUndefined(callback) && _.isFunction(callback)) {
								callback(data);
							}
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
				);
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
