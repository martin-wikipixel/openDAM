<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag("request/sendRequest", array("name"=>"sendRequestForm", "id"=>"sendRequestForm", "class"=>"form"))?>
			<?php echo $form['_csrf_token']->render(); ?>

			<label for="data_group_id" style="width:180px;"><?php echo __("Group")?> :</label>
			<?php echo $form['group_id']->render(); ?>

			<br clear="all">
			<br clear="all">

			<label for="data_message" style="width:180px;"><?php echo __("Message")?> :</label>
			<?php echo $form['message']->render(); ?>
			<span class="description" style="width:100px;"><span class='require_field'>*</span></span>
		</form>

		<br clear="all"><span class="description"><span class='require_field'>*&nbsp;<?php echo __("Required field"); ?></span></span><br clear="all">
		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
			<a href="#" onclick="jQuery('#sendRequestForm').submit();" class="button btnBS"><span><?php echo __("SEND")?></span></a>
		</div>
	</div>
</div>