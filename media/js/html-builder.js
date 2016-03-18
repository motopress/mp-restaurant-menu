/* global console:false,$:false,jQuery:false, _:false, MP_RM_Registry:false */
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