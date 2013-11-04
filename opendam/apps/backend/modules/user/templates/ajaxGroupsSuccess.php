<?php $groups_pager = GroupePeer::getUserGroupPager($user_id)?>
<table style="width: 80%;">
	<tr>
		<td class="text" style="background-color: #eee"><?php echo __("Group"); ?></td>
		<td class="text" style="background-color: #eee"><?php echo __("Right"); ?></td>
		<td class="text" style="background-color: #eee"><?php echo __("Actions"); ?></td>
	</tr>
	<?php if($groups_pager->getNbResults() == 0):?>
		<tr>
			<td class="no-border" colspan="3"><div class="info" style='position: relative; top: 0px;'><?php echo __("No group found.")?></div></td>
		</tr>
	<?php else:?>
		<?php foreach ($groups_pager->getResults() as $group):?>
			<tr class="admin_tab_border_bottom">
				<td width="45%" class="no-border text"><?php echo $group; ?><input type='hidden' name='group_ids[]' id='group_ids[]' value='<?php echo $group->getId(); ?>' /></td>
				<td width="25%" class="no-border text">
					<?php $selected = UserGroupPeer::getRole($user_id, $group->getId(), true);?>
					<?php $options = getUserRoles(); ?>
					<select name='role_<?php echo $group->getId(); ?>' id='role_<?php echo $group->getId(); ?>' class='left'>
						<?php foreach($options as $key => $value) : ?>
							<option value='<?php echo $key; ?>' <?php echo $key == $selected ? "selected" : ""; ?>><?php echo $value; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
				<td width="30%" class="no-border text">
					<a href='#' class='but_admin' style='margin-top: 10px;' id="remove_right_<?php echo $group->getId(); ?>"><span><?php echo __("Remove right"); ?></span></a>
					<span id="indicator_group_<?php echo $group->getId()?>" style="display: none;"><img src="<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>" style="margin-top: 10px;" /></span>
				</td>
			</tr>
			<script>
				jQuery(document).ready(function() {
					jQuery("#remove_right_<?php echo $group->getId(); ?>").bind("click", function () {
						if(confirm("<?php echo __("Are you sure want to remove user right from this group?"); ?>")) {
							jQuery("#indicator_group_<?php echo $group->getId(); ?>").show();

							jQuery.post(
								"<?php echo url_for("user/removeGroup"); ?>",
								{id: "<?php echo $user_id; ?>", group_id: "<?php echo $group->getId(); ?>" },
								function(data){
									jQuery("#user-groups").html(data);
									jQuery("#indicator_group_<?php echo $group->getId(); ?>").hide();
								}
							);
						}
					});
				});
			</script>
		<?php endforeach;?>
	<?php endif;?>
</table>

<br clear="all">
<br clear="all">

<!--add group start-->
<label><?php echo __("Add access rights to the following group.")?></label>
<?php $groups = GroupePeer::getGroupsNoRight($user_id)?>
<select name='group_id' id='group_id' style='width: 250px; float: left;'>
	<option value=''><?php echo __("Select group"); ?></option>
	<?php foreach($groups as $key => $value) : ?>
		<option value='<?php echo $key; ?>'><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
<?php $options = getUserRoles(); ?>
<select name='role' id='role' style='width: 200px; float: left;'>
	<option value=''><?php echo __("Select role"); ?></option>
	<?php foreach($options as $key => $value) : ?>
		<option value='<?php echo $key; ?>'><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>
&nbsp;
<span><a href='#' class='but_admin' style='margin-top: 10px;' id="add_group"><span><?php echo __("Add group role"); ?></span></a></span>
<span id="indicator" style="display: none;"><img src="<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>" style="margin-top: 10px;" /></span>
<script>
	jQuery(document).ready(function() {
		jQuery("#add_group").bind("click", function() {
			jQuery("#indicator").show();

			jQuery.post(
				"<?php echo url_for("user/addGroup"); ?>",
				{ id: "<?php echo $user_id; ?>", role: jQuery("#role").val(), group_id: jQuery("#group_id").val() },
				function(data) {
					jQuery("#user-groups").html(data);
					jQuery("#indicator").hide();
				}
			);
		});
	});
</script>