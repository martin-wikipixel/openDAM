<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_VERSIONNING)) : ?>
	<?php if(FilePeer::hasHistory($file->getPath().DIRECTORY_SEPARATOR, $file)) : ?>

		<?php $versions = FilePeer::getHistory($file->getPath().DIRECTORY_SEPARATOR, $file); ?>
		<?php $passe = false; ?>

		<div class="cat-right">
			<a href="javascript: void(0);" class="deploy-cat">
				<i class="icon-time"></i> <?php echo __("Version history")?><i class="icon-chevron-down right"></i>
			</a>
			<div class="content" style="display: none;">
				<div class="rub">
					<div class="label-right no-margin">
						<?php echo __("Click on the desired version to view a preview."); ?>
					</div>
				</div>

				<br clear="all" />
				<br clear="all" />

				<?php foreach($versions as $key => $value) : ?>
					<div class="rub">
						<div class="value-right">
							<a href='#' onclick='viewVersion("<?php echo $value; ?>", this);' class='history-link <?php echo ($passe == false) ? "selected"  : ""; ?>'><?php echo __("At")." ".date("d/m/Y H:i:s", $key); ?></a>
							<?php if($sf_user->isAdmin() && $passe == true): ?>
								<a href='#' class='delete-history history-link <?php echo ($passe == false) ? "selected"  : ""; ?>' onclick="deleteHistory('<?php echo $value; ?>');"><i class="icon-trash"></i></a>
							<?php endif; ?>
						</div>
					</div>

					<br clear="all" />
					<?php $passe = true; ?>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>
<script>
  function viewVersion(version, obj) {
	jQuery('.restore').find('a').find('span').html("<?php echo __("Restore this version"); ?>");
	jQuery('.restore').find('a').attr('href', '<?php echo url_for("file/restore?id=".$file->getId()."&version="); ?>' + version);
	jQuery('.restore').addClass('restore-selected');

	jQuery('.history-link').each(function() {
		if(!jQuery(this).hasClass("delete-history"))
		{
			if(jQuery(this).html() == jQuery(obj).html())
				jQuery(this).addClass('selected');
			else
				jQuery(this).removeClass('selected');
		}
	});
	
	jQuery('#<?php echo $file->getId(); ?>').html('<?php echo image_tag("icons/loader/big-circle.gif", array("align"=>"absmiddle")); ?>');

	jQuery.post(
		"<?php echo url_for("file/viewVersion?file_id=".$file->getId()); ?>",
		{ version: version },
		function(data) {
			jQuery('#<?php echo $file->getId(); ?>').html(data);
		}
	);
  }
  
  function deleteHistory(version) {
	if(version == '')
		var msg = "<?php echo __("Are you sure you want to delete this version? (The previous version will be restored)"); ?>";
	else
		var msg = "<?php echo __("Are you sure you want to delete this version?"); ?>";

	if(confirm(msg)) {
		jQuery.post(
			"<?php echo url_for("file/deleteVersion?file_id=".$file->getId()); ?>",
			{ version: version },
			function(data) {
				jQuery('#<?php echo $file->getId(); ?>').html(data);
			}
		);
	}
  }
</script>