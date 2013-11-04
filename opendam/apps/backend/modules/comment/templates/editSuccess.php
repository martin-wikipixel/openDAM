<label style="border:0px solid red;">
	<?php echo $comment->getUser()->getFirstname()?><br />
	<?php setlocale(LC_ALL, 'fr_FR'); ?>
	<?php echo utf8_encode(strftime("%d %B %Y", strtotime($comment->getCreatedAt())))?>
</label>

<div class="text" style="float:left; width:50%;">
	<div id="comment_show_<?php echo $comment->getId()?>">
		<?php echo $comment->getContent()?>
	</div>
	<textarea name='comment_edit_<?php echo $comment->getId(); ?>' id='comment_edit_<?php echo $comment->getId(); ?>' style='display: none; width: 100%; height: 100px;'><?php echo $comment->getContent(); ?></textarea>
</div>

<?php if(($sf_user->getId() == $comment->getUserId()) || ($sf_user->getId() == $comment->getFile()->getUserId()) || $sf_user->isAdmin()):?>
	<div style="float:left; margin-left:20px;">
		<a href="javascript: void(0);" class="but_admin" id="edit-comment-<?php echo $comment->getId()?>"><span><?php echo __("Edit"); ?></span></a>
		<a href="javascript: void(0);" class="but_admin" id="delete-comment-<?php echo $comment->getId()?>"><span><?php echo __("Delete"); ?></span></a>
	</div>
<?php endif;?>
<br clear="all" />
<script>
jQuery(document).ready(function() {
	jQuery("#edit-comment-<?php echo $comment->getId(); ?>").bind("click", function() {
		if(modify(jQuery(this), "<?php echo $comment->getId(); ?>"))
		{
			jQuery.post(
				"<?php echo url_for("comment/edit?id=".$comment->getId()); ?>",
				{ "comment":  jQuery("#comment_edit_<?php echo $comment->getId(); ?>").val() },
				function(data) {
					jQuery("#comment_<?php echo $comment->getId(); ?>").html(data);
				}
			);
		}
	});

	jQuery("#delete-comment-<?php echo $comment->getId(); ?>").bind("click", function() {
		if(confirm("<?php echo __("Are you sure want to delete this comment?"); ?>"))
		{
			jQuery.post(
				"<?php echo url_for("@comment_delete?id=".$comment->getId()); ?>",
				function(data) {
					jQuery("#comments").html(data);
				}
			);
		}
	});
});
</script>