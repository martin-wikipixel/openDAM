(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-api-ip-list-page");

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure you want to remove this IP address?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});
	});
})(jQuery);