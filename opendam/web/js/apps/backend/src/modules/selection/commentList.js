(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#selection-comment-list-page");
		var notification = services.notification;
		
		$root.find("[data-action=delete]").on("click", function() {
			var $commentBlock = $(this).closest(".comment-item");
			var commentId = $commentBlock.attr("data-comment-id");

			if (confirm(__("Are you sure want to delete this comment?"))) {
				notification.loading();

				$.ajax(Routing.generate("selection_comment_delete", {id: commentId}), {
					type: "post",
				})
				.done(function() {
					notification.success(__("The comment has been deleted."));
					$commentBlock.fadeOut();
				})
				.fail(function() {
					notification.error();
				});
			}
		});

		$root.find("[data-action=edit]").on("click", function() {
			var $commentBlock = $(this).closest(".comment-item");
			var $editBlock = $commentBlock.find(".comment-edit");
			var $content = $commentBlock.find(".comment-header .content");
			
			if (!$editBlock.is(":visible")) {
				$content.fadeOut(200, function() {
					$editBlock.fadeIn();
				});
			}
		});

		$root.find("[data-action=cancel]").bind("click", function() {
			var $commentBlock = $(this).closest(".comment-item");
			var $editBlock = $commentBlock.find(".comment-edit");
			var $content = $commentBlock.find(".comment-header .content");

			$editBlock.fadeOut(200, function() {
				$content.fadeIn();
			});
		});
		
		$root.find("[data-action=save]").bind("click", function() {
			var $commentBlock = $(this).closest(".comment-item");
			var $editBlock = $commentBlock.find(".comment-edit");
			var $content = $commentBlock.find(".comment-header .content");
			var commentId = $commentBlock.attr("data-comment-id");
			var $textarea = $editBlock.find("textarea");
			
			notification.loading();
			
			$.ajax(Routing.generate("selection_comment_update", {id: commentId}), {
				type: "post",
				data: {
					"content": $textarea.val()
				}
			})
			.done(function() {
				notification.success(__("The comment has been updated."));

				$content.html($textarea.val());

				$editBlock.fadeOut(200, function() {
					$content.fadeIn();
				});
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);