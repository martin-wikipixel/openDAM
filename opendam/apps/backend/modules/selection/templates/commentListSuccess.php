<div id="selection-comment-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@selection_list"), "text" => __("Collections")),
			array("link" => path("@selection_edit", array("id" => $selection->getId())), "text" => $selection->getTitle()),
			array("link" => path("@selection_comment_list", array("selection" => $selection->getId())), "text" => __("Comments")),
		));
	?>
	
	<?php include_partial("selection/tab", array("selection" => $selection, "selected" => "comment"))?>
	
	<?php if (!count($comments)):?>
		<?php echo __("No comment found."); ?>
	<?php else:?>
			<?php foreach($comments as $comment) : ?>
				<div data-comment-id="<?php echo $comment->getId()?>" class="clearfix comment-item">
					<div class="avatar">
						<img src="<?php echo image_path("avatar_man_blank.jpg"); ?>" />
					</div>

					<div class="comment-panel">
						<div class="comment-header">
							<span class="username"><?php echo $comment->getEmail(); ?></span>
							<span class="content">- <?php echo nl2br($comment->getComment()); ?></span>
						</div>

						<div class="comment-actions">
							<span class="date"><?php echo myTools::formatDateForComment($comment->getCreatedAt()); ?></span>
							
							<span class="action">
								- <a data-action="edit" href="javascript:void(0);"><?php echo __("Edit"); ?></a>
								- <a data-action="delete" href="javascript:void(0);"><?php echo __("Delete"); ?></a>
							</span>
						</div>
						
						<div class="comment-edit">
							<textarea><?php echo $comment->getComment() ?></textarea>
							<div class="submit">
								<button data-action="save" class="btn btn-primary"><?php echo __("Save"); ?></button>
								<button data-action="cancel" class="btn"><?php echo __("Cancel"); ?></button>
							</div>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
	<?php endif;?>
</div>
