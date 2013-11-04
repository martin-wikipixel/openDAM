<div class="row title-tree">
	<div class="span4">
		<h3><?php echo __("Explorer"); ?></h3>
	</div>
</div>
<div class="row content-tree">
	<div class="span4">
		<ul>
			<?php foreach ($albums as $album) : ?>
				<li>
					<a href="javascript: void(0);" class="album-tree"
							data-href="<?php echo path("@album_show", array("id" => $album->getId())); ?>"
							data-album-id="<?php echo $album->getId(); ?>">
						<i class="icon-book"></i> <?php echo $album->getName(); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</div>
<div class="row info-tree">
	<div class="span4">
		<p>
			<strong><?php echo __("Click:"); ?></strong> 
			<?php echo __("direct access to the selected album / folder."); ?><br />
			<strong><?php echo __("Double click:"); ?></strong> 
			<?php echo __("displays the contents of the selected album / folder."); ?>
		</p>
	</div>
</div>