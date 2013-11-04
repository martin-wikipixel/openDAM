<div style="position:relative;">
	<?php if($role) : ?>
		<div class="add add-comment left" style="width: 90%; margin-top: 0px;">
			<div class="textarea-wrapper">
				<ul id="tag_title" style="border: 0; margin: 0; min-height: 24px;"></ul>
			</div>
		</div>

			<a class="left" href='javascript: void(0);' style="margin-left: 5px; margin-top: 8px;" id='addTagRemote'><?php echo image_tag("icons/add4Bis.gif", array("align"=>"absmiddle")); ?></a>

		<span id="indicator" style="margin-left: 5px; display: none;"><?php echo image_tag('icons/loader/small-yellow-circle.gif', array('style'=>'vertical-align: -12px;'))?></span>

		<br clear="all" />
		<br clear="all" />
	<?php endif; ?>

	<div id="file_tags" class="cloud-tag-file">
		<?php include_partial("tag/selectedTagsFile", array("file"=>$file));?>
	</div>
	<?php if($role) : ?>
		<script type="text/javascript">
			function onTagFocus(obj)
			{
				if(obj.value.toLowerCase() == "<?php echo __("add tag ...")?>")
				{
					obj.value = "";
					jQuery(obj).removeClass("nc");
				}
			}

			function onTagBlur(obj)
			{
				if(obj.value == "")
				{
					obj.value = "<?php echo __("Add tag ...")?>";
					jQuery(obj).addClass("nc");
				}
			}

			jQuery(document).ready(function() {
				jQuery("#tag_title").tagit({
					tagSource: '<?php echo url_for("tag/fetchTags"); ?>',
					triggerKeys: ['enter', 'comma', 'tab'],
					minLength: 3,
					select: true,
					checkEmail: false,
					initialText: "<?php echo __("Add tag ..."); ?>",
					saveOnblur: true
				});

				jQuery('#addTagRemote').bind("click", function() {
					var values = jQuery("select.tagit-hiddenSelect").val();

					if(jQuery.trim(values).length > 0)
					{
						jQuery(this).fadeOut(200, function() {
							jQuery('#indicator').fadeIn(200, function() {
								jQuery.post(
									"<?php echo url_for('tag/addByUser?file_id='.$file->getId()); ?>",
									{ tag_title: values },
									function(data) {
										jQuery('#file_tags').html(data);
										jQuery('#indicator').fadeOut(200, function() {
											jQuery("#tag_title").tagit("reset");
											jQuery('#addTagRemote').fadeIn()
										});
									}
								);
							});
						});
					}
				});
			}); 
		</script>
	<?php endif; ?>
</div>