<?php
	$groups = $groups->getRawValue();
	$folders = $folders->getRawValue();
	$filesObj = $files->getRawValue();
	$files = $filesObj["files"];
	$extended = $extended->getRawValue();
	$types = $types->getRawValue();
	$orientations = $orientations->getRawValue();
	$sizes = $sizes->getRawValue();
	$dates = $dates->getRawValue();
	$filesLicences = $licences->getRawValue();
	$filesUses = $uses->getRawValue();
	$filesDistributions = $distributions->getRawValue();

	$showFoldersGroups = false;

	if(!empty($groups) || !empty($folders))
		$showFoldersGroups = true;
?>

<div class="search-folders-container <?php echo !$showFoldersGroups ? "hide" : ""; ?>">
	<div class="container groups-folders-container">
		<div class="groups-folders">
			<div class="container">
				<div class="row">
					<?php if($showFoldersGroups) : ?>
						<?php include_component("search", "sidebar", Array("formatedKeywords" => $formatedKeywords, "infoGroups" => $countGroups, "infoFolders" => $countFolders, "infoFiles" => $extended, "types" => $types, "orientations" => $orientations, "sizes" => $sizes, "dates" => $dates, "filesLicences" => $filesLicences, "filesUses" => $filesUses, "filesDistributions" => $filesDistributions)) ; ?>
					<?php else: ?>
						<div class="span3"></div>
					<?php endif; ?>
					<div id="groups-folders" class="span9">
						<?php foreach ($groups as $group) : ?>
							<?php include_partial("search/group", Array("group" => $group)); ?>
						<?php endforeach; ?>

						<?php foreach ($folders as $folder) : ?>
							<?php include_partial("search/folder", Array("folder" => $folder)); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="search-files-container">
	<div class="container files-container <?php echo !$showFoldersGroups ? "margin" : ""; ?>">
		<div class="files">
			<div class="container">
				<div class="row">
					<?php if(!$showFoldersGroups) : ?>
						<?php include_component("search", "sidebar", Array("formatedKeywords" => $formatedKeywords, "infoGroups" => $countGroups, "infoFolders" => $countFolders, "infoFiles" => $extended, "types" => $types, "orientations" => $orientations, "sizes" => $sizes, "dates" => $dates, "filesLicences" => $filesLicences, "filesUses" => $filesUses, "filesDistributions" => $filesDistributions)) ; ?>
					<?php else: ?>
						<div class="span3"></div>
					<?php endif; ?>
					<div id="files" class="span9">
						<?php if(empty($groups) && empty($folders) && empty($files)) : ?>
							<div class="no-result">
								<?php echo __("No results for the selected type."); ?>
								<?php if(($filesObj["count"] + $countGroups + $countFolders) > 0) : ?>
									<div class="subtext">
										<a href="javascript: void(0);" id="show_all_result"><?php echo __("Click here to view all available results."); ?></a>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php foreach ($files as $file) : ?>
							<?php include_partial("search/file", Array("file" => $file)); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if($paginateFiles) : ?>
	<div id="nav">
		<a href="<?php echo $url; ?>"><?php echo ($page + 1); ?></a>
	</div>
