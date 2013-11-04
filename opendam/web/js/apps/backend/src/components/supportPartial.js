(function($) {
	"use strict";

	$(document).ready(function() {
		var notification = window.services.notification;
		
		var $root = $("#support-body");
		var $inputSupport = $root.find("input[name=support]");
		var $supportList = $root.find(".support-list");
		
		$root.find("button[data-action=add]").on("click", function() {
			var $this = $this;

			if (!$inputSupport.val()) {
				notification.error(__("Support is required."));
				return;
			}

			notification.loading();

			$.ajax(Routing.generate("right_support_add"), {
				data: {"name" : $inputSupport.val()}
			})
			.done(function(data) {
				$supportList.html(data.html);
			})
			.fail(function(xhr) {
				var data = xhr.responseJSON;

				if (data.errors.length) {
					notification.error(data.errors[0].message);
				}
			});
		});
	});
})(jQuery);