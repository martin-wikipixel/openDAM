<div class="inner">
	<table style="width: 800px; margin: auto;">
		<tr>
			<td class="text" style="background-color: #eee"><?php echo __("Filename"); ?></td>
			<td class="text" style="background-color: #eee"><?php echo __("Size"); ?></td>
			<td class="text" style="background-color: #eee"><?php echo __("Download"); ?></td>
		</tr>
		<tr>
			<td width="30%" class="no-border text" style="padding: 4px;"><?php echo $file->getOriginal(); ?></td>
			<td width="15%" class="no-border text" style="padding: 4px;"><?php echo myTools::getSize($file->getSize()); ?></td>
			<td width="25%" class="no-border text" style="padding: 4px;"><a href="<?php echo url_for("permalink/download?link=".$link); ?>" class="but_admin"><span><?php echo __("Download"); ?></span></a></td>
		</tr>
	</table>

	<br clear="all" />
	<br clear="all" />

	<div align="center" id="<?php echo $file->getId()?>" class="file-div">
		<?php switch($file->getType()) :
			case FilePeer::__TYPE_VIDEO : ?>
				<?php $videoFormat = explode(";",ConfigurationPeer::retrieveByType("video_format_allowed")->getValue());
				if(in_array($file->getExtention(), $videoFormat)) : ?>
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
				<?php endif; ?>
			<?php break; ?>
			<?php case FilePeer::__TYPE_AUDIO : ?>
				<?php $audioFormat = explode(";",ConfigurationPeer::retrieveByType("audio_format_allowed")->getValue());
				if(in_array($file->getExtention(), $audioFormat)) : ?>
					<video id="player" class="projekktor" poster="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "poster")); ?>" title="<?php echo $file; ?>" width="640" height="385" controls>
						<?php if(file_exists(sfConfig::get("app_path_upload_dir")."/".$file->getDisk()->getPath()."/cust-".$file->getCustomerId()."/folder-".$file->getFolderId()."/".$file->getOriginal().".mp3")) : ?>
							<source src="<?php echo "/".sfConfig::get("app_path_upload_dir_name")."/".$file->getDisk()->getPath()."/cust-".$file->getCustomerId()."/folder-".$file->getFolderId()."/".$file->getOriginal().".mp3"; ?>" type="audio/mp3" />
						<?php else : ?>
							<source src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "original")); ?>" type="audio/mp3" />
						<?php endif; ?>
					</video>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							projekktor('#player', {
								plugin_display: {
									logoImage:"<?php echo image_path("video_player/player_logo.png"); ?>",
									logoURL:"<?php echo __("http://www.wikipixel.com/"); ?>",
									target:"_blank"
								}
							});
						});
					</script>
				<?php endif; ?>
			<?php break; ?>
			<?php case FilePeer::__TYPE_DOCUMENT : ?>
				<iframe src="<?php echo url_for("file/viewDocument?file_id=".$file->getId()); ?>" style="width: 760px; height: 640px; border: 0px;" frameborder="0"></iframe>
			<?php break; ?>
		<?php endswitch; ?>
	</div>
</div>