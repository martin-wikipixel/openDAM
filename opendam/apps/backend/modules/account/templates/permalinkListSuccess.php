<div id="account-permalink-list-page" class="span12">
	<?php
		$host  = $_SERVER['SERVER_NAME'];
	
		draw_breadcrumb(array(
			array("link" => path("@account"), "text" => "<i class='icon-user icon-large'></i>"." ".__("Account")),
			array("link" => path("@account_permalink"), "text" => __("My permalinks")),
		));
	?>

	<?php include_partial("account/tab", array("selected" => "permalink"));?>

	<table class="table">
		<thead>
			<tr>
				<th><?php echo __("File")." / ".__("Folder"); ?></th>
				<th><?php echo __("Format"); ?></th>
				<th><?php echo __("Permalinks"); ?></th>
				<th><?php echo __("Actions"); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if (!count($permalinks)):?>
				<tr>
					<td colspan="4">
						<?php echo __("No permalink found.")?>
					</td>
				</tr>
			<?php else:?>
				<?php foreach ($permalinks as $permalink): ?>
					<?php switch ($permalink->getObjectType()):
						case PermalinkPeer::__OBJECT_FILE:
							$file = FilePeer::retrieveByPK($permalink->getObjectId()); ?>
							<?php if ($file && $file->getState() == FilePeer::__STATE_VALIDATE) : ?>
								<tr>
									<td>
										<img alt="" src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "100")); ?>" class="img-size" />
										<br/>
										<?php echo $file->getName();?>
									</td>
									<td><?php echo $permalink->getType() == 1 ?__('Original'):__('Web') ?></td>
	
									<td>
										<?php echo __("Permalink"); ?> : 
										<a class="premalink_link" href="<?php echo url("permalink_show", array("link" => $permalink->getLink()));?>" target="_blank">
											<?php echo url("permalink_show", array("link" => $permalink->getLink()));?>
										</a>
										<br />
											
										<?php echo __("Permalink"); ?> (<?php echo __("integrated version"); ?>) : <input type="text" class="integrated" value="&lt;img src='<?php echo url("permalink_show", array("link" => $permalink->getLink()));?>' />" />
	
										<?php if ($sf_user->haveAccessModule(ModulePeer::__MOD_QR_CODE) && file_exists(sfConfig::get("app_path_qrcode_dir")."/".$permalink->getQrcode().".png")): ?>
											<br />
											<?php echo __("Permalink (QR code)"); ?> :
											<img src='<?php echo "/".sfConfig::get("app_path_qrcode_dir_name")."/".$permalink->getQrcode().".png"; ?>' style='vertical-align: -65px;' />
										<?php endif; ?>
									</td>
									<td>
										<a class="btn btn-danger" data-action="delete" href="<?php echo path("@account_permalink_delete", array("id" => $permalink->getId()));?>">
											<i class="icon-trash"></i> <?php echo __("Delete")?>
										</a>
									</td>
								</tr>
							<?php endif; ?>
						<?php break;
						case PermalinkPeer::__OBJECT_FOLDER:
							$folder = FolderPeer::retrieveByPK($permalink->getObjectId()); ?>
							<?php if ($folder && $folder->getState() == FolderPeer::__STATE_ACTIVE) : ?>
								<tr>
									<td>
										<img src="<?php echo image_path("no-access-file-100x100.png"); ?>" />
										<br/>
										<?php echo $folder->getName();?>
									</td>
									<td><?php echo $permalink->getType() == 1?__('Original'):__('Web') ?></td>
									<td>
										<?php echo __("Permalink"); ?> : <a class="premalink_link" href="<?php echo url("permalink_show", array("link" => $permalink->getLink()));?>" target="_blank">
											<?php echo url("permalink_show", array("link" => $permalink->getLink()));?>
										</a>
										<br />
										<?php echo __("Permalink"); ?> (<?php echo __("integrated version"); ?>) : <input type="text" class="integrated" value="&lt;img src='<?php echo url("permalink_show", array("link" => $permalink->getLink()));?>' />" />
										
										<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_QR_CODE)): ?>
											<br />
											<?php echo __("Permalink (QR code)"); ?> : 
											<img src='<?php echo "/".sfConfig::get("app_path_qrcode_dir_name")."/".$permalink->getQrcode().".png"; ?>' style='vertical-align: -65px;' />
										<?php endif; ?>
									</td>
									
									<td>
										<a class="btn btn-danger" data-action="delete" href="<?php echo path("@account_permalink_delete", array("id" => $permalink->getId()));?>">
											<i class="icon-trash"></i> <?php echo __("Delete")?>
										</a>
									</td>
								</tr>
							<?php endif; ?>
					<?php endswitch; ?>
				<?php endforeach; ?>
			<?php endif;?>
		</tbody>
	</table>
	<?php echo pagination($permalinks, "@account_permalink");?>
</div>