<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag('file/sendFileForm', array('id'=>'file_move_form', 'name'=>'file_move_form')) ?>
			<?php echo $form['_csrf_token']->render(); ?>
			<?php echo $form['file_id']->render(); ?>

			<label><?php echo __("Sender");?></label>
			<label style="width: 300px;"><?php echo $user->getEmail(); ?></label>

			<br clear="all" />

			<label for="data_receivers"><?php echo __("Receivers");?></label>
			<?php echo $form['receivers']->render(); ?>

			<br clear="all" />

			<label><?php echo __("File");?></label>
			<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "100")); ?>" style="z-index:0; float: left;"/>

			<br clear="all" />

			<label for="data_subject"><?php echo __("Subject");?></label>
			<?php echo $form['subject']->render(); ?>

			<br clear="all" />

			<label for="data_message"><?php echo __("Message");?></label>
			<?php echo $form['message']->render(); ?>
		</form>

		<br clear="all"/>
		<br clear="all"/>

		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CANCEL")?></span></a>
			<a href="#" onClick="jQuery('#file_move_form').submit();" class="button btnBS"><span><?php echo __("Send")?></span></a>
		</div>
	</div>
</div>
<script>
jQuery(document).ready(function() {
	jQuery("#data_receivers").bind("focus", function() {
		if(jQuery(this).val() == "<?php echo __("Write one or more email addresses separated by commas"); ?>")
		{
			jQuery(this).val("");
			jQuery(this).removeClass("nc");
		}
	});

	jQuery("#data_receivers").bind("blur", function() {
		if(jQuery(this).val() == "")
		{
			jQuery(this).val("<?php echo __("Write one or more email addresses separated by commas"); ?>");
			jQuery(this).addClass("nc");
		}
	});
});
</script>