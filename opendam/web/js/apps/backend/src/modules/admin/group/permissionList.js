(function($) {
	"use strict";

	/**
	 * Main
	 */
	$(document).ready(function() {
		var $root = $("#admin-group-permission-list-page");

		var $permissionsTable = $root.find("#permissions-table");
		var notification = services.notification;

		var $permissionForm = $root.find("form[name=add-permission]");
		var $albumSelect = $permissionForm.find("select[name=album]");
		var $roleSelect = $permissionForm.find("select[name=role]");
		var $submitBtn = $permissionForm.find("[data-action=submit]")

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sur to want to delete this unit's right?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});

		$albumSelect.on("change", function() {
			var $select = $(this);
			var $selectedOption = $select.find(":selected");

			// si select la valeur vide
			if (!$selectedOption.val()) {
				$roleSelect.addClass("hide");
				$submitBtn.addClass("hide");
			}
			else {
				// replace le select sur "choisissez"
				$roleSelect.find("option:first-child").attr("selected", "selected");
				$roleSelect.removeClass("hide");
				$submitBtn.addClass("hide");
			}
		});

		$roleSelect.on("change", function() {
			var $selectedOption = $(this).find(":selected");

			if (!$selectedOption.val()) {
				$submitBtn.addClass("hide");
				
			}
			else {
				$submitBtn.removeClass("hide");
			}
		});

		$permissionsTable.find("tbody input[type=radio]").on("change", function() {
			var $this = $(this);
			var roleId = $this.val();
			
			var albumId = $this.attr("data-album-id");
			var groupId = $this.attr("data-group-id");
			
			notification.loading();
			
			$.ajax(Routing.generate("admin_group_permission_update", {"album": albumId, "group": groupId}), {
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