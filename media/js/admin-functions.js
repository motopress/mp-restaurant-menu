/* global console:false,$:false,jQuery:false, _:false, MP_RM_Registry:false, wp:false, jBox:false */
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