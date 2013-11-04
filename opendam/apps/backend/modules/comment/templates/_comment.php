<div style="position:relative;">
	<div class="add add-comment" style="margin-top: 0px;">
		<div class="textarea-wrapper">
			<textarea style="display: inline-block; height: 27px; padding-top: 3px;" id="comment" placeholder="<?php echo __("Enter your comment.")?>"></textarea>
		</div>

		<div class="submit right" style="display: none;">
			<span class="erreur"></span>
			<a href="#comments" id="validate-comment" class="custom-button">
				<?php echo __("Add")?>
			</a>
		</div>
	</div>

	<div class="clearfix"></div>

	<div id="comments">
		<?php include_partial("comment/list", array("file_id"=>$file->getId()));?>
	</div>

	<?php echo input_hidden_tag("file_id", $file->getId(), array())?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("#comment").bind("focus", function() {
		if(jQuery('#comment').val() == "")
		{
			jQuery('#comment').val("")
			jQuery('#comment').animate({"height": "+=70px"}, "normal");
			jQuery("#validate-comment").parent().fadeIn();
		}
	});

	jQuery("#comment").bind("blur", function() {
		if(jQuery('#comment').val() == "")
		{
			jQuery('#comment').animate({"height": "-=70px"}, "normal")
			jQuery("#validate-comment").parent().fadeOut();
		}
	});


	jQuery('#validate-comment').bind('click', function() {
		jQuery.post(
			'<?php echo url_for("comment/save"); ?>',
			{ 'file_id': jQuery('#file_id').val(), 'comment': jQuery('#comment').val() },
			function(data) {
				jQuery('#comment').animate({"height": "-=70px"}, "normal")
				jQuery("#validate-comment").parent().fadeOut();

				jQuery('#comments').fadeOut(200, function() {
					jQuery('#comments').html(data);
					jQuery('#comments').fadeIn()
				});
			}
		);
	});
});
</script>