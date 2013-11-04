<?php foreach($tags as $tag) : ?>
	<a href='javascript: void(0);' class="thesaurus-tag" id="tag_thesaurus_<?php echo $tag->getId(); ?>"><span style="padding-right: 5px;"><?php echo $tag->getTitle(); ?> <i class="icon-plus-sign"></i></span></a>
<?php endforeach; ?>