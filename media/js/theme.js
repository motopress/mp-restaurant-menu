/* global console:false,$:false,jQuery:false, _:false, MP_RM_Registry:false, wp:false */
MP_RM_Registry.register("Theme", (function($) {
	"use strict";
	var state;

	function createInstance() {
		return {
			init: function() {
				// Init slider
				if (!_.isUndefined($('.mprm-item-gallery'))) {
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
	$(document).ready(function() {
		MP_RM_Registry._get("Theme").init();
	});
}(jQuery));


