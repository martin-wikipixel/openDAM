<?php $countFile = $countFile->getRawValue(); ?>
<div class="container">
	<div class="row">
		<div class="span12">
			<div id="slider" class="flexslider">
				<ul class="slides">
					<?php $download = null; ?>
					<?php $count = 0; ?>
					<?php foreach($files as $file) : ?>
						<?php
							$absolute = null;
							$relative = null;

							if ($file->existsThumbTabW()) {
								$absolute = $file->getThumbTabWPathname();
								$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "tabw"));
							}
							else if ($file->existsThumbMobW()) {
								$absolute = $file->getThumbMobWPathname();
								$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "mobw"));
							}
							else if ($file->existsThumbWeb()) {
								$absolute = $file->getThumbWebPathname();
								$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "web"));
							}
						?>

						<?php if($absolute && $relative) : ?>
							<?php
								$size = getimagesize($absolute);
								$maxW = $width - 70;
								$maxH = $height - 70;
								$paddingTop = null;
								$marginLoader = null;

								if($size[0] > $maxW || $size[1] > $maxH)
								{
									$ratioW = $maxW / $size[0];
									$ratioH = $maxH / $size[1];

									if($ratioW < $ratioH)
										$ratio = $ratioW;
									else
										$ratio = $ratioH;
								}
								else
									$ratio = 1;

								if(round($size[1] * $ratio) < $maxH)
									$paddingTop = round(($maxH - round($size[1] * $ratio)) / 2);

								if(empty($download))
									$download = url_for("download/downloadFile?id=".$file->getId()."&permalink_id=".$permalink->getLink()."&definition=".($permalink->getFormatHd() ? "original" : "web"));

								$marginLoader = ($maxH - 25) / 2;
							?>
							<li id="<?php echo $file->getId(); ?>" data-img-src="<?php echo $relative; ?>" data-img-width = "<?php echo round($size[0] * $ratio); ?>" data-img-height = "<?php echo round($size[1] * $ratio); ?>" data-img-padding="<?php echo $paddingTop ? $paddingTop : 0; ?>">
								<div class="loader" style="<?php echo $marginLoader ? "margin-top: ".$marginLoader."px;" : ""; ?>">
									<i class="icon-spinner icon-spin icon-2x"></i>
								</div>
							</li>
							<?php $countFile[$file->getId()] = $count; ?>
							<?php $count++; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="nav-slideshow">
	<div class="container">
		<div class="row">
			<div class="span12 nav-container">
				<div class="row">
					<div class="span2">
						<a href="javascript: void(0);" class="btn display">
							<?php echo __("Display all"); ?> <i class="icon-angle-up"></i>
						</a>
					</div>
					<div class="span3 offset7">
						<a href="<?php echo $download; ?>" class="btn download">
							<?php echo __("Download"); ?> <i class="icon-download-alt"></i>
						</a>
						<a href="javascript: void(0);" class="btn close-win">
							<?php echo __("Close"); ?> <i class="icon-remove"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<div id="carousel" class="flexslider">
	<ul class="slides">
		<?php foreach($files as $file) : ?>
			<?php
				$absolute = null;
				$relative = null;

				if ($file->existsThumbTabW()) {
					$absolute = $file->getThumbTabWPathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "tabw"));
				}
				else if ($file->existsThumbMobW()) {
					$absolute = $file->getThumbMobWPathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "mobw"));
				}
				else if ($file->existsThumbWeb()) {
					$absolute = $file->getThumbWebPathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "web"));
				}
			?>

			<?php if($absolute && $relative) : ?>
				<?php
					$size = getimagesize($absolute);
					$maxW = 200;
					$maxH = 200;
					$paddingTop = null;
					$marginLoader = null;

					if($size[0] > $maxW || $size[1] > $maxH)
					{
						$ratioW = $maxW / $size[0];
						$ratioH = $maxH / $size[1];

						if($ratioW < $ratioH)
							$ratio = $ratioW;
						else
							$ratio = $ratioH;
					}
					else
						$ratio = 1;

					if(round($size[1] * $ratio) < $maxH)
						$paddingTop = round(($maxH - round($size[1] * $ratio)) / 2);

					$marginLoader = ($maxH - 25) / 2;
				?>
				<li data-img-src="<?php echo $relative; ?>" data-img-width = "<?php echo round($size[0] * $ratio); ?>" data-img-height = "<?php echo round($size[1] * $ratio); ?>" data-img-padding="<?php echo $paddingTop ? $paddingTop : 0; ?>">
					<div class="loader" style="<?php echo $marginLoader ? "margin-top: ".$marginLoader."px;" : ""; ?>">
						<i class="icon-spinner icon-spin icon-2x"></i>
					</div>
				</li>
				<?php /*<li>
					<img src="<?php echo $relative; ?>" style="width: <?php echo round($size[0] * $ratio); ?>px; height: <?php echo round($size[1] * $ratio); ?>px; <?php echo $paddingTop ? "padding-top: ".$paddingTop."px;" : ""; ?>" />
				</li>*/ ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>

