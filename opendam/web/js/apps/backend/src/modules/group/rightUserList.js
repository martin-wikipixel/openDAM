(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#right-user-list-page");
		var $permissionsTable = $root.find("#permissions-table");
		var notification = services.notification;

		$root.find("#access-type").on("change", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");

			switch ($this.val()) {
				case "user":
					window.location.href = Routing.generate("group_right_user_list", {"album": albumId});
				break;

				case "group":
					window.location.href = Routing.generate("group_right_group_list", {"album": albumId});
				break;

				case "guest":
					window.location.href = Routing.generate("group_right_guest_list", {"album": albumId});
				break;
			}
		});

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this user?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});

		$permissionsTable.find("tbody [data-action=notify-user]").on("click", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var userId = $this.attr("data-user-id");

			notification.loading();

			$.ajax(Routing.generate("group_right_user_notify", {"album": albumId, "user": userId}))
			.done(function(){
				notification.success(__("Invitation returned successfully."));
			})
			.fail(function() {
				notification.error();
			});
		});

		$permissionsTable.find("tbody input[type=radio]:not([name=radio-everybody])").on("change", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var userId = $this.attr("data-user-id");
			var roleId = $this.val();

			notification.loading();

			$.ajax(Routing.generate("group_right_user_update", {"album": albumId, "user": userId}), {
				data: {"role": roleId}
			})
			.done(function(){
				notification.success(__("The album's rights were been updated."));
			})
			.fail(function() {
				notification.error();
			});
		});

		$permissionsTable.find("tbody input[type=radio][name=radio-everybody]").on("change", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var roleId = $this.val();

			notification.loading();

			$.ajax(Routing.generate("group_right_everybody_update", {"album": albumId}), {
				data: {"role": roleId}
			})
			.done(function(){
				notification.success(__("The album's rights were been updated."));
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);