<?php
	$folderss = $folders->getRawValue();
	$files = $files->getRawValue();
	$selected_tag_ids = $selected_tag_ids->getRawValue();
	$creationDateRange = $creationDateRange->getRawValue();
	$shootingDateRange = $shootingDateRange->getRawValue();
	$sizeRange = $sizeRange->getRawValue();

	if(!empty($selected_tag_ids))
		$url = url_for("folder/show")."?id=".$folder->getId()."&page=".($page + 1)."&selected_tag_ids[]=".implode("&selected_tag_ids[]=", $selected_tag_ids);
	else
		$url = url_for("folder/show?id=".$folder->getId()."&page=".($page + 1));
?>

<?php if(!empty($folderss)) : ?>
	<div class="folders-container inner">
		<div class="container">
			<div class="row">
				<div class="folders span9">
					<div id="folders">
						<?php foreach ($folderss as $folders) : ?>
							<?php include_partial("folder/grid", Array("folder" => $folders)); ?>
						<?php endforeach; ?>
					</div>
					<div class="show-more-folders bottom"<?php echo $count < $itemsToShow ? "style='display: none;'" : ""; ?>>
						<a href="javascript: void(0);" class="btn-header"><?php echo __("Show more"); ?></a>
					</div>
				</div>
				<div class="span3" id="sidebar_block">
					<div class="slide-sidebar show-bar">
						<a href="javascript: void(0);" id="show-sidebar"><i class="icon-double-angle-left"></i></a>
					</div>
					<div class="sidebar-container">
						<?php include_component("folder", "sidebar", Array("folder" => $folder, "tagsSelected" => TagPeer::retrieveByPks($selected_tag_ids), "addedByMe" => $added_by_me_input, "creationMin" => $creationDateRange["min"], "creationMax" => $creationDateRange["max"], "shootingMin" => $shootingDateRange["min"], "shootingMax" => $shootingDateRange["max"], "sizeMin" => $sizeRange["min"], "sizeMax" => $sizeRange["max"])); ?>
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
<?php endif; ?>
<div id="add_folder_container" class="dialog">
	<?php include_component("folder", "speedStep", array("subfolder" => $folder->getId(), "group_id" => $folder->getGroupeId())); ?>
</div>

<div class="files-container inner <?php echo empty($folderss) ? "margin" : ""; ?>">
	<div class="container">
		<div class="row">
			<div class="files span9">
				<div id="files" class="no-padding">
					<?php if(empty($folderss) && empty($files)) : ?>
						<div class="no-result">
							<?php echo __("No file found."); ?>
						</div>
					<?php else: ?>
						<?php foreach ($files as $file) : ?>
							<?php include_partial("file/grid", Array("file" => $file)); ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
			<?php if(empty($folderss)) : ?>
				<div class="span3" id="sidebar_block">
					<div class="slide-sidebar show-bar">
						<a href="javascript: void(0);" id="show-sidebar"><i class="icon-double-angle-left"></i></a>
					</div>
					<div class="sidebar-container">
						<?php include_component("folder", "sidebar", Array("folder" => $folder, "tagsSelected" => TagPeer::retrieveByPks($selected_tag_ids), "addedByMe" => $added_by_me_input, "creationMin" => $creationDateRange["min"], "creationMax" => $creationDateRange["max"], "shootingMin" => $shootingDateRange["min"], "shootingMax" => $shootingDateRange["max"], "sizeMin" => $sizeRange["min"], "sizeMax" => $sizeRange["max"])); ?>
					</div>
					<div class="slide-sidebar hide-bar">
						<a href="javascript: void(0);" id="hide-sidebar"><i class="icon-double-angle-right"></i></a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<div class="context-files">
</div>
<form name="files_form" id="files_form">
	<input type="hidden" name="folder_id" id="folder_id" value="<?php echo $folder->getId(); ?>" />
</form>

<?php if($paginateFiles) : ?>
	<div id="nav">
		<a href="<?php echo $url; ?>"><?php echo ($page + 1); ?></a>
	</div>
