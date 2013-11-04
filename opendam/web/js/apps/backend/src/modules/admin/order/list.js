(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-order-list-page");
		
		$root.find("[data-action=cancel]").ConfirmModal({
			title: __("Cancel confirmation"),
			message: __("Are you sure you want to cancel this order?"),
			confirmBtn: {
				"class": "btn btn-warning",
				"text": __("Cancel order")
			}
		});
	});
})(jQuery);