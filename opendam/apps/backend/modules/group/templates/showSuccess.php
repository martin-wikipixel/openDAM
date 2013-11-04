<?php
	$folders = $folders->getRawValue();
	$selected_tag_ids = $selected_tag_ids->getRawValue();
	$dateRange = $dateRange->getRawValue();
?>

<div class="folders-container inner">
	<div class="container">
		<div class="row">
			<div class="folders span9">
				<div id="folders">
					<?php if(empty($folders)) : ?>
						<div class="no-result">
							<?php echo __("No folder found."); ?>
						</div>
					<?php else: ?>
						<?php foreach ($folders as $folder) : ?>
							<?php include_partial("folder/grid", Array("folder" => $folder)); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<?php if($count > $itemsToShow) : ?>
					<div class="show-more-folders">
						<a href="javascript: void(0);" class="btn-header"><?php echo __("Show more"); ?></a>
					</div>
				<?php endif; ?>
			</div>
			<div class="span3" id="sidebar_block">
				<div class="slide-sidebar show-bar">
					<a href="javascript: void(0);" id="show-sidebar"><i class="icon-double-angle-left"></i></a>
				</div>
				<div class="sidebar-container">
					<?php include_component("group", "sidebar", Array("group" => $group, "tagsSelected" => TagPeer::retrieveByPks($selected_tag_ids), "addedByMe" => $added_by_me_input, "min" => $dateRange["min"], "max" => $dateRange["max"])); ?>
				</div>
				<div class="slide-sidebar hide-bar">
					<a href="javascript: void(0);" id="hide-sidebar"><i class="icon-double-angle-right"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="context-folders">
</div>
<div id="add_folder_container" class="dialog">
	<?php include_component("folder", "speedStep", array("subfolder" => null, "group_id" => $group->getId())); ?>
