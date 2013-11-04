<?php $file_tags = FileTagPeer::retrieveByFileIdType(3, $file->getId());?>
<?php $text = ""; ?>
<span class="list_tags">
	<?php foreach ($file_tags as $file_tag):?>
		<?php $role = UserGroupPeer::getRole($sf_user->getId(), $file->getGroupeId()); ?>
		<?php if($sf_user->getId() == $file->getUserId() || ($sf_user->getId() == $file->getFolder()->getUserId()) || ($role == RolePeer::__ADMIN) || $sf_user->isAdmin()) : ?>

				<a href='javascript: void(0);' onclick="deleteTag(this, <?php echo $file->getId(); ?>);"><span><?php echo $file_tag->getTag(); ?></span><em id=""></em></a>

		<?php else : ?>
			<a href='javascript: void(0);'><span><?php echo $file_tag->getTag(); ?></span></a>
		<?php endif; ?>
		<?php $text .= $file_tag->getTag()."|"; ?>
	<?php endforeach;?>
</span>
<script>
	jQuery(document).ready(function() {
		jQuery("#tags_input_<?php echo $file->getId(); ?>").val("<?php echo $text; ?>");
	});
</script>