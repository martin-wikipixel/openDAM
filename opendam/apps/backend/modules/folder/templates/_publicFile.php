<?php
	$relative = null;
	$absolute = null;

	if ($fileIn->existsThumb400())
	{
		$relative = path("@file_thumbnail", array("id" => $fileIn->getId(), "format" => "400", "link" => $permalink->getLink()));
		$absolute = $fileIn->getThumb400Pathname();
	}
	else if($fileIn->existsThumb200())
	{
		$relative = path("@file_thumbnail", array("id" => $fileIn->getId(), "format" => "200", "link" => $permalink->getLink()));
		$absolute = $fileIn->getThumb200Pathname();
	}
?>

<?php if($relative && $absolute) : ?>
	<div class="item-file item file">
		<div class="contain">

				<?php
					$size = getimagesize($absolute);

					if($size[0] < 250)
						$padding = 20;
					else
						$padding = null;
				?>

				<img src="<?php echo $relative; ?>" <?php echo $padding ? 'style="padding: '.$padding.'px 0;"' : ''; ?> />

			<div class="info">
				<div class="title">
					<?php echo $fileIn; ?>
				</div>
				<a title="<?php echo __("Download"); ?>" href="<?php echo url_for("download/downloadFile?id=".$fileIn->getId()."&permalink_id=".$permalink->getLink()."&definition=".($permalink->getFormatHd() ? "original" : "web")); ?>" class="download">
					<i class="icon-download-alt"></i>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>