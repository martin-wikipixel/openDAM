<div class="comments">
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
				<a href="javascript: void(0);" class="btn-header submit"><i class="icon-envelope-alt"></i> <?php echo __("Add comment"); ?></a>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery(".add-post-label a").bind("click", function() {
			jQuery(".add-post-label").slideUp("slow", function() {
				jQuery(".add-post").slideDown("slow", function() {
					defaultContainer.isotope("reloadItems").isotope({ sortBy : 'order' });
				});
			});
		});

		jQuery(".submit").bind("click", function() {
			var error = false;
			var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;

			if(jQuery("#email").val().length == 0)
			{
				error = true;
				jQuery("#email").closest(".control-group").addClass("error");
				jQuery("#email").closest(".controls").append("<span class='help-inline'><?php echo __("Email is required."); ?></span>");
			}
			else if(!emailReg.test(jQuery("#email").val()))
			{
				error = true;
				jQuery("#email").closest(".control-group").addClass("error");
				jQuery("#email").closest(".controls").append("<span class='help-inline'><?php echo __("Email address is invalid."); ?></span>");
			}
			else
			{
				jQuery("#email").closest(".control-group").removeClass("error");
				jQuery("#email").closest(".controls").find(".help-inline").remove();
			}

			if(jQuery("#comment").val().length == 0)
			{
				error = true;
				jQuery("#comment").closest(".control-group").addClass("error");
				jQuery("#comment").closest(".controls").append("<span class='help-inline'><?php echo __("Comment is required."); ?></span>");
			}
			else
			{
				jQuery("#comment").closest(".control-group").removeClass("error");
				jQuery("#comment").closest(".controls").find(".help-inline").remove();
			}

			if(!error)
			{
				jQuery.post(
					"<?php echo url_for("folder/postComment"); ?>",
					{ permalink: "<?php echo $permalink->getLink(); ?>", email: jQuery("#email").val(), comment: jQuery("#comment").val() },
					function(data) {
						if(data.errorCode > 0)
						{
							jQuery("#email").closest(".control-group").addClass("error");
							jQuery("#email").closest(".controls").append("<span class='help-inline'><?php echo __("Email address is invalid."); ?></span>");
						}
						else
						{
							jQuery.post(
								"<?php echo url_for("folder/loadComments"); ?>",
								{ permalink: "<?php echo $permalink->getLink(); ?>" },
								function(data) {
									jQuery(".comments-container").html(data);
								}
							);
						}
					},
					"json"
				);
			}
		});
	});
</script>