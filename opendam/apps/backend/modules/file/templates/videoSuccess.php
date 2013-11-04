<?php if($file->existsVideoMp4() && $file->existsVideoWebm()) : ?>
	<video class="video-js vjs-default-skin" controls preload="auto" width="640" height="385" poster="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "poster")); ?>" title="<?php echo $file; ?>" data-setup=''>
		<source src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "webm")); ?>" type="video/webm" />
		<source src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "mp4")); ?>" type="video/mp4" />
	</video>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			videojs.options.flash.swf = "/flash/videojs/video-js.swf";
		});
	</script>
<?php else: ?>
	<div class='no-player'>
		<div class='text'>
			<h4><?php echo __("Video encoding in progress"); ?>...</h4>
		</div>
		<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "poster")); ?>" style='height: 385px;' class="main" alt="<?php echo $file?>" />
	</div>
<?php endif; ?>