<?php $files = $files->getRawValue(); ?>
<?php $count = 0; ?>

<?php foreach($files as $file) : ?>
	<?php $dimension = getimagesize($file->getThumb100Pathname()); ?>
	<?php $count++; ?>
	<?php $merge = "margin-top: ".floor((100 - $dimension[1]) / 2)."px;"; ?>
	<div>
		<a href="javascript: void(0);" class="thumb_files" rel="<?php echo $file->getId(); ?>">
			<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "100")); ?>" style="z-index:0; <?php echo $merge; ?>" />
		</a>
	</div>
	
<?php endforeach; ?>

<?php if(sizeof($files) == 0 || $count == 0) : ?>
	<p style="width: 100%; text-align: center;" class="require_field"><?php echo __("No photo available."); ?></p>
<?php else : ?>
<script>
	jQuery(document).ready(function() {
		var tab = mediaSelected.split(",");

		for(var i = 0; i < tab.length; i++)
		{
			if(tab[i] != "")
				jQuery('.files-thumb div a[rel="' + tab[i] + '"]').parent().addClass("selected");
		}

		jQuery(".files-thumb div a").bind("click", function() {
			if(jQuery(this).parent().hasClass("selected"))
			{
				deleteMediaSelected(jQuery(this).attr("rel"));
				jQuery(this).parent().removeClass("selected", 300);
			}
			else
			{
				mediaSelected += jQuery(this).attr("rel") + ",";
				jQuery(this).parent().addClass("selected", 300);
			}
		});
	});

	function deleteMediaSelected(id)
	{
		var tab = mediaSelected.split(",");
		var temp = "";

		for(var i = 0; i < tab.length; i++)
		{
			if(tab[i] != id)
				temp += tab[i] + ",";
		}

		mediaSelected = temp;
	}
</script>
<?php endif; ?>