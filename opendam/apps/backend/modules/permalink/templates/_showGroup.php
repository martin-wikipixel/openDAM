<?php
	$role = $sf_user->getRole($group->getId());

	if($role && $role <= RolePeer::__ADMIN)
		$role = true;
	else
		$role = false;

	$permalink = PermalinkPeer::getByObjectId($group->getId(), PermalinkPeer::__TYPE_CUSTOM, PermalinkPeer::__OBJECT_GROUP);

	if($permalink && !$permalink->getQrcode())
	{
		$url = PermalinkPeer::getUrl();
		$qrcode = PermalinkPeer::buildQrCode($group->getId(), $url, PermalinkPeer::__OBJECT_GROUP);

		$permalink->setLink($url);
		$permalink->setQrcode($qrcode);

		$permalink->save();
	}

	if($permalink)
		$notification = PermalinkNotificationPeer::retrieveByPermalinkIdAndUserId($permalink->getId(), $sf_user->getId());
?>

<div class="filterBox">
	<?php if($role) : ?>
		<div id="div_enabled_group" style="width: 100%; text-align: center; <?php echo !$permalink || $permalink->getState() == PermalinkPeer::__STATE_DISABLED ? "" : "display: none;"; ?>">
			<?php if(!$showLabel) : ?>
				<div class="left" style="width: 100%; text-align: center;">
					<div class="text" style="margin: auto; width: 75px;">
						<a href='javascript: void(0);' class="but_admin" id="state_group"><span><?php echo __("Activate"); ?></span></a>
					</div>
					<span class="flag_save left" id="ok_state_group" style="margin-top: 0px;"><i class="icon-ok-sign"></i></span>
				</div>
			<?php endif; ?>
			<br clear="all" />
		</div>
	<?php else: ?>
		<?php if(!$permalink) : ?>
			<div class="text nc"><?php echo __("This main folder does not have permalink."); ?></div>
		<?php endif; ?>
	<?php endif; ?>

	<?php if($showLabel) : ?>
		<div class="right">
			<select name="state_permalink_group" id="state_permalink_group" style="width: 150px;">
				<option value="activate" <?php echo $permalink ? "selected" : ""; ?>><?php echo __("Activate"); ?></option>
				<option value="desactivate" <?php echo !$permalink ? "selected" : ""; ?>><?php echo __("Desactivate"); ?></option>
				<option value="regenerate"><?php echo __("Regenerate"); ?></option>
			</select>
		</div>
	<?php endif; ?>

	<?php if($permalink) : ?>
		<span id="enabled_permalink_group" style="<?php echo $permalink->getState() == PermalinkPeer::__STATE_DISABLED ? "display: none;" : ""; ?>">
			<?php if($showLabel) : ?>
				<label><?php echo __("Permalink"); ?> <a href="javascript: void(0);" class="tooltip" name="<?php echo __("Direct access link to the main folder."); ?>"><i class="icon-question-sign"></i></a></label><br clear="all" />
			<?php endif; ?>
			<input type="text" name="permalink_group" id="permalink_group" class="input_permalink" value="<?php echo url("permalink_group", array("link" => $permalink->getLink()))?>" style="float: left; width: 210px; margin-top: 3px;" />

			<br clear="all" />
			<br clear="all" />

			<?php if($role) : ?>
				<div class="filterBox">
					<div class="title" style="cursor:pointer;" onclick="toggleContainer('auth_container_group', 'auth_img_group');">
						<?php echo image_tag("down-arr.gif", array("id"=>"auth_img_group", "style"=>"float:left; margin-top:4px; cursor:pointer;", "align"=>"absmiddle"))?>
						<h4><?php echo __("Authorizations")?></h4>
					</div>

					<div id="auth_container_group">
						<div id="filterByInformation" class="text">
							<div class="filterRow">
								<ul class="left" style="clear: both; padding-left: 5px; margin-top: 0px;">
									<li class='left' style="list-style-type: none; clear: both;">
										<input type="checkbox" name="comment_check_group" id="comment_check_group" <?php echo $permalink->getAllowComments() ? "checked" : ""; ?> class="left" style="margin-right: 5px;" />
										<label for="comment_check_group" style="width: auto;"><?php echo __("Comments"); ?></label>
										<span class="flag_save" id="ok_comment_group" style="margin-top: 0px;"><i class="icon-ok-sign"></i></span>
									</li>
									<li class='left' style="list-style-type: none; clear: both;">
										<input type="checkbox" name="format_check_group" id="format_check_group" <?php echo $permalink->getFormatHd() ? "checked" : ""; ?> class="left" style="margin-right: 5px;" />
										<label for="format_check_group" style="width: auto;"><?php echo __("Downloading HD"); ?></label>
										<span class="flag_save" id="ok_format_group" style="margin-top: 0px;"><i class="icon-ok-sign"></i></span>
									</li>
									<li class='left' style="list-style-type: none; clear: both;">
										<input type="checkbox" name="type_group" id="type_group" <?php echo $permalink->getState() == PermalinkPeer::__STATE_PRIVATE ? "checked" : ""; ?> class="left" style="margin-right: 5px;" />
										<label for="type" style="width: auto;"><?php echo __("Protect by password"); ?></label>
										<span class="flag_save" id="ok_type_group" style="margin-top: 0px;"><i class="icon-ok-sign"></i></span>

										<span class="permalink-password" id="permalink-password_group" class="permalink_password" <?php echo $permalink->getState() == PermalinkPeer::__STATE_PRIVATE ? "style='display: block;'" : ""; ?>>
											<input type="<?php echo $permalink->getState() == PermalinkPeer::__STATE_PRIVATE ? "password" : "text"; ?>" name="password_group" id="password_group" value="<?php echo $permalink->getState() == PermalinkPeer::__STATE_PRIVATE ? "00000" : ""; ?>" style="width: 80%; float:left;" />
											<span class="flag_save" id="ok_password_group" style="margin-top: 0px;"><i class="icon-ok-sign"></i></span>
										</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<br clear="all" />

				<div class="filterBox">
					<div class="title" style="cursor:pointer;" onclick="toggleContainer('notif_container_group', 'notif_img_group');">
						<?php echo image_tag("down-arr.gif", array("id"=>"notif_img_group", "style"=>"float:left; margin-top:4px; cursor:pointer;", "align"=>"absmiddle"))?>
						<h4><?php echo __("Notifications")?></h4>
					</div>

					<div id="notif_container_group">
						<div id="filterByInformation" class="text">
							<div class="filterRow">
								<ul class="left" style="clear: both; padding-left: 5px; margin-top: 0px;">
									<li class='left' style="list-style-type: none; clear: both;">
										<input type="checkbox" name="notify_comment_check_group" id="notify_comment_check_group" <?php echo $notification && $notification->getAddComment() ? "checked" : ""; ?> class="left" style="margin-right: 5px;" />
										<label for="notify_comment_check_group" style="width: auto;"><?php echo __("Leave comments"); ?></label>
										<?php if(count(PermalinkCommentPeer::retrieveByPermalinkId($permalink->getId())) > 0) : ?>
											<label style="width: auto; margin-left: 5px;"><a href="<?php echo url_for("group/comment?id=".$group->getId()); ?>" rel="facebox">(<?php echo __("View"); ?>)</a></label>
										<?php endif; ?>
										<span class="flag_save" id="ok_notify_comment_group" style="margin-top: 0px;"><i class="icon-ok-sign"></i></span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<br clear="all" />

			<div class="filterBox">
				<div class="title" style="cursor:pointer;" onclick="toggleContainer('qr_container_group', 'qr_img_group');">
					<?php echo image_tag("right-arr.gif", array("id"=>"qr_img_group", "style"=>"float:left; margin-top:4px; cursor:pointer;", "align"=>"absmiddle"))?>
					<h4><?php echo __("QR code")?></h4>
				</div>

				<div class="qr_container" id="qr_container_group">
					<div id="filterByInformation" class="text">
						<div class="filterRow">
							<img src='<?php echo "/".sfConfig::get("app_path_qrcode_dir_name")."/".$permalink->getQrcode().".png"; ?>' style="float: left; margin-left: 0px;" />
							<br clear="all" />
						</div>
					</div>
				</div>
			</div>
		</span>

		<?php if(!$showLabel) : ?>
			<?php if($role) : ?>
				<div style="margin: auto; width: 180px; <?php echo $permalink->getState() != PermalinkPeer::__STATE_DISABLED ? "" : "display: none;" ?>" id="div_disabled_group">
					<br clear="all" />
					<br clear="all" />

					<a href="javascript: void(0);" class="but_admin" id="delete_permalink_group"><span><?php echo __("Desactivate"); ?></span></a>
					<a href="javascript: void(0);" class="but_admin" id="regenerate_permalink_group"><span><?php echo __("Regenerate"); ?></span></a>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<br clear="all" />
	<?php endif; ?>
