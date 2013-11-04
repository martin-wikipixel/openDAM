(function($) {
	"use strict";
	
	var services = {};
	
	$(document).ready(function() {
		services.notification = new Notification($("#notifications-container"));
	});

	window.services = services;
})(jQuery);