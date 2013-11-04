<div id="public-selection-show-page" data-selection-code="<?php echo $basket->getCode()?>" data-max-page="<?php echo $maxPage?>">
	<div class="container">
		<div class="row">
			<div class="span5 title-folder">
				<h2><?php echo $basket->getName(); ?> <span class='content'><?php echo $allContents; ?> <i class='icon-picture'></i></span></h2>
			</div>
			<div class="span7 title-actions">
				<ul class="inline">
					<?php if ($basket->getAllowComments()):?>
						<li>
							<a href="javascript: void(0);" class="toogle-comments">
								<?php echo __("Comments"); ?> <i class="icon-comment"></i>
							</a>
						</li>
					<?php endif; ?>
					
					<li>
						<a data-toggle="modal" href="#download-modal" href="javascript:void(0);">
							<?php echo __("Download"); ?> <i class="icon-download-alt"></i>
						</a>
					</li>
					
					<li>
						<a href="javascript: void(0);" class="toogle-slideshow">
							<?php echo __("Slideshow"); ?> <i class="icon-play-circle"></i>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
	
	<div class="files">
		<div class="container">
			<div id="files">
				<?php foreach($contents as $content) : ?>
					<?php $file = $content->getFile(); ?>
					<?php include_partial("selection/publicFile", Array("file" => $file, "basket" => $basket)); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
	
	<?php if (empty($allContents)) : ?>
		<div class="container">
			<div class="row">
				<div class="span12">
					<?php echo __("No file found."); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	
	<?php if ($maxPage > 1) : ?>
		<div id="nav">
			<a href="<?php echo path("public_selection_show", array("code" => $basket->getCode(), "page" => ($page + 1)))?>">
				<?php echo ($page + 1); ?>
			</a>
		</div>
	<?php endif; ?>
	
	<!-- download modal -->
	<div id="download-modal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i></button>
			<h3><?php echo __("Download basket"); ?></h3>
		</div>
		<div class="modal-body">
			<p>
				<?php echo __("Number of files:"); ?> <?php echo $allContents; ?><br />
				<?php echo __("Size:"); ?> <?php echo myTools::getSize($filesSize); ?>
			</p>
		</div>
		<div class="modal-footer">
			<iframe name="download_frame" id="download_frame" class="hide"></iframe>
			<a id="download_basket" href="javascript: void(0);" class="btn-header"><span><?php echo __("Download basket"); ?></span></a>
		</div>
	</div>
</div>