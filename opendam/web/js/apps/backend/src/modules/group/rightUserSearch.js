(function($) {
	"use strict";

	/**
	 * Main
	 */
	$(document).ready(function() {
		var $root = $("#right-user-search-page");
		var $permissionsTable = $root.find("#permissions-table");
		var notification = services.notification;

		$root.find("#user-autocomplete").autocomplete({
			source: Routing.generate("user_results_fetch"),
			minLength: 1,
			autoFocus: true,
			select: function(event, ui) {
				var $this = $(this);
				var albumId = $this.attr("data-album-id");

				window.location.href = Routing.generate("group_right_user_search",
					{"album": albumId, "id": ui.item.id});
			}
		});

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this album?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});

		$permissionsTable.find("tbody input[type=radio]").on("change", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var userId = $this.attr("data-user-id");
			
			var roleId = $this.val();
			
			notification.loading();
			
			$.ajax(Routing.generate("admin_user_album_right_update", {"album": albumId, "user": userId}), {
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