<script>
	function showPicture(slider)
	{
		var img = jQuery("<img src='" + jQuery(slider).find(".flex-active-slide").attr("data-img-src") + "' style='width: " + jQuery(slider).find(".flex-active-slide").attr("data-img-width") + "px; height: " + jQuery(slider).find(".flex-active-slide").attr("data-img-height") + "px; padding-top: " + jQuery(slider).find(".flex-active-slide").attr("data-img-padding") + "px;' />").hide();
		img.bind("load", function() {
			jQuery(slider).find(".flex-active-slide .loader").fadeOut(400, function() {
				jQuery(slider).find(".flex-active-slide .loader").replaceWith(img);
				jQuery(img).fadeIn(400);
			});
		});
	}

	function showThumbnail(i, max)
	{
		var object = jQuery("#carousel li").eq(i);

		if(object.find(".loader").length > 0)
		{
			var img = jQuery("<img src='" + object.attr("data-img-src") + "' style='width: " + object.attr("data-img-width") + "px; height: " + object.attr("data-img-height") + "px; padding-top: " + object.attr("data-img-padding") +"px' />").hide();

			img.bind("load", function() {
				object.find(".loader").fadeOut(200, function() {
					object.find(".loader").replaceWith(img);
					jQuery(img).fadeIn(200);

					if(i < max)
					{
						i++;
						showThumbnail(i, max);
					}
				});
			});
		}
		else
		{
			if(i < max)
			{
				i++;
				showThumbnail(i, max);
			}
		}
	}

	jQuery(document).ready(function() {
		jQuery("#carousel").flexslider({
			animation: "slide",
			controlNav: false,
			directionNav: true,
			animationLoop: false,
			slideshow: false,
			itemWidth: 200,
			itemMargin: 5,
			asNavFor: "#slider",
			prevText: "<i class='icon-caret-left'></i>",
			nextText: "<i class='icon-caret-right'></i>",
			start: function(slider) {
				showThumbnail(0, slider.visible);
			},
			after: function(slider) {
				var index = jQuery(slider).find(".flex-active-slide").index();

				showThumbnail(index, (index + slider.visible));
			},
			before: function(slider) {
				var currentSlide = slider.animatingTo;
				var begin = currentSlide * slider.visible;

				showThumbnail(begin, (begin + slider.visible));
			}
		});

		jQuery("#slider").flexslider({
			animation: "slide",
			controlNav: false,
			animationLoop: false,
			slideshow: false,
			sync: "#carousel",
			prevText: "<i class='icon-caret-left'></i>",
			nextText: "<i class='icon-caret-right'></i>",
			keyboard: true,
			multipleKeyboard: true,
			<?php if($start > 0) : ?>
				<?php if(array_key_exists($start, $countFile)) : ?>
				startAt: <?php echo $countFile[$start]; ?>,
				<?php endif; ?>
			<?php endif; ?>
			start: function(slider) {
				showPicture(slider);
			},
			after: function(slider) {
				showPicture(slider);

				jQuery("a.download").attr("href", configPath + "/download/downloadFile?id=" + jQuery(slider).find(".flex-active-slide").attr("id") + "&permalink_id=<?php echo $permalink->getLink(); ?>&definition=<?php echo ($permalink->getFormatHd() ? "original" : "web"); ?>");
			} 
		});

		jQuery(".display").bind("click", function() {
			if(jQuery(this).hasClass("active"))
			{
				jQuery(this).removeClass("active");
				jQuery(this).find("i").addClass("icon-angle-up").removeClass("icon-angle-down");
				jQuery(".nav-slideshow").removeClass("active");

				jQuery("#carousel").animate({
					height: "0px"
				}, 400);

				jQuery(".nav-slideshow").animate({
					bottom: "0px"
				}, 400);
			}
			else
			{
				jQuery(this).addClass("active");
				jQuery(this).find("i").addClass("icon-angle-down").removeClass("icon-angle-up");
				jQuery(".nav-slideshow").addClass("active");

				jQuery("#carousel").animate({
					height: "220px"
				}, 400);

				jQuery(".nav-slideshow").animate({
					bottom: "220px"
				}, 400);
			}
		});

		jQuery(".close-win").bind("click", function() {
			jQuery(".overlay").fadeOut(800, function() {
				jQuery(".overlay").remove();
			});

			jQuery("#slideshow").fadeOut(600, function() {
				jQuery("body").removeClass("no-scroll");
				jQuery("#slideshow").remove();
			});
		});
	});
</script>