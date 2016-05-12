/*global jQuery:false, MP_RM_Registry:false, _:false,console:false,wp:false,jBox:false,admin_lang:false*/
window.MP_RM_Registry = (function() {
	"use strict";
	var modules = {};

	/**
	 * Test module
	 * @param module
	 * @returns {boolean}
	 * @private
	 */
	function _testModule(module) {
		if (module.getInstance && typeof module.getInstance === 'function') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Register module
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
	 * Unregister
	 * @param name
	 */
	function unregister(name) {
		delete modules[name];
	}

	/**
	 * Get instance module
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
			 * @param {object} params
			 * @param {function} callbackSuccess
			 * @param {function} callbackError
			 * @returns {undefined}
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
			//$.ajax({
			//url: 'ajax/test.html',
			//success: function(){
			//	alert('Load was performed.');
			//}
			/**
			 * Call Tool tip
			 *
			 * @param selector
			 * @param text
			 * @param timeOut
			 * @returns {jBox}
			 */
			callTooltip: function(selector, text, timeOut) {
				if (_.isUndefined(timeOut)) {
					timeOut = 5;
				}
				if (_.isUndefined(selector)) {
					console.warn("Parameter 'selector' not find");
				}
				if (_.isUndefined(text)) {
					console.warn("Parameter 'text' not find");
				}
				var tooltip = new jBox('Tooltip', {
					content: text,
					target: selector,
					closeOnEsc: true,
					adjustPosition: "flip",
					adjustTracker: true,
					reposition: true,
					closeOnClick: "body",
					maxWidth: 200,
					onOpen: function() {
						if (timeOut > 0) {
							setTimeout(function() {
								tooltip.close();
							}, timeOut * 1000);
						}
					}
				});
				tooltip.open();
				return tooltip;
			},
			/**
			 * Open popup window function
			 *
			 * @param start_content
			 * @param open_callback
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
						addClass: 'mprm-modal',
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
			callNotice: function(text, type, timeOut) {
				var color,
					Notice = {};
				switch (type) {
					case "done":
						color = "green";
						break;
					case "error":
						color = "red";
						break;
					default:
						color = "green";
						break;
				}
				if (_.isEmpty(timeOut)) {
					timeOut = 3;
				}
				text = (_.isEmpty(text)) ? "" : text;
				var notice = new jBox('Notice', {
					content: text,
					color: color,
					theme: "NoticeBorder",
					attributes: {
						x: 'left',
						y: 'bottom'
					},
					animation: {open: 'slide:bottom', close: 'slide:left'},
					onOpen: function() {
						Notice = this;
						setTimeout(function() {
							Notice.close();
						}, timeOut * 1000);
					}
				});
				notice.open();
			},
			/**
			 * Show block group
			 * @param name (value attr data-display)
			 * @param container (parent where search)
			 */
			showBlocks: function(name, container) {
				state.doActionForObj(name, container, "show");
			},
			/**
			 * Show block group
			 * @param name (value attr data-display)
			 * @param container (parent where search)
			 */
			showSomeBlock: function(name, container) {
				state.doActionForObj(name, container, "some-show");
			},
			/**
			 * Hide block group
			 * @param name (value attr data-display)
			 * @param container (parent where search)
			 */
			hideBlocks: function(name, container) {
				state.doActionForObj(name, container, "hide");
			},
			/**
			 * Do action for obj
			 *
			 * @param {type} name
			 * @param {type} container
			 * @param {type} action
			 */
			doActionForObj: function(name, container, action) {
				if (_.isUndefined(action)) {
					action = "show";
				}
				var hided = false, //hide status
					result = false, //find element by name status
					arrayBlocks; //Array of block
				if (_.isUndefined(container)) { //if parameter parent exist
					arrayBlocks = $('[data-display]');
				} else {
					arrayBlocks = container.find('[data-display]');
				}
				arrayBlocks.each(function() {
					var value = $(this),
						find = [],
						searchArray,
						attrValues = value.attr("data-display").split(","); //construct array attrs
					$.each(attrValues, function(key, value) { //each attrs array
						attrValues[key] = $.trim(value); //trim space around value
					});
					searchArray = name.split(",");
					$.each(searchArray, function(keySearch, search) {
						$.extend(find, state.inArray(attrValues, search));
					});
					if (!_.isEmpty(find)) {
						if (!hided && action === "show") {
							arrayBlocks.addClass("hidden"); //hide all
							hided = true; //change hide status
						}
						switch (action) {
							case "show":
							case "some-show":
								value.removeClass("hidden"); //show find element
								break;
							case "hide":
								value.addClass("hidden"); //show find element
								break;
						}
						result = true; //change element by name status
					}
				});
				if (!result) { // if not find element by parameter name
					console.warn("The element with attribute 'data-display = " + name + "' is not find");
				}
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

	function createInstance() {
		return {
			init: function() {

			},
			/**
			 * Add to cart
			 */
			addToCart: function() {
				$('.mprm-add-to-cart').on('click', function(e) {
					e.preventDefault();
					var $this = $(this), form = $this.closest('form');
					var $params = form.serializeArray();
					$params.push({
						name: "is_ajax",
						value: true
					});

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							form.find('.mprm_go_to_checkout').show();
							form.find('.mprm-add-to-cart').hide();

							$('.mprm-cart-added-alert', form).fadeIn();
							setTimeout(function() {
								$('.mprm-cart-added-alert', form).fadeOut();
							}, 3000);
						},
						function(data) {
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
					state.loadGateway()
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
						}, {
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
							$('.mprm-no-js').hide();
							$('#mprm_purchase_form_wrap').html(data.html);
							state.purchaseForm();
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				}
			},
			removeFromCart: function() {
				//$('.mprm_cart_actions .mprm_cart_remove_item_btn').on('click', function(e) {
				//	e.preventDefault();
				//
				//	var $this = $(this), $params = [], tr = $this.closest('tr');
				//	$params.push(
				//		{
				//			name: "is_ajax",
				//			value: true
				//		},
				//		{
				//			name: "cart_item",
				//			value: tr.attr('data-cart-key')
				//		},
				//		{
				//			name: "mprm_action",
				//			value: "remove"
				//		},
				//		{
				//			name: "controller",
				//			value: "cart"
				//		}
				//	);
				//
				//	MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
				//		function(data) {
				//			$('.mprm_cart_amount').html(data.total).text();//.text().html();
				//			$('.mprm_cart_amount').attr('data-subtotal', data.subtotal);
				//			$('.mprm_cart_amount').attr('data-total', data.subtotal);
				//			tr.remove();
				//		},
				//		function(data) {
				//			console.warn('Some error!!!');
				//			console.warn(data);
				//		}
				//	);
				//});
			},
			/**
			 * Purchase form
			 */
			purchaseForm: function() {

				$('#mprm_purchase_submit input[type=submit]', '#mprm_checkout_wrap').off('click').on('click', function(e) {

					var purchaseForm = document.getElementById('mprm_purchase_form');

					if (typeof purchaseForm.checkValidity === "function" && false === purchaseForm.checkValidity()) {
						return;
					}

					e.preventDefault();

					var complete_purchase_val = $(this).val();

					//$(this).val(edd_global_vars.purchase_loading);

					$(this).after('<span class="mprm-cart-ajax"><i class="mprm-icon-spinner mprm-icon-spin"></i></span>');
					var $params = $(purchaseForm).serializeArray();

					$params.push(
						{
							name: 'controller',
							value: 'cart'
						},
						{
							name: 'mprm_action',
							value: 'purchase'
						}
					);

					$.each($params, function(index, element) {
						if (element) {
							if (element.name == "mprm_action" && element.value == "gateway_select") {
								$params.splice(index, 1);
							}
						}
					});

					$('.mprm-cart-ajax').show();

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							$('.mprm_errors').remove();
							$('.mprm-error').hide();
							$('#mprm_purchase_form').submit();
						},
						function(data) {
							//$('#mprm-purchase-button').val(complete_purchase_val);
							$('.mprm-cart-ajax').remove();
							$('.mprm_errors').remove();
							$('.mprm-error').hide();
							//$('#mprm_purchase_submit').before(data);
							console.warn('Some error!!!');
							console.warn(data);
						}
					);

					//
					//$.post(edd_global_vars.ajaxurl, $('#edd_purchase_form').serialize() + '&action=edd_process_checkout&edd_ajax=true', function(data) {
					//	if ($.trim(data) == 'success') {
					//		$('.edd_errors').remove();
					//		$('.edd-error').hide();
					//		$(eddPurchaseform).submit();
					//	} else {
					//		$('#edd-purchase-button').val(complete_purchase_val);
					//		$('.edd-cart-ajax').remove();
					//		$('.edd_errors').remove();
					//		$('.edd-error').hide();
					//		$('#edd_purchase_submit').before(data);
					//	}
					//});
				});
			}
		}
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
				state.initChosen();
				state.addCustomer();
				state.removeMenuItem();
				state.addMenuItem();
				state.recalculate_total();
				state.changeOrderBaseCountry();
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
									stateSelect.append($('<option>').text(value).attr('value', i))
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
				})
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
							state.removeComment()
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

				$('#mprm-order-add-menu-item').on('click', function(e) {
					e.preventDefault();

					var order_menu_item_select = $('[name="mprm-order-menu-item-select"]'),
						order_menu_item_quantity = $('#mprm-order-menu-item-quantity'),
						order_menu_item_amount = $('[name="mprm-order-menu-item-amount"]');
					//selected_price_option = $('.edd_price_options_select option:selected');
					var menu_item_id = order_menu_item_select.val(),
						menu_item_title = $('[name="mprm-order-menu-item-select"] option:selected').text(),
						quantity = order_menu_item_quantity.val(),
						amount = order_menu_item_amount.val(),
						price_id = 0, //selected_price_option.val(),
						price_name = false; //selected_price_option.text();


					if (menu_item_id < 1) {
						return false;
					}

					if (!amount) {
						amount = 0;
					}

					amount = parseFloat(amount);
					if (isNaN(amount)) {
						alert(admin_lang.numeric_item_price);
						return false;
					}

					var item_price = amount;

					if (admin_lang.quantities_enabled === '1') {
						if (!isNaN(parseInt(quantity))) {
							amount = amount * quantity;
						} else {
							alert(admin_lang.numeric_quantity);
							return false;
						}
					}


					amount = amount.toFixed(admin_lang.currency_decimals);

					var formatted_amount = amount + admin_lang.currency_sign;
					if ('before' === admin_lang.currency_pos) {
						formatted_amount = admin_lang.currency_sign + amount;
					}

					if (price_name) {
						menu_item_title = menu_item_title + ' - ' + price_name;
					}

					var count = $('.mprm-row.item').length;
					var clone = $('#mprm-purchased-wrapper .mprm-row.item:last').clone();

					clone.find('.menu_item span').html('<a href="post.php?post=' + menu_item_id + '&action=edit"></a>');
					clone.find('.item span a').text(menu_item_title);
					clone.find('.price-text').text(formatted_amount);
					clone.find('.item-quantity').text(quantity);
					clone.find('.item-price').text(admin_lang.currency_sign + ( amount / quantity ).toFixed(admin_lang.currency_decimals));
					clone.find('input.mprm-order-detail-id').val(menu_item_id);
					clone.find('input.mprm-order-detail-price-id').val(price_id);
					clone.find('input.mprm-order-detail-item-price').val(item_price);
					clone.find('input.mprm-order-detail-amount').val(amount);
					clone.find('input.mprm-order-detail-quantity').val(quantity);
					clone.find('input.mprm-order-detail-has-log').val(0);

					// Replace the name / id attributes
					clone.find('input').each(function() {
						var name = $(this).attr('name');

						name = name.replace(/\[(\d+)\]/, '[' + parseInt(count) + ']');

						$(this).attr('name', name).attr('id', name);
					});

					// Flag the Downloads section as changed
					$('#mprm-payment-menu-items-changed').val(1);

					$(clone).insertAfter('#mprm-purchased-wrapper .mprm-row.item:last');

					$('.mprm-order-recalc-totals').show();
				})

			},
			/**
			 * Recalculate order total
			 */
			recalculate_total: function() {

				$('#mprm-order-recalc-total').on('click', function(e) {
					e.preventDefault();

					var total = 0,
						purchased = $('#mprm-purchased-wrapper .mprm-row.item .mprm-order-detail-amount');

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

					$('input[name="mprm-order-total"]').val(total.toFixed(admin_lang.currency_decimals));

					$('.mprm-order-recalc-totals').hide();
				});

			},
			/**
			 * Remove menu item
			 */
			removeMenuItem: function() {

				$('.mprm-order-remove-menu-item.mprm-delete').on('click', function(e) {
					e.preventDefault();
					if ($('.mprm-row.item').length > 1) {
						var menuItem = $(this);
						menuItem.parents('.mprm-row').remove();

						$('.mprm-order-recalc-totals').show();
					} else {
						alert(admin_lang.one_menu_item_min);
						return false;
					}

				});

			},
			/**
			 * Add new customer
			 */
			addCustomer: function() {

				$('.mprm-new-customer').on('click', function() {
					$('.customer-info.mprm-row').hide();
					$('.new-customer.mprm-row').show();
					$('[name="mprm-new-customer"]').val(1);
				});

				$('.mprm-new-customer-cancel').on('click', function() {
					$('.customer-info.mprm-row').show();
					$('.new-customer.mprm-row').hide();
					$('[name="mprm-new-customer"]').val(0);
				});

				$('.mprm-new-customer-save').on('click', function(e) {

					e.preventDefault();
					var $params = {
						action: 'add_customer',
						controller: 'customer',
						name: $('[name="mprm-new-customer-name"]').val(),
						email: $('[name="mprm-new-customer-email"]').val()
					};

					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
							$('.customer-info.mprm-row').show();
							$('.new-customer.mprm-row').hide();
							$('[name="customer-id"]').replaceWith(data.html);

						},
						function(data) {
							$('.customer-info.mprm-row').show();
							$('.new-customer.mprm-row').hide();
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
				$('.mprm-select-chosen').chosen({
					inherit_select_classes: true,
					placeholder_text_single: admin_lang.one_option,
					placeholder_text_multiple: admin_lang.one_or_more_option
				});
			},
			/**
			 * Remove comment
			 */
			removeComment: function() {
				$('.mprm-delete-order-note').off('click').on('click', function(e) {
					e.preventDefault();
					var note_id = $(this).attr('data-note-id');
					var $params = {
						action: 'remove_comment',
						controller: 'order',
						order_id: $(this).attr('data-order-id'),
						note_id: note_id
					};
					MP_RM_Registry._get('MP_RM_Functions').wpAjax($params,
						function(data) {
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
				$('#submitdiv').hide();
				$('#order-log').hide();
				$('#titlewrap').parents('#post-body-content').hide();
				$('#commentstatusdiv').parent().hide();
			}
		}
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
					state.saveSettings(params, function($data) {
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
			 * @param {type} $params
			 * @param {type} callback
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
			settingsUpload: function() {
				// Settings Upload field JS
				if (typeof wp === "undefined" || '1' !== admin_lang.new_media_ui) {
					//Old Thickbox uploader
					var mprm_settings_upload_button = $('.mprm_settings_upload_button');
					if (mprm_settings_upload_button.length > 0) {
						window.formfield = '';

						$(document.body).on('click', mprm_settings_upload_button, function(e) {
							e.preventDefault();
							window.formfield = $(this).parent().prev();
							window.tbframe_interval = setInterval(function() {
								jQuery('#TB_iframeContent').contents().find('.savesend .button').val(admin_lang.use_this_file).end().find('#insert-gallery, .wp-post-thumbnail').hide();
							}, 2000);
							tb_show(admin_lang.add_new_menu_item, 'media-upload.php?TB_iframe=true');
						});

						window.mprm_send_to_editor = window.send_to_editor;
						window.send_to_editor = function(html) {
							if (window.formfield) {
								var imgurl = $('a', '<div>' + html + '</div>').attr('href');
								window.formfield.val(imgurl);
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
							selection.each(function(attachment, index) {
								attachment = attachment.toJSON();
								window.formfield.val(attachment.url);
							});
						});

						// Finally, open the modal
						file_frame.open();
					});
					// WP 3.5+ uploader
					var file_frame;
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
									stateSelect.append($('<option>').text(value).attr('value', i))
								})
							}
						},
						function(data) {
							console.warn('Some error!!!');
							console.warn(data);
						}
					);
				})
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
						$('#mprm-menu-item-gallery .mp_menu_images').append(state._buildImages($data));
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
					this.window = wp.media.frames.menu_itemable_file = wp.media({
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
				$('.mprm-item-gallery').magnificPopup({
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
		// if settings
		if ('restaurant-menu_page_admin?page=mprm-settings' === window.pagenow) {
			MP_RM_Registry._get('Menu-Settings').init();
		}
		// if edit and add menu_category
		if ('edit-mp_menu_category' === window.pagenow) {
			MP_RM_Registry._get("Menu-Category").init();
		}

		if ('mprm_order' === $(window.post_type).val()) {
			MP_RM_Registry._get("Order").init();
		}

		MP_RM_Registry._get('Menu-Shop').addToCart();
		MP_RM_Registry._get('Menu-Shop').removeFromCart();
		MP_RM_Registry._get('Menu-Shop').loadGateway();
		MP_RM_Registry._get('Menu-Shop').changeGateway();
		MP_RM_Registry._get('Menu-Shop').purchaseForm();

		if ($('.mprm-item-gallery').length) {
			MP_RM_Registry._get("Theme").init();
		}
	});
}(jQuery));