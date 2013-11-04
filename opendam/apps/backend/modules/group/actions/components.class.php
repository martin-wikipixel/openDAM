<?php

class groupComponents extends sfComponents
{
	/*________________________________________________________________________________________________________________*/
	public function executeListFields()
	{
		$this->group = GroupePeer::retrieveByPk($this->group_id);
		$this->fields = FieldPeer::retrieveByGroupId($this->group->getId());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeListRequireFields()
	{
		$this->group = GroupePeer::retrieveByPk($this->group_id);

		$this->fields = GroupeRequireFieldPeer::retrieveByGroupeId($this->group->getId());
		$this->labels = GroupeRequireFieldPeer::getAllFields();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeHome()
	{
		if ($this->getUser()->isAdmin()) {
			$firstAlbum = GroupePeer::getFirstAlbumOfCustomer($this->getUser()->getCustomerId());
			
			// création d'un album "Album1"
			if (!$firstAlbum) {
				$firstAlbum = new Groupe();
				
				$firstAlbum->setName("Album1");
				$firstAlbum->setCustomerId($this->getUser()->getCustomerId());
				$firstAlbum->setUserId($this->getUser()->getId());
				
				$firstAlbum->save();
			}
			
			if ($firstAlbum) {
				$folder = $firstAlbum->getFirstFolder();//TODO a refaire

				// création d'un dossier "Folder1"
				if (!$folder) {
					$folder = new Folder();
					
					$folder->setName("Folder1");
					$folder->setGroupe($firstAlbum);
					$folder->setUserId($this->getUser()->getId());

					$folder->save();
				}
				
				if ($folder) {
					$this->getResponse()->setSlot("link_upload", "upload/uploadify?folder_id=".$folder->getId());
				}
			}
		}

		$preferences = $this->getUser()->getPreferences("group/home", true, array("sort" => "activity_desc", "perPage" => 8));
		$temp = GroupePeer::getHomeGroups($preferences["perPage"], 0, $preferences["sort"]);

		$this->groups = $temp["groups"];
		$this->count = $temp["count"];
		$this->itemsToShow = $preferences["perPage"];
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSidebar()
	{
		$this->tags =  $this->group->getTagsInside(20, 0);
		$this->dateRange = FolderPeer::getDateRange($this->group->getId());
	}
}
