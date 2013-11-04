<?php
	$contents = $contents->getRawValue();
?>

<div class="navbar navbar-fixed-top top-under <?php echo empty($contents) ? "empty" : ""; ?>">
	<div class="navbar-inner">
		<div class="container">
			<div class="row">
				<div class="span2">
					<h4>
						<?php echo __("Collection"); ?>
						<div <?php echo empty($contents) ? "class='empty'" : ""; ?>>
							<span><?php echo count($contents); ?></span><i class="icon-picture"></i>
						</div>
					</h4>
				</div>
				<div class="span8 selection-thumb-container">
					<div class="over-droppable">
						<div>+</div>
						<label></label>
					</div>
					<?php foreach($contents as $content) : ?>
						<?php include_partial("selection/myBasketThumbContent", Array("content" => $content)); ?>
					<?php endforeach; ?>
				</div>
				<div class="span2">
					<div class="pull-right" id="selection-actions">
						<a href="javascript: void(0);" class="custom-button trash"><i class="icon-trash"></i><span><?php echo __("To empty"); ?></span></a>
						<a target="_blank" href="<?php echo path("selection_show_current"); ?>" class="custom-button share"><i class="icon-share-alt"></i><span><?php echo __("Share"); ?></span></a>
						<h3><a href="javascript: void(0);" id="toogle-selection"><i class="icon-angle-down"></i></a></h3>
					</div>
				</div>
			</div>
			<div class="row selection-container collapse">
				<div class="span12" style="overflow: hidden;">
					<div id="selection-container">
						<?php foreach($contents as $content) : ?>
							<?php include_partial("selection/myBasketContent", Array("content" => $content)); ?>
						<?php endforeach; ?>
					</div>
					<div class="navbar navbar-fixed-bottom" style="position: absolute;">
						<div class="container">
							<div class="row">
								<div id="pagination-selection" class="span12"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<label class="float-name"></label>
		</div>
	</div>
