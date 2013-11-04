(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#account-permalink-list-page");
		var notification = services.notification;

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this permalink?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});
	});
})(jQuery);