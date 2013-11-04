<?php $temp = FilePeer::getHistory($file->getPath().DIRECTORY_SEPARATOR, $file);
	  $versions = array();

	  foreach($temp as $key => $value) :
		$versions[$value] = __("At")." ".date("d/m/Y H:i:s", $key);
	  endforeach; ?>
<?php echo form_tag('file/restore', array('name'=>'restore_form', 'id'=>'restore_form', "class"=>"form", 'multipart'=>true))?>
	<?php echo input_hidden_tag("id", $file->getId(), array())?>
	<div id="searchResults-popup">
		<div class="inner">
			<label for="version"><?php echo __("Version to restore")?> :</label>
			<?php echo select_tag('version', options_for_select($versions, $sf_params->get("version")), array("onchange" => "viewVersion();", "id" => "version")); ?>
			<br clear="all">
			<br clear="all">
			<div id="<?php echo $file->getId(); ?>" align="center" class="file-div"></div>

			<br clear="all">

			<div class="right">
				<a href="javascript:window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
				<a href="javascript:valideFormRestore();" class="button btnBS"><span><?php echo __("RESTORE")?></span></a>
			</div>
		</div>
	</div>
</form>
<script>
	function valideFormRestore() {
		if(confirm("<?php echo __("Are you sure want to restore this version?"); ?>")) {
			jQuery('#restore_form').submit();
		}
	}

	function viewVersion() {
		jQuery('#<?php echo $file->getId(); ?>').html('<?php echo image_tag("icons/loader/big-circle.gif", array("align"=>"absmiddle")); ?>');

		jQuery.post(
			"<?php echo url_for("file/viewVersion?file_id=".$file->getId()); ?>",
			{ version: jQuery('#version').val() },
			function(data) {
				jQuery('#<?php echo $file->getId(); ?>').html(data);
			}
		);
	}

	viewVersion();
</script>