<?php
	$file = FilePeer::retrieveByPk($content->getFileId());

	$relative = path("@file_thumbnail", array("id" => $file->getId(), "format" => "100"));
	$absolute = $file->getThumb100Pathname();

	$size = @getimagesize($absolute);

	$minW = 36;
	$minH = 36;

	if($size[0] > $minW || $size[1] > $minH)
	{
		if($size[0] > $size[1])
			$ratio = $minH / $size[1];
		else
			$ratio = $minW / $size[0];
	}
	else
		$ratio = 1;

	$width = round($size[0] * $ratio);
	$height = round($size[1] * $ratio);

	if($width > $minW)
	{
		$margin = (($width - $minW) / 2);
		$css = "clip:rect(auto, ".($minW + $margin)."px, auto, ".$margin."px); width: ".$width."px; height: ".$height."px; left: ".($margin * -1)."px";
	}
	elseif($height > $minH)
	{
		$margin = (($height - $minH) / 2);
		$css = "clip:rect(".$margin."px, auto, ".($minH + $margin)."px, auto); width: ".$width."px; height: ".$height."px; top: ".($margin * -1)."px;";
	}
	else
	{
		$paddingLeft = (($minW - $width) / 2);
		$paddingTop = (($minH - $height) / 2);
		$css = "width: ".$width."px; height: ".$height."px; padding-top: ".$paddingTop."px; padding-left: ".$paddingLeft."px;";
	}
?>
<a href="javascript: void(0);" class="thumbnail-selection" data-id="<?php echo $file->getId(); ?>" data-name="<?php echo $file; ?>">
	<img src="<?php echo $relative; ?>" style="<?php echo $css; ?>" />
	<span class="remove-thumb" data-id="<?php echo $file->getId(); ?>"><i class="icon-remove-circle"></i></span>
</a>