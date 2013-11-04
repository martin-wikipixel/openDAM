<?php if(!$favorite_pager->getNbResults()):?>

  <div class="info" style="position: static; width: auto;"><?php echo __("No favorite item found.")?></div>
  
<?php else:?>
	<div class="left" id="container_of_favorites" style="width: 100%;">
		<?php foreach ($favorite_pager->getResults() as $favorite):?>
			<div id="item_<?php echo $favorite->getId()?>" class="favorite-item left">
				<?php
					switch($favorite->getObjectType())
					{
						case FavoritesPeer::__TYPE_FILE:
						{
							$file = FilePeer::retrieveByPK($favorite->getObjectId());

							if($file)
								include_partial("file/grid", array("file"=>$file));
						}
						break;

						case FavoritesPeer::__TYPE_FOLDER:
						{
							$folder = FolderPeer::retrieveByPK($favorite->getObjectId());

							if($folder)
								include_partial("folder/grid", array("folder"=>$folder));
						}
						break;

						case FavoritesPeer::__TYPE_GROUP:
						{
							$group = GroupePeer::retrieveByPkNoCustomer($favorite->getObjectId());

							if($group)
								include_partial("group/grid", array("group" => $group));
						}
						break;
					}
				?>
				<br clear="all">
			</div>
		<?php endforeach;?>  
	</div>
<?php endif;?>