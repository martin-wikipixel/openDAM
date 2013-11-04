(function($) {
	"use strict";

	/**
	 * Main
	 */
	$(document).ready(function() {
		var $root = $("#right-group-search-page");
		var $permissionsTable = $root.find("#permissions-table");
		var notification = services.notification;

		$root.find("#group-autocomplete").autocomplete({
			source: Routing.generate("unit_results_fetch"),
			minLength: 1,
			autoFocus: true,
			select: function(event, ui) {
				var $this = $(this);
				var albumId = $this.attr("data-album-id");

				window.location.href = Routing.generate("group_right_group_search",
					{"album": albumId, "id": ui.item.id});
			}
		});

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sur to want to delete this unit's right?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});

		$permissionsTable.find("tbody input[type=radio]").on("change", function() {
			var $this = $(this);
			var albumGroupId = $this.attr("data-album-group-id");
			var roleId = $this.val();
			
			notification.loading();
			
			$.ajax(Routing.generate("unit_right_update"), {
				data: {"id": albumGroupId, "role": roleId}
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