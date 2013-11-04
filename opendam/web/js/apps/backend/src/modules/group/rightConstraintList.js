(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#right-constraint-list-page");
		var notification = services.notification;

		$root.find("input[type=checkbox]").on("click", function() {
			var $this = $(this);
			var isChecked = $this.prop("checked");
			var constraintId = $this.val();
			var albumId = $this.attr("data-album-id");

			notification.loading();

			$.ajax(Routing.generate("group_right_constraint_update", {"album": albumId, "constraint": constraintId}), {
				data: {"delete": Number(!isChecked)}
			})
			.done(function(){
				notification.success(__("Constraint updated successfully."));
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);