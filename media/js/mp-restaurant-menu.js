/* globals jQuery:false, MP_RM_Registry:false, _:false,wp:false,jBox:false,mprm_admin_vars:false,tb_show:false,tb_remove:false,confirm:false,console:false,alert:false,magnificPopup:false*/
window.MP_RM_Registry = (function() {
	"use strict";
	var modules = {};

	/**
	 * Test module
	 *
	 * @param module
	 * @returns {boolean}
	 * @private
	 */
	function _testModule(module) {

		return (typeof module.getInstance) === 'function';
	}

	/**
	 * Register module
	 *
	 * @param name
	 * @param module
	 */
	function register(name, module) {
		if (_testModule(module)) {
			modules[name] = module;
		} else {
			throw new Error('Invalide module "' + name + '". The function "getInstance" is not defined.');
		}
	}

	/**
	 * Register modules
	 *
	 * @param map
	 */
	function MP_RM_RegistryMap(map) {
		for (var name in map) {
			if (!map.hasOwnProperty(name)) {
				continue;
			}
			if (_testModule(map[name])) {
				modules[name] = map[name];
			} else {
				throw new Error('Invalide module "' + name + '" inside the collection. The function "getInstance" is not defined.');
			}
		}
	}

	/**
	 * Unregister module
	 *
	 * @param name
	 */
	function unregister(name) {
		delete modules[name];
	}

	/**
	 * Get instance module
	 *
	 * @param name
	 * @returns {*|wp.mce.View}
	 */
	function _get(name) {
		var module = modules[name];
		if (!module) {
			throw new Error('The module "' + name + '" has not been registered or it was unregistered.');
		}

		if (typeof module.getInstance !== 'function') {
			throw new Error('The module "' + name + '" can not be instantiated. ' + 'The function "getInstance" is not defined.');
		}

		return modules[name].getInstance();
	}

	return {
		register: register,
		unregister: unregister,
		_get: _get,
		MP_RM_RegistryMap: MP_RM_RegistryMap
	};

})();

/**
 * Global function
 */
