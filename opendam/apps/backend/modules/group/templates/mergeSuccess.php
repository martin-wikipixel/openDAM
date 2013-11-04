<?php include_partial("group/navigationManage", array("selected"=>"merge", "group"=>$group));?>
<?php include_partial("group/subMenubarActions", array("selected" => "merge", "group" => $group)); ?>

<div id="searchResults-popup">
	<div class="inner">
		<form name='group_form' id='group_form' class='form' action='<?php echo url_for("group/merge"); ?>' method='post'>
			<?php echo $form['_csrf_token']->render(); ?>

			<label for="data_group_from" style="width:180px;"><?php echo __("Select group")?> : </label>
			<?php echo $form['group_from']->render(); ?>

			<br clear="all" />
			<br clear="all" />

			<label for="data_group_to" style="width:180px;"><?php echo __("Another group")?> : </label>
			<span id="group_id2_container">
				<?php echo $form['group_to']->render(); ?>
			</span>

			<br clear="all" />
			<br clear="all" />

			<label for="data_rights" style="width: 370px; text-align: justify;"><?php echo __("Give the same rights to users of the removed groups")?> : </label>
			<br clear="all" />
			<?php echo $form['rights']->render(); ?>
			<a href="javascript: void(0);" name="<?php echo __("If you choose no")?> : <br /><?php echo __("Managers, contributors and readers of the removed group will loose their roles in the re-assigned new group.")?>" class="left tooltip"><img src="<?php echo image_path("help.gif"); ?>" style="margin-left: 5px; margin-top: 12px;" /></a>
		</form>
		<br clear="all"/>
		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>

				<a href="#" onclick="jQuery('#group_form').submit();" class="button btnBS"><span><?php echo __("SAVE")?></span></a>

		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function() {
		tooltip();

		jQuery("#data_group_from").bind("change", function() {
			jQuery.post(
			"<?php echo url_for("group/manageGroup2"); ?>", 
			{ "group_id1": jQuery("#data_group_from").val() },
			function(data) {
				jQuery("#group_id2_container").html(data);
				jQuery("#submitButtons").fadeIn();
			}
		);
		});
	});
</script>