</div>
<div id="div_share" class="dialog"></div>
<script>
	function bindFolders()
	{
			jQuery(".item-folder[data-id^=folder_].open").draggable({
					helper: "clone",
					distance: 30,
					start: function(event, ui) {
						jQuery(ui.helper).css("z-index", 1500);
						showSelectionBar();
					},
					stop: function(event, ui) {
						if(!jQuery(this).hasClass("dropped"))
							hideSelectionBar();

						jQuery(this).removeClass("dropped");
					}
				});
		/*
			jQuery(".item-folder[data-id^=folder_].moveDnd").droppable({
				drop: function(event, ui) {
					if(ui.draggable.hasClass("file-div") || ui.draggable.hasClass("list-file"))
					{
						if(confirm("<?php echo __("Are you sure you want to move this file?"); ?>"))
						{
							file_id = ui.draggable.attr("id");
							folder_id = jQuery(this).attr("id");

							jQuery.post(
								"<?php echo url_for("file/moveDnd"); ?>",
								{ file_id: file_id, folder_id: folder_id },
								function(data) {
									ui.draggable.remove();
								},
								"json"
							)
						}
					}
					else
					{
						if(confirm("<?php echo __("Are you sure you want to move this folder?"); ?>"))
						{
							from = ui.draggable.attr("id");
							to = jQuery(this).attr("id");

							jQuery.post(
								"<?php echo url_for("folder/moveDnd"); ?>",
								{ from: from, to: to },
								function(data) {
									ui.draggable.remove();
								},
								"json"
							)
						}
					}

					return true;
				}
			});
		*/

		jQuery('a[rel*=facebox]').bind("click", function(){
			jQuery.facebox({ iframe: this.href });
			return false;
		});

		jQuery('a[rel*=faceframe]').bind("click", function(){
			jQuery.facebox.settings.minHeight = 670;
			jQuery.facebox({ iframe: this.href });
			return false;
		});
	}

	function unbindFolders()
	{
		jQuery(".item-folder[data-id^=folder_].open").draggable("destroy");
		// jQuery(".item-folder[data-id^=folder_].moveDnd").droppable("destroy");
		jQuery('a[rel*=facebox]').unbind("click");
		jQuery('a[rel*=faceframe]').unbind("click");
	}

	function displayFolders()
	{
		jQuery(".item-folder").each(function() {
			var width = jQuery(this).width();
			var infoHeight = jQuery(this).find(".info-folder").get(0).offsetHeight + 1;
			var availableHeight = width - infoHeight;
			var originalWidth = jQuery(this).find("img").attr("data-width");
			var originalHeight = jQuery(this).find("img").attr("data-height");

			if(originalHeight <= availableHeight)
			{
				jQuery(this).find("img").css("padding-top", ((availableHeight - originalHeight) / 2) + "px");
				jQuery(this).find("img").css("padding-bottom", ((availableHeight - originalHeight) / 2) + "px");
				jQuery(this).find("img").closest(".thumbnail").css("height", availableHeight + "px");
			}
			else
			{
				var minW = width;
				var minH = availableHeight;

				if(originalWidth > minW)
					var ratio = minW / originalWidth;
				else if(originalHeight > minH)
					var ratio = minH / originalHeight;
				else
					var ratio = 1;

				var tWidth = Math.round(originalWidth * ratio);
				var tHeight = Math.round(originalHeight * ratio);

				if(tWidth > minW)
				{
					var margin = Math.floor((tWidth - minW) / 2);
					var css = { "clip": "rect(auto, " + (minW + margin) + "px, auto, 0px)", "width": tWidth + "px", "height": tHeight + "px" };
				}
				else if(tHeight > minH)
				{
					var margin = Math.floor((tHeight - minH) / 2);
					var css = { "clip": "rect(" + margin + "px, auto, " + (minH + margin) + "px, auto)", "width": tWidth + "px", "height": tHeight + "px", "top": (margin * -1) + "px" };
				}
				else
				{
					var paddingLeft = Math.floor((minW - tWidth) / 2);
					var paddingTop = Math.floor((minH - tHeight) / 2);
					var css = { "width": tWidth + "px", "height": tHeight + "px", "padding-top": paddingTop + "px", "padding-left": paddingLeft + "px" };
				}

				jQuery(this).find("img").closest(".thumbnail").css("height", availableHeight + "px");
				jQuery(this).find("img").addClass("cropp").css(css);
			}
		});
	}

	function initMasonryFolders()
	{
		var gutterWidthFolders = 10;
		var minWidthFolders = 270;

		jQuery("#folders").imagesLoaded(function() {
			jQuery("#folders").masonry({
				itemSelector : '.item-folder',
				gutterWidth: gutterWidthFolders,
				isAnimated: true,
				columnWidth: function(containerWidth) {
					var itemNbr = (containerWidth / minWidthFolders | 0);

					var itemWidth = (((containerWidth - (itemNbr - 1) * gutterWidthFolders) / itemNbr) | 0);

					if (containerWidth < minWidthFolders)
						itemWidth = containerWidth;

					jQuery(".item-folder").width(itemWidth);

					displayFolders();

					return itemWidth;
				}
			});

			if(jQuery(".slimScrollDiv").height() > jQuery("#folders").height())
				jQuery("#folders").css("height", jQuery(".slimScrollDiv").height() + "px");
		});
	}

	function setAffix(object)
	{
		var height = object.height();
		var offsetTop = object.offset().top;
		var visibleHeight = 0;
		var margin = 20;

		object.attr("data-height", height);

		jQuery(".navbar-fixed-top").each(function() {
			if(jQuery(this).is(":visible"))
				visibleHeight += jQuery(this).height();
		});

		visibleHeight += margin;
		offsetTop -= visibleHeight;

		jQuery(window).scroll(function() {
			if(jQuery(window).scrollTop() >= offsetTop)
			{
				if(!jQuery(".show-bar").is(":visible"))
					object.addClass("fixed").css("top", visibleHeight + "px").removeClass("absolute");

				if(object.height() < (jQuery(window).height() - visibleHeight))
				{
					object.css("height", (jQuery(window).height() - visibleHeight - margin) + "px");
					object.find(">:first-child").css("height", (jQuery(window).height() - visibleHeight - margin) + "px");
				}
			}

			if(jQuery(window).scrollTop() < offsetTop)
			{
				if(!jQuery(".show-bar").is(":visible"))
					object.addClass("absolute").css("top", "auto").removeClass("fixed");

				object.find(">:first-child").css("height", object.attr("data-height") + "px");
			}

			var top = jQuery(window).scrollTop() + (jQuery(window).height() / 2);

			jQuery(".slide-sidebar.hide-bar").css("top", top + "px");
		});
	}

	function initSidebar()
	{
		var temp = jQuery("<div class='span3'></div>");
		jQuery("body").append(temp);
		var slimScrollWidth = temp.width();
		jQuery(temp).remove();

		var marginFolder = jQuery(".folders").css("margin-left").replace(/[^-\d\.]/g, '')
		var offset = (jQuery(".folders").width() + jQuery("#folders").offset().left + parseInt(marginFolder, 10));

		jQuery(".slimScrollDiv").addClass("absolute").css({ "left": offset + "px", "width": slimScrollWidth + "px"});
		setAffix(jQuery(".slimScrollDiv"));
	}

	jQuery(document).ready(function() {
		var foldersWidth = 0;
		var pageFolders = 2;
		var onPageFolders = <?php echo $itemsToShow != "all" ? $itemsToShow : 0; ?>;;

		tooltip();

		bindFolders();

		initMasonryFolders();

		initSidebar();


		jQuery("#add_folder").bind("click", function() {
			jQuery("#add_folder_container").dialog({
				title: "<span class='first-title'><?php echo __("Create new folder"); ?></span>",
				resizable: false,
				draggable: false,
				modal: true,
				width: 450,
				height: 265,
				show: 'fade',
				hide: 'fade',
				open: function(event, ui) {
					jQuery("#create_folder").parent().css("width", jQuery("#create_folder")[0].offsetWidth + "px");
				},
				create: function(event, ui) {
					jQuery("#create_folder").bind("click", function() {
						if(jQuery.trim(jQuery('#add_folder_folder_name').val()).length <= 0)
							jQuery("#error span.require_field").hide().html("<?php echo __("Folder name is required."); ?>").fadeIn();
						else
						{
							jQuery("#error span.require_field").fadeOut();

							jQuery.post(
								"<?php echo url_for("folder/create"); ?>",
								{ name: jQuery("#add_folder_folder_name").val(), group_id: jQuery("#add_folder_group_id").val(), folder_id: jQuery("#add_folder_folder_id").val() },
								function(data) {
									if(data.code == 0)
									{
										jQuery("#create_folder").fadeOut(200, function() {
											jQuery("#create_folder").parent().hide().css("width", "auto").html("<div><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /><?php echo __("Creation of folder in progress..."); ?></div>").fadeIn();
											window.location.href = data.html;
										});
									}
									else
										jQuery("#error span.require_field").hide().html("<?php echo __("Folder name already exists in this group."); ?>").fadeIn();
								},
								"json"
							);
						}
					});
				}
			});
		});

		jQuery("#share_group").bind("click", function() {
			jQuery("#div_share").dialog({
				title: "<span class='first-title'><?php echo __("Main folder sharing"); ?></span>",
				resizable: false,
				draggable: false,
				modal: true,
				width: "500",
				height: "400",
				show: 'fade',
				hide: 'fade',
				open: function(event, ui) {
					var object = jQuery(this);

					object.html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path('loader-rotate.gif'); ?>' /></div>");

					jQuery.post(
						"<?php echo url_for("group/share"); ?>",
						{ id: <?php echo $group->getId(); ?> },
						function(data) {
							object.fadeOut(400, function() {
								object.html(data);
								object.fadeIn(400);
							});
						}
					);
				},
			});
		});

		jQuery(".item-folder .actions.share:not(.disabled)").bind("click", function() {
			var parent = jQuery(this).closest(".item-folder");

			jQuery("#div_share").dialog({
				title: "<span class='first-title'><?php echo __("Folder sharing"); ?></span>",
				resizable: false,
				draggable: false,
				modal: true,
				width: "500",
				height: "400",
				show: 'fade',
				hide: 'fade',
				open: function(event, ui) {
					var object = jQuery(this);

					object.html("<div style='width: 100%; text-align: center;'><img src='<?php echo image_path('loader-rotate.gif'); ?>' /></div>");

					jQuery.post(
						"<?php echo url_for("folder/share"); ?>",
						{ id: parent.attr("data-value") },
						function(data) {
							object.fadeOut(400, function() {
								object.html(data);
								object.fadeIn(400);
							});
						}
					);
				},
			});
		});

		jQuery(".bread.favorites").live("click", function() {
			var object = jQuery(this);

			jQuery.post(
				"<?php echo url_for("favorite/add"); ?>",
				{ type: "<?php echo FavoritesPeer::__TYPE_GROUP; ?>", id: "<?php echo $group->getId(); ?>" },
				function(data) {
					if(data.errorCode == 0)
					{
						object.html("<i class='icon-star'></i> <?php echo __("Remove from favorites"); ?>");
						object.addClass("unfavorites").removeClass("favorites");
					}
				},
				"json"
			);
		});

		jQuery(".bread.unfavorites").live("click", function() {
			var object = jQuery(this);

			jQuery.post(
				"<?php echo url_for("favorite/delete"); ?>",
				{ type: "<?php echo FavoritesPeer::__TYPE_GROUP; ?>", id: "<?php echo $group->getId(); ?>" },
				function(data) {
					if(data.errorCode == 0)
					{
						object.html("<i class='icon-star-empty'></i> <?php echo __("Add to favorites"); ?>");
						object.addClass("favorites").removeClass("unfavorites");
					}
				},
				"json"
			);
		});

		jQuery("#hide-sidebar").bind("click", function() {
			jQuery(".slide-sidebar").hide();

			var left = jQuery(".slimScrollDiv").css("left").replace(/[^-\d\.]/g, '')

			if(!jQuery(".slimScrollDiv").attr("data-left"))
				jQuery(".slimScrollDiv").attr("data-left", left);

			jQuery(".slimScrollDiv").removeClass("absolute").css("position", "fixed");

			jQuery(".slimScrollDiv").stop().animate({
				left: window.innerWidth
			}, 500, function() {
				jQuery(".slide-sidebar.show-bar").css("right", "0px").show();

				jQuery(".folders").addClass("span12").removeClass("span9");
				jQuery("#folders").masonry("reload");
			});
		});

		jQuery("#show-sidebar").bind("click", function() {
			jQuery(".slide-sidebar").hide();

			jQuery(".folders").addClass("span9").removeClass("span12");
			jQuery("#folders").masonry("reload");

			setTimeout(function() {
				jQuery(".slimScrollDiv").stop().animate({
					left: jQuery(".slimScrollDiv").attr("data-left")
				}, 500, function() {
					var left = (jQuery(".slimScrollDiv").offset().left + jQuery(".slimScrollDiv").width());
					var top = (jQuery(".slimScrollDiv").height() - jQuery(".slide-sidebar.hide-bar").height()) / 2;
					top += jQuery(".slimScrollDiv").offset().top;

					jQuery(".slide-sidebar.hide-bar").css({"top": top + "px", "left": left + "px"});
				});
			}, 300);
		});

		<?php if($count > $itemsToShow) : ?>
			jQuery(".show-more-folders a").bind("click", function() {
				if(!jQuery(".show-more-folders a").data("clicked"))
				{
					jQuery(".show-more-folders a").data("clicked", true);
					jQuery(".show-more-folders a").html("<i class='icon-spinner icon-spin'></i> <?php echo __("Loading folders..."); ?>");
	
					jQuery.post(
						"<?php echo url_for("folder/loadFolders"); ?>",
						{ id: "<?php echo $group->getId(); ?>", page: pageFolders, onPage: onPageFolders, sort: jQuery("#sort").val() },
						function(data) {
							unbindFolders();
	
							pageFolders++;
	
							var folders = "<div>" + data.folders + "</div>";
							var childs = jQuery(folders).children();
							jQuery("#folders").append(childs);
	
							bindFolders();
	
							jQuery("#folders").imagesLoaded(function() {
								jQuery("#folders").masonry("reload");
							});
	
							if(data.index == 0)
								jQuery(".show-more-folders").fadeOut(400);
							else
							{
								jQuery(".show-more-folders a").html("<?php echo __("Show more"); ?>");
								jQuery(".show-more-folders a").data("clicked", false);
							}
						},
						"json"
					);
				}
			});
		<?php endif; ?>

		jQuery("#per_page").bind("change", function() {
			if(!jQuery(".show-more-folders a").data("clicked"))
			{
				onPageFolders = jQuery(this).val();

				jQuery(".show-more-folders").hide();

				jQuery("#folders").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading folders..."); ?></div>");
				jQuery("#folders").addClass("overflow-hidden").css("height", "80px");

				jQuery.post(
					"<?php echo url_for("folder/loadFolders"); ?>",
					{ id: "<?php echo $group->getId(); ?>", page: 1, onPage: onPageFolders, sort: jQuery("#sort").val() },
					function(data) {
						pageFolders = 2;

						jQuery("#folders").removeClass("overflow-hidden");

						unbindFolders();

						var folders = "<div>" + data.folders + "</div>";
						var childs = jQuery(folders).children();
						jQuery("#folders").html(childs);

						bindFolders();

						jQuery("#folders").imagesLoaded(function() {
							jQuery("#folders").masonry("reload");
						});

						if(data.index == 0)
							jQuery(".show-more-folders").fadeOut(400);
						else
						{
							jQuery(".show-more-folders a").html("<?php echo __("Show more"); ?>");
							jQuery(".show-more-folders a").data("clicked", false);
							jQuery(".show-more-folders").fadeIn(400);
						}
					},
					"json"
				);
			}
		});

		jQuery("#sort").bind("change", function() {
			if(!jQuery(".show-more-folders a").data("clicked"))
			{
				jQuery(".show-more-folders").hide();

				jQuery("#folders").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading folders..."); ?></div>");
				jQuery("#folders").addClass("overflow-hidden").css("height", "80px");

				jQuery.post(
					"<?php echo url_for("folder/loadFolders"); ?>",
					{ id: "<?php echo $group->getId(); ?>", page: 1, onPage: onPageFolders, sort: jQuery("#sort").val() },
					function(data) {
						pageFolders = 2;

						jQuery("#folders").removeClass("overflow-hidden");

						unbindFolders();

						var folders = "<div>" + data.folders + "</div>";
						var childs = jQuery(folders).children();
						jQuery("#folders").html(childs);

						bindFolders();

						jQuery("#folders").imagesLoaded(function() {
							jQuery("#folders").masonry("reload");
						});

						if(data.index == 0)
							jQuery(".show-more-folders").fadeOut(400);
						else
						{
							jQuery(".show-more-folders a").html("<?php echo __("Show more"); ?>");
							jQuery(".show-more-folders a").data("clicked", false);
							jQuery(".show-more-folders").fadeIn(400);
						}
					},
					"json"
				);
			}
		});
	});
</script>
