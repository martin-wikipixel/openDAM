(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#selection-file-list-page");
		var $message = $("form[name=download-files] [data-class=alert]");
		var filesCount = $root.attr("data-file-count");

		if (filesCount == 1) {
			$root.find("[data-action=delete]").ConfirmModal({
				title: __("Delete confirmation"),
				message: __("Are you sure you want to remove the last file?<br> The selection will be deleted."),
				confirmBtn: {
					"class": "btn btn-danger",
					"text": '<i class="icon-trash"></i> '+__("Delete")
				}
			});
		}
		else {
			$root.find("[data-action=delete]").ConfirmModal({
				title: __("Delete confirmation"),
				message: __("Are you sure you want to remove this file?"),
				confirmBtn: {
					"class": "btn btn-danger",
					"text": '<i class="icon-trash"></i> '+__("Delete")
				}
			});
		}

		$root.find("#data_format").on("change", function() {
			var selected = $(this).val();
			
			if (selected == 1) {
				$message.hide();
			}
			else {
				$message.show();
			}
		});
	});
})(jQuery);