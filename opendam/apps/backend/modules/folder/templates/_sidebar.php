<script>var firedSidebar = false;</script>
<?php
	$tags = $tags->getRawValue();
	$tagsSelected = $tagsSelected->getRawValue();
	$creationDateRange = $creationDateRange->getRawValue();
	$shootingDateRange = $shootingDateRange->getRawValue();
	$sizeRange = $sizeRange->getRawValue();
	$edit = false;

	$roleGroup = $sf_user->getRole($folder->getGroupeId());

	if($roleGroup) {
		if($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN && ($sf_user->hasCredential("admin") || $sf_user->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $sf_user->getId())) || ($roleGroup == RolePeer::__CONTRIB && ($sf_user->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) || $folder->getUserId() == $sf_user->getId()))) {
			
				$edit = true;
	
		}	
	}
?>
<form name="sidebarFolder" id="sidebarFolder" action="<?php echo url_for("folder/show"); ?>" method="get">
	<input type="hidden" name="id" id="id" value="<?php echo $folder->getId(); ?>" />
	<div class="right-column">
		<div id="rightColumn">
			<div class="title-sidebar">
				<h1 <?php echo $edit ? "class='eotf'" : ""; ?> id="<?php echo $folder->getId(); ?>" rel="name"><?php echo $folder->getName(); ?></h1>
			</div>
			<div class="inside">
				<div class="cat-right">
					<div class="content">
						<div class="rub">
							<div class="value-right textarea <?php echo $edit ? "eotf-textarea" : ""; ?>" id="<?php echo $folder->getId(); ?>" rel="description"><?php echo $folder->getDescription() ? nl2br($folder->getDescription()) : __("Add a description."); ?></div>
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
								<input type="text" class="input-block-level date-range" name="creation_min" id="creation_min" value="<?php echo $creationMin; ?>" placeholder="<?php echo __("from.date"); ?>" />
								<input type="text" class="input-block-level date-range" name="creation_max" id="creation_max" value="<?php echo $creationMax; ?>" placeholder="<?php echo __("to.date"); ?>" />
							</div>
						</div>
						<div class="divider"></div>
						<div class="rub">
							<div class="label-right"><?php echo __("Size"); ?> (<span id="sizeLabel"><?php echo MyTools::getSize($sizeMin ? $sizeMin : $sizeRange["min"]); ?> - <?php echo MyTools::getSize($sizeMax ? $sizeMax : $sizeRange["max"]); ?></span>)</div>
						</div>
						<div class="rub full-length">
							<div class="value-right full-length">
								<input type="hidden" name="size_min" id="size_min" value="<?php echo $sizeMin; ?>" />
								<input type="hidden" name="size_max" id="size_max" value="<?php echo $sizeMax; ?>" />
								<div id="size-slider" class="slider input-block-level"></div>
							</div>
						</div>
						<!--<div class="rub">
							<div class="label-right"><?php echo __("Shooting date"); ?></div>
							<div class="value-right">
								<input type="text" class="date-range" name="shooting_min" id="shooting_min" value="<?php echo $shootingMin; ?>" placeholder="<?php echo __("from.date"); ?>" />
								<input type="text" class="date-range" name="shooting_max" id="shooting_max" value="<?php echo $shootingMax; ?>" placeholder="<?php echo __("to.date"); ?>" />
							</div>
						</div>-->
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</form>
<script>
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

	function getSize(size)
	{
		return (size > 1048576) ? ((size/1048576).toFixed(1) + "<?php echo __("Mb")?>") : ((size/1024).toFixed(1) + "<?php echo __("Kb")?>");
	}

	jQuery(document).ready(function() {
		if(!firedSidebar)
			return;

		jQuery(".eotf").editable(
			"<?php echo url_for("folder/field"); ?>",
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
			"<?php echo url_for("folder/field"); ?>",
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

		jQuery("#added_by_me_input").bind("click", function() {
			jQuery("#sidebarFolder").submit();
		});

		jQuery(".tags-selected .label").live("click", function() {
			var id = jQuery(this).parent().find("input").val();
			var title = jQuery(this).text();

			jQuery(this).parent().remove();

			if(jQuery(".tags-selected").children().length == 0)
				jQuery(".tags-selected").addClass("empty");

			jQuery(".tags-cloud .keyword[data-id=" + id + "]").removeClass("hide");
			jQuery(".tags-cloud .keyword[data-id=" + id + "]").next().removeClass("hide");

			jQuery("#sidebarFolder").submit();
		});

		jQuery(".keyword").bind("click", function() {
			var id = jQuery(this).attr("data-id");
			var title = jQuery(this).text();

			jQuery(this).next(".divider").addClass("hide");
			jQuery(this).addClass("hide");

			jQuery(".tags-selected").removeClass("empty").append("<span><a href='javascript: void(0);' class='label'>" + title + "<i class='icon-remove-sign'></i></a><input type='hidden' name='selected_tag_ids[]' value='" + id + "' /></span>");

			jQuery("#sidebarFolder").submit();
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

		jQuery("#creation_min").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $creationDateRange["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y", $creationDateRange["max"]); ?>",
			dateFormat: "dd/mm/yy",
			<?php if(!empty($creationMin)) : ?>
				defaultDate: "<?php echo $creationMin; ?>",
			<?php endif; ?>
			onClose: function(selectedDate) {
				jQuery("#creation_max").datepicker("option", "minDate", selectedDate);

				if(!jQuery("#creation_max").val())
					jQuery("#creation_max").datepicker("show");
				else
					jQuery("#sidebarFolder").submit();
			}
		});

		jQuery("#creation_max").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $creationDateRange["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y",  $creationDateRange["max"]); ?>",
			dateFormat: "dd/mm/yy",
			<?php if(!empty($creationMax)) : ?>
				defaultDate: "<?php echo $creationMax; ?>",
			<?php endif; ?>
			onClose: function(selectedDate) {
				jQuery("#creation_min").datepicker("option", "maxDate", selectedDate);

				if(!jQuery("#creation_min").val())
					jQuery("#creation_min").datepicker("show");
				else
					jQuery("#sidebarFolder").submit();
			}
		});

		jQuery("#size-slider").slider({
			range: true,
			min: <?php echo !empty($sizeRange["min"]) ? $sizeRange["min"] : 0; ?>,
			max: <?php echo !empty($sizeRange["max"]) ? $sizeRange["max"] : 0; ?>,
			values: [<?php echo $sizeMin ? $sizeMin : $sizeRange["min"]; ?>, <?php echo $sizeMax ? $sizeMax : $sizeRange["max"]; ?>],
			slide: function(event, ui) {
				jQuery("#size_min").val(ui.values[0]); 
				jQuery("#size_max").val(ui.values[1]);
				jQuery("#sizeLabel").html(getSize(ui.values[0]) + " - " +  getSize(ui.values[1]));
			},
			change: function(event, ui) {
				jQuery("#sidebarFolder").submit();
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

		/*jQuery("#shooting_min").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $shootingDateRange["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y", $shootingDateRange["max"]); ?>",
			dateFormat: "dd/mm/yy",
			<?php if(!empty($creationMin)) : ?>
				defaultDate: "<?php echo $creationMin; ?>",
			<?php endif; ?>
			onClose: function(selectedDate) {
				jQuery("#shooting_max").datepicker("option", "minDate", selectedDate);

				if(!jQuery("#shooting_max").val())
					jQuery("#shooting_max").datepicker("show");
				else
					jQuery("#sidebarFolder").submit();
			}
		});

		jQuery("#shooting_max").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $shootingDateRange["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y",  $shootingDateRange["max"]); ?>",
			dateFormat: "dd/mm/yy",
			<?php if(!empty($creationMax)) : ?>
				defaultDate: "<?php echo $creationMax; ?>",
			<?php endif; ?>
			onClose: function(selectedDate) {
				jQuery("#shooting_min").datepicker("option", "maxDate", selectedDate);

				if(!jQuery("#shooting_min").val())
					jQuery("#shooting_min").datepicker("show");
				else
					jQuery("#sidebarFolder").submit();
			}
		});*/
	});
</script>
<script>firedSidebar = true;</script>
