<?php
	$formatedKeywords = $formatedKeywords->getRawValue();
	$infoFiles = $infoFiles->getRawValue();
	$types = $types->getRawValue();
	$orientations = $orientations->getRawValue();
	$sizes = $sizes->getRawValue();
	$dates = $dates->getRawValue();
	$filesLicences = $filesLicences->getRawValue();
	$filesUses = $filesUses->getRawValue();
	$filesDistributions = $filesDistributions->getRawValue();
?>
<div class="span3">
	<div class="search-sidebar">
		<div class="right-column search-sidebar-container">
			<div id="rightColumn">
				<div class="title-sidebar">
					<h1>
						<?php echo __("Filters"); ?>
					</h1>
				</div>
				<div class="inside">
					<div class="cat-right">
						<a class="deploy-cat active" href="javascript: void(0);"><i class="icon-key"></i> <?php echo __("By tags"); ?> <i class="icon-chevron-up right"></i></a>

						<div class="content keywords-list">
							<?php foreach($formatedKeywords as $keyword) : ?>
								<a class="label" href="<?php echo $keyword["link"]; ?>"><?php echo $keyword["label"]; ?><i class="icon-remove"></i></a><br />
							<?php endforeach; ?>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="cat-right" id="sidebar_information">
						<a class="deploy-cat active" href="javascript: void(0);"><i class="icon-info-sign"></i> <?php echo __("By information"); ?> <i class="icon-chevron-up right"></i></a>

						<div class="content list-informations" id="filter-extention">
							<ul class="unstyled">
								<li><a href="javascript: void(0);" <?php echo in_array("albums", $types) && $infoGroups > 0 ? "class='selected'" : ""; ?> data-type="albums"><i class="icon-book"></i><?php echo __("Groups"); ?><span class="count"><?php echo $infoGroups; ?></span></a></li>
								<li><a href="javascript: void(0);" <?php echo in_array("folders", $types) && $infoFolders > 0 ? "class='selected'" : ""; ?> data-type="folders"><i class="icon-folder-close"></i><?php echo __("Folders"); ?><span class="count"><?php echo $infoFolders; ?></span></a></li>
								<li>
									<a href="javascript: void(0);" <?php echo in_array("pictures", $types) && $infoFiles[FilePeer::__TYPE_PHOTO]["count"] > 0 ? "class='selected'" : ""; ?> data-type="pictures"><i class="icon-picture"></i><?php echo __("Pictures"); ?><span class="count"><?php echo $infoFiles[FilePeer::__TYPE_PHOTO]["count"]; ?></span></a>
									<?php if(!empty($infoFiles[FilePeer::__TYPE_PHOTO]["type"])) : ?>
										<ul class="unstyled select-extention">
											<?php foreach($infoFiles[FilePeer::__TYPE_PHOTO]["type"] as $extention => $countExtention) : ?>
											<li><a href="javascript: void(0);" data-type="<?php echo $extention; ?>" <?php echo in_array("pictures", $types) ? "class='selected'" : ""; ?>><?php echo $extention; ?><span class="count"><?php echo $countExtention; ?></span></a></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
								<li>
									<a href="javascript: void(0);" <?php echo in_array("videos", $types) && $infoFiles[FilePeer::__TYPE_VIDEO]["count"] > 0 ? "class='selected'" : ""; ?> data-type="videos"><i class="icon-play-circle"></i><?php echo __("Videos"); ?><span class="count"><?php echo $infoFiles[FilePeer::__TYPE_VIDEO]["count"]; ?></span></a>
									<?php if(!empty($infoFiles[FilePeer::__TYPE_VIDEO]["type"])) : ?>
										<ul class="unstyled select-extention">
											<?php foreach($infoFiles[FilePeer::__TYPE_VIDEO]["type"] as $extention => $countExtention) : ?>
											<li><a href="javascript: void(0);" data-type="<?php echo $extention; ?>" <?php echo in_array("videos", $types) ? "class='selected'" : ""; ?>><?php echo $extention; ?><span class="count"><?php echo $countExtention; ?></span></a></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
								<li>
									<a href="javascript: void(0);" <?php echo in_array("audios", $types) && $infoFiles[FilePeer::__TYPE_AUDIO]["count"] > 0 ? "class='selected'" : ""; ?> data-type="audios"><i class="icon-music"></i><?php echo __("Audios"); ?><span class="count"><?php echo $infoFiles[FilePeer::__TYPE_AUDIO]["count"]; ?></span></a>
									<?php if(!empty($infoFiles[FilePeer::__TYPE_AUDIO]["type"])) : ?>
										<ul class="unstyled select-extention">
											<?php foreach($infoFiles[FilePeer::__TYPE_AUDIO]["type"] as $extention => $countExtention) : ?>
											<li><a href="javascript: void(0);" data-type="<?php echo $extention; ?>" <?php echo in_array("audios", $types) ? "class='selected'" : ""; ?>><?php echo $extention; ?><span class="count"><?php echo $countExtention; ?></span></a></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
								<li>
									<a href="javascript: void(0);" <?php echo in_array("documents", $types) && $infoFiles[FilePeer::__TYPE_DOCUMENT]["count"] > 0 ? "class='selected'" : ""; ?> data-type="documents"><i class="icon-file-alt"></i><?php echo __("Documents"); ?><span class="count"><?php echo $infoFiles[FilePeer::__TYPE_DOCUMENT]["count"]; ?></span></a>
									<?php if(!empty($infoFiles[FilePeer::__TYPE_DOCUMENT]["type"])) : ?>
										<ul class="unstyled select-extention">
											<?php foreach($infoFiles[FilePeer::__TYPE_DOCUMENT]["type"] as $extention => $countExtention) : ?>
											<li><a href="javascript: void(0);" data-type="<?php echo $extention; ?>" <?php echo in_array("documents", $types) ? "class='selected'" : ""; ?>><?php echo $extention; ?><span class="count"><?php echo $countExtention; ?></span></a></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
							</ul>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="cat-right">
						<a class="deploy-cat" href="javascript: void(0);"><i class="icon-bar-chart"></i> <?php echo __("By properties"); ?> <i class="icon-chevron-down right"></i></a>

						<div class="content list-informations" id="filter-properties" style="display: none;">
							<ul class="unstyled">
								<li>
									<a href="javascript: void(0);" class="selected"><i class="icon-rotate-right"></i><?php echo __("Orientation"); ?></a>
									<ul class="unstyled" id="select-orientation">
										<li><a href="javascript: void(0);" data-type="portrait" <?php echo $orientations["portrait"] > 0 ? "class='selected'" : ""; ?>><?php echo __("Portrait"); ?><span class="count"><?php echo $orientations["portrait"]; ?></span></a></li>
										<li><a href="javascript: void(0);" data-type="landscape" <?php echo $orientations["landscape"] > 0 ? "class='selected'" : ""; ?>><?php echo __("Landscape"); ?><span class="count"><?php echo $orientations["landscape"]; ?></span></a></li>
										<li><a href="javascript: void(0);" data-type="square" <?php echo $orientations["square"] > 0 ? "class='selected'" : ""; ?>><?php echo __("Square"); ?><span class="count"><?php echo $orientations["square"]; ?></span></a></li>
									</ul>
								<li>
								<li>
									<a href="javascript: void(0);" class="selected"><i class="icon-hdd"></i><?php echo __("Size"); ?></a>
									<ul class="unstyled" id="select-size">
										<li><a href="javascript: void(0);" data-type="-5" <?php echo $sizes["-5"] > 0 ? "class='selected'" : ""; ?>><?php echo __("< 5MB"); ?><span class="count"><?php echo $sizes["-5"]; ?></span></a></li>
										<li><a href="javascript: void(0);" data-type="5" <?php echo $sizes["5"] > 0 ? "class='selected'" : ""; ?>><?php echo __(">= 5MB < 25MB"); ?><span class="count"><?php echo $sizes["5"]; ?></span></a></li>
										<li><a href="javascript: void(0);" data-type="25" <?php echo $sizes["25"] > 0 ? "class='selected'" : ""; ?>><?php echo __(">= 25MB < 50MB"); ?><span class="count"><?php echo $sizes["25"]; ?></span></a></li>
										<li><a href="javascript: void(0);" data-type="50" <?php echo $sizes["50"] > 0 ? "class='selected'" : ""; ?>><?php echo __(">= 50MB < 100MB"); ?><span class="count"><?php echo $sizes["50"]; ?></span></a></li>
										<li><a href="javascript: void(0);" data-type="100" <?php echo $sizes["100"] > 0 ? "class='selected'" : ""; ?>><?php echo __(">= 100MB < 250MB"); ?><span class="count"><?php echo $sizes["100"]; ?></span></a></li>
										<li><a href="javascript: void(0);" data-type="250" <?php echo $sizes["250"] > 0 ? "class='selected'" : ""; ?>><?php echo __(">= 250MB"); ?><span class="count"><?php echo $sizes["250"]; ?></span></a></li>
									</ul>
								<li>
								<li>
									<a href="javascript: void(0);" class="selected"><i class="icon-time"></i><?php echo __("Uploaded date"); ?></a>
									<ul class="unstyled" id="select-date">
										<li>
											<input type="text" name="date_start" id="date_start" placeholder="<?php echo __("Start date"); ?>" value="<?php echo date("d/m/Y", $dates["min"]); ?>" />
										</li>
										<li>
											<input type="text" name="date_end" id="date_end" placeholder="<?php echo __("End date"); ?>" value="<?php echo date("d/m/Y", $dates["max"]); ?>" />
										</li>
									</ul>
								<li>
							</ul>
						</div>
					</div>
					<div class="clearfix"></div>
					<div class="cat-right">
						<a class="deploy-cat" href="javascript: void(0);"><i class="icon-cog"></i> <?php echo __("By rights and uses"); ?> <i class="icon-chevron-down right"></i></a>

						<div class="content list-informations" id="filter-rights" style="display: none;">
							<ul class="unstyled">
								<li>
									<a href="javascript: void(0);" <?php echo $filesLicences["count"] > 0 ? "class='selected'" : ""; ?>><i class="icon-angle-right"></i><?php echo __("Licence"); ?><span class="count"><?php echo $filesLicences["count"]; ?></a>
									<ul class="unstyled" id="select-licence">
										<?php foreach($licences as $licence) : ?>
											<li><a href="javascript: void(0);" <?php echo array_key_exists($licence->getId(), $filesLicences["data"]) ? "class='selected'" : ""; ?> data-type="<?php echo $licence->getId(); ?>"><?php echo $licence->getTitle(); ?><span class="count"><?php echo array_key_exists($licence->getId(), $filesLicences["data"]) ? $filesLicences["data"][$licence->getId()] : 0; ?></span></a></li>
										<?php endforeach; ?>
									</ul>
								<li>
								<li>
									<a href="javascript: void(0);" <?php echo $filesUses["count"] > 0 ? "class='selected'" : ""; ?>><i class="icon-angle-right"></i><?php echo __("Use"); ?><span class="count"><?php echo $filesUses["count"]; ?></a>
									<ul class="unstyled" id="select-use">
										<?php foreach($uses as $use) : ?>
											<li><a href="javascript: void(0);" <?php echo array_key_exists($use->getId(), $filesUses["data"]) ? "class='selected'" : ""; ?> data-type="<?php echo $use->getId(); ?>"><?php echo $use->getTitle(); ?><span class="count"><?php echo array_key_exists($use->getId(), $filesUses["data"]) ? $filesUses["data"][$use->getId()] : 0; ?></span></a></li>
										<?php endforeach; ?>
									</ul>
								<li>
								<li>
									<a href="javascript: void(0);" <?php echo $filesDistributions["count"] > 0 ? "class='selected'" : ""; ?>><i class="icon-angle-right"></i><?php echo __("Distribution"); ?><span class="count"><?php echo $filesDistributions["count"]; ?></a>
									<ul class="unstyled" id="select-distribution">
										<?php foreach($distributions as $distribution) : ?>
											<li><a href="javascript: void(0);" <?php echo array_key_exists($distribution->getId(), $filesDistributions["data"]) ? "class='selected'" : ""; ?> data-type="<?php echo $distribution->getId(); ?>"><?php echo $distribution->getTitle(); ?><span class="count"><?php echo array_key_exists($distribution->getId(), $filesDistributions["data"]) ? $filesDistributions["data"][$distribution->getId()] : 0; ?></span></a></li>
										<?php endforeach; ?>
									</ul>
								<li>
							</ul>
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	var searchDateStart = "<?php echo date("d/m/Y", $dates["min"]); ?>";
	var searchDateEnd = "<?php echo date("d/m/Y", $dates["max"]); ?>";

	jQuery(document).ready(function() {
		jQuery(".keywords-list .label").bind("click", function() {
			jQuery(this).fadeOut(400);
		});

		jQuery("#filter-extention > ul > li > a").bind("click", function() {
			var type = jQuery(this).attr("data-type");

			if(type != "albums" && type != "folders")
			{
				if(jQuery(this).parent().find("ul").length > 0)
				{
					if(jQuery(this).parent().find("ul").is(":visible"))
						jQuery(this).parent().find("ul").slideUp(400);
					else
						jQuery(this).parent().find("ul").slideDown(400);
				}
			}
			else
			{
				if(jQuery(this).hasClass("selected"))
				{
					jQuery(this).removeClass("selected");

					removeTopbarItem(type);

					switch(type)
					{
						case "folders": var elements = jQuery("#groups-folders .item-folder"); break;
						case "albums": var elements = jQuery("#groups-folders .item-group"); break;
					}

					jQuery("#groups-folders").masonry("remove", elements);
					jQuery("#groups-folders").masonry();

					if(jQuery("#groups-folders").children().length == 0)
					{
						jQuery(".search-folders-container").addClass("hide");
						jQuery(".search-files-container .span3").append(jQuery(".slimScrollDiv"));
					}
				}
				else
				{
					jQuery(this).addClass("selected");

					addTopbarItem(type);

					var post = jQuery("#top_search_form").serialize() + "&filterType=" + jQuery(this).attr("data-type");

					jQuery.post(
						"<?php echo url_for("search/filterSearch"); ?>",
						post,
						function(data) {
							var items = "<div>" + data.html + "</div>";
							var childs = jQuery(items).children();
							jQuery("#groups-folders").prepend(childs);

							jQuery("#groups-folders").imagesLoaded(function() {
								jQuery(".search-folders-container").removeClass("hide");
								jQuery("#groups-folders").masonry("reload");

								if(jQuery(".search-folders-container .span3").children().length == 0)
								{
									jQuery(".search-folders-container .span3").append(jQuery(".slimScrollDiv"));
								}
							});
						},
						"json"
					);
				}
			}
		});

		jQuery(".select-extention a").bind("click", function() {
			var type = jQuery(this).closest(".select-extention").parent().find("> a").attr("data-type");

			if(jQuery(this).hasClass("selected"))
				jQuery(this).removeClass("selected");
			else
				jQuery(this).addClass("selected");

			if(jQuery(this).closest(".select-extention").find("a.selected").length > 0)
			{
				jQuery(this).closest(".select-extention").parent().find("> a").addClass("selected");

				addTopbarItem(type);
			}
			else
			{
				jQuery(this).closest(".select-extention").parent().find("> a").removeClass("selected");

				removeTopbarItem(type);
			}

			var ext = jQuery(this).attr("data-type");
			var post = jQuery("#top_search_form").serialize() + "&extention=" + ext + "&filterType=files";

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
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
				},
				"json"
			);
		});

		jQuery("#filter-properties > ul > li > a").bind("click", function() {
			if(jQuery(this).parent().find("ul").is(":visible"))
				jQuery(this).parent().find("ul").slideUp(400);
			else
				jQuery(this).parent().find("ul").slideDown(400);
		});

		jQuery("#filter-rights > ul > li > a").bind("click", function() {
			if(jQuery(this).parent().find("ul").is(":visible"))
			{
				jQuery(this).find("i").addClass("icon-angle-right").removeClass("icon-angle-down");
				jQuery(this).parent().find("ul").slideUp(400);
			}
			else
			{
				jQuery(this).find("i").addClass("icon-angle-down").removeClass("icon-angle-right");
				jQuery(this).parent().find("ul").slideDown(400);
			}
		});

		jQuery("#select-orientation a").bind("click", function() {
			if(jQuery(this).hasClass("selected"))
				jQuery(this).removeClass("selected");
			else
				jQuery(this).addClass("selected");

			var orientation = jQuery(this).attr("data-type");
			var post = jQuery("#top_search_form").serialize() + "&file_orientation=" + orientation + "&filterType=files";

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
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
				},
				"json"
			);
		});

		jQuery("#select-size a").bind("click", function() {
			if(jQuery(this).hasClass("selected"))
				jQuery(this).removeClass("selected");
			else
				jQuery(this).addClass("selected");

			var size = jQuery(this).attr("data-type");
			var post = jQuery("#top_search_form").serialize() + "&file_size=" + size + "&filterType=files";

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
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
				},
				"json"
			);
		});

		jQuery.datepicker.setDefaults($.datepicker.regional['<?php echo $sf_user->getCulture(); ?>']);

		jQuery("#date_start").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $dates["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y", $dates["max"]); ?>",
			dateFormat: "dd/mm/yy",
			onClose: function(selectedDate) {
				jQuery("#date_end").datepicker("option", "minDate", selectedDate);
				searchDateStart = selectedDate;

				submitDates();
			}
		});

		jQuery("#date_end").datepicker({
			changeMonth: true,
			minDate: "<?php echo date("d/m/Y", $dates["min"]); ?>",
			maxDate: "<?php echo date("d/m/Y", $dates["max"]); ?>",
			dateFormat: "dd/mm/yy",
			onClose: function(selectedDate) {
				jQuery("#date_start").datepicker("option", "maxDate", selectedDate);
				searchDateEnd = selectedDate;

				submitDates();
			}
		});

		jQuery("#select-licence a").bind("click", function() {
			if(jQuery(this).hasClass("selected"))
				jQuery(this).removeClass("selected");
			else
				jQuery(this).addClass("selected");

			var licence = jQuery(this).attr("data-type");
			var post = jQuery("#top_search_form").serialize() + "&licence=" + licence + "&filterType=files";

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
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
				},
				"json"
			);
		});

		jQuery("#select-use a").bind("click", function() {
			if(jQuery(this).hasClass("selected"))
				jQuery(this).removeClass("selected");
			else
				jQuery(this).addClass("selected");

			var use = jQuery(this).attr("data-type");
			var post = jQuery("#top_search_form").serialize() + "&use=" + use + "&filterType=files";

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
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
				},
				"json"
			);
		});

		jQuery("#select-distribution a").bind("click", function() {
			if(jQuery(this).hasClass("selected"))
				jQuery(this).removeClass("selected");
			else
				jQuery(this).addClass("selected");

			var distribution = jQuery(this).attr("data-type");
			var post = jQuery("#top_search_form").serialize() + "&distribution=" + distribution + "&filterType=files";

			jQuery.post(
				"<?php echo url_for("search/filterSearch"); ?>",
				post,
				function(data) {
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
				},
				"json"
			);
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

		var availableHeight = jQuery(window).height() - jQuery(".search-sidebar").offset().top - 15;
		var availableWidth = jQuery(".search-sidebar").width();

		jQuery(".search-sidebar").slimScroll({
			width: availableWidth + "px",
			height: availableHeight + "px"
		});
	});

	function submitDates()
	{
		var post = jQuery("#top_search_form").serialize() + "&min_date=" + searchDateStart + "&max_date=" + searchDateEnd + "&filterType=files";

		jQuery.post(
			"<?php echo url_for("search/filterSearch"); ?>",
			post,
			function(data) {
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
			},
			"json"
		);
	}
</script>