(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-module-list-page");
		var notification = services.notification;
		
		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this module?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});
		
		$root.find(".values").on("change", function() {
			var $this = $(this);
			var $tr = $this.closest("tr");
			
			var customerId = $tr.attr("data-customer-id");
			var moduleId = $tr.attr("data-module-id");
			
			var selected = $this.val();
		
			notification.loading();
			
			$.ajax(Routing.generate("admin_module_value_update", {moduleId: moduleId}), {
				type: "post",
				data: {"moduleId": moduleId, "value": selected}
			})
			.done(function() {
				notification.success("Module's value has been updated.");
			})
			.fail(function() {
				notification.error();
			});
		});

		$root.find(".active").on("change", function(event) {
			var $this = $(this);
			var $tr = $this.closest("tr");
			
			var customerId = $tr.attr("data-customer-id");
			var moduleId = $tr.attr("data-module-id");
			
			var active = $this.val();

			notification.loading();
	
			$.ajax(Routing.generate("admin_module_value_activate", {moduleId: moduleId}), {
				type: "post",
				data: {"moduleId": moduleId, "active": active}
			})
			.done(function() {
				notification.success("Module's value has been updated.");
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);