<?php $file_tags = FileTagPeer::retrieveByFileIdType(3, $file->getId());?>

<?php foreach ($file_tags as $file_tag):?>
	<a class="label" href='javascript: void(0);' id="file_tag_<?php echo $file_tag->getId(); ?>"><?php echo $file_tag->getTag(); ?><i class='icon-remove-sign'></i></a>
	<script>
		jQuery(document).ready(function() {
			jQuery("#file_tag_<?php echo $file_tag->getId(); ?>").bind("click", function() {
				jQuery('#indicator').show();
				jQuery.post(
					"<?php echo url_for("tag/removeByUser?id=".$file_tag->getId()."&file_id=".$file_tag->getFileId()); ?>",
					{ "type": 3, "file_id": jQuery('#file_id').val(), "id": "<?php echo $file_tag->getId(); ?>" },
					function(data) {
						jQuery('#indicator').hide();
						jQuery('#file_tag_<?php echo $file_tag->getId(); ?>').remove();
					}
				);
			});
		});
	</script>
<?php endforeach;?>