<?php if($permalink_original) : ?>
	<label style="width: auto;"><?php echo __("HD version"); ?></label>

	<br clear="all" />

	<textarea class="input_permalink" style="height: 50px;"><iframe src="<?php echo url_for("file/video?link=".$permalink_original->getLink(), true); ?>" width="640" height="385" frameborder="0"></iframe></textarea>

	<br clear="all" />
<?php endif; ?>

<?php if($permalink_web) : ?>
	<label style="width: auto;"><?php echo __("Standard version"); ?></label>

	<br clear="all" />

	<textarea class="input_permalink" style="height: 50px;"><iframe src="<?php echo url_for("file/video?link=".$permalink_web->getLink(), true); ?>" width="640" height="385" frameborder="0"></iframe></textarea>
<?php endif; ?>

<script>
jQuery(document).ready(function() {
	jQuery(".input_permalink").bind("click", function() {
		jQuery(this).focus();
		jQuery(this).select();
	});
});
</script>