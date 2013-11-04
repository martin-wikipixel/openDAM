<?php if($sf_user->isAdmin()):?>
	<li><a href="javascript: void(0);" id="add_main_folder_button"><i class="icon-plus-sign"></i> <?php echo __("Create a group"); ?></a></li>
<?php endif; ?>

<li class="pull-right">
	<div>
		<div class="custom-select">
			<?php echo __("Sort by"); ?>
			<select name="sort" id="sort">
				<?php foreach($sorts["values"] as $id => $label) :?>
					<option value="<?php echo $id; ?>" <?php echo $sorts["selected"] == $id ? "selected" : ""; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
	<div>
		<div class="custom-select">
			<?php echo __("Per page"); ?>
			<select name="per_page" id="per_page">
				<?php foreach($results["values"] as $id => $label) :?>
					<option value="<?php echo $id; ?>" <?php echo $results["selected"] == $id ? "selected" : ""; ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>
</li>