MP_RM_Registry.register("MP_RM_Functions", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			init: function() {

			},
			/**
			 * WP Ajax
			 *
			 * @param {Object} params
			 * @param {function} callbackSuccess
			 * @param {function} callbackError
			 * @returns {Object}
			 */
			wpAjax: function(params, callbackSuccess, callbackError) {
				params.mprm_action = params.action;
				delete params.action;
				wp.ajax.send("route_url", {
					success: function(data) {
						if (!_.isUndefined(callbackError) && _.isFunction(callbackError)) {
							callbackSuccess(data);
						}
					},
					error: function(data) {
						if (!_.isUndefined(callbackError) && _.isFunction(callbackError)) {
							callbackError(data);
						} else {
							console.log(data);
						}
					},
					data: params
				});
			},
			/**
			 * Parse URI
			 *
			 * @param url
			 * @param name
			 * @returns {*}
			 */
			getParameterByName: function(url, name) {
				var vars = [], hash;
				if (url) {
					var hashes = url.slice(url.indexOf('?') + 1).split('&');
					for (var i = 0; i < hashes.length; i++) {
						hash = hashes[i].split('=');
						vars.push(hash[0]);
						vars[hash[0]] = hash[1];
					}
					if ((typeof name) !== "undefined") {
						return vars[name];
					}
					return vars;
				} else {
					return false;
				}
			},
			/**
			 * String convert to Bool
			 * @param string
			 * @returns {boolean}
			 */
			stringToBoolean: function(string) {
				switch (string.toLowerCase().trim()) {
					case "true":
					case "yes":
					case "1":
						return true;
					case "false":
					case "no":
					case "0":
					case null:
						return false;
					default:
						return Boolean(string);
				}
			},

			/**
			 * Open popup window function
			 *
			 * @param start_content
			 * @param open_callback
			 * @param args
			 */
			callModal: function(start_content, open_callback, args) {
				start_content = (_.isEmpty(start_content)) ? spinner : start_content;
				var height = $(window).outerHeight() - 60,
					width = $(window).outerWidth() - 60,
					spinner = wp.html.string({
							tag: "span",
							attrs: {
								class: "spinner is-active"
							},
							content: ""
						}
					),
					params = {
						content: start_content,
						closeOnEsc: true,
						animation: {open: 'zoomIn', close: 'zoomOut'},
						width: width,
						height: height,
						closeButton: "box",
						addClass: 'mprm-modal mprm-restaurant',
						onOpen: function() {
							var jbox_container = $("#" + this.id);
							open_callback.call(this, jbox_container);
						},
						onClose: function() {
							$("#" + this.id).remove();
						}
					};
				if (!_.isUndefined(args)) {
					$.extend(params, args);
				}
				var popup = new jBox('Modal', params);
				popup.open();
			},
			/**
			 * In array
			 *
			 * @param {type} array
			 * @param {type} search
			 * @returns {Array}
			 */
			inArray: function(array, search) {
				var result = [],
					key = $.inArray(search, array);
				if (key >= 0) {
					result.push(array[key]);
				}
				return result;
			},
			/**
			 * Validate form
			 * @param formSelectorByID
			 * @returns {boolean}
			 */
			validateForm: function(formSelectorByID) {
				if (formSelectorByID) {

					var formObject = $('#' + formSelectorByID),
						form = document.getElementById(formSelectorByID);

					if (typeof form.checkValidity === "function" && false === form.checkValidity()) {
						formObject.find(":invalid").addClass('mprm-form-error');
						// var firstField = false;
						formObject.find('input').not(":submit, :reset, :image, [disabled], :hidden").each(function() {
							if (!this.validity.valid) {
								$(this).focus();
							}
						});
						$("input", formObject).off('keypress').on("keypress", function(event) {
							var type = $(this).attr("type");
							if (!(/date|email|month|number|search|tel|text|time|url|week/.test(type) && event.keyCode === 13)) {
								$(this).removeClass('mprm-form-error');
							}
						});

						$("select", formObject).off('change').on("change", function() {
							$(this).removeClass('mprm-form-error');
						});

						return false;
					} else {
						return true;
					}
				} else {
					return false;
				}
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

/**
 * Html build function
 */
MP_RM_Registry.register("HtmlBuilder", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			/**
			 * example json
			 */
			_singleHtmlTag: {
				tag: "div",
				attrs: {
					"id": 356,
					"class": "item",
					"data-selector": "some-button"
				},
				content: "<%= data-key %>"
			},
			_htmlStructure: {
				tag: "div",
				attrs: {
					"id": 356,
					"class": "item",
					"data-selector": "some-button"
				},
				content: {
					tag: "div",
					attrs: {
						"class": "title"
					},
					content: "<%= data-key %>"
				}
			},
			_htmlStructureWithArray: {
				tag: "div",
				attrs: {
					"id": 356,
					"class": "item",
					"data-selector": "some-button"
				},
				content: [{
					tag: "div",
					attrs: {
						"class": "title"
					},
					content: "<%= data-key %>"
				}, {
					tag: "span",
					attrs: {
						"class": "date"
					},
					content: "<%= data-key %>"
				}]
			},
			/**
			 * Generate HTML
			 *
			 * @param params - json
			 * @returns {string|*|n|string}
			 */
			generateHTML: function(params) {
				var content = "",
					result;
				if (_.isObject(params)) {
					var element = document.createElement(params.tag);
					if (!_.isUndefined(params.attrs)) {
						$.each(params.attrs, function(key, value) {
							element.setAttribute(key, value);
						});
					}
					if (_.isArray(params.content)) {

						$.each(params.content, function(key, value) {
							content += state.generateHTML(value);
						});
						$(element).html(content);
					} else if (_.isObject(params.content)) {
						content = state.generateHTML(params.content);
						$(element).html(content);
					} else {
						if (!_.isUndefined(params.content)) {
							$(element).html(params.content);
						} else {
							$(element).html("");
						}
					}
					result = $(element).get(0).outerHTML;
				} else if (_.isString(params)) {
					result = params;
				} else {
					result = false;
				}
				return result;
			},
			/**
			 * Put the data to hetml code and return here
			 *
			 * @param $template
			 * @param $data
			 * @returns {boolean}
			 */
			getHtml: function($template, $data) {
				if (_.isUndefined($template)) {
					return false;
				}
				var result = false;
				if (_.isUndefined($data)) {
					if (_.isArray($template)) {
						result = "";
						$.each($template, function(key, value) {
							result += state.generateHTML(value);
						});
					} else {
						result = state.generateHTML($template);
					}
				}
				if (_.isObject($data)) {
					var template = _.template(result);
					result = template($data);
				}
				return result;
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

/**
 * Html build function
 */
MP_RM_Registry.register("Menu-Shop", (function($) {
	"use strict";
	var state;
	var delayTimer;

	function createInstance() {
		return {
			/**
			 * Init preloader
			 *
			 * @param view
			 * @param container
			 */
			initPreloader: function(view, container) {
				if (view === 'show') {
					container.find('.mprm-add-menu-item .mprm-add-to-cart').addClass('mprm-preloader-color');
					container.find('.mprm-add-menu-item .mprm-container-preloader .mprm-floating-circle-wrapper').removeClass('mprm-hidden');
				} else {
					container.find('.mprm-add-menu-item .mprm-container-preloader .mprm-floating-circle-wrapper').addClass('mprm-hidden');
					container.find('.mprm-add-menu-item .mprm-add-to-cart').removeClass('mprm-preloader-color');
				}
			},

			/**
			 * Add to cart
			 */
			addToCart: function() {

				$('.mprm-add-to-cart.mprm-has-js').on('click', function(e) {
					e.preventDefault();

					var $this = $(this);
					var form = $this.closest('form');
					var $params = form.serializeArray();
					var noticeContainer = form.parents('.mprm_menu_item_buy_button').find('> .mprm-notice');
					var parentContainer = form.parents('.mprm_menu_item_buy_button');
					$('.mprm_menu_item_buy_button').find('> .mprm-notice').addClass('mprm-hidden');

					$params.push({
						name: "is_ajax",
						value: true
					});

					if ($('.widget_mprm_cart_widget').length) {
						$params.push({
							name: "cart",
							value: true
						});
					}

					state.initPreloader('show', parentContainer);

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,

						function(data) {

							noticeContainer.addClass('mprm-notice-success');
							noticeContainer.removeClass('mprm-hidden mprm-notice-error');

							$('.widget_mprm_cart_widget .mprm-cart-content').html(data.cart);

							state.initPreloader('hide', parentContainer);

							if (!data.redirect) {
								$('.mprm-cart-added-alert', form).fadeIn();
								setTimeout(function() {
									$('.mprm-cart-added-alert', form).fadeOut();
								}, 3000);
							} else {
								window.location = data.redirect;
							}
						},
						function(data) {
							var noticeContainer = form.parents('.mprm_menu_item_buy_button').find('.mprm-notice');

							noticeContainer.addClass('mprm-notice-error').removeClass('mprm-hidden');

							state.initPreloader('hide', parentContainer);

							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});
			},
			/**
			 *  Change gateway
			 */
			changeGateway: function() {
				$('input[name=payment-mode]', '#mprm_purchase_form').on('change', function() {
					$('#mprm_purchase_form_wrap').html('');
					state.loadGateway();
				});
			},
			/**
			 * Load gateway
			 */
			loadGateway: function() {
				var gateway = $('input[name=payment-mode]:checked', '#mprm_purchase_form').val();
				if (!!gateway) {
					var $params = [
						{
							name: 'controller',
							value: 'cart'
						},
						{
							name: 'mprm_action',
							value: 'load_gateway'
						},
						{
							name: 'payment-mode',
							value: gateway
						}
					];

					$('.mprm-cart-ajax').show();

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							$('.mprm-no-js:not(.mprm-add-to-cart)').hide();
							$('#mprm_purchase_form_wrap').html(data.html);
							state.showTerms();

						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				}
			},
			/**
			 * Update item quantities
			 */
			update_item_quantities: function() {


				$('.mprm-item-quantity').on('change', function() {
					var stepper = this;
					clearTimeout(delayTimer);
					delayTimer = setTimeout(function() {
						var $this = $(stepper),
							quantity = $this.val(),
							key = $this.data('key'),
							menu_item_id = $this.closest('.mprm_cart_item').data('menu-item-id'),
							options = $this.parent().find('input[name="mprm-cart-menu-item-' + key + '-options"]').val();

						var $params = {
							action: 'update_cart_item_quantity',
							controller: 'cart',
							quantity: quantity,
							menu_item_id: menu_item_id,
							options: options,
							position: key
						};

						MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
							/**
							 *
							 * @param {Object} data
							 * @param {string} data.taxes
							 * @param {string} data.subtotal
							 * @param {string} data.total
							 */
							function(data) {
								$('.mprm_cart_subtotal_amount').each(function() {
									var element = $(this);
									element.text(data.subtotal);
									element.attr('data-subtotal', data.subtotal);
									element.attr('data-total', data.subtotal);
								});

								$('.mprm_cart_tax_amount').each(function() {
									$(this).text(data.taxes);
								});

								$('.mprm_cart_amount').each(function() {
									var element = $(this);
									element.text(data.total);
									element.attr('data-subtotal', data.total);
									element.attr('data-total', data.total);
								});
							},
							function(data) {
								console.warn('Some error!!!');
								console.warn(data);
							}
						);
					}, 1000);
				});

			},
			/**
			 * Purchase form
			 */
			purchaseForm: function() {
				$(document).on('click', '#mprm_purchase_form #mprm_purchase_submit input[type=submit]', function(e) {

					var form = $(this).parents('form');
					if (form.length) {
						if (!form.hasClass('mprm-no-js')) {

							var purchaseForm = document.getElementById('mprm_purchase_form');

							if (!MP_RM_Registry._get('MP_RM_Functions').validateForm('mprm_purchase_form')) {
								return;
							}

							e.preventDefault();

							$(this).after('<span class="mprm-cart-ajax"><i class="mprm-icon-spinner mprm-icon-spin"></i></span>');
							var $params = $(purchaseForm).serializeArray();

							$.each($params, function(index, element) {
								if (element) {
									if (element.name === "mprm_action" && element.value === "gateway_select") {
										$params.splice(index, 1);
									}
								}
							});

							$('.mprm-cart-ajax').show();
							$('.mprm-errors').remove();

							MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
								function(data) {
									if (data.errors) {
										$('#mprm_final_total_wrap').before(data.errors);
									} else {
										$('#mprm_purchase_form').submit();
									}
								},
								function(data) {
									if (data.error) {
										$('#mprm_final_total_wrap').before(data.errors);
									}
									$('.mprm-cart-ajax').remove();
									console.warn('Some error!!!');
									console.warn(data);
								}
							);
						}
					}
				});

			},
			/**
			 * Terms show/hide
			 */
			showTerms: function() {
				$('#mprm_show_terms').find('.mprm_terms_links').on('click', function(e) {
					e.preventDefault();
					$(this).parents('#mprm_show_terms').find('.mprm_terms_links').toggle();
					$('#mprm_terms').toggle();
				});
			},
			/**
			 * Get login form
			 */
			get_login: function() {
				$('#mprm_checkout_form_wrap').on('click', '.mprm_checkout_register_login', function(e) {
					e.preventDefault();
					var $this = $(this),
						$params = {
							action: 'get_login',
							controller: 'customer'
						};

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							$this.parent().html(data.html);

						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});
			},
			loginAjax: function() {
				$('#mprm_checkout_form_wrap').on('click', '#mprm_login_submit,[name="mprm_login_submit"]', function(e) {
					e.preventDefault();
					$('.mprm-errors').remove();

					var $params = {
						action: 'login_ajax',
						controller: 'customer',
						nonce: $('[name="mprm_login_nonce"]').val(),
						redirect: $('[name="redirect"]').val(),
						pass: $('[name="mprm_user_pass"]').val(),
						login: $('[name="mprm_user_login"]').val()

					};

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						/**
						 *
						 * @param {Object} data
						 * @param {string} data.redirect_url
						 * @param {string} data.redirect
						 */
						function(data) {
							if (data.redirect) {
								window.location = data.redirect_url;
							} else {
								window.location.reload();
							}
						},
						function(data) {
							if (data.data.html) {
								$('#mprm_checkout_form_wrap').find('.mprm-login-fields').after(data.data.html);
							} else {
								console.warn('Some error!!!');
								console.warn(data);
							}

						}
					);
				});

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

/**
 * Html build function
 */
MP_RM_Registry.register("Order", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			init: function() {
				state.hideElementOrder();
				state.addComment();
				state.removeComment();
				// state.initChosen();
				state.addCustomer();
				state.removeMenuItem();
				state.addMenuItem();
				state.recalculate_total();
				state.changeOrderBaseCountry();
				state.changeCustomer();
			},

			/**
			 * Change order Base Country
			 */
			changeOrderBaseCountry: function() {

				if ($("[name='mprm_settings[base_state]'] option").length < 1) {
					$("[name='mprm_settings[base_state]']").parents('tr').hide();
				}

				$("select.mprm-country-list").on('change', function() {
					var $params = {
						action: 'get_state_list',
						controller: 'settings',
						country: $(this).val()
					};

					var $parent = $(this).parents('.mprm-columns.mprm-four');
					var stateSelect = $parent.find('select.mprm-country-state');

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							stateSelect.parents('#mprm-order-address-state-wrap').hide();
							if ($.isEmptyObject(data)) {
								stateSelect.parents('#mprm-order-address-state-wrap').hide();
							} else {
								stateSelect.find("option").remove();
								$.each(data, function(i, value) {
									stateSelect.append($('<option>').text(value).attr('value', i));
								});
								stateSelect.trigger("chosen:updated");
								stateSelect.parents('#mprm-order-address-state-wrap').show();
							}
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});
			},
			/**
			 * Add comment
			 */
			addComment: function() {
				$('#mprm-add-order-note').on('click', function(e) {

					e.preventDefault();
					var $params = {
						action: 'add_comment',
						controller: 'order',
						order_id: $(this).attr('data-order-id'),
						noteText: $('#mprm-order-note').val()
					};

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							$('.mprm-no-order-notes').hide();
							$('#mprm-order-notes-inner').append(data.html);
							$('#mprm-order-note').val('');
							state.removeComment();
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});
			},
			/**
			 * Add menu item
			 *
			 */
			addMenuItem: function() {
				$('[name="mprm-order-menu-item-select"]').on('change', function() {

					var $params = {
						action: 'get_price',
						controller: 'menu_item',
						menu_item: $(this).val()
					};

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							$('[name="mprm-order-menu-item-amount"]').val(data.price);
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);

				});
				/**
				 * Add menu item
				 * @param {Object} mprm_admin_vars
				 * @param {String} mprm_admin_vars.numeric_item_price
				 * @param {Number} mprm_admin_vars.currency_decimals
				 * @param {String} mprm_admin_vars.currency_sign
				 * @param {String} mprm_admin_vars.enable_taxes
				 * @param {String} mprm_admin_vars.quantities_enabled
				 * @param {String} mprm_admin_vars.currency_pos
				 * @param {String} mprm_admin_vars.numeric_tax
				 * @param {String} mprm_admin_vars.numeric_quantity
				 */
				$('#mprm-order-add-menu-item').on('click', function(e) {

					e.preventDefault();

					var order_menu_item_select = $('[name="mprm-order-menu-item-select"]'),
						order_menu_item_quantity = $('#mprm-order-menu-item-quantity'),
						order_menu_item_amount = $('[name="mprm-order-menu-item-amount"]');

					var menu_item_id = order_menu_item_select.val(),
						menu_item_title = $('[name="mprm-order-menu-item-select"] option:selected').text(),
						quantity = order_menu_item_quantity.val(),
						amount = order_menu_item_amount.val(),
						price_id = 0,
						price_name = false,
						tax = 0;

					if (menu_item_id < 1) {
						return false;
					}

					if (!amount) {
						amount = 0;
					}

					amount = parseFloat(amount);

					if (isNaN(amount)) {
						alert(mprm_admin_vars.numeric_item_price);
						return false;
					}

					var item_price = amount;

					if (mprm_admin_vars.quantities_enabled === '1') {
						if (!isNaN(parseInt(quantity))) {
							amount = amount * quantity;
						} else {
							alert(mprm_admin_vars.numeric_quantity);
							return false;
						}
					}

					var formatted_item_price = ( amount / quantity ).toFixed(mprm_admin_vars.currency_decimals) + mprm_admin_vars.currency_sign;

					if ('before' === mprm_admin_vars.currency_pos) {
						formatted_item_price = mprm_admin_vars.currency_sign + ( amount / quantity ).toFixed(mprm_admin_vars.currency_decimals);
					}

					if (MP_RM_Registry._get('MP_RM_Functions').stringToBoolean(mprm_admin_vars.enable_taxes)) {
						if (mprm_admin_vars.rate > 1) {
							// Convert to a number we can use
							var rate = mprm_admin_vars.rate / 100;

							if (!isNaN(rate)) {
								tax = amount * rate;
								amount = parseFloat(amount) + parseFloat(tax);
							} else {
								alert(mprm_admin_vars.numeric_tax);
								return false;
							}
						}
					}

					amount = amount.toFixed(mprm_admin_vars.currency_decimals);

					// formatted amount
					var formatted_amount = amount + mprm_admin_vars.currency_sign;

					if ('before' === mprm_admin_vars.currency_pos) {
						formatted_amount = mprm_admin_vars.currency_sign + amount;
					}

					if (price_name) {
						menu_item_title = menu_item_title + ' - ' + price_name;
					}

					var count = $('.mprm-row.item').length;
					var clone = $('#mprm-purchased-wrapper').find('.mprm-row.item:last').clone();

					clone.find('.menu_item span').html('<a href="post.php?post=' + menu_item_id + '&action=edit"></a>');
					clone.find('.item span a').text(menu_item_title);
					clone.find('.price-text').text(formatted_amount);
					clone.find('.item-quantity').text(quantity);
					clone.find('.item-price').text(formatted_item_price);

					clone.find('input.mprm-order-detail-id').val(menu_item_id);
					clone.find('input.mprm-order-detail-price-id').val(price_id);
					clone.find('input.mprm-order-detail-item-price').val(item_price);
					clone.find('input.mprm-order-detail-amount').val(amount);
					clone.find('input.mprm-order-detail-tax').val(tax);
					clone.find('input.mprm-order-detail-quantity').val(quantity);
					clone.find('input.mprm-order-detail-has-log').val(0);

					// Replace the name / id attributes
					clone.find('input').each(function() {
						var name = $(this).attr('name');

						name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');

						$(this).attr('name', name).attr('id', name);
					});

					// Flag the Menu items section as changed
					$('#mprm-payment-menu-items-changed').val(1);

					$(clone).insertAfter('#mprm-purchased-wrapper .mprm-row.item:last');

					$('.mprm-order-recalc-totals').show();
				});
			},
			/**
			 *  Recalculate order total
			 */
			recalculate_total: function() {
				$('#mprm-order-recalc-total').on('click', function(e) {
					e.preventDefault();

					var total = 0,
						purchased = $('#mprm-purchased-wrapper').find('.mprm-row.item .mprm-order-detail-amount'),
						tax = 0.00;

					if (purchased.length) {
						purchased.each(function() {
							total += parseFloat($(this).val());
						});
					}

					if ($('.mprm-order-fees').length) {
						$('.mprm-order-fees span.fee-amount').each(function() {
							total += parseFloat($(this).data('fee'));
						});
					}

					if ($('.mprm-order-detail-tax').length) {
						$('.mprm-order-detail-tax', '#mprm-purchased-wrapper').each(function() {
							tax += parseFloat($(this).val());
						});
					}

					$('input[name="mprm-order-total"]').val(total.toFixed(mprm_admin_vars.currency_decimals));
					$('input[name="mprm-order-tax"]').val(tax.toFixed(mprm_admin_vars.currency_decimals));

					$('.mprm-order-recalc-totals').hide();
				});

			},
			/**
			 * Remove menu item
			 */
			removeMenuItem: function() {

				$('.mprm-order-remove-menu-item.mprm-delete').on('click', function(e) {
					e.preventDefault();

					var count = $(document.body).find('#mprm-purchased-wrapper > .mprm-row').length;

					if (count === 1) {
						alert(mprm_admin_vars.one_menu_item_min);
						return false;
					}

					if (confirm(mprm_admin_vars.delete_payment_menu_item)) {
						var key = $(this).data('key');
						var orderRemovedInput = $('input[name="mprm-order-removed"]');
						//var purchase_id = $('.mprm-order-id').val();
						var menu_item_id = $('input[name="mprm-order-details[' + key + '][id]"]').val();
						var price_id = $('input[name="mprm-order-details[' + key + '][price_id]"]').val();
						var quantity = $('input[name="mprm-order-details[' + key + '][quantity]"]').val();
						var amount = $('input[name="mprm-order-details[' + key + '][amount]"]').val();

						var currently_removed = orderRemovedInput.val();
						currently_removed = $.parseJSON(currently_removed);
						if (currently_removed.length < 1) {
							currently_removed = {};
						}

						currently_removed[key] = [{'id': menu_item_id, 'price_id': price_id, 'quantity': quantity, 'amount': amount, 'cart_index': key}];

						orderRemovedInput.val(JSON.stringify(currently_removed));

						$(this).parents('.mprm-row').remove();

						// Flag the Menu items section as changed
						$('#mprm-order-menu-items-changed').val(1);

						$('.mprm-order-recalc-totals').show();
					}
					return false;
				});
			},
			/**
			 * Change customer in dropdown
			 */
			changeCustomer: function() {

				$('[name="customer-id"]').on('change', function() {
					var $params = {
						action: 'get_customer_information',
						controller: 'customer',
						customer_id: $(this).val()
					};

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						/**
						 *
						 * @param {Object} data
						 */
						function(data) {
							$('.mprm-customer-information').html(data.customer_information);
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});

			},
			/**
			 * Add new customer
			 */
			addCustomer: function() {
				var customerInfoRow = $('.customer-info.mprm-row');
				var newCustomerRow = $('.new-customer.mprm-row');
				var newCustomerInputObject = $('[name="mprm-new-customer"]');

				$('.mprm-new-customer').on('click', function() {
					customerInfoRow.hide();
					newCustomerRow.show();
					newCustomerInputObject.val(1);
				});

				$('.mprm-new-customer-cancel').on('click', function() {
					customerInfoRow.show();
					newCustomerRow.hide();
					newCustomerInputObject.val(0);
				});

				$('.mprm-new-customer-save').on('click', function(e) {

					e.preventDefault();
					var $params = {
						action: 'add_customer',
						controller: 'customer',
						name: $('[name="mprm-new-customer-name"]').val(),
						email: $('[name="mprm-new-customer-email"]').val(),
						phone: $('[name="mprm-new-phone-number"]').val()
					};

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							customerInfoRow.show();
							newCustomerRow.hide();
							$('[name="customer-id"]').replaceWith(data.html);
							$('.mprm-customer-information').html(data.customer_information);
						},
						function(data) {
							customerInfoRow.show();
							newCustomerRow.hide();
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});
			},
			/**
			 * Init chosen
			 */
			initChosen: function() {
				var selector = $('.mprm-select-chosen');

				if (selector.length) {
					$.each(selector, function() {
						var selectObject = $(this);
						var text_single = typeof selectObject.attr('data-text_single') !== "undefined" ? selectObject.attr('data-text_single') : mprm_admin_vars.one_option;
						var text_multiple = typeof selectObject.attr('data-text_multiple') !== "undefined" ? selectObject.attr('data-text_multiple') : mprm_admin_vars.one_or_more_option;
						selectObject.chosen({
							inherit_select_classes: true,
							placeholder_text_single: text_single,
							placeholder_text_multiple: text_multiple
						});
					});
				}

			},
			/**
			 * Remove comment
			 */
			removeComment: function() {
				$(document).on('click.remove_order_note', '.mprm-delete-order-note', function(e) {
					e.preventDefault();
					var note_id = $(this).attr('data-note-id');
					var $params = {
						action: 'remove_comment',
						controller: 'order',
						order_id: $(this).attr('data-order-id'),
						note_id: note_id
					};
					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function() {
							$('#mprm-payment-note-' + note_id).remove();

							if ($('.mprm-payment-note').length < 1) {
								$('.mprm-no-order-notes').show();
							}
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});
			},
			/**
			 * Edit post hide
			 */
			hideElementOrder: function() {
				var postStatusBlock = $('#post_status');
				$('#submitdiv').hide();
				$('#order-log').hide();
				$('#titlewrap').parents('#post-body-content').hide();
				$('#commentstatusdiv').parent().hide();
				postStatusBlock.find('option').remove();
				$('select[name="mprm-order-status"]').find('option').clone().appendTo(postStatusBlock);
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

/**
 * Menu settings module
 */
MP_RM_Registry.register("Menu-Settings", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			init: function() {
				$('#rm_settings').on('submit', function() {
					var params = $(this).serializeArray();
					$('#setting-error-settings_updated:visible').remove();

					state.saveSettings(params, function() {
						var $message = $('#setting-error-settings_updated').clone();
						$message.removeClass('hidden');
						$('.wrap #settings-title').after($message);
						$('.notice-dismiss').on('click', function() {
							$(this).parent().remove();
						});
					});

					return false;
				});
				state.changeBaseCountry();
				state.settingsUpload();
			},

			/**
			 * add_atribute
			 *
			 * @param {Object} $params
			 * @param {function} callback
			 */
			saveSettings: function($params, callback) {
				MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
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
			},
			/**
			 * Delete customer checked
			 */
			delete_checked: function() {
				$('#mprm-customer-delete-confirm').change(function() {
					var records_input = $('#mprm-customer-delete-records');
					var submit_button = $('#mprm-delete-customer');

					if ($(this).prop('checked')) {
						records_input.attr('disabled', false);
						submit_button.attr('disabled', false);
					} else {
						records_input.attr('disabled', true);
						records_input.prop('checked', false);
						submit_button.attr('disabled', true);
					}
				});
			},

			settingsUpload: function() {
				// Settings Upload field JS

				if (typeof wp === "undefined" || '1' !== mprm_admin_vars.new_media_ui) {
					//Old Thickbox uploader
					var mprm_settings_upload_button = $('.mprm_settings_upload_button');
					if (mprm_settings_upload_button.length > 0) {
						window.formfield = '';

						$(document.body).on('click', mprm_settings_upload_button, function(e) {
							e.preventDefault();
							window.formfield = $(this).parent().prev();
							window.tbframe_interval = setInterval(function() {
								jQuery('#TB_iframeContent').contents().find('.savesend .button').val(mprm_admin_vars.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
							}, 2000);
							tb_show(mprm_admin_vars.add_new_menu_item, 'media-upload.php?TB_iframe=true');
						});

						window.mprm_send_to_editor = window.send_to_editor;
						window.send_to_editor = function(html) {
							if (window.formfield) {
								var imageUrl = $('a', '<div>' + html + '</div>').attr('href');
								window.formfield.val(imageUrl);
								window.clearInterval(window.tbframe_interval);
								tb_remove();
							} else {
								window.mprm_send_to_editor(html);
							}
							window.send_to_editor = window.mprm_send_to_editor;
							window.formfield = '';
							window.imagefield = false;
						};
					}
				} else {
					// WP 3.5+ uploader
					var file_frame;
					window.formfield = '';

					$(document.body).on('click', '.mprm_settings_upload_button', function(e) {

						e.preventDefault();

						var button = $(this);

						window.formfield = $(this).parent().prev();

						// If the media frame already exists, reopen it.
						if (file_frame) {
							//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.file_frame = wp.media({
							frame: 'post',
							state: 'insert',
							title: button.data('uploader_title'),
							button: {
								text: button.data('uploader_button_text')
							},
							multiple: false
						});

						file_frame.on('menu:render:default', function(view) {
							// Store our views in an object.
							var views = {};

							// Unset default menu items
							view.unset('library-separator');
							view.unset('gallery');
							view.unset('featured-image');
							view.unset('embed');

							// Initialize the views in our view object.
							view.set(views);
						});

						// When an image is selected, run a callback.
						file_frame.on('insert', function() {

							var selection = file_frame.state().get('selection');

							selection.each(function(attachment) {
								attachment = attachment.toJSON();
								window.formfield.val(attachment.url);
							});
						});
						// Finally, open the modal
						file_frame.open();
					});
					// WP 3.5+ uploader
					window.formfield = '';
				}

			},
			changeBaseCountry: function() {

				if ($("[name='mprm_settings[base_state]'] option").length < 1) {
					$("[name='mprm_settings[base_state]']").parents('tr').hide();
				}

				$("[name='mprm_settings[base_country]']").on('change', function() {
					var $params = {
						action: 'get_state_list',
						controller: 'settings',
						country: $(this).val()
					};
					var $parentTr = $(this).closest('tr');
					var stateSelect = $parentTr.next().find('select');

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							if ($.isEmptyObject(data)) {
								$parentTr.next().hide();
							} else {
								$parentTr.next().show();
								stateSelect.remove('option');
								$.each(data, function(i, value) {
									stateSelect.append($('<option>').text(value).attr('value', i));
								});
							}
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				});
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

/**
 * Menu item module
 */
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
				$(document).on('click.mprm_delete_img', '.mp_menu_images a.mprm-delete', function() {
					$(this).parents('li.mprm-image').remove();
					state.refreshImages();
					return false;
				});
				state.refreshImages();

				// Image ordering
				$('#mprm-menu-item-gallery').find('.mp_menu_images').sortable({
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
			 * Open add media frame
			 *
			 * @returns {Boolean}
			 */
			openMediaWindow: function() {
				if (this.window === undefined) {
					this.window = wp.media({
						title: mprm_admin_vars.insert_media,
						library: {type: 'image'},
						multiple: true,
						button: {text: mprm_admin_vars.insert}
					});

					var self = this;
					// Needed to retrieve our variable in the anonymous function below
					this.window.on('select', function() {
						var $data = self.window.state().get('selection').toJSON();
						// biuld gallery html
						$('#mprm-menu-item-gallery').find('.mp_menu_images').append(state._buildImages($data));
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
				var menuItemGallery = $('#mprm-menu-item-gallery');
				menuItemGallery.find('.mp_menu_images li.mprm-image').each(function(key, value) {
					ids[key] = $(value).attr('data-attachment_id');
					$(value).attr('data-key', key);
				});
				menuItemGallery.find(' input[name="mp_menu_gallery"]').val(ids.join(','));
			},
			/**
			 * Build images
			 *
			 * @param {type} $data
			 * @returns
			 */
			_buildImages: function($data) {
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
													title: mprm_admin_vars.delete_img,
													href: "#"
												},
												content: mprm_admin_vars.delete
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

			quickEdit: function() {
				$(document).on('click', '#the-list .editinline', function() {

					var parent = $(this).closest('tr');
					var post_id = parent.attr('id').replace(/\D/ig, '');

					var qPrice = parent.find('.mprm-data-price').text(),
						sku = parent.find('.mprm-data-sku').text(),

						calories = parent.find('.mprm-data-calories').text(),
						cholesterol = parent.find('.mprm-data-cholesterol').text(),
						fiber = parent.find('.mprm-data-fiber').text(),
						sodium = parent.find('.mprm-data-sodium').text(),
						carbohydrates = parent.find('.mprm-data-carbohydrates').text(),
						fat = parent.find('.mprm-data-fat').text(),
						protein = parent.find('.mprm-data-protein').text(),

						bulk = parent.find('.mprm-data-bulk').text(),
						size = parent.find('.mprm-data-size').text(),
						weight = parent.find('.mprm-data-weight').text();

					var editParent = $('#edit-' + post_id);
					editParent.find('.inline-price [name="price"]').val(qPrice);
					editParent.find('.inline-sku [name="sku"]').val(sku);

					editParent.find('[name="nutritional[calories][val]"]').val(calories);
					editParent.find('[name="nutritional[cholesterol][val]"]').val(cholesterol);
					editParent.find('[name="nutritional[fiber][val]"]').val(fiber);
					editParent.find('[name="nutritional[sodium][val]"]').val(sodium);
					editParent.find('[name="nutritional[carbohydrates][val]"]').val(carbohydrates);
					editParent.find('[name="nutritional[fat][val]"]').val(fat);
					editParent.find('[name="nutritional[protein][val]"]').val(protein);

					editParent.find('[name="attributes[bulk][val]"]').val(bulk);
					editParent.find('[name="attributes[size][val]"]').val(size);
					editParent.find('[name="attributes[weight][val]"]').val(weight);

				});
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

/**
 * Category module
 */
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
					var categoryThumbnail = $('#menu_category_thumbnail');
					categoryThumbnail.find('img').attr('src', categoryThumbnail.find('img').attr('data-placeholder'));
					$('#menu_category_thumbnail_id').val('');
					$('.remove_image_button').hide();
					return false;
				});
			},
			/**
			 * Open upload window
			 *
			 * @returns {void}
			 */
			openUploadWindow: function() {
				if (this.window === undefined) {
					// Create the media frame.
					this.window = wp.media.frames.menu_itemable_file = wp.media({
						title: mprm_admin_vars.choose_image,
						button: {
							text: mprm_admin_vars.use_image
						},
						multiple: false
					});
					var self = this;
					// When an image is selected, run a callback.
					this.window.on('select', function() {
						var attachment = self.window.state().get('selection').first().toJSON();
						$('#menu_category_thumbnail_id').val(attachment.id);
						$('#menu_category_thumbnail').find('img').attr('src', attachment.sizes.thumbnail.url);
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

/**
 * Theme module
 */
MP_RM_Registry.register("Theme", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			init: function() {
				// Init slider
				if ((typeof $.magnificPopup) === 'undefined') {
					return;
				}
				$('.type-mp_menu_item .gallery-item,.type-mp_menu_item .mprm-item-gallery').magnificPopup({
					delegate: 'a',
					type: 'image',
					tLoading: 'Loading image #%curr%...',
					mainClass: 'mfp-img-mobile',
					gallery: {
						enabled: true,
						navigateByImgClick: true,
						preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
					},
					image: {
						tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
						titleSrc: function(item) {
							return item.el.attr('title');
						}
					},
					zoom: {
						enabled: true,
						duration: 300 // don't foget to change the duration also in CSS
					}
				});
			},
			viewParams: function(parent, view) {
				switch (view) {

					case "simple-list" :
						parent.find('.mprm-widget-feat_img').addClass('hidden');
						parent.find('.mprm-widget-buy').addClass('hidden');
						parent.find('select.mprm-widget-categ_name option[value="with_img"]').change('only_text').addClass('hidden');
						parent.find('.mprm-widget-price_pos').removeClass('hidden');
						break;
					default:
						parent.find('.mprm-widget-feat_img').removeClass('hidden');
						parent.find('.mprm-widget-buy').removeClass('hidden');
						parent.find('select.mprm-widget-categ_name option[value="with_img"]').change('only_text').removeClass('hidden');
						parent.find('.mprm-widget-price_pos').addClass('hidden');
						break;
				}

			},
			customizeWidget: function() {
				$.each($('.mprm-widget-view'), function() {
					var parent = $(this).parents('.widget-content');
					var view = $(this).val();
					state.viewParams(parent, view);
				});
				$(document.body).on('change', '.mprm-widget-view', function() {
					var parent = $(this).parents('.widget-content');
					var view = $(this).val();
					state.viewParams(parent, view);
				});
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

(function($) {
	"use strict";
	$(document).ready(function() {

		// if edit and add menu_item
		if ('mp_menu_item' === $(window.post_type).val()) {
			MP_RM_Registry._get("Menu-Item").init();
		}
		if ('edit-mp_menu_item' === window.pagenow) {
			MP_RM_Registry._get("Menu-Item").quickEdit();
		}
		// if settings
		if ('restaurant-menu_page_mprm-settings' === window.pagenow) {
			MP_RM_Registry._get('Menu-Settings').init();
		}

		if ('restaurant-menu_page_mprm-customers' === window.pagenow) {
			MP_RM_Registry._get('Menu-Settings').delete_checked();
		}
		// if edit and add menu_category
		if ('edit-mp_menu_category' === window.pagenow) {
			MP_RM_Registry._get("Menu-Category").init();
		}

		if ('mprm_order' === $(window.post_type).val()) {
			MP_RM_Registry._get("Order").init();
		}
		if ($('.mprm-select-chosen').length) {
			MP_RM_Registry._get("Order").initChosen();
		}

		if ($('.mprm-add-to-cart').length) {
			MP_RM_Registry._get('Menu-Shop').addToCart();
		}
		if ($('#mprm_checkout_wrap').length) {
			MP_RM_Registry._get('Menu-Shop').purchaseForm();
		}

		if ($('#mprm_purchase_form').length) {

			MP_RM_Registry._get('Menu-Shop').loadGateway();
			MP_RM_Registry._get('Menu-Shop').changeGateway();
			MP_RM_Registry._get('Menu-Shop').update_item_quantities();
			MP_RM_Registry._get('Menu-Shop').showTerms();
			MP_RM_Registry._get('Menu-Shop').get_login();
			MP_RM_Registry._get('Menu-Shop').loginAjax();
		}

		if ($('.mprm-widget-view').length) {
			MP_RM_Registry._get("Theme").customizeWidget();
		}
		if ($('.gallery-item').length || $('.mprm-item-gallery').length) {
			MP_RM_Registry._get("Theme").init();
		}
	});
}(jQuery));