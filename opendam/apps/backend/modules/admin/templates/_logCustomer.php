<?php if($type == "html") : ?>
	<tr class="admin_tab_border_bottom">
		<td class="no-border text"><?php echo $group; ?></td>
		<td class="no-border text"><?php echo empty($logs["used_space_disk"]) ? "-" : MyTools::getSize($logs["used_space_disk"]); ?></td>
		<td class="no-border text"><?php echo empty($logs["folders"]) ? "-" : $logs["folders"]; ?></td>
		<td class="no-border text"><?php echo empty($logs["files"]) ? "-" : $logs["files"]; ?></td>
		<td class="no-border text"><?php echo empty($logs["used_space_disk"]) || empty($logs["files"]) ? "-" :  MyTools::getSize(round($logs["used_space_disk"] / $logs["files"], 2)); ?></td>
		<td class="no-border text"><?php echo empty($logs["used_space_disk"]) || empty($logs["folders"]) ? "-" :  MyTools::getSize(round($logs["used_space_disk"] / $logs["folders"], 2)); ?></td>
		<td class="no-border text"><?php echo empty($logs["files"]) || empty($logs["folders"]) ? "-" :  round($logs["files"] / $logs["folders"], 2); ?></td>
		<td class="no-border text"><?php echo empty($logs["upload_traffic"]) ? "-" : MyTools::getSize($logs["upload_traffic"])."<br />".$logs["upload_traffic_files"]." ".__("files"); ?></td>
		<td class="no-border text"><?php echo empty($logs["download_traffic"]) ? "-" : MyTools::getSize($logs["download_traffic"])."<br />".$logs["download_traffic_files"]." ". __("files"); ?></td>
		<td class="no-border text"><?php echo empty($logs["views"]) ? "-" : $logs["views"]; ?></td>
		<td class="no-border text"><?php echo empty($logs["unique_views"]) ? "-" : $logs["unique_views"]; ?></td>
		<td class="no-border text"><?php echo $nb_users; ?></td>
	</tr>
<?php else: ?>
<?php echo $group; ?>;<?php echo empty($logs["used_space_disk"]) ? "-" : MyTools::getSize($logs["used_space_disk"]); ?>;<?php echo empty($logs["folders"]) ? "-" : $logs["folders"]; ?>;<?php echo empty($logs["files"]) ? "-" : $logs["files"]; ?>;<?php echo empty($logs["used_space_disk"]) || empty($logs["files"]) ? "-" :  MyTools::getSize(round($logs["used_space_disk"] / $logs["files"], 2)); ?>;<?php echo empty($logs["used_space_disk"]) || empty($logs["folders"]) ? "-" :  MyTools::getSize(round($logs["used_space_disk"] / $logs["folders"], 2)); ?>;<?php echo empty($logs["files"]) || empty($logs["folders"]) ? "-" :  round($logs["files"] / $logs["folders"], 2); ?>;<?php echo empty($logs["upload_traffic"]) ? "-" : MyTools::getSize($logs["upload_traffic"]).",".$logs["upload_traffic_files"]." ".__("files"); ?>;<?php echo empty($logs["download_traffic"]) ? "-" : MyTools::getSize($logs["download_traffic"]).",".$logs["download_traffic_files"]." ". __("files"); ?>;<?php echo empty($logs["views"]) ? "-" : $logs["views"]; ?>;<?php echo empty($logs["unique_views"]) ? "-" : $logs["unique_views"]; ?>;<?php echo $nb_users."\n"; ?>
<?php endif; ?>