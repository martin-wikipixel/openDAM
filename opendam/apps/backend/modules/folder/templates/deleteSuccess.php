<?php include_partial("folder/navigationManage", array("selected"=>"move", "folder"=>$folder));?>
<?php include_partial("folder/subMenubarActions", array("selected"=>"delete", "folder"=>$folder));?>

<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag('folder/delete', array('name'=>'folder_delete_form', 'id'=>'folder_delete_form', 'onsubmit' => 'return validDeleteForm();'))?>
			<?php echo input_hidden_tag("id", $folder->getId(), array())?>

			<div class="text"><?php echo __("Are you sure want to delete the folder?")?></div>

			<br clear="all">

			<label style="width: auto;"><?php echo __("To confirm the folder deletion, please write \"FOLDER\":"); ?></label>
			<input type="text" name="captcha" id="captcha" style="float: left; width: 150px;" />

			<span class="require_field" style="clear: both; float: left; display: none; margin-bottom: 10px;" id="error_captcha"></span>

			<br clear="all">

			<div class="red"><?php echo __("ATTENTION: REMOVAL FOLDER AND ALL ITS CONTENTS IS IRREVERSIBLE.")?></div>

			<br clear="all"/>
			<br clear="all"/>

			<div class="right">
				<a href="javascript: void(0);" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>


					<a href="javascript: void(0);" id="delete_folder" class="button btnBS"><span><?php echo __("REMOVE")?></span></a>

			</div>
		</form>
	</div>
</div>
<script>
function validDeleteForm()
{
	if(jQuery("#captcha").val() == "<?php echo __("FOLDER"); ?>")
	{
		jQuery("#error_captcha").hide();
		jQuery("#error_captcha").html("");

		return true;
	}
	else
		jQuery("#error_captcha").html('<?php echo __("Please enter \"FOLDER\"."); ?>').fadeIn('slow');

	return false;
}
jQuery(document).ready(function() {
	jQuery("#delete_folder").bind("click", function() {
		if(validDeleteForm())
			jQuery('#folder_delete_form').submit();
	});
});
</script>