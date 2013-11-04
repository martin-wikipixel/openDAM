<?php include_partial("group/navigationManage", array("selected"=>"merge", "group"=>$group));?>
<?php include_partial("group/subMenubarActions", array("selected"=>"delete", "group"=>$group));?>

<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag('group/remove', array('name'=>'group_delete_form', 'id'=>'group_delete_form'))?>
			<?php echo input_hidden_tag("id", $group->getId(), array())?>
			<?php echo input_hidden_tag("iframe", "1", array())?>
		</form>

		<div class="text"><?php echo __("Are you sure want to delete the group \"%1%\" and all content?", Array("%1%" => $group));?></div>

		<br clear="all">

		<label style="width: auto;"><?php echo __("To confirm album deletion, please write \"ALBUM\":"); ?></label>
		<input type="text" name="captcha" id="captcha" style="float: left; width: 150px;" />

		<span class="require_field" style="clear: both; float: left; display: none; margin-bottom: 10px;" id="error_captcha"></span>

		<br clear="all">

		<div class="red"><?php echo __("ATTENTION: ALL REMOVAL IS IRREVERSIBLE.")?></div>

		<br clear="all"/>

		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>

				<a href="javascript: void(0);" id="delete_album" class="button btnBS"><span><?php echo __("REMOVE")?></span></a>

		</div>
	</div>
</div>

<script>
function validDeleteForm()
{
	if(jQuery("#captcha").val() == "<?php echo __("ALBUM"); ?>")
	{
		jQuery("#error_captcha").hide();
		jQuery("#error_captcha").html("");

		return true;
	}
	else
		jQuery("#error_captcha").html('<?php echo __("Please enter \"ALBUM\"."); ?>').fadeIn('slow');

	return false;
}

jQuery(document).ready(function() {
	jQuery("#delete_album").bind("click", function() {
		if(validDeleteForm())
			jQuery('#group_delete_form').submit();
	});
});
</script>