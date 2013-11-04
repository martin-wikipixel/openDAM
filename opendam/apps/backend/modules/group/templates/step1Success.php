<?php 
if($group->isNew() || $sf_params->get("navigation") == "create"){
  include_partial("group/navigationCreate", array("selected"=>"step1", "group"=>$group));
}else{
  include_partial("group/navigationManage", array("selected"=>"step1", "group"=>$group));
}
?>
<?php include_partial("group/subMenubarInformations", array("selected" => "step1", "group" => $group)); ?>

<div id="searchResults-popup">
	<div class="inner">
		<form name='step1_form' id='step1_form' class='form' action='<?php echo url_for("group/step1"); ?>' enctype='multipart/form-data' method='post'>
			<?php echo $form['_csrf_token']->render(); ?>

			<?php echo $form['id']->render(); ?>
			<?php echo $form['redirect']->render(); ?>

			<label for="data_name"><?php echo __("Group name")?> :</label>
			<?php echo $form['name']->render(); ?>
			<span class="description" style="width: 15px;"><span class='require_field'>*</span></span>

			<br clear="all">

			<label for="data_description"><?php echo __("Description")?> :</label>
			<?php echo $form['description']->render(); ?>
			<span class="description" style="width: 15px;"><span class='require_field'>*</span></span>


			<br clear="all">

			<br clear="all"><span class="description"><span class='require_field'>*&nbsp;<?php echo __("Required field"); ?></span></span><br clear="all">

			<div class="right">
				<?php if($sf_params->get("navigation") == "create"):?>
					<a href="#" onclick="window.parent.closeFacebox(); window.parent.location='<?php echo urlfor_("group/cancelCreatingGroup?id=".$group->getId()); ?>'" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
				<?php else: ?>
					<a href="#" onclick="window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
				<?php endif; ?>

				<?php if($group->isNew() || ($sf_params->get("navigation") == "create")):?>

						<a href="#" onclick="jQuery('#data_redirect').val(1); jQuery('#step1_form').submit(); " class="button btnBS"><span><?php echo __("NEXT STEP")?></span></a>

				<?php endif;?> 

				<?php if(!$group->isNew() && $sf_params->get("navigation") != "create"):?>

						<a href="#" onclick="jQuery('#data_redirect').val(2); jQuery('#step1_form').submit();" class="button btnBS"><span><?php echo __("SAVE CHANGES")?></span></a>

				<?php endif;?>
			</div>
			<br clear="all">
		</form>
	</div>
</div>