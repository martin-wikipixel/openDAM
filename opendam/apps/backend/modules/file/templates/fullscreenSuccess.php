<div class="container">
	<div class="row">
		<div class="span12 img-container">
			<?php
				$absolute = null;
				$relative = null;

				if ($file->getThumbTabW() && $file->existsThumbTabW()) {
					$absolute = $file->getThumbTabWPathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "tabw"));
				}
				else if ($file->getThumbMobW() && $file->existsThumbMobW()) {
					$absolute = $file->getThumbMobWPathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "mobw"));
				}
				else if ($file->existsThumbWeb()) {
					$absolute = $file->getThumbWebPathname();
					$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "web"));
				}

				if ($absolute && $relative) {
					$size = getimagesize($absolute);
					$maxW = $width;
					$maxH = $height - 70;
					$paddingTop = null;

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
					?>
					<img src="<?php echo $relative; ?>" style="width: <?php echo round($size[0] * $ratio); ?>px; height: <?php echo round($size[1] * $ratio); ?>px; <?php echo $paddingTop ? "padding-top: ".$paddingTop."px;" : 0; ?>" />
				<?php }
			?>
		</div>
	</div>
</div>

<div class="nav-slideshow">
	<div class="container">
		<div class="row">
			<div class="span12 nav-container">
				<div class="row">
					<div class="span12">
						<a href="javascript: void(0);" class="btn close-win pull-right">
							<?php echo __("Close"); ?> <i class="icon-remove"></i>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function() {
		jQuery(".close-win").bind("click", function() {
			jQuery(".overlay-full").fadeOut(800, function() {
				jQuery(".overlay-full").remove();
			});

			jQuery("#full_screen").fadeOut(600, function() {
				jQuery("body").removeClass("no-scroll");
				jQuery("#full_screen").remove();
			});
		});
	});
</script>