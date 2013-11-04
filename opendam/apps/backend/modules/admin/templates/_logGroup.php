<?php if($type == "html") : ?>
	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("Group"); ?></th>
				<th><?php echo __("Used disk space"); ?></th>
				<th><?php echo __("Number of folders"); ?></th>
				<th><?php echo __("Number of files"); ?></th>
				<th><?php echo __("Average size file"); ?></th>
				<th><?php echo __("Average size folder"); ?></th>
				<th><?php echo __("Average number of files by folder"); ?></th>
				<th><?php echo __("Upload traffic"); ?></th>
				<th><?php echo __("Download traffic"); ?></th>
				<th><?php echo __("Number of views"); ?></th>
				<th><?php echo __("Number of views (unique)"); ?></th>
				<th><?php echo __("Number of users"); ?></th>
			</tr>
		</thead>
<?php else : ?>
	<?php echo __("Group"); ?>;<?php echo __("Used disk space"); ?>;<?php echo __("Number of folders"); ?>;<?php echo __("Number of files"); ?>;<?php echo __("Average size file"); ?>;<?php echo __("Average size folder"); ?>;<?php echo __("Average number of files by folder"); ?>;<?php echo __("Upload traffic"); ?>;<?php echo __("Download traffic"); ?>;<?php echo __("Number of views"); ?>;<?php echo __("Number of views (unique)"); ?>;<?php echo __("Number of users"); ?>
<?php endif; ?>

<?php if($type == "html") : ?>
	<tbody>
<?php endif?>

	<?php foreach($groups as $group) : ?>
		<?php include_component("admin", "logCustomer", array("group" => $group, "year" => $year, "month" => $month, "type" => $type)); ?>
	<?php endforeach; ?>

<?php if($type == "html") : ?>
	</tbody>
</table>
<?php endif; ?>