</div>
<script>
	var maxHeight = jQuery(window).height() / 2;

	function initMasonrySelection()
	{
		jQuery("#selection-container").myWall({
			"maxHeight": (maxHeight - 100),
			"itemSelector": ".item-selection",
			"notFoundPath": "<?php echo "/".sfConfig::get("app_path_images_dir_name")."/no-access-file-200x200.png"; ?>",
			"gutterWidth": 8,
			"showLastLine": true,
			"inline": true,
			"lazyImg": "<?php echo "data:image/gif;base64,".base64_encode(file_get_contents(image_path("icons/loader/loader-selection.gif", true))); ?>"
		});
	}

	jQuery(document).ready(function() {
		jQuery(".selection-container").css("max-height", maxHeight + "px").attr("data-height", maxHeight);

		initMasonrySelection();

		<?php if(count($contents)) : ?>
			displayPagination(jQuery("#selection-container").myWall("getLines"));
		<?php else: ?>
			jQuery("#selection-container").myWall("setContainerWidth", jQuery("#selection-container").parent().width());
		<?php endif; ?>

		jQuery("#selection-actions .trash").bind("click", function() {
			emptySelectionFile();
		});

		jQuery(".selection-thumb-container .thumbnail-selection").live({
			mouseenter: function () {
				jQuery(".float-name").css("left", jQuery(this).offset().left);
				jQuery(".float-name").html(jQuery(this).attr("data-name"));
			},
			mouseleave: function () {
				jQuery(".float-name").html("");
				jQuery(".float-name").css("left", 0);
			}
		});
		
		jQuery(".selection-thumb-container .thumbnail-selection .remove-thumb").live("click", function(event) {
			event.stopPropagation();
			event.preventDefault();
			removeSelectionFile(jQuery(this).attr("data-id"));
		});

		jQuery("#selection-container .item-selection .contain .remove").live("click", function(event) {
			event.stopPropagation();
			event.preventDefault();
			removeSelectionFile(jQuery(this).attr("data-id"));
		});


		jQuery("#toogle-selection").bind("click", function() {
			var height = jQuery(".selection-container").attr("data-height");
			var paddingTop = jQuery("body").css("padding-top").replace(/[^-\d\.]/g, '');

			if(jQuery("#toogle-selection").find(".icon-angle-up").length > 0)
			{
				jQuery(".selection-container").removeAttr("style");

				jQuery(".selection-thumb-container").addClass("span8").removeClass("span7").removeAttr("style");

				jQuery("#selection-actions .share").removeClass("expand");
				jQuery("#selection-actions .trash").removeClass("expand");
				jQuery("#selection-actions").parent().addClass("span2").removeClass("span3");

				jQuery("#toogle-selection").find(".icon-angle-up").addClass("icon-angle-down").removeClass("icon-angle-up");

				if(jQuery(".actions-container").hasClass("navbar-fixed-top"))
				{
					var height = jQuery(".actions-wrapper").height();

					jQuery(".actions-container").css("top", jQuery(".actions-container").attr("data-top") + "px");

					var offset = jQuery(".actions-container").offset().top;

					jQuery("body").css("padding-top", (parseInt(offset, 10) + height + 25) + "px");
				}
				else
					jQuery("body").removeAttr("style");
			}
			else
			{
				if(jQuery(".actions-container").hasClass("navbar-fixed-top"))
				{
					var top = jQuery(".actions-container").css("top").replace(/[^-\d\.]/g, '');
					jQuery(".actions-container").attr("data-top", top);
					jQuery(".actions-container").css("top", (jQuery(".top-under").height() + parseInt(height, 10)) + "px");
				}
				
				jQuery(".selection-container").css("height", height + "px");

				jQuery("body").css("padding-top", (parseInt(paddingTop, 10) + parseInt(height, 10)) + "px");

				jQuery(".selection-thumb-container").addClass("span7").removeClass("span8").css("opacity", 0);

				jQuery("#selection-actions").parent().addClass("span3").removeClass("span2");
				jQuery("#selection-actions .share").addClass("expand");
				jQuery("#selection-actions .trash").addClass("expand");

				jQuery("#toogle-selection").find(".icon-angle-down").addClass("icon-angle-up").removeClass("icon-angle-down");
			}
		});

		jQuery(".selection-thumb-container").droppable({
			tolerance: "touch",
			drop: function(event, ui) {
				var type = "";
				var object = this;
				var paddingTop = jQuery("body").css("padding-top").replace(/[^-\d\.]/g, '');
				var height = jQuery(".top-under").height();

				ui.draggable.addClass("dropped");

				jQuery(".top-under").removeClass("empty");

				jQuery(object).find(".over-droppable").find("label").html("");
				jQuery(object).find(".over-droppable").hide();
				jQuery(".top-under .navbar-inner").removeClass("hover");

				if(ui.draggable.hasClass("group"))
					type = "group";
				else if(ui.draggable.hasClass("folder"))
					type = "folder";
				else if(ui.draggable.hasClass("file"))
				{
					type = "file";

					if(!jQuery(".item-file[data-value=" + ui.draggable.attr("data-value") + "]").find(".actions.basket").hasClass("added"))
						jQuery(".item-file[data-value=" + ui.draggable.attr("data-value") + "]").find(".actions.basket").addClass("added");
				}

				processToAdd(type, ui.draggable.attr("data-value"));
			},
			over: function(event, ui) {
				var text = "";

				if(ui.draggable.hasClass("group"))
					text = "<?php echo __("Drop your mainfolder here"); ?>";
				else if(ui.draggable.hasClass("folder"))
					text = "<?php echo __("Drop your folder here"); ?>";
				else if(ui.draggable.hasClass("file"))
					text = "<?php echo __("Drop your file here"); ?>";

				jQuery(this).find(".over-droppable").find("label").html(text);

				if(!jQuery(this).find(".over-droppable").is(":visible"))
				{
					jQuery(".top-under .navbar-inner").addClass("hover");
					jQuery(this).find(".over-droppable").fadeIn(250);
				}
			},
			out: function(event, ui) {
				jQuery(".top-under .navbar-inner").removeClass("hover");

				jQuery(this).find(".over-droppable").find("label").html("");
				jQuery(this).find(".over-droppable").fadeOut(250);
			}
		});
	});
</script>