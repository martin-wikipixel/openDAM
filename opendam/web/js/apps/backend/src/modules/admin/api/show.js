(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-api-show-page");
		var notification = services.notification;
		
		$root.find("[data-action=send-password]").on("click", function(event) {
			event.preventDefault();
			notification.loading();
			
			$.ajax(Routing.generate("admin_api_password_send"), {
				type: "post",
			})
			.done(function() {
				notification.success("The password sent to you by email.");
			})
			.fail(function() {
				notification.error();
			});
		});
		
		$root.find("[data-action=regenerate-password]").on("click", function(event) {
			event.preventDefault();
			notification.loading();
			
			$.ajax(Routing.generate("admin_api_password_regenerate"), {
				type: "post",
			})
			.done(function() {
				notification.success("The password was regenerated and sent to you by email.");
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);