<?php
	$groups = $groups->getRawValue();
?>

<div class="container groups-container margin">
	<div class="row">
		<div class="groups span12">
			<div class="container">
				<div id="groups">
					<?php foreach ($groups as $group) : ?>
						<?php include_partial("group/grid", Array("group" => $group)); ?>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="show-more-groups"<?php echo $count < $itemsToShow ? " style='display: none;'" : ""; ?>>
				<a href="javascript: void(0);" class="btn-header"><?php echo __("Show more"); ?></a>
			</div>
		</div>
	</div>
</div>

<div id="add_main_folder_container" class="dialog"></div>
<div id="div_share" class="dialog"></div>
<script>
	function bindGroups()
	{

			jQuery(".item-group[data-id^=group_].open").draggable({
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


		jQuery(".item-group [data-toogle=modal-iframe]").IframeModal();

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

	function unbindGroups()
	{
		jQuery(".item-group[data-id^=group_].open").draggable("destroy");
		jQuery('a[rel*=facebox]').unbind("click");
		jQuery('a[rel*=faceframe]').unbind("click");
	}

	function displayGroups()
	{
		jQuery(".item-group").each(function() {
			var width = jQuery(this).width();
			var infoHeight = jQuery(this).find(".info-group").get(0).offsetHeight + 1;
			var availableHeight = width - infoHeight;
			var originalWidth = jQuery(this).find("img").attr("data-width");
			var originalHeight = jQuery(this).find("img").attr("data-height");

			if(originalHeight <= availableHeight)
			{
				jQuery(this).find("img").css("padding-top", Math.round((availableHeight - 1 - originalHeight) / 2) + "px");
				jQuery(this).find("img").css("padding-bottom", Math.round((availableHeight - 1 - originalHeight) / 2) + "px");
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
					var margin = ((tWidth - minW) / 2);
					var css = { "clip": "rect(auto, " + (minW + margin) + "px, auto, 0px)", "width": tWidth + "px", "height": tHeight + "px" };
				}
				else if(tHeight > minH)
				{
					var margin = ((tHeight - minH) / 2);
					var css = { "clip": "rect(" + margin + "px, auto, " + (minH + margin) + "px, auto)", "width": tWidth + "px", "height": tHeight + "px", "top": (margin * -1) + "px" };
				}
				else
				{
					var paddingLeft = ((minW - tWidth) / 2);
					var paddingTop = ((minH - tHeight) / 2);
					var css = { "width": tWidth + "px", "height": tHeight + "px", "padding-top": paddingTop + "px", "padding-left": paddingLeft + "px" };
				}

				jQuery(this).find("img").closest(".thumbnail").css("height", availableHeight + "px");
				jQuery(this).find("img").addClass("cropp").css(css);
			}
		});
	}

	function initMasonryGroups()
	{
		var gutterWidthGroups = 10;
		var minWidthGroups = 270;

		jQuery("#groups").imagesLoaded( function() {
			jQuery("#groups").masonry({
				itemSelector : '.item-group',
				gutterWidth: gutterWidthGroups,
				isAnimated: true,
				columnWidth: function(containerWidth) {
					var itemNbr = (containerWidth / minWidthGroups | 0);

					var itemWidth = (((containerWidth - (itemNbr - 1) * gutterWidthGroups) / itemNbr) | 0);

					if (containerWidth < minWidthGroups)
						itemWidth = containerWidth;

					jQuery(".item-group").width(itemWidth);

					displayGroups();

					return itemWidth;
				}
			});
		});
	}

	jQuery(document).ready(function() {
		var pageGroups = 2;
		var onPageGroups = <?php echo $itemsToShow != "all" ? $itemsToShow : 0; ?>;

		tooltip();
		bindGroups();

		initMasonryGroups();

		jQuery(".show-more-groups a").bind("click", function() {
			if(!jQuery(".show-more-groups a").data("clicked"))
			{
				jQuery(".show-more-groups a").data("clicked", true);
				jQuery(".show-more-groups a").html("<i class='icon-spinner icon-spin'></i> <?php echo __("Loading groups..."); ?>");

				jQuery.post(
					"<?php echo url_for("group/loadHomeGroups"); ?>",
					{ page: pageGroups, onPage: onPageGroups, sort: jQuery("#sort").val() },
					function(data) {
						unbindGroups();

						pageGroups++;

						var groups = "<div>" + data.groups + "</div>";
						var childs = jQuery(groups).children();
						jQuery("#groups").append(childs);

						/* jQuery(".context-groups").html(data.rightclick); */

						bindGroups();

						jQuery("#groups").imagesLoaded(function() {
							jQuery("#groups").masonry("reload");
						});

						if(data.index == 0)
							jQuery(".show-more-groups").fadeOut(400);
						else
						{
							jQuery(".show-more-groups a").html("<?php echo __("Show more"); ?>");
							jQuery(".show-more-groups a").data("clicked", false);
						}
					},
					"json"
				);
			}
		});

		jQuery("#add_main_folder_button").on("click", function() {
			var $groups = jQuery("#groups");

			if (jQuery("#add-group").length > 0) {
				return;
			}

			jQuery.post(
				"<?php echo url_for("group/loadNew"); ?>",
				function (data) {
					var childs = $groups.find("> div").length;
					var childsDisplay = (pageGroups - 1) * onPageGroups;

					$groups.prepend(data.html);

					if (childs == childsDisplay) {
						jQuery("body").prepend($groups.find("> div:last-child").hide());
					}

					$groups.masonry("reload");
				},
				"json"
			);
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


		jQuery("#per_page").bind("change", function() {
			if(!jQuery(".show-more-groups a").data("clicked"))
			{
				onPageGroups = jQuery(this).val();

				jQuery(".show-more-groups").hide();

				jQuery("#groups").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading groups..."); ?></div>");
				jQuery("#groups").addClass("overflow-hidden").css("height", "80px");

				jQuery.post(
					"<?php echo url_for("group/loadHomeGroups"); ?>",
					{ page: 1, onPage: onPageGroups, sort: jQuery("#sort").val() },
					function(data) {
						pageGroups = 2;

						jQuery("#groups").removeClass("overflow-hidden");

						unbindGroups();

						var groups = "<div>" + data.groups + "</div>";
						var childs = jQuery(groups).children();
						jQuery("#groups").html(childs);

						bindGroups();

						jQuery("#groups").imagesLoaded(function() {
							jQuery("#groups").masonry("reload");
						});

						if(data.index == 0)
							jQuery(".show-more-groups").fadeOut(400);
						else
						{
							jQuery(".show-more-groups a").html("<?php echo __("Show more"); ?>");
							jQuery(".show-more-groups a").data("clicked", false);
							jQuery(".show-more-groups").fadeIn(400);
						}
					},
					"json"
				);
			}
		});

		jQuery("#sort").bind("change", function() {
			if(!jQuery(".show-more-groups a").data("clicked"))
			{
				jQuery(".show-more-groups").hide();

				jQuery("#groups").html("<div class='loading'><i class='icon-spinner icon-spin'></i> <?php echo __("Loading groups..."); ?></div>");
				jQuery("#groups").addClass("overflow-hidden").css("height", "80px");

				jQuery.post(
					"<?php echo url_for("group/loadHomeGroups"); ?>",
					{ page: 1, onPage: onPageGroups, sort: jQuery("#sort").val() },
					function(data) {
						pageGroups = 2;

						jQuery("#groups").removeClass("overflow-hidden");

						unbindGroups();

						var groups = "<div>" + data.groups + "</div>";
						var childs = jQuery(groups).children();
						jQuery("#groups").html(childs);

						/* jQuery(".context-groups").html(data.rightclick); */

						bindGroups();

						jQuery("#groups").imagesLoaded(function() {
							jQuery("#groups").masonry("reload");
						});

						if(data.index == 0)
							jQuery(".show-more-groups").fadeOut(400);
						else
						{
							jQuery(".show-more-groups a").html("<?php echo __("Show more"); ?>");
							jQuery(".show-more-groups a").data("clicked", false);
							jQuery(".show-more-groups").fadeIn(400);
						}
					},
					"json"
				);
			}
		});
	});
</script>
