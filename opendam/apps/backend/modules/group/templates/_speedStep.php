<label for="group_name"><?php echo __("Group name"); ?></label>
<input type="text" name="group_name" id="group_name" style="width: 100%;" />


<div id="error">
	<span class="require_field"></span>
</div>

<br clear="all" />
<br clear="all" />

<div class="navigation_inner">
	<a class="button btnBS" id="create_group" href="javascript: void(0);"><span><?php echo __("Create a group"); ?></span></a>
</div>

<script>
	jQuery(document).ready(function() {
		jQuery("#create_group").unbind();

		jQuery("#create_group").bind("click", function() {
			if(jQuery.trim($('#group_name').val()).length <= 0)
				jQuery("#error span.require_field").hide().html("<?php echo __("Group name is required."); ?>").fadeIn();
			else
			{
				jQuery("#error span.require_field").fadeOut();

				jQuery("#create_group").fadeOut(200, function() {
					var access = null;

					if(jQuery("#free_access").is(":checked") == true)
						access = "free";
					else
						access = "managed";

					jQuery.post(
						"<?php echo url_for("group/create"); ?>",
						{ access: access, name: jQuery("#group_name").val(), role: jQuery("#users_right").val(), next_step: <?php echo $next_step; ?> },
						function(data) {
							if(data.code == 0)
							{
								jQuery("#create_group").parent().hide().css("width", "auto").html("<div><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /><?php echo __("Creation of main folder in progress..."); ?></div>").fadeIn();

								<?php if($next_step == "true"): ?>
									jQuery("#first").hide().html("<div id='loading-step' style='text-align: center; width: 100%;'><img src='<?php echo image_path("loader-rotate.gif"); ?>' /></div>").fadeIn();
									
									jQuery("#first").fadeOut(200, function() {
										jQuery("#first").html(data.html);
										jQuery("#first").dialog("option", "title", "<span class='first-title'><?php echo __("Create new folder"); ?></span>");

										jQuery("#first").fadeIn(200, function() {
											jQuery("#create_folder").parent().css("width", jQuery("#create_folder")[0].offsetWidth + "px");
											jQuery("#create_folder").parent().fadeIn(200, function() {
												jQuery("#create_folder").bind("click", function() {
													if(jQuery.trim(jQuery('#add_folder_folder_name').val()).length <= 0)
													{
														jQuery("#error span.require_field").hide().html("<?php echo __("Folder name is required."); ?>").fadeIn();
													}
													else
													{
														jQuery("#error span.require_field").fadeOut();

														jQuery("#create_folder").fadeOut(200, function() {
															jQuery("#create_folder").parent().hide().css("width", "auto").html("<div><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /><?php echo __("Creation of folder in progress..."); ?></div>").fadeIn();

															jQuery.post(
																"<?php echo url_for("folder/create"); ?>",
																{ name: jQuery("#add_folder_folder_name").val(), group_id: jQuery("#add_folder_group_id").val(), folder_id: jQuery("#add_folder_folder_id").val(), next_step: <?php echo $next_step; ?> },
																function(data) {
																	if(data.code == 0)
																	{
																		jQuery("#first").dialog("close");
																		jQuery.facebox({ iframe: data.html });
																	}
																},
																"json"
															);
														});
													}
												});
											});
										});
									});
								<?php else: ?>
									window.location.href = data.html;
								<?php endif; ?>
							}
							else
								jQuery("#error span.require_field").hide().html("<?php echo __("The same named group already exists. Please enter an another name."); ?>").fadeIn();
						},
						"json"
					);
				});
			}
		});
	});
</script>