<?php  include_partial("group/navigationManage", array("selected" => "files", "group" => $group)); ?>
<div id="searchResults-popup">
	<div class="inner">
		<table style="width: 100%;">
			<tr>
				<td class="caption" colspan="6" align="right" style="padding: 3px;">
					<?php include_partial("search/form", array("keyword" => $keyword)); ?>
				</td>
			</tr>
			<tr>
				<td class="caption" colspan="6"><?php echo __("DISPLAY")?>
					<a style="<?php echo $state == FileWaitingPeer::__STATE_WAITING_VALIDATE ? "color: black" : ""; ?>" href="<?php echo url_for("group/waiting?state=".FileWaitingPeer::__STATE_WAITING_VALIDATE."&id=".$group->getId()); ?>"><?php echo "&nbsp;".__("FILES PENDING VALIDATION")."&nbsp; "?></a>  
					<a style="<?php echo $state == FileWaitingPeer::__STATE_WAITING_DELETE ? "color: black" : ""; ?>" href="<?php echo url_for("group/waiting?state=".FileWaitingPeer::__STATE_WAITING_DELETE."&id=".$group->getId()); ?>"><?php echo "&nbsp;".__("FILES PENDING DELETION");?></a>
				</td>
			</tr>
			<tr>
				<td class="text" style="background-color: #eee;">
					<?php echo __("Thumbnail"); ?>
				</td>
				<td class="text" style="background-color: #eee;">
					<?php
						$arrow = null;

						if($sort == "name_asc") {
							$sortCell = "name_desc";
							$arrow = "black-up.png";
						}
						elseif($sort == "name_desc") {
							$sortCell = "name_asc";
							$arrow = "black-down.png";
						}
						else {
							$sortCell = "name_asc";
						}

						if($arrow) : ?>
							<img src='<?php echo image_path("icons/arrow/".$arrow); ?>' style='vertical-align: middle;' />
						<?php endif;
					?>
					<a href='<?php echo url_for("group/waiting?id=".$group->getId()."&sort=".$sortCell); ?>'><?php echo __("Name"); ?></a>
				</td>
				<td class="text" style="background-color: #eee;">
					<?php
						$arrow = null;

						if($sort == "user_asc") {
							$sortCell = "user_desc";
							$arrow = "black-up.png";
						}
						elseif($sort == "user_desc") {
							$sortCell = "user_asc";
							$arrow = "black-down.png";
						}
						else {
							$sortCell = "user_asc";
						}

						if($arrow) : ?>
							<img src='<?php echo image_path("icons/arrow/".$arrow); ?>' style='vertical-align: middle;' />
						<?php endif;
					?>
					<a href='<?php echo url_for("group/waiting?id=".$group->getId()."&sort=".$sortCell); ?>'><?php echo __("Uploaded by"); ?></a>
				</td>
				<td class="text" style="background-color: #eee;">
					<?php
						$arrow = null;

						if($sort == "date_asc") {
							$sortCell = "date_asc";
							$arrow = "black-up.png";
						}
						elseif($sort == "date_desc") {
							$sortCell = "date_asc";
							$arrow = "black-down.png";
						}
						else {
							$sortCell = "date_asc";
						}

						if($arrow) : ?>
							<img src='<?php echo image_path("icons/arrow/".$arrow); ?>' style='vertical-align: middle;' />
						<?php endif;
					?>
					<a href='<?php echo url_for("group/waiting?id=".$group->getId()."&sort=".$sortCell); ?>'><?php echo __("Uploaded date"); ?></a>
				</td>
				<td class="text" style="background-color: #eee;">
					<?php echo __("Comment"); ?>
				</td>
				<td class="text" style="background-color: #eee;">
					<?php echo __("Actions"); ?>
				</td>
			</tr>
			<?php if(sizeof($pager->getResults()) == 0):?>
				<tr>
					<td class="no-border" colspan="6"><div class="info"><?php echo __("No file found.")?></div></td>
				</tr>
			<?php endif;?>

			<?php foreach ($pager->getResults() as $waitingFile): ?>
				<?php $file = $waitingFile->getFile(); ?>
				<tr class="admin_tab_border_bottom">
					<td class="no-border text">
						<?php if(file_exists($file->getPath()."/".$file->getThumb100())) : ?>
							<img src="<?php echo "/".$file->getPath(false)."/".$file->getThumb100(); ?>" />
						<?php else: ?>
							-
						<?php endif; ?>
					</td>
					<td class="no-border text"><a href="<?php echo url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId()); ?>" target="_blank"><?php echo $file->getName(); ?></a></td>
					<td class="no-border text"><?php echo $file->getUser()->getEmail(); ?></td>
					<td class="no-border text"><?php echo $file->getCreatedAt("d/m/Y"); ?></td>
					<td class="no-border text">
						<?php
							if ($waitingFile->getState() == FileWaitingPeer::__STATE_WAITING_DELETE) {
								echo nl2br($waitingFile->getCause());
							}
							else {
								echo "-";
							}
						?>
					</td>
					<td class="no-border text">
						<?php if ($waitingFile->getState() == FileWaitingPeer::__STATE_WAITING_DELETE) : ?>
							<a href="javascript: void(0);" class="but_admin accept-waiting" data-id="<?php echo $file->getId(); ?>" data-state="<?php echo FileWaitingPeer::__STATE_DELETE; ?>"><span><?php echo __("Delete")?></span></a>
							<a href="javascript: void(0);" class="but_admin deny-waiting" data-id="<?php echo $file->getId(); ?>" data-state="<?php echo FileWaitingPeer::__STATE_VALIDATE; ?>"><span><?php echo __("Cancel")?></span></a>
						<?php else: ?>
							<a href="javascript: void(0);" class="but_admin accept-waiting" data-id="<?php echo $file->getId(); ?>" data-state="<?php echo FileWaitingPeer::__STATE_VALIDATE; ?>"><span><?php echo __("Validate")?></span></a>
							<a href="javascript: void(0);" class="but_admin deny-waiting" data-id="<?php echo $file->getId(); ?>" data-state="<?php echo FileWaitingPeer::__STATE_DELETE; ?>"><span><?php echo __("Delete")?></span></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
</div>
<script>
jQuery(document).ready(function() {
	jQuery(".accept-waiting").on("click", function() {
		var self = jQuery(this);
		var state = self.data("state");
		var id = self.data("id");

		jQuery.post(
			"<?php echo url_for("file/accept"); ?>",
			{ id: id, type: state },
			function(data) {
				if (data.errorCode <= 0) {
					self.closest("tr").fadeOut(400, function() {
						jQuery(this).remove();
					});
				}
			},
			"json"
		);
	});

	jQuery(".deny-waiting").on("click", function() {
		var self = jQuery(this);
		var state = self.data("state");
		var id = self.data("id");

		jQuery.post(
			"<?php echo url_for("file/deny"); ?>",
			{ id: id, type: state },
			function(data) {
				if (data.errorCode <= 0) {
					self.closest("tr").fadeOut(400, function() {
						jQuery(this).remove();
					});
				}
			},
			"json"
		);
	});
});
</script>