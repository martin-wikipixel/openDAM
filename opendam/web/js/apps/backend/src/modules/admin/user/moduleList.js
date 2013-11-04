(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-user-module-list-page");
		var notification = services.notification;

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this module for this user?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});

		$root.find("select.active").on("change", function() {
			var $this = $(this);
			var $tr = $this.closest("tr");
			
			var moduleId = $tr.attr("data-module-id");
			var userId = $tr.attr("data-user-id");
			var active = $this.val();

			notification.loading();
			
			$.ajax(Routing.generate("admin_user_module_state_update"), {
				type: "post",
				data: {"_module" : moduleId, "user": userId, "state": active}
			})
			.done(function() {
				var message;
			
				if (active == 1) {
					message = __("The module has been actived.");
				}
				else {
					message = __("The module has been disabled.")
				}
			
				notification.success(message);
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);