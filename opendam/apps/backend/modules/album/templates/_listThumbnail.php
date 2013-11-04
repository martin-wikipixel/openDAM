		<?php foreach ($albums as $album):?><!-- suppression des whitespace du display: inline-block
			--><div class="thumbnail" data-album-id="<?php echo $album->getId()?>">
				<div class="thumbnail-actions">
						<a role="button" class="btn btn-primary" 
							href="<?php echo path("@group_right_user_list", array("album" => $album->getId())); ?>"
							data-toggle="tooltip" 
							title="<?php echo __("Access management")."\n".__("This section allows you to invite users and manage permissions to read and write."); ?>"
							data-toogle="modal-iframe"
							><i class="icon-lock"></i>
						</a>

						<button role="button" data-action="share" class="btn btn-primary"
							data-toggle="tooltip" 
							title="<?php echo __("Share this album"); ?>"
							>
							<i class="icon-share"></i>
						</button>
				</div>

				<div class="image">
					<a title="<?php echo __("Open");?>" href="<?php echo path("@album_show", array("id" => $album->getId()));?>">
						<img alt="" src="<?php echo path("album_generate_thumbnail", 
								array("album" => $album->getId(), "max-width" => "285px", "max-height" => "215px"))?>">
					</a>
				</div>

				<div class="thumbnail-capton">
					<div class="title">
						<i class="icon-book"></i> 
						<?php echo $album->getName();?>
					</div>
	
					<div class="infos">
						<?php
							$files = $album->getNumberOfFiles();
							echo $files.($files > 1 ? " ".__("files") : " ".__("file"));
	
							if ($files > 0) {
								echo " | ".MyTools::getSize($album->getSize());
							}
						?>
					</div>
				</div>
			</div><!--
		--><?php endforeach;?>