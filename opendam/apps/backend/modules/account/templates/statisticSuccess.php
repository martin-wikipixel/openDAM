<div id="account-statistic-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@account"), "text" => "<i class='icon-user icon-large'></i>"." ".__("Account")),
			array("link" => path("@account_statistic"), "text" => __("My statistics")),
		));
	?>
	
	<?php include_partial("account/tab", array("selected" => "statistic"));?>
	
	<div class="row">
		<div class="span2">
			<?php echo __("Used disk space")?>
		</div>

		<div class="progress span3">
			<div class="bar" style="width: <?php echo $progressWidth * 100;?>%;"></div>
			<span class="text"><?php echo MyTools::getSize($usedSpace)." / ".($available ? MyTools::getSize($available) : __("unlimited"))." ".__("used"); ?></span>
		</div>
	</div>

	<div class="row">
		<div class="span2">
			<?php echo __("Upload traffic")?>
		</div>
		<div class="span10">
			<?php echo MyTools::getSize($stats["upload"]); ?>
		</div>
	</div>

	<div class="row">
		<div class="span2">
			<?php echo __("Stored pictures")?>
		</div>
		<div class="span10">
			<?php echo MyTools::getSize($stats["nb"]); ?>
		</div>
	</div>

	<div class="row">
		<div class="span2">
			<?php echo __("Average size of picture")?>
		</div>
		<div class="span10">
			<?php echo MyTools::getSize(round($stats["size"] / $stats["nb"], 2)); ?>
		</div>
	</div>
	
	<div class="row">
		<div class="span2">
			<?php echo __("Most active in the group")?>
		</div>
		<div class="span10">
			<?php echo html_entity_decode($stats["group"]); ?>
		</div>
	</div>
	<!--  
	<table>
		<tr>
			<td><?php echo __("Download traffic")?> : <?php echo MyTools::getSize($stats["download"]); ?></td>
		</tr>
	</table>
-->

</div>