<?php endif; ?>
<div id="div_share" class="dialog"></div>
<script>
	var globalWidth = 0;

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

			<?php if(empty($files)) : ?>
				if(jQuery(".slimScrollDiv").height() > jQuery("#folders").height())
					jQuery("#folders").css("height", jQuery(".slimScrollDiv").height() + "px");
			<?php endif; ?>
		});
	}

	function initSelectedActions()
	{
		var offsetTop = jQuery(".actions-container").offset().top;

		jQuery(window).scroll(function() {
			if(jQuery(".selected-actions").is(":visible"))
			{
				if(jQuery(window).scrollTop() >= offsetTop && !jQuery(".actions-container").hasClass("navbar-fixed-top"))
				{
					var cssTop = 0;

					if(jQuery(".top-under").is(":visible"))
						cssTop += jQuery(".top-under").attr("data-top");

					if(!jQuery(".actions-container").attr("data-hide"))
					{
						if(jQuery("#fake_actions_container").length == 0)
						{
							jQuery("<div id='fake_actions_container' style='height: " + jQuery(".actions-container").outerHeight(true) + "px;'></div>").insertBefore(".actions-container");
						}
					}

					jQuery(".actions-container").addClass("navbar-fixed-top").css("top", cssTop + "px");
				}

				if(jQuery(window).scrollTop() < (offsetTop - parseInt(jQuery(".navbar-inverse").attr("data-height"), 10)))
				{
					jQuery(".actions-container").css("top", "auto").removeClass("navbar-fixed-top");
					jQuery("#fake_actions_container").remove();
				}
			}
		});

		if(jQuery(".selected-actions").is(":visible"))
		{
			if(jQuery(window).scrollTop() >= offsetTop)
			{
				if(!jQuery(".actions-container").attr("data-hide"))
				{
					if(jQuery("#fake_actions_container").length == 0)
					{
						jQuery("<div id='fake_actions_container' style='height: " + jQuery(".actions-container").outerHeight(true) + "px;'></div>").insertBefore(".actions-container");
					}
				}

				var cssTop = 0;

				if(jQuery(".navbar-inverse").css("position") == "fixed")
				{
					cssTop += jQuery(".navbar-inverse").attr("data-height");
				}
				else
				{
					var docViewTop = jQuery(window).scrollTop();
					var docViewBottom = docViewTop + jQuery(window).height();

					var elemTop = jQuery(".navbar-inverse").offset().top;
					var elemBottom = elemTop + jQuery(".navbar-inverse").height();

					if((elemBottom <= docViewBottom) && (elemTop >= docViewTop))
						cssTop += (jQuery(window).scrollTop() - jQuery(".navbar-inverse").offset().top);
					else
						cssTop += (jQuery(".navbar-inverse").attr("data-height") - (docViewTop - elemTop));
				}

				if(jQuery(".top-under").is(":visible"))
					cssTop += jQuery(".top-under").attr("data-top");

				jQuery(".actions-container").addClass("navbar-fixed-top").stop().animate({
					"top": cssTop + "px"
				}, 400);
			}

			if(jQuery(window).scrollTop() < offsetTop)
				jQuery(".actions-container").css("top", "auto").removeClass("navbar-fixed-top");
		}
	}

	function showSelectedActions()
	{
		if(!jQuery(".selected-actions").is(":visible"))
		{
			jQuery(".default-actions").addClass("hidden");
			jQuery(".selected-actions").removeClass("hide");

			jQuery(".actions-container .navbar-inner").addClass("selected");
			jQuery(".actions-container").addClass("selected");

			initSelectedActions();
		}
	}

	function proceedHideSelectedActions()
	{
		jQuery(".default-actions").removeClass("hidden");
		jQuery(".selected-actions").addClass("hide");

		jQuery("#fake_actions_container").remove();
		jQuery(".actions-container .navbar-inner").removeClass("selected");
		jQuery(".actions-container").removeClass("selected");

		jQuery(".actions-container").removeClass("navbar-fixed-top");
		jQuery(".actions-container").removeAttr("style");
	}

	function hideSelectedActions()
	{
		if(jQuery(".selected-actions").is(":visible") && jQuery(".item-file.clicked").length == 0)
		{
			if(jQuery(".actions-container").hasClass("navbar-fixed-top"))
			{
				var cssTop = jQuery(".actions-container").css("top").replace(/[^-\d\.]/g, '');
				var height = jQuery(".actions-container").outerHeight();

				jQuery(".actions-container").stop().animate({
					"top": (parseInt(cssTop, 10) - parseInt(height, 10) - 1) + "px"
				}, 400, function() {
					proceedHideSelectedActions();
				});
			}
			else
				proceedHideSelectedActions();
		}
	}

	function addSelectedFile(object)
	{
		jQuery("#files_form").append("<input type='checkbox' name='file_ids[]' id='selected_file_" + object.attr("data-value") + "' value='" + object.attr("data-value") + "' checked class='hide' />");
	}

	function removeSelectedFile(object)
	{
		jQuery("#selected_file_" + object.attr("data-value")).remove();
	}

	function bindFiles()
	{

			jQuery(".item-file[data-id^=file_]").draggable({
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

			jQuery(".item-file[data-id^=file_]").hover(
				function() {
					var item = jQuery(this);

					if(!item.hasClass("clicked"))
					{
						item.find(".overlay").css({
							"width": item.find(".thumbnail").width() + "px",
							"height": item.find(".thumbnail").height() + "px"
						}).removeClass("hide");

						item.addClass("hovered");
					}
				},
				function() {
					var item = jQuery(this);

					if(item.hasClass("hovered"))
					{
						item.removeClass("hovered");
						item.find(".overlay").addClass("hide");
					}
				}
			);

			jQuery(".item-file[data-id^=file_] .overlay").bind("click", function() {
				var item = jQuery(this).closest(".item-file");

				window.location.href = item.attr("data-href");
			});

			jQuery(".item-file[data-id^=file_] .overlay .actions").bind("click", function(e) {
				if(jQuery(this).hasClass("favorites"))
					addFavoriteFile(jQuery(this));

				if(jQuery(this).hasClass("unfavorites"))
					deleteFavoriteFile(jQuery(this));

				if(jQuery(this).hasClass("share"))
					shareFile(jQuery(this));

				e.stopPropagation();
			});

			jQuery(".item-file[data-id^=file_] .overlay .actions.selected-file").bind("click", function() {
				var item = jQuery(this).closest(".item-file");
				var count = parseInt(jQuery("#countSelection").html(), 10);

				if(jQuery(this).find("i").hasClass("icon-check"))
				{
					jQuery(this).find("i").addClass("icon-check-empty").removeClass("icon-check");

					if(item.hasClass("clicked"))
					{
						jQuery("#countSelection").html(count - 1);

						item.removeClass("clicked");
						item.find(".overlay").addClass("hide");
						hideSelectedActions();
						removeSelectedFile(item);
					}
				}
				else
				{
					jQuery(this).find("i").addClass("icon-check").removeClass("icon-check-empty");

					if(item.hasClass("hovered"))
					{
						jQuery("#countSelection").html(count + 1);

						item.find(".overlay").css({
							"width": item.find(".thumbnail").width() + "px",
							"height": item.find(".thumbnail").height() + "px"
						}).removeClass("hide");

						item.addClass("clicked").removeClass("hovered");

						addSelectedFile(item);
						showSelectedActions();
					}
				}
			});


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

	function unbindFiles()
	{
		jQuery(".item-file[data-id^=file_]").each(function() {
			if(jQuery(this).data("draggable")) {
				jQuery(this).draggable("destroy");
			}
		});

		jQuery(".item-file[data-id^=file_] a:not(.actions)").unbind("click");
		jQuery(".item-file[data-id^=file_] .overlay .actions").unbind("click");
		jQuery(".item-file[data-id^=file_] .overlay").unbind("click");
		jQuery(".item-file[data-id^=file_]").unbind("mouseenter");
		jQuery(".item-file[data-id^=file_]").unbind("mouseleave");
		jQuery('a[rel*=facebox]').unbind("click");
		jQuery('a[rel*=faceframe]').unbind("click");
		/*jQuery(".item-file[data-id^=file_]").droppable("destroy");*/
	}

	function initMasonryFiles()
	{
		jQuery("#files").myWall({
			"maxHeight": 350,
			"itemSelector": ".item-file",
			"notFoundPath": "<?php echo "/".sfConfig::get("app_path_images_dir_name")."/no-access-file-200x200.png"; ?>",
			"gutterWidth": 5,
			"showLastLine": <?php echo $paginateFiles ? "false" : "true"; ?>
		});

		<?php if($paginateFiles) : ?>
			jQuery("#files").infinitescroll({
				loading: {
					finishedMsg: "<?php echo __("All files are loaded."); ?>",
					img: "<?php echo image_path("icons/loader/big-gray.gif"); ?>",
					msgText : '<i class="icon-spinner icon-spin"></i> <?php echo __("Loading next files ..."); ?>'
				},
				navSelector  : "#nav",
				nextSelector : "#nav a",
				itemSelector : ".item-file",
				errorCallback: function() {
					unbindFiles();

					jQuery("#files").myWall("addItems", []);

					bindFiles();
				}
			},
			function(newElements) {
				unbindFiles();

				jQuery("#files").myWall("addItems", newElements);

				bindFiles();
			});
		<?php endif; ?>

		<?php if(empty($folderss)) : ?>
			if(jQuery(".slimScrollDiv").height() > jQuery("#files").height())
				jQuery("#files").css("height", jQuery(".slimScrollDiv").height() + "px");
		<?php endif; ?>
	}

	function addFavoriteFile(object)
	{
		var id = object.closest(".item-file").attr("data-value");

		jQuery.post(
			"<?php echo url_for("favorite/add"); ?>",
			{ type: "<?php echo FavoritesPeer::__TYPE_FILE; ?>", id: id },
			function(data) {
				if(data.errorCode == 0)
				{
					var icon = object.find(".icon-star-empty");
					icon.addClass("icon-star").removeClass("icon-star-empty");

					object.addClass("unfavorites").removeClass("favorites");
				}
			},
			"json"
		);
	}

	function deleteFavoriteFile(object)
	{
		var id = object.closest(".item-file").attr("data-value");

		jQuery.post(
			"<?php echo url_for("favorite/delete"); ?>",
			{ type: "<?php echo FavoritesPeer::__TYPE_FILE; ?>", id: id },
			function(data) {
				if(data.errorCode == 0)
				{
					var icon = object.find(".icon-star");
					icon.addClass("icon-star-empty").removeClass("icon-star");

					object.addClass("favorites").removeClass("unfavorites");
				}
			},
			"json"
		);
	}

	function shareFile(object)
	{
		var parent = object.closest(".item-file");

		jQuery("#div_share").dialog({
			title: "<span class='first-title'><?php echo __("File sharing"); ?></span>",
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
					"<?php echo url_for("file/share"); ?>",
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

		if(jQuery(".folders").length > 0)
		{
			var margin = jQuery(".folders").css("margin-left").replace(/[^-\d\.]/g, '');
			var offset = (jQuery(".folders").width() + jQuery("#folders").offset().left + parseInt(margin, 10));
		}
		else
		{
			var margin = jQuery(".files").css("margin-left").replace(/[^-\d\.]/g, '');
			var offset = (jQuery(".files").width() + jQuery("#files").offset().left + parseInt(margin, 10));
		}

		jQuery(".slimScrollDiv").addClass("absolute").css({ "left": offset + "px", "width": slimScrollWidth + "px"});
		
		var scroll = jQuery(".slimScrollDiv");

		if (scroll.length) {
			setAffix(jQuery(".slimScrollDiv"));
		}
	}

	jQuery(document).ready(function() {
		var pageFolders = 2;
		var onPageFolders = <?php echo $itemsToShow != "all" ? $itemsToShow : 0; ?>;;
		tooltip();

		bindFolders();

		initMasonryFolders();

		bindFiles();

		initMasonryFiles();

		initSidebar();


		jQuery(".list-actions .map").bind("click", function() {
			jQuery.facebox({ iframe: '<?php echo url_for("map/file"); ?>?folder_id=<?php echo $folder->getId(); ?>' });
		});

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
					jQuery("#add_folder_group_id").bind("change", function() {
						jQuery("#add_folder_folder_id").replaceWith("<div style='clear: left; height: 30px;' class='left' id='add_folder_folder_id'><img src='<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>' /></div>");

						jQuery.post(
							"<?php echo url_for("folder/getSubfolders"); ?>",
							{ group_id: jQuery("#add_folder_group_id").val() },
							function(data) {
								jQuery("#add_folder_folder_id").replaceWith(data);
							}
						);
					});

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

				if(jQuery("#folders").length > 0)
				{
					jQuery(".folders").addClass("span12").removeClass("span9");
					jQuery("#folders").masonry("reload");
				}

				jQuery(".files").addClass("span12").removeClass("span9");
				jQuery("#files").css("width", jQuery(".files").width() + "px");
				jQuery("#files").myWall("reload");
			});
		});

		jQuery("#show-sidebar").bind("click", function() {
			jQuery(".slide-sidebar").hide();

			if(jQuery("#folders").length > 0)
			{
				jQuery(".folders").addClass("span9").removeClass("span12");
				jQuery("#folders").masonry("reload");
			}

			jQuery(".files").addClass("span9").removeClass("span12");
			jQuery("#files").css("width", jQuery(".files").width() + "px");
			jQuery("#files").myWall("reload");

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

		jQuery("#share_<?php echo $folder->getId(); ?>:not(.disabled)").bind("click", function() {
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
						{ id: <?php echo $folder->getId(); ?> },
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
				{ type: "<?php echo FavoritesPeer::__TYPE_FOLDER; ?>", id: "<?php echo $folder->getId(); ?>" },
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
				{ type: "<?php echo FavoritesPeer::__TYPE_FOLDER; ?>", id: "<?php echo $folder->getId(); ?>" },
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

		jQuery(".selected-basket").bind("click", function() {
			var is_load = false;

			jQuery('input[name="file_ids[]"]').each(function (index, object) {
				if(jQuery(object).is(":checked") == true)
					is_load = true;
			});

			if(is_load == false)
				alert("<?php echo __("Please select 1 file at least.")?>");
			else
				addFilesToBasket();
		});

		jQuery(".selected-favorites").bind("click", function() {
			var data = "type=files&" + jQuery('#files_form').serialize();

			jQuery.post(
				"<?php echo url_for("favorite/add"); ?>",
				data,
				function(data) {
					if(data.errorCode <= 0)
					{
						jQuery('input[name="file_ids[]"]').each(function (index, object) {
							if(jQuery(object).is(":checked") == true)
							{
								var id = jQuery(this).val();
								var item = jQuery(".item-file[data-value=" + id + "] .overlay .actions.favorites");

								var icon = item.find(".icon-star-empty");
								icon.addClass("icon-star").removeClass("icon-star-empty");

								item.addClass("unfavorites").removeClass("favorites");
							}
						});
					}
				},
				"json"
			)
		});

		jQuery(".selected-info").bind("click", function() {
			var is_load = false;

			jQuery('input[name="file_ids[]"]').each(function (index, object) {
				if(jQuery(object).is(":checked") == true)
					is_load = true;
			});

			if(is_load == false)
				alert("<?php echo __("Please select 1 file at least.")?>");
			else
				jQuery.facebox({ iframe: '<?php echo url_for("file/editSelected"); ?>?first_call1=1&' + jQuery('#files_form').serialize() });
		});

		jQuery(".selected-move").bind("click", function() {
			var is_load = false;

			jQuery('input[name="file_ids[]"]').each(function (index, object) {
				if(jQuery(object).is(":checked") == true)
					is_load = true;
			});

			if(is_load == false)
				alert("<?php echo __("Please select 1 file at least.")?>");
			else
				jQuery.facebox({ iframe: '<?php echo url_for("file/moveSelected"); ?>?' + jQuery('#files_form').serialize() });
		});

		jQuery(".selected-copy").bind("click", function() {
			var is_load = false;

			jQuery('input[name="file_ids[]"]').each(function (index, object) {
				if(jQuery(object).is(":checked") == true)
					is_load = true;
			});

			if(is_load == false)
				alert("<?php echo __("Please select 1 file at least.")?>");
			else
				jQuery.facebox({ iframe: '<?php echo url_for("file/copySelected"); ?>?' + jQuery('#files_form').serialize() });
		});

		jQuery(".selected-map").bind("click", function() {
			var is_load = false;

			jQuery('input[name="file_ids[]"]').each(function (index, object) {
				if(jQuery(object).is(":checked") == true)
					is_load = true;
			});

			if(is_load == false)
				alert("<?php echo __("Please select 1 file at least.")?>");
			else
				jQuery.facebox({ iframe: '<?php echo url_for("map/file"); ?>?selected=true&' + jQuery('#files_form').serialize() });
		});

		jQuery(".selected-remove").bind("click", function() {
			var is_load = false;

			jQuery('input[name="file_ids[]"]').each(function (index, object) {
				if(jQuery(object).is(":checked") == true)
					is_load = true;
			});
		  
			if(is_load == false)
				alert("<?php echo __("Please select 1 file at least.")?>");
			else
				jQuery.facebox({ iframe: '<?php echo url_for("file/deleteSelected"); ?>?' + jQuery('#files_form').serialize() });
		});

		jQuery(".show-more-folders a").bind("click", function() {
			if(!jQuery(".show-more-folders a").data("clicked"))
			{
				jQuery(".show-more-folders a").data("clicked", true);
				jQuery(".show-more-folders a").html("<i class='icon-spinner icon-spin'></i> <?php echo __("Loading folders..."); ?>");

				jQuery.post(
					"<?php echo url_for("folder/loadFolders"); ?>",
					{ id: "<?php echo $folder->getGroupeId(); ?>", folder_id: "<?php echo $folder->getId(); ?>", page: pageFolders, onPage: onPageFolders, sort: jQuery("#sort").val() },
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

		jQuery("#per_page").bind("change", function() {
			if(!jQuery(".show-more-folders a").data("clicked"))
			{
				onPageFolders = jQuery(this).val();

				jQuery(".show-more-folders").hide();

				jQuery("#folders").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading folders..."); ?></div>");
				jQuery("#folders").addClass("overflow-hidden").css("height", "80px");

				jQuery.post(
					"<?php echo url_for("folder/loadFolders"); ?>",
					{ id: "<?php echo $folder->getGroupeId(); ?>", folder_id: "<?php echo $folder->getId(); ?>", page: 1, onPage: onPageFolders, sort: jQuery("#sort").val() },
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

				jQuery("#files").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading files..."); ?></div>");
				jQuery("#files").addClass("overflow-hidden").css("height", "80px");

				jQuery("#files").infinitescroll("pause");

				jQuery.post(
					"<?php echo url_for("folder/loadFolders"); ?>",
					{ id: "<?php echo $folder->getGroupeId(); ?>", folder_id: "<?php echo $folder->getId(); ?>", page: 1, onPage: onPageFolders, sort: jQuery("#sort").val() },
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

				jQuery.post(
					"<?php echo url_for("file/loadFiles"); ?>",
					{ id: "<?php echo $folder->getId(); ?>", page: 1, sort: jQuery("#sort").val() },
					function(data) {
						jQuery("#files").removeClass("overflow-hidden");

						unbindFiles();

						var files = "<div>" + data.files + "</div>";
						var childs = jQuery(files).children();

						jQuery("#files").html(childs);
						jQuery("#files").myWall("reload");

						bindFiles();

						bindFiles();

						if(data.index != 0) {
							jQuery("#files").infinitescroll("resume");
						}
					},
					"json"
				);
			}
		});
	});
</script>