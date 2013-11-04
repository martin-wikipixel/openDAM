<?php
	$files = $files->getRawValue();
?>

<div class="container files-container border margin">
	<div class="row">
		<div class="files span12">
			<div class="container">
				<div class="border">
					<div id="files" class="home-display">
						<?php foreach ($files as $file) : ?>
							<?php include_partial("file/grid", Array("file" => $file)); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<div class="show-more-files"<?php echo $count < $itemsToShow ? " style='display: none;'" : ""; ?>>
				<a href="javascript: void(0);" class="btn-header"><?php echo __("Show more"); ?></a>
			</div>
		</div>
	</div>
</div>

<div id="div_share"></div>

<form name="files_form" id="files_form"></form>
<script>
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

				if(jQuery(this).hasClass("share")) {
					shareFile(jQuery(this));
				}

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
		jQuery(".item-file[data-id^=file_]").draggable("destroy");
		jQuery(".item-file[data-id^=file_] a:not(.actions)").unbind("click");
		jQuery(".item-file[data-id^=file_] .overlay .actions").unbind("click");
		jQuery(".item-file[data-id^=file_] .overlay").unbind("click");
		jQuery(".item-file[data-id^=file_]").unbind("mouseenter");
		jQuery(".item-file[data-id^=file_]").unbind("mouseleave");
		jQuery(".actions.favorites").unbind("click");
		jQuery(".actions.unfavorites").unbind("click");
		jQuery('a[rel*=facebox]').unbind("click");
		jQuery('a[rel*=faceframe]').unbind("click");
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

			if(jQuery(".actions-container").hasClass("hide"))
			{
				jQuery(".actions-container").attr("data-hide", 1);
				jQuery(".actions-container").removeClass("hide");
			}

			jQuery(".actions-container .navbar-inner").addClass("selected");
			jQuery(".actions-container").addClass("selected");

			initSelectedActions();
		}
	}

	function proceedHideSelectedActions()
	{
		jQuery(".default-actions").removeClass("hidden");
		jQuery(".selected-actions").addClass("hide");

		jQuery(".actions-container .navbar-inner").removeClass("selected");
		jQuery(".actions-container").removeClass("selected");

		jQuery("#fake_actions_container").remove();
		jQuery(".actions-container").removeClass("navbar-fixed-top");
		jQuery(".actions-container").removeAttr("style");

		if(jQuery(".actions-container").attr("data-hide") == 1)
		{
			jQuery(".actions-container").addClass("hide");
		}
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

	function initMasonryFiles()
	{
		jQuery("#files").myWall({
			"maxHeight": 350,
			"itemSelector": ".item-file",
			"notFoundPath": "<?php echo "/".sfConfig::get("app_path_images_dir_name")."/no-access-file-200x200.png"; ?>",
			"gutterWidth": 5
		});
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

	jQuery(document).ready(function() {
		var pageFile = 2;
		var onPageFile = "<?php echo $itemsToShow; ?>";

		bindFiles();

		initMasonryFiles();

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

		jQuery(".show-more-files a").bind("click", function() {
			if(!jQuery(".show-more-files a").data("clicked"))
			{
				jQuery(".show-more-files a").data("clicked", true);
				jQuery(".show-more-files a").html("<i class='icon-spinner icon-spin'></i> <?php echo __("Loading files..."); ?>");

				jQuery.post(
					"<?php echo url_for("file/loadHomeFiles"); ?>",
					{ page: pageFile, onPage: onPageFile, sort: jQuery("#sort").val() },
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

		jQuery("#per_page").bind("change", function() {
			if(!jQuery(".show-more-files a").data("clicked"))
			{
				onPageFile = jQuery(this).val();

				jQuery(".show-more-files").hide();

				jQuery("#files").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading files..."); ?></div>");
				jQuery("#files").addClass("overflow-hidden").css("height", "80px");

				jQuery.post(
					"<?php echo url_for("file/loadHomeFiles"); ?>",
					{ page: 1, onPage: onPageFile, sort: jQuery("#sort").val() },
					function(data) {
						pageFile = 2;

						jQuery("#files").removeClass("overflow-hidden");

						unbindFiles();

						var files = "<div>" + data.files + "</div>";
						var childs = jQuery(files).children();

						jQuery("#files").html(childs);
						jQuery("#files").myWall("reload");
						// jQuery("#files").myWall("addItems", childs);

						bindFiles();

						if(data.index == 0)
							jQuery(".show-more-files").fadeOut(400);
						else
						{
							jQuery(".show-more-files a").html("<?php echo __("Show more"); ?>");
							jQuery(".show-more-files a").data("clicked", false);
							jQuery(".show-more-files").fadeIn(400);
						}
					},
					"json"
				);
			}
		});

		jQuery("#sort").bind("change", function() {
			if(!jQuery(".show-more-files a").data("clicked"))
			{
				jQuery(".show-more-files").hide();

				jQuery("#files").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading files..."); ?></div>");
				jQuery("#files").addClass("overflow-hidden").css("height", "80px");

				jQuery.post(
					"<?php echo url_for("file/loadHomeFiles"); ?>",
					{ page: 1, onPage: onPageFile, sort: jQuery("#sort").val() },
					function(data) {
						pageFile = 2;

						jQuery("#files").removeClass("overflow-hidden");

						unbindFiles();

						var files = "<div>" + data.files + "</div>";
						var childs = jQuery(files).children();

						jQuery("#files").html(childs);
						jQuery("#files").myWall("reload");
						// jQuery("#files").myWall("addItems", childs);

						bindFiles();

						if(data.index == 0)
							jQuery(".show-more-files").fadeOut(400);
						else
						{
							jQuery(".show-more-files a").html("<?php echo __("Show more"); ?>");
							jQuery(".show-more-files a").data("clicked", false);
							jQuery(".show-more-files").fadeIn(400);
						}
					},
					"json"
				);
			}
		});
	});
</script>