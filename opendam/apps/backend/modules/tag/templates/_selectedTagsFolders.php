<?php if(get_class($folders_tags) == "sfOutputEscaperArrayDecorator") $folders_tags = $folders_tags->getRawValue(); ?>

<?php $text = ""; ?>

<?php if(is_array($folders_tags)) : ?>
	<span class="list_tags">
		<?php foreach ($folders_tags as $folders_tag):?>
			<a href='javascript: void(0);' onclick="deleteTag(this);"><span><?php echo $folders_tag->getTag(); ?></span><em id=""></em></a>
			<?php $text .= $folders_tag->getTag()."|"; ?>
		<?php endforeach;?>
	</span>
<?php else : ?>
	<?php $folders_tags = explode("|", $folders_tags); ?>
	<span class="list_tags">
		<?php foreach ($folders_tags as $folders_tag):?>
			<?php if(!empty($folders_tag)) : ?>
				<a href='javascript: void(0);' onclick="deleteTag(this);"><span><?php echo $folders_tag; ?></span><em id=""></em></a>
				<?php $text .= $folders_tag."|"; ?>
			<?php endif; ?>
		<?php endforeach;?>
	</span>
<?php endif; ?>
<script>
	jQuery(document).ready(function() {
		jQuery("#tags_input").val("<?php echo $text; ?>");
	});
</script>