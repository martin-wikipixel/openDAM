<?php if(is_object($groups_tags) && get_class($groups_tags) == "sfOutputEscaperArrayDecorator") : ?>
	<?php $groups_tags = $groups_tags->getRawValue(); ?>
<?php endif; ?>
	
<?php $text = ""; ?>

<?php if(is_array($groups_tags)) : ?>
	<span class="list_tags">
		<?php foreach ($groups_tags as $groups_tag):?>
			<a href='javascript: void(0);' onclick="deleteTag(this);"><span><?php echo $groups_tag->getTag(); ?></span><em id=""></em></a>
			<?php $text .= $groups_tag->getTag()."|"; ?>
		<?php endforeach;?>
	</span>
<?php else : ?>
	<?php $groups_tags = explode("|", $groups_tags); ?>
	<span class="list_tags">
		<?php foreach ($groups_tags as $groups_tag):?>
			<?php if(!empty($groups_tag)) : ?>
				<a href='javascript: void(0);' onclick="deleteTag(this);"><span><?php echo $groups_tag; ?></span><em id=""></em></a>
				<?php $text .= $groups_tag."|"; ?>
			<?php endif; ?>
		<?php endforeach;?>
	</span>
<?php endif; ?>
<script>
	jQuery(document).ready(function() {
		jQuery("#tags_input").val("<?php echo $text; ?>");
	});
</script>