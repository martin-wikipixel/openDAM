<script>var firedSidebar = false;</script>
<?php
	$tags = $tags->getRawValue();
	$tagsSelected = $tagsSelected->getRawValue();
	$dateRange = $dateRange->getRawValue();
	$edit = false;

	$role = $sf_user->getRole($group->getId());

	if($role && $role <= RolePeer::__ADMIN) {
		
			$edit = true;

	}
?>
<form name="sidebarGroup" id="sidebarGroup" action="<?php echo url_for("group/show"); ?>" method="get">
	<input type="hidden" name="id" id="id" value="<?php echo $group->getId(); ?>" />
	<div class="right-column">
		<div id="rightColumn">
			<div class="title-sidebar">
				<h1 <?php echo $edit ? "class='eotf'" : ""; ?> id="<?php echo $group->getId(); ?>" rel="name"><?php echo $group->getName(); ?></h1>
			</div>
			<div class="cat-right">
				<div class="content">
					<div class="rub">
						<div class="value-right textarea <?php echo $edit ? "eotf-textarea" : ""; ?>" id="<?php echo $group->getId(); ?>" rel="description"><?php echo $group->getDescription() ? nl2br($group->getDescription()) : __("Add a description."); ?></div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="cat-right">
				<a class="deploy-cat active" href="javascript: void(0);"><i class="icon-tags"></i> <?php echo __("Filter by tags"); ?> <i class="icon-chevron-up right"></i></a>

				<div class="content">
					<div class="tags-selected <?php echo empty($tagsSelected) ? "empty" : ""; ?>">
						<?php foreach($tagsSelected as $tag) : ?>
							<span>
								<a href="javascript: void(0);" class="label"><?php echo $tag->getTitle(); ?><i class='icon-remove-sign'></i></a>
								<input type="hidden" name="selected_tag_ids[]" value="<?php echo $tag->getId(); ?>" />
							</span>
						<?php endforeach; ?>
					</div>

					<div class="tags-cloud">
						<?php foreach($tags as $tag) : ?>
							<a href="javascript: void(0);" class="keyword <?php echo in_array($tag, $tagsSelected) ? "hide" : ""; ?>" data-id="<?php echo $tag->getId(); ?>"><?php echo $tag->getTitle(); ?></a>
							<span class="divider <?php echo in_array($tag, $tagsSelected) ? "hide" : ""; ?>"><?php echo $tag != end($tags) && !in_array($tag, $tagsSelected) ? "-" : ""; ?></span>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="cat-right">
				<a class="deploy-cat active" href="javascript: void(0);"><i class="icon-info-sign"></i> <?php echo __("Filter by information"); ?> <i class="icon-chevron-up right"></i></a>
				
				<div class="content">
					<div class="rub">
						<div class="label-right"><input type="checkbox" name="added_by_me_input" id="added_by_me_input" <?php echo $addedByMe ? "checked" : ""; ?> /></div>
						<div class="value-right"><?php echo __("Added by me")?></div>
					</div>
					<div class="divider"></div>
					<div class="rub">
						<div class="label-right"><?php echo __("Creation date"); ?></div>
					</div>
					<div class="rub">
						<div class="value-right">
							<input type="text" class="input-block-level date-range" name="min_range" id="min_range" value="<?php echo $min; ?>" placeholder="<?php echo __("from.date"); ?>" />
							<input type="text" class="input-block-level date-range" name="max_range" id="max_range" value="<?php echo $max; ?>" placeholder="<?php echo __("to.date"); ?>" />
						</div>
					</div>
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
</form>
<script>
	<?php if($edit) : ?>
		function bindBorder(settings, object)
		{
			jQuery(object).attr("style", "background: transparent!important; border-color: transparent!important;");
			jQuery('.eotf').bind('mouseover', overTd);
			jQuery('.eotf').bind('mouseout', outTd);

			return true;
		}

		function unbindBorder(settings, object)
		{
			jQuery('.eotf').unbind('mouseover');
			jQuery('.eotf').unbind('mouseout');
			jQuery(object).attr("style", "background: transparent!important; border-color: transparent!important;");

			return true;
		}

		function bindBorderTextarea(settings, object)
		{
			jQuery(object).attr("style", "background: transparent!important; border-color: transparent!important;");
			jQuery('.eotf-textarea').bind('mouseover', overTd);
			jQuery('.eotf-textarea').bind('mouseout', outTd);

			return true;
		}

		function unbindBorderTextarea(settings, object)
		{
			jQuery('.eotf-textarea').unbind('mouseover');
			jQuery('.eotf-textarea').unbind('mouseout');
			jQuery(object).attr("style", "background: transparent!important; border-color: transparent!important;");

			return true;
		}

		function overTd(event)
		{
			jQuery(event.currentTarget).attr("style", "background: #FAFAFA!important; border-color: #E6E6E6!important;");
		}

		function outTd(event)
		{
			jQuery(event.currentTarget).attr("style", "background: transparent!important; border-color: transparent!important;");
		}
	<?php endif; ?>

	jQuery(document).ready(function() {
		if(!firedSidebar)
			return;

		<?php if($edit) : ?>
			jQuery(".eotf").editable(
				"<?php echo url_for("group/field"); ?>",
				{
					indicator: '<?php echo __("Saving");?>...',
					placeholder: '',
					cssclass: 'editable',
					onedit: unbindBorder,
					onreset: bindBorder,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						jQuery(this).html(value);
						bindBorder(settings, this);
					},
					data: function(value, settings) {
						var regexp = new RegExp("(<?php echo strtolower(__("To inform")); ?>)","g");

						if(regexp.test(value.toLowerCase()))
								return "";

						return value;
					}
				}
			);

			jQuery(".eotf-textarea").editable(
				"<?php echo url_for("group/field"); ?>",
				{
					type: 'textarea',
					indicator: '<?php echo __("Saving");?>...',
					placeholder: '',
					cssclass: 'editable',
					onedit: unbindBorderTextarea,
					onreset: bindBorderTextarea,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						jQuery(this).html(value);
						bindBorderTextarea(settings, this);
					},
					data: function(value, settings) {
						var regexp = new RegExp("(<?php echo strtolower(__("Add a description.")); ?>)","g");

						if(regexp.test(value.toLowerCase()))
								return "";

						var retval = value.replace(/<br\s*\/?>/mg,"");
						return retval;
					}
				}
			);

			jQuery(".eotf").bind("mouseover", overTd);
			jQuery(".eotf").bind("mouseout", outTd);
			jQuery(".eotf-textarea").bind("mouseover", overTd);
			jQuery(".eotf-textarea").bind("mouseout", outTd);
		<?php endif; ?>

		jQuery("#added_by_me_input").bind("click", function() {
			jQuery("#sidebarGroup").submit();
		});

		jQuery(".tags-selected .label").live("click", function() {
			var id = jQuery(this).parent().find("input").val();
			var title = jQuery(this).text();

			jQuery(this).parent().remove();

			if(jQuery(".tags-selected").children().length == 0)
				jQuery(".tags-selected").addClass("empty");

			jQuery(".tags-cloud .keyword[data-id=" + id + "]").removeClass("hide");
			jQuery(".tags-cloud .keyword[data-id=" + id + "]").next().removeClass("hide");

			jQuery("#sidebarGroup").submit();
		});

		jQuery(".keyword").bind("click", function() {
			var id = jQuery(this).attr("data-id");
			var title = jQuery(this).text();

			jQuery(this).next(".divider").addClass("hide");
			jQuery(this).addClass("hide");

			jQuery(".tags-selected").removeClass("empty").append("<span><a href='javascript: void(0);' class='label'>" + title + "<i class='icon-remove-sign'></i></a><input type='hidden' name='selected_tag_ids[]' value='" + id + "' /></span>");

			jQuery("#sidebarGroup").submit();
		});

		jQuery(".deploy-cat:not(.inactive)").bind("click", function() {
			var div = jQuery(this).parent().find(".content");

			if(div.is(":visible"))
			{
				jQuery(this).removeClass("active");
				jQuery(this).find(".right").removeClass("icon-chevron-up").addClass("icon-chevron-down");
				div.slideUp("slow");
			}
			else
			{
				jQuery(this).addClass("active");
				jQuery(this).find(".right").removeClass("icon-chevron-down").addClass("icon-chevron-up");
				div.slideDown("slow");
			}
		});

		var availableHeight = jQuery(window).height() - jQuery(".sidebar-container").offset().top - 15;

		jQuery(".sidebar-container").slimScroll({
			height: availableHeight + "px"
		});

		jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);

		jQuery("#min_range").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $dateRange["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y", $dateRange["max"]); ?>",
			dateFormat: "dd/mm/yy",
			<?php if(!empty($min)) : ?>
				defaultDate: "<?php echo $min; ?>",
			<?php endif; ?>
			onClose: function(selectedDate) {
				jQuery("#max_range").datepicker("option", "minDate", selectedDate);

				if(!jQuery("#max_range").val())
					jQuery("#max_range").datepicker("show");
				else
					jQuery("#sidebarGroup").submit();
			}
		});

		jQuery("#max_range").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $dateRange["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y",  $dateRange["max"]); ?>",
			dateFormat: "dd/mm/yy",
			<?php if(!empty($max)) : ?>
				defaultDate: "<?php echo $max; ?>",
			<?php endif; ?>
			onClose: function(selectedDate) {
				jQuery("#min_range").datepicker("option", "maxDate", selectedDate);

				if(!jQuery("#min_range").val())
					jQuery("#min_range").datepicker("show");
				else
					jQuery("#sidebarGroup").submit();
			}
		});

		jQuery("#sidebar_block").hover(
			function (event) {
				var item = jQuery(event.target);

				if(!item.hasClass("show-bar") && !jQuery(".slimScrollDiv").is(":animated"))
				{
					var top = (jQuery(".slimScrollDiv").height() - jQuery(".slide-sidebar.hide-bar").height()) / 2;
					top += jQuery(".slimScrollDiv").offset().top;

					var left = (jQuery(".slimScrollDiv").offset().left + jQuery(".slimScrollDiv").width());
					jQuery(".slide-sidebar.hide-bar").css({"top": top + "px", "left": left + "px"});
					jQuery(".slide-sidebar.hide-bar").fadeIn();
				}
			},
			function () {
				jQuery(".slide-sidebar.hide-bar").stop(true, true).fadeOut();
			}
		);
	});
</script>
<script>firedSidebar = true;</script>
