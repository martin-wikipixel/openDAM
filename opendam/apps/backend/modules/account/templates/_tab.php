<?php 
function isActive($selected, $name) {
	return $selected == $name ? "class='active'" : "";
}
?>

<ul class="nav nav-tabs">
	<li <?php echo isActive($selected, "edit")?>>
		<a href="<?php echo path("@account"); ?>"><?php echo __("Edit my profile"); ?></a> 
	</li>
	
	<?php if ($sf_user->haveAccessModule(ModulePeer::__MOD_REINIT_PASSWORD)): ?>
		<li <?php echo isActive($selected, "password")?>>
			<a href="<?php echo path("@account_password"); ?>"><?php echo __("Change my password"); ?></a>
		</li>
	<?php endif; ?>
	
	<li <?php echo isActive($selected, "permalink")?>>
		<a href="<?php echo path("@account_permalink"); ?>"><?php echo __("My permalinks"); ?></a>
	</li>
	
	<li <?php echo isActive($selected, "log")?>>
		<a href="<?php echo path("@account_log"); ?>"><?php echo __("My logs"); ?></a>
	</li>
	
	<li <?php echo isActive($selected, "statistic")?>>
		<a href="<?php echo path("@account_statistic"); ?>"><?php echo __("My statistics"); ?></a>
	</li>
	
	<!--  
	<li <?php echo isActive($selected, "module")?>>
		<a href="<?php echo path("@account_module_list"); ?>"><?php echo __("Modules"); ?></a>
	</li>
	-->
</ul>
