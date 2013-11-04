<script>var fired_permalink_video = false; </script>
<div class="rub">
	<div class="label-right no-margin">
		<?php echo __("HD version"); ?>
	</div>
</div>

<br clear="all" />

<div class="rub" style="width: 100%;">
	<div class="value-right" style="width: 100%;">
		<div class="add-comment" style="float: left; margin-top: 0px; width: 100%;">
			<div class="textarea-wrapper" style="padding-right: 3px;">
				<textarea style="width: 100%; height: 65px;" class="input_permalink"><iframe src="<?php echo url_for("file/video?link=".$permalink_original->getLink(), true); ?>" width="640" height="385" frameborder="0"></iframe></textarea>
			</div>
		</div>
	</div>
</div>

<br clear="all" />
<br clear="all" />

<div class="rub">
	<div class="label-right no-margin">
		<?php echo __("Standard version"); ?>
	</div>
</div>

<br clear="all" />

<div class="rub" style="width: 100%;">
	<div class="value-right" style="width: 100%;">
		<div class="add-comment" style="float: left; margin-top: 0px; width: 100%;">
			<div class="textarea-wrapper" style="padding-right: 3px;">
				<textarea style="width: 100%; height: 65px;" class="input_permalink"><iframe src="<?php echo url_for("file/video?link=".$permalink_web->getLink(), true); ?>" width="640" height="385" frameborder="0"></iframe></textarea>
			</div>
		</div>
	</div>
</div>

<br clear="all" />
<script>
jQuery(document).ready(function() {
	if(fired_permalink_video) return;

	jQuery(".input_permalink").bind("click", function() {
		jQuery(this).focus();
		jQuery(this).select();
	});
});
</script>
<script>fired_permalink_video = true; </script>