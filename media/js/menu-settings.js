/* global console:false,$:false,jQuery:false, _:false, MP_RM_Registry:false, wp:false */
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
						$('.notice-dismiss').on('click',function(){
							$(this).parent().remove();
						});
					});
					return false;
				});
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


