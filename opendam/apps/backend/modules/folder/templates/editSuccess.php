<?php 
if($folder->isNew() || $sf_params->get("navigation") == "create") {
  include_partial("folder/navigationCreate", array("selected"=>"edit", "folder"=>$folder));
}else{
  include_partial("folder/navigationManage", array("selected"=>"edit", "folder"=>$folder));
}?>

<?php include_partial("folder/subMenubarInformations", array("selected"=>"edit", "folder"=>$folder));?>

<div id="searchResults-popup">
	<div class="inner">
		<?php echo form_tag('folder/edit', array('name'=>'edit_form', 'id'=>'edit_form', "class"=>"form", 'multipart'=>true))?>
			<?php echo $form['group_id']->render(); ?>
			<?php if($subfolder) : ?>
				<?php echo $form['subfolder']->render(); ?>
			<?php else:?>
				<?php echo $form['subfolder2']->render(); ?>
			<?php endif; ?>

			<?php include_partial("folder/edit", array("selected"=>"edit", "folder"=>$folder, "form" => $form, "group_id" => $group_id, "subfolder" => $subfolder));?>  
		</form>
	</div>
</div>