</div>

<script>
var password = "<?php echo myTools::makeRandomPassword(); ?>";

jQuery(document).ready(function() {
	tooltip();

	jQuery('a[rel*=facebox]').bind("click", function(){
		jQuery.facebox({ iframe: this.href });
		return false;
	});

	jQuery(".input_permalink").click(function() {
		jQuery(this).focus();
		jQuery(this).select();
	});

	<?php if($role) : ?>
		<?php if($showLabel) : ?>
			jQuery("#state_permalink_group").bind("change", function() {
				switch(jQuery("#state_permalink_group").val())
				{
					case "regenerate":
					{
						if(confirm("<?php echo __("Are you sure you want to regenerate the permalinks?"); ?>"))
						{
							jQuery("#permalink_container_group").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>");

							jQuery.post(
								"<?php echo url_for("permalink/regenerate"); ?>",
								{ group_id: <?php echo $group->getId(); ?>, showLabel: "<?php echo $showLabel; ?>" },
								function(data) {
									jQuery("#permalink_container_group").html(data);
									jQuery("#state_permalink_group").val("activate");
								}
							);
						}
					}
					break;

					case "desactivate":
					{
						if(confirm("<?php echo __("Are you sure you want to desactivate the permalink?"); ?>"))
						{
							jQuery("#enabled_permalink_group").slideUp();
							jQuery("#div_disabled_group").fadeOut(200, function() {
								jQuery("#div_enabled_group").fadeIn();
							});

							if(jQuery.browser.msie)
								jQuery("#enabled_permalink_group").hide();

							saveField("state", 0);
						}
					}
					break;

					case "activate":
					{
						jQuery("#enabled_permalink_group").slideDown();
						jQuery("#div_enabled_group").fadeOut(200, function() {
							jQuery("#div_disabled_group").fadeIn();
						});

						if(jQuery.browser.msie)
							jQuery("#enabled_permalink_group").show();

						saveField("state", 1);
					}
					break;
				}
			});
		<?php endif; ?>

		jQuery("#notify_comment_check_group").bind("click", function() {
			if(jQuery(this).is(":checked") == false)
				var value = 0;
			else
				var value = 1;

			saveField("notify_comment", value);
		});

		jQuery("#notify_upload_check_group").bind("click", function() {
			if(jQuery(this).is(":checked") == false)
				var value = 0;
			else
				var value = 1;

			saveField("notify_upload", value);
		});

		jQuery("#comment_check_group").bind("click", function() {
			if(jQuery(this).is(":checked") == false)
				var value = 0;
			else
				var value = 1;

			saveField("comment", value);
		});

		jQuery("#format_check_group").bind("click", function() {
			if(jQuery(this).is(":checked") == false)
				var value = 0;
			else
				var value = 1;

			saveField("format", value);
		});

		jQuery("#upload_check_group").bind("click", function() {
			if(jQuery(this).is(":checked") == false)
				var value = 0;
			else
				var value = 1;

			saveField("upload", value);
		});

		jQuery("#state_group").bind("click", function() {
			jQuery("#enabled_permalink_group").slideDown();
			jQuery("#div_enabled_group").fadeOut(200, function() {
				jQuery("#div_disabled_group").fadeIn();
			});

			if(jQuery.browser.msie)
				jQuery("#enabled_permalink_group").show();

			saveField("state", 1);
		});

		jQuery("#delete_permalink_group").bind("click", function() {
			jQuery("#enabled_permalink_group").slideUp();
			jQuery("#div_disabled_group").fadeOut(200, function() {
				jQuery("#div_enabled_group").fadeIn();
			});

			if(jQuery.browser.msie)
				jQuery("#enabled_permalink_group").hide();

			saveField("state", 0);
		});

		jQuery("#password_group").live("blur", function() {
			if(jQuery(this).val() == "")
				jQuery(this).replaceWith('<input type="text" name="password_group" id="password_group" style="width: 80%; float:left;" value="' + password + '" />');
			else
				saveField("password", jQuery(this).val());
		});

		jQuery("#password_group").live("focus", function() {
			if(jQuery(this).val() == password)
			{
				jQuery(this).replaceWith('<input type="password" name="password_group" id="password_group" style="width: 80%; float:left;" />');
				jQuery("#password").focus();
			}
		});

		jQuery("#type_group").bind("click", function() {
			if(jQuery(this).is(":checked") == false)
			{
				jQuery("#permalink-password_v").slideUp(200, function() {
					saveField("type", 1);
					jQuery("#password_group").replaceWith('<input type="text" name="password_group" id="password_group" style="width: 80%; float:left;" value="' + password + '" />');
				});
			}
			else
			{
				jQuery("#permalink-password_group").slideDown(200, function() {
					jQuery("#password_group").val(password);
				});
			}
		});

		jQuery("#regenerate_permalink_group").bind("click", function() {
			if(confirm("<?php echo __("Are you sure you want to regenerate the permalinks?"); ?>"))
			{
				jQuery("#permalink_container_group").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>");

				jQuery.post(
					"<?php echo url_for("permalink/regenerate"); ?>",
					{ group_id: <?php echo $group->getId(); ?>, showLabel: "<?php echo $showLabel; ?>" },
					function(data) {
						jQuery("#permalink_container_group").html(data);
					}
				);
			}
		});
	<?php endif; ?>
});

<?php if($role) : ?>
	function saveField(field, value)
	{
		<?php if(!$permalink) : ?>
			jQuery("#permalink_container_group").fadeOut(200, function() {
				jQuery("#permalink_container_group").html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>");
				jQuery("#permalink_container_group").fadeIn(200, function() {
					jQuery.post(
						"<?php echo url_for("permalink/create"); ?>",
						{type: "group", id: "<?php echo $group->getId(); ?>", showLabel: "<?php echo $showLabel; ?>" },
						function(data) {
							jQuery("#permalink_container_group").fadeOut(200, function() {
								jQuery("#permalink_container_group").html(data);
								jQuery("#permalink_container_group").fadeIn();
							});
						}
					);
				});
			});
		<?php else: ?>
			jQuery.post(
				"<?php echo url_for("permalink/field"); ?>",
				{ field: field, value: value, id: "<?php echo $permalink->getId(); ?>" },
				function(data)
				{
					if(field != "type")
						jQuery("#ok_" + field + "_group").fadeIn('slow').delay(1000).fadeOut('slow');
				}
			);
		<?php endif; ?>
	}
<?php endif; ?>
</script>
