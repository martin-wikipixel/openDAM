(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#selection-edit-page");
		var $selectionPasswordPanel = $("#selection-password-panel");
		var $shareContainer = $("#share-container");
		
		$root.find("input[name=allow_password]").on("click", function() {
			var allowPassword = $(this).val();

			if (allowPassword) {
				$selectionPasswordPanel.removeClass("hide");
			}
			else {
				$selectionPasswordPanel.addClass("hide");
			}
		});

		$root.find("[data-action=select]").on("click", function() {
			this.select();
		});
		
		$root.find("input[name=isShared]").on("click", function() {
			var isShared = $(this).val();
			
			if (isShared) {
				$shareContainer.removeClass("hide");
			}
			else {
				$shareContainer.addClass("hide");
			}
		});
	});
})(jQuery);