<?php endif; ?>

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

	}

	function unbindFiles()
	{
		jQuery(".item-file[data-id^=file_]").each(function() {
			if(jQuery(this).data("draggable")) {
				jQuery(this).draggable("destroy");
			}
		});
	}

	function displayGroupsAndFolders()
	{
		jQuery(".item-search").each(function() {
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
				itemSelector : '.item-search',
				gutterWidth: gutterWidthGroupsFolders,
				isAnimated: true,
				columnWidth: function(containerWidth) {
					var itemNbr = (containerWidth / minWidthGroupsFolders | 0);

					var itemWidth = (((containerWidth - (itemNbr - 1) * gutterWidthGroupsFolders) / itemNbr) | 0);

					if (containerWidth < minWidthGroupsFolders)
						itemWidth = containerWidth;

					jQuery(".item-search").width(itemWidth);

					displayGroupsAndFolders();

					return itemWidth;
				}
			});
		});
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
			initPagination();
		<?php endif; ?>
	}

	function initPagination()
	{
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
	}

	function setAffix(object)
	{
		var height = object.height();
		var offsetTop = object.offset().top;
		var visibleHeight = 0;
		var margin = 15;

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
				object.addClass("fixed").css("top", visibleHeight + "px").removeClass("absolute");

				if(object.height() < (jQuery(window).height() - visibleHeight))
				{
					object.css("height", (jQuery(window).height() - visibleHeight - margin) + "px");
					object.find(">:first-child").css("height", (jQuery(window).height() - visibleHeight - margin) + "px");
					// object.find(".search-sidebar-container").css("height", (jQuery(window).height() - visibleHeight - margin) + "px");
				}
			}

			if(jQuery(window).scrollTop() < offsetTop)
			{
				object.addClass("absolute").css("top", "auto").removeClass("fixed");
				object.find(">:first-child").css("height", object.attr("data-height") + "px");
				// object.find(".search-sidebar-container").css("height", object.attr("data-height") + "px");
			}
		});
	}

	function addTopbarItem(type)
	{
		jQuery("#top_search_form").append("<input type='hidden' class='type-form' name='types[]' value='" + type + "' />");
	}

	function removeTopbarItem(type)
	{
		jQuery(".type-form[value=" + type + "]").remove();
	}

	function displaySort(type, sort)
	{
		if(type.length > 0)
		{
			var post = jQuery("#top_search_form").serialize() + "&sort=" + sort + "&filterType=" + type[0];

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
					switch(type[0])
					{
						case "albums":
						case "folders":
						{
							var items = "<div>" + data.html + "</div>";
							var childs = jQuery(items).children();
							jQuery("#groups-folders").prepend(childs);

							jQuery("#groups-folders").imagesLoaded(function() {
								jQuery(".search-folders-container").removeClass("hide");
								jQuery("#groups-folders").masonry("reload");
							});
						}
						break;

						case "files":
						{
							jQuery("#nav").html("");
							jQuery("#files").infinitescroll("unbind");

							if(data.showPagination == true)
							{
								jQuery("#files").myWall("setOptions", {"showLastLine": false });
								jQuery("#nav").html("<a href='" + data.urlPagination + "'>2</a>");
								jQuery("#files").infinitescroll("bind");
							}
							else
								jQuery("#files").myWall("setOptions", {"showLastLine": true });

							jQuery("#files").html(data.html);
							jQuery("#files").css("height", "0px");
							jQuery("#files").myWall("reload");
						}
						break;
					}

					displaySort(type.slice(1, type.length), sort);
				},
				"json"
			);
		}
	}

	function displayAll(type)
	{
		if(type.length > 0)
		{
			var post = jQuery("#top_search_form").serialize() + "&reset=true&filterType=" + type[0];

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
					switch(type[0])
					{
						case "albums":
						case "folders":
						{
							var items = "<div>" + data.html + "</div>";
							var childs = jQuery(items).children();
							jQuery("#groups-folders").prepend(childs);

							jQuery("#groups-folders").imagesLoaded(function() {
								jQuery(".search-folders-container").removeClass("hide");
								jQuery("#groups-folders").masonry("reload");
							});

							if(jQuery("#groups-folders").children().length == 0)
							{
								jQuery(".search-folders-container").addClass("hide");
								jQuery(".search-files-container .span3").append(jQuery(".slimScrollDiv"));
							}
						}
						break;

						case "files":
						{
							jQuery("#nav").html("");
							jQuery("#files").infinitescroll("unbind");

							if(data.showPagination == true)
							{
								jQuery("#files").myWall("setOptions", {"showLastLine": false });
								jQuery("#nav").html("<a href='" + data.urlPagination + "'>2</a>");
								jQuery("#files").infinitescroll("bind");
							}
							else
								jQuery("#files").myWall("setOptions", {"showLastLine": true });

							jQuery("#files").html(data.html);
							jQuery("#files").css("height", "0px");
							jQuery("#files").myWall("reload");

							if(jQuery(".search-folders-container .span3").children().length == 0)
							{
								jQuery(".search-folders-container .span3").append(jQuery(".slimScrollDiv"));
							}
						}
						break;
					}

					displayAll(type.slice(1, type.length));
				},
				"json"
			);
		}
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

		jQuery("#show_all_result").bind("click", function() {
			var querySort = [];
			var cpt = 0;

			querySort[cpt] = "files";
			cpt++;
			querySort[cpt] = "albums";
			cpt++;
			querySort[cpt] = "folders";

			jQuery("#filter-extention > ul > li > a").each(function() {
				if(parseInt(jQuery(this).find(".count").html(), 10) > 0)
				{
					addTopbarItem(jQuery(this).attr("data-type"));
					jQuery(this).addClass("selected");
					jQuery(this).parent().find(".select-extention a").addClass("selected");
				}
			});

			displayAll(querySort);
		});

		setAffix(jQuery(".slimScrollDiv"));

		jQuery(".search-display a").bind("click", function() {
			var sort = jQuery(this).attr("data-sort");
			var querySort = [];
			var cpt = 0;

			if(jQuery("#groups-folders .item-folder").length > 0)
			{
				querySort[cpt] = "folders";
				cpt++;
			}

			if(jQuery("#groups-folders .item-group").length > 0)
			{
				querySort[cpt] = "albums";
				cpt++;
			}

			if(jQuery("#files .item-file").length > 0)
			{
				querySort[cpt] = "files";
				cpt++;
			}

			if(cpt > 0)
				displaySort(querySort, sort);
		});
	});
</script>