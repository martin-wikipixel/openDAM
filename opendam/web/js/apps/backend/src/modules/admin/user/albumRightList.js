(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-user-album-right-list-page");
		var $permissionsTable = $root.find("#permissions-table");
		var notification = services.notification;
		
		var $permissionForm = $root.find("form[name=add-permission]");
		var $albumSelect = $permissionForm.find("select[name=album]");
		var $roleSelect = $permissionForm.find("select[name=role]");
		var $submitBtn = $permissionForm.find("[data-action=submit]")
		
		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this album?"),
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