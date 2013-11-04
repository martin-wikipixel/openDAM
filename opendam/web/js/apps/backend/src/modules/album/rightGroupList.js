(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#album-right-group-list-page");
		var notification = services.notification;
		var $permissionsTable = $root.find("#permissions-table");

		$permissionsTable.find("input[data-action=update]").on("change", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var groupId = $this.attr("data-group-id");
			var roleId = $this.val();
			
			notification.loading();
			
			$.ajax(Routing.generate("album_right_group_update", {"album": albumId, "group": groupId}), {
				data: {"role": roleId}
			})
			.done(function(){
				notification.success(__("The album's rights were been updated."));
			})
			.fail(function() {
				notification.error();
			});
		});
		
		$permissionsTable.find("input[data-action=delete]").on("change", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var groupId = $this.attr("data-group-id");
			var roleId = $this.val();
			
			notification.loading();
			
			$.ajax(Routing.generate("album_right_group_delete", {"album": albumId, "group": groupId}), {
				data: {}
			})
			.done(function(){
				notification.success(__("The album's rights were been deleted."));
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);