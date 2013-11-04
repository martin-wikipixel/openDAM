(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-user-group-list-page");

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this user into this group?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});
	});
})(jQuery);