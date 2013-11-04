(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-album-list-page");

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this album?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});
	});
})(jQuery);