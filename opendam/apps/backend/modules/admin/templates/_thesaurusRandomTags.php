<?php foreach ($tags as $tag): ?><button type="button" data-id="<?php echo $tag->getId()?>" class="btn btn-primary">
	<i class="icon-plus-sign"></i> <?php echo $tag->getTitle(); ?>
</button><?php endforeach; ?>