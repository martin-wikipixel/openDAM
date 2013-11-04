<h2><?php echo __("Home info edit")?></h2>

<br clear="all">
<br clear="all">

<?php echo form_tag("public/infoEdit", array("name"=>"info_form", "id"=>"info_form", "class"=>"form"))?>
	<?php echo $form['_csrf_token']->render(); ?>

	<?php echo $form['content']->render(); ?>

	<br clear="all">
	<br clear="all">

	<label style="width: 300px;">
		<?php echo $form['is_active']->render(); ?>
		<?php echo __("Is shown on the homepage?"); ?>
	</label>

	<br clear="all">
	<br clear="all">

	<a href="<?php echo url_for("@homepage"); ?>" class="button btnBSG"><span><?php echo __("CANCEL")?></span></a>
	<a href="#" onclick="jQuery('#info_form').submit();" class="button btnBS"><span><?php echo __("SAVE")?></span></a>
</form>