(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#folder-right-group-list-page");
		var $permissionsTable = $root.find("#permissions-table");
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

		$permissionsTable.find("tbody input[type=radio]").on("change", function() {
			var $this = $(this);
			var folderId = $this.attr("data-folder-id");
			var groupId = $this.attr("data-group-id");
			var roleId = $this.val();

			notification.loading();

			$.ajax(Routing.generate("folder_right_group_update", {"folder": folderId, "group": groupId}), {
				data: {"role": roleId}
			})
			.done(function(){
				notification.success(__("The folder's rights were been updated."));
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);