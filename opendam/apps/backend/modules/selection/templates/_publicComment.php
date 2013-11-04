<div id="comment-sidebar" class="comments" data-selection-code="<?php echo $basket->getCode()?>">
	<div class="title">
		<h1><?php echo __("Comments"); ?></h1>
	</div>
	<div class="posts">
		<?php foreach($comments as $comment) : ?>
			<div class="post">
				<span class="author"><?php echo $comment->getEmail(); ?></span>
				<span class="posting-date"><?php echo myTools::formatDateForComment($comment->getCreatedAt()); ?></span>
				<div class="post-text"><?php echo nl2br($comment->getComment()); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
	
	<div class="add-post-label">
		<a href="javascript: void(0);"><?php echo __("Add comment..."); ?></a>
	</div>
	
	<div class="add-post">
		<div class="form-horizontal">
			<div class="control-group">
				<div class="controls">
					<input type="email" required id="email" name="email" class="input-block-level" placeholder="<?php echo __("Email address"); ?>" />
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<textarea required name="comment" id="comment" class="input-block-level" placeholder="<?php echo __("Enter your comment here."); ?>"></textarea>
				</div>
			</div>
			<div class="control-group control-button">
				<a class="btn-header submit" href="javascript: void(0);" data-action="add-comment">
					<i class="icon-envelope-alt"></i> <?php echo __("Add comment")?>
				</a>
			</div>
		</div>
	</div>
</div>
<script>
(function($) {
	"use strict";

	$(document).ready(function() {
		var $commentContainer = $(".comments-container");
		
		var $root = $("#comment-sidebar");
		var $addBtn = $root.find("a[data-action=add-comment]");
		var $email = $root.find("#email");
		var $comment = $root.find("#comment");
		var $addCommentContainer = $root.find(".add-post");
		
		var selectionCode = $root.attr("data-selection-code");
			
		$(".add-post-label a").on("click", function() {
			$(".add-post-label").slideUp("slow", function() {
				$(".add-post").slideDown("slow", function() {
				});
			});
		});

		$addBtn.on("click", function() {
			var error = false;

			// clear all error
			$addCommentContainer.find(".help-block").remove();
			$addCommentContainer.find(".control-group").removeClass("error");

			if ($email.val().length == 0) {
				error = true;
				$email.closest(".control-group").addClass("error");
				$email.closest(".controls").append("<span class='help-block'>"+__("Email is required.")+"</span>");
			}

			if ($comment.val().length == 0) {
				error = true;
				$comment.closest(".control-group").addClass("error");
				$comment.closest(".controls").append("<span class='help-block'>"+__("Comment is required.")+"</span>");
			}

			if (!error) {
				$.post(
					Routing.generate("public_selection_comment_add", {"code": selectionCode}),
					{ "email": $email.val(), "comment": $comment.val() },
					function(data) {
						if (data.code == 0) {
							$commentContainer.html(data.html);
						}
						else {
							if (data.code == 1) {
								$email.closest(".control-group").addClass("error");
								$email.closest(".controls").append("<span class='help-block'>"+__("Email address is invalid.")+"</span>");
							}
						}
					},
					"json"
				);
			}
		});
	});
})(jQuery);
</script>