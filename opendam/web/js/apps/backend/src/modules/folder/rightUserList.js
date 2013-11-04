(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#folder-right-user-list-page");
		var $permissionsTable = $root.find("#permissions-table");
		var $inputUsers = $permissionsTable.find("tbody input[type=radio]:not([name=radio-everybody])");
		var $inputEverybody = $permissionsTable.find("tbody input[type=radio][name=radio-everybody]");
		var notification = services.notification;

		$root.find("#access-type").on("change", function() {
			var $this = $(this);
			var folderId = $this.attr("data-folder-id");

			switch ($this.val()) {
				case "user":
					window.location.href = Routing.generate("folder_right_user_list", {"folder": folderId});
				break;

				case "group":
					window.location.href = Routing.generate("folder_right_group_list", {"folder": folderId});
				break;
			}
		});

		$inputUsers.on("change", function() {
			var $this = $(this);
			var folderId = $this.attr("data-folder-id");
			var userId = $this.attr("data-user-id");
			var roleId = $this.val();

			notification.loading();

			$.ajax(Routing.generate("folder_right_user_update", {"folder": folderId, "user": userId}), {
				data: {"role": roleId}
			})
			.done(function(){
				notification.success(__("The folder's rights were been updated."));
			})
			.fail(function() {
				notification.error();
			});
		});

		$inputEverybody.on("change", function() {
			var $this = $(this);
			var folderId = $this.attr("data-folder-id");
			var roleId = $this.val();

			notification.loading();

			$.ajax(Routing.generate("folder_right_everybody_update", {"folder": folderId}), {
				data: {"role": roleId}
			})
			.done(function(){
				$inputUsers.filter(":not(.pending)[value=" + roleId + "]").prop("checked", true);

				notification.success(__("The folder's rights were been updated."));
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);