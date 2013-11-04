<?php
	$files = $files->getRawValue();
	$groupsAndFolders = $groupsAndFolders->getRawValue();
?>

<?php if(!empty($groupsAndFolders)) : ?>
	<div class="container groups-folders-container">
		<div class="row">
			<div class="span12">
				<h3><?php echo __("Groups and folders")?></h3>
			</div>
		</div>
		<div class="row">
			<div class="groups-folders span12">
				<div class="container">
					<div id="groups-folders">
						<?php foreach ($groupsAndFolders as $favorite) : ?>
							<?php
								switch($favorite->getObjectType())
								{
									case FavoritesPeer::__TYPE_GROUP:
										include_partial("favorite/group", Array("group" => GroupePeer::retrieveByPkNoCustomer($favorite->getObjectId())));
									break;

									case FavoritesPeer::__TYPE_FOLDER:
										include_partial("favorite/folder", Array("folder" => FolderPeer::retrieveByPK($favorite->getObjectId())));
									break;
								}
							?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php if($countGroupsAndFolders > 8) : ?>
					<div class="show-more-groups-folders">
						<a href="javascript: void(0);" class="btn-header"><?php echo __("Show more"); ?></a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="context-groups-folders">
	</div>
<?php endif; ?>

<?php if(!empty($files)) : ?>
	<div class="container files-container border">
		<div class="row">
			<div class="span12">
				<div class="border-left border-right">
					<h3><?php echo __("Files"); ?></h3>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="files span12">
				<div class="container">
					<div class="border">
						<div id="files">
							<?php foreach ($files as $favorite) : ?>
								<?php $file = FilePeer::retrieveByPK($favorite->getObjectId()); ?>
								<?php include_partial("file/grid", Array("file" => $file)); ?>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php if($countFiles > 8) : ?>
					<div class="show-more-files">
						<a href="javascript: void(0);" class="btn-header"><?php echo __("Show more"); ?></a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="context-files">
	</div>
<?php endif; ?>
<div id="div_share" class="dialog"></div>
<form name="files_form" id="files_form"></form>
<script>
	function bindGroupsAndFolders()
	{
			jQuery(".item-group[data-id^=group_].open").draggable({
				helper: "clone",
				distance: 30,
				start: function(event, ui) {
					jQuery(ui.helper).css("z-index", 8500);
					showSelectionBar();
				},
				stop: function(event, ui) {
					hideSelectionBar();
				}
			});

			jQuery(".item-folder[data-id^=folder_].open").draggable({
				helper: "clone",
				distance: 30,
				start: function(event, ui) {
					jQuery(ui.helper).css("z-index", 8500);
					showSelectionBar();
				},
				stop: function(event, ui) {
					hideSelectionBar();
				}
			});

		jQuery(".item-group [data-toogle=modal-iframe]").IframeModal();
	}

	function unbindGroupsAndFolders()
	{
		jQuery(".item-group[data-id^=group_].open").draggable("destroy");
		jQuery(".item-folder[data-id^=folder_].open").draggable("destroy");
	}

	function bindFiles()
	{
			jQuery(".item-file[data-id^=file_]").draggable({
				helper: "clone",
				distance: 30,
				start: function(event, ui) {
					jQuery(ui.helper).css("z-index", 8500);
					showSelectionBar();
				},
				stop: function(event, ui) {
					hideSelectionBar();
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

						showSelectedActions();
						addSelectedFile(item);
					}
				}
			});
	}

	function unbindFiles()
	{
		jQuery(".item-file[data-id^=file_]").draggable("destroy");
		jQuery(".item-file[data-id^=file_] a:not(.actions)").unbind("click");
		jQuery(".item-file[data-id^=file_] .overlay .actions").unbind("click");
		jQuery(".item-file[data-id^=file_] .overlay").unbind("click");
		jQuery(".item-file[data-id^=file_]").unbind("mouseenter");
		jQuery(".item-file[data-id^=file_]").unbind("mouseleave");
		/*jQuery(".item-file[data-id^=file_]").droppable("destroy");*/
	}

	function addSelectedFile(object)
	{
		jQuery("#files_form").append("<input type='checkbox' name='file_ids[]' id='selected_file_" + object.attr("data-value") + "' value='" + object.attr("data-value") + "' checked class='hide' />");
	}

	function removeSelectedFile(object)
	{
		jQuery("#selected_file_" + object.attr("data-value")).remove();
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
							jQuery("<div id='fake_actions_container' style='height: " + jQuery(".actions-container").outerHeight(true) + "px;'>test</div>").insertBefore(".actions-container");
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
						jQuery("<div id='fake_actions_container' style='height: " + jQuery(".actions-container").outerHeight(true) + "px;'>test</div>").insertBefore(".actions-container");
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

				console.log(1);
				jQuery(".actions-container").addClass("navbar-fixed-top").stop().animate({
					"top": cssTop + "px"
				}, 900);
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

			if(jQuery(".actions-container").hasClass("hide"))
			{
				jQuery(".actions-container").attr("data-hide", 1);
				jQuery(".actions-container").removeClass("hide");
			}

			jQuery(".actions-container .navbar-inner").addClass("selected");

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

	function displayGroupsAndFolders()
	{
		jQuery(".item-favorite").each(function() {
			var width = jQuery(this).width();
			var infoHeight = jQuery(this).find(".info-group-folder").get(0).offsetHeight + 1;
			var availableHeight = width - infoHeight;
			var originalWidth = jQuery(this).find("img").attr("data-width");
			var originalHeight = jQuery(this).find("img").attr("data-height");

			if(originalHeight <= availableHeight)
			{
				jQuery(this).find("img").css("padding-top", ((availableHeight - 1 - originalHeight) / 2) + "px");
				jQuery(this).find("img").css("padding-bottom", ((availableHeight - 1 - originalHeight) / 2) + "px");
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

				jQuery(this).find("img").closest(".thumbnail").css("height", (availableHeight - 1) + "px");
				jQuery(this).find("img").addClass("cropp").css(css);
			}
		});
	}

	function initMasonryGroupsAndFolders()
	{
		var gutterWidthGroupsFolders = 10;
		var minWidthGroupsFolders = 270;

		jQuery("#groups-folders").imagesLoaded(function() {
			jQuery("#groups-folders").masonry({
				itemSelector : '.item-favorite',
				gutterWidth: gutterWidthGroupsFolders,
				isAnimated: true,
				columnWidth: function(containerWidth) {
					var itemNbr = (containerWidth / minWidthGroupsFolders | 0);

					var itemWidth = (((containerWidth - (itemNbr - 1) * gutterWidthGroupsFolders) / itemNbr) | 0);

					if (containerWidth < minWidthGroupsFolders)
						itemWidth = containerWidth;

					jQuery(".item-favorite").width(itemWidth);

					displayGroupsAndFolders();

					return itemWidth;
				}
			});
		});
	}

	function initMasonryFiles()
	{
		jQuery("#files").myWall({
			"maxHeight": 250,
			"itemSelector": ".item-file",
			"notFoundPath": "<?php echo "/".sfConfig::get("app_path_images_dir_name")."/no-access-file-200x200.png"; ?>",
			"gutterWidth": 5,
			"showLastLine": true
		});
	}

	jQuery(document).ready(function() {
		var pageGroupsFolders = 2;
		var onPageGroupsFolder = 8;

		var pageFile = 2;
		var onPageFile = 8;

		initMasonryGroupsAndFolders();

		bindGroupsAndFolders();

		initMasonryFiles();

		bindFiles();

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
			var data = "type=files2&" + jQuery('#files_form').serialize();

			jQuery.post(
				"<?php echo url_for("favorite/delete"); ?>",
				data,
				function(data) {
					if(data.errorCode <= 0)
					{
						jQuery('input[name="file_ids[]"]').each(function (index, object) {
							if(jQuery(object).is(":checked") == true)
							{
								var id = jQuery(this).val();
								var item = jQuery(".item-file[data-value=" + id + "] .overlay .actions.unfavorites");

								var icon = item.find(".icon-star");
								icon.addClass("icon-star-empty").removeClass("icon-star");

								item.addClass("favorites").removeClass("unfavorites");
							}
						});
					}
				},
				"json"
			)
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
				jQuery.facebox({ iframe: '<?php echo url_for("map/filesSelected"); ?>?selected=true&' + jQuery('#files_form').serialize() });
		});

		jQuery(".item-group .actions.share").live("click", function() {
			var parent = jQuery(this).closest(".item-group");

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

		jQuery(".item-folder .actions.share").live("click", function() {
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

		<?php if($countGroupsAndFolders > 8) : ?>
			jQuery(".show-more-groups-folders a").bind("click", function() {
				if(!jQuery(".show-more-groups-folders a").data("clicked"))
				{
					jQuery(".show-more-groups-folders a").data("clicked", true);
					jQuery(".show-more-groups-folders a").html("<i class='icon-spinner icon-spin'></i> <?php echo __("Loading groups..."); ?>");

					jQuery.post(
						"<?php echo url_for("favorite/loadFavoriteGroupsFolders"); ?>",
						{ page: pageGroupsFolders, onPage: onPageGroupsFolder },
						function(data) {
							unbindGroupsAndFolders();

							pageGroupsFolders++;

							var groupsFolders = "<div>" + data.groupsFolders + "</div>";
							var childs = jQuery(groupsFolders).children();
							jQuery("#groups-folders").append(childs);

							bindGroupsAndFolders();

							jQuery("#groups-folders").imagesLoaded(function() {
								jQuery("#groups-folders").masonry("reload");
							});

							if(data.index == 0)
								jQuery(".show-more-groups-folders").fadeOut(400);
							else
							{
								jQuery(".show-more-groups-folders a").html("<?php echo __("Show more"); ?>");
								jQuery(".show-more-groups-folders a").data("clicked", false);
							}
						},
						"json"
					);
				}
			});
		<?php endif; ?>

		<?php if($countFiles > 8) : ?>
			jQuery(".show-more-files a").bind("click", function() {
				if(!jQuery(".show-more-files a").data("clicked"))
				{
					jQuery(".show-more-files a").data("clicked", true);
					jQuery(".show-more-files a").html("<i class='icon-spinner icon-spin'></i> <?php echo __("Loading files..."); ?>");

					jQuery.post(
						"<?php echo url_for("favorite/loadFavoriteFiles"); ?>",
						{ page: pageFile, onPage: onPageFile },
						function(data) {
							unbindFiles();

							pageFile++;

							var files = "<div>" + data.files + "</div>";
							var childs = jQuery(files).children();

							jQuery("#files").append(childs);
							jQuery("#files").myWall("addItems", childs);

							bindFiles();

							if(data.index == 0)
								jQuery(".show-more-files").fadeOut(400);
							else
							{
								jQuery(".show-more-files a").html("<?php echo __("Show more"); ?>");
								jQuery(".show-more-files a").data("clicked", false);
							}
						},
						"json"
					);
				}
			});
		<?php endif; ?>
	});
</script>