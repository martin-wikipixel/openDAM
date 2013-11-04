<?php
	$relative = null;
	$absolute = null;

	if ($file->existsThumb400()) {
		$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "400"));
		$absolute = $file->getThumb400Pathname();
	}
	else if($file->existsThumb200()) {
	{
		$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "200"));
		$absolute = $file->getThumb200Pathname();
	}
?>

<?php if($relative && $absolute) : ?>
	<div class="item-file file">
		<div class="contain">
			<div class="thumbnail">
				<?php
					$size = getimagesize($absolute);

					if($size[0] < 250)
						$padding = 20;
					else
						$padding = null;
				?>

				<img src="<?php echo $relative; ?>" <?php echo $padding ? 'style="padding: '.$padding.'px 0;"' : ''; ?> />
			</div>
			<div class="info">
				<div class="title">
					<?php echo $file->getName(); ?>
				</div>
				<a class="download" title="<?php echo __("Download"); ?>" href="<?php echo path("public_selection_file_download", 
						array("code" => $basket->getCode(), "file" => $file->getId()))?>">
					<i class="icon-download-alt"></i>
				</a>
			</div>
		</div>
	</div>
<?php endif; ?>