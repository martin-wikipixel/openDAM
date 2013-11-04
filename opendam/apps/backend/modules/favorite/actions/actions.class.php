<?php

/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class favoriteActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	}

	/*_____________________________________________r___________________________________________________________________*/
	public function executeList()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
		$perPage = 8;

		$files = FavoritesPeer::getFavorites(Array(FavoritesPeer::__TYPE_FILE), $this->getUser()->getId(), $perPage, 0);
		$groupsAndFolders = FavoritesPeer::getFavorites(Array(FavoritesPeer::__TYPE_GROUP, 
				FavoritesPeer::__TYPE_FOLDER), $this->getUser()->getId(), $perPage, 0);

		$breadCrumbs = array();

		array_push($breadCrumbs, array(
						"link"		=> path("@homepage"),
						"label"		=> __("Groups")." (".GroupePeer::getCountHomeGroups().")"
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@selection_list"),
						"label"		=> __("Selections")
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@favorite_list"),
						"label"		=> __("Favorites"),
						"selected"	=> true
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@file_recent_list"),
						"label"		=> __("Recents")
				)
		);

		$this->getResponse()->setSlot('bread_crumbs', $breadCrumbs);
	
		$this->files = $files["favorites"];
		$this->countFiles = $files["count"];
		$this->groupsAndFolders = $groupsAndFolders["favorites"];
		$this->countGroupsAndFolders = $groupsAndFolders["count"];
		$this->getResponse()->setSlot("selectedActions", $this->getPartial("favorite/selectedActions"));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadFavoriteGroupsFolders()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$page = $this->getRequestParameter("page");
			$perPage = $this->getRequestParameter("onPage");

			$temp = FavoritesPeer::getFavorites(Array(FavoritesPeer::__TYPE_GROUP, FavoritesPeer::__TYPE_FOLDER), 
					$this->getUser()->getId(), $perPage, ($perPage * ($page - 1)));

			$return = Array();
			$return["groupsFolders"] = "";
			$return["rightclick"] = "";

			foreach($temp["groupsFolders"] as $favorite)
			{
				switch($favorite->getObjectType())
				{
					case FavoritesPeer::__TYPE_GROUP:
						$group = GroupePeer::retrieveByPkNoCustomer($favorite->getObjectId());

						$return["groupsFolders"] .= $this->getPartial("favorite/group", Array("group" => $group));

					break;

					case FavoritesPeer::__TYPE_FOLDER:
						$folder = FolderPeer::retrieveByPK($favorite->getObjectId());

						$return["groupsFolders"] .= $this->getPartial("favorite/folder", Array("folder" => $folder));

					break;
				}
			}

			$return["index"] = $temp["count"] > ($page * $perPage) ? ($page * $perPage) : 0;

			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode($return));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadFavoriteFiles()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$this->getResponse()->setContentType("application/json");
		
		$page = $this->getRequestParameter("page");
		$perPage = $this->getRequestParameter("onPage");

		$temp = FavoritesPeer::getFavorites(Array(FavoritesPeer::__TYPE_FILE), $this->getUser()->getId(), 
				$perPage, ($perPage * ($page - 1)));

		$return = Array();
		$return["files"] = "";
		$return["rightclick"] = "";

		foreach ($temp["favorites"] as $favorite) {
			$file = FilePeer::retrieveByPk($favorite->getObjectId());

			$return["files"] .= $this->getPartial("file/grid", Array("file" => $file));

		}

		$return["index"] = $temp["count"] > ($page * $perPage) ? ($page * $perPage) : 0;

		return $this->renderText(json_encode($return));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAdd()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}

		switch($this->getRequestParameter("type"))
		{
			case FavoritesPeer::__TYPE_GROUP:
				$this->forward404Unless($group = GroupePeer::retrieveByPK($this->getRequestParameter("id")));
				$this->forward404Unless($this->getUser()->getRole($group->getId()));

				if(!FavoritesPeer::getFavorite($group->getId(), FavoritesPeer::__TYPE_GROUP, $this->getUser()->getId()))
				{
					$favorite = new Favorites();
					
					$favorite->setObjectId($group->getId());
					$favorite->setObjectType(FavoritesPeer::__TYPE_GROUP);
					$favorite->setUserId($this->getUser()->getId());

					$favorite->save();
				}
			break;

			case FavoritesPeer::__TYPE_FOLDER:
				$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("id")));
				$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));

				if(!FavoritesPeer::getFavorite($folder->getId(), FavoritesPeer::__TYPE_FOLDER, $this->getUser()->getId()))
				{
					$favorite = new Favorites();
					
					$favorite->setObjectId($folder->getId());
					$favorite->setObjectType(FavoritesPeer::__TYPE_FOLDER);
					$favorite->setUserId($this->getUser()->getId());

					$favorite->save();
				}
			break;

			case FavoritesPeer::__TYPE_FILE:
				$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter('id')));
				$this->forward404Unless($this->getUser()->getRole($file->getGroupeId(), $file->getFolderId()));

				if(!FavoritesPeer::getFavorite($file->getId(), FavoritesPeer::__TYPE_FILE, $this->getUser()->getId()))
				{
					$favorite = new Favorites();

					$favorite->setObjectId($file->getId());
					$favorite->setObjectType(FavoritesPeer::__TYPE_FILE); // 2-file
					$favorite->setUserId($this->getUser()->getId());
					$favorite->save();
				}
			break;

			case "files":
				$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id")));
				$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));

				$file_ids = $this->getRequestParameter("file_ids");

				if(sizeof($file_ids) > 0)
				{
					foreach ($file_ids as $file_id)
					{
						if(!FavoritesPeer::getFavorite($file_id, FavoritesPeer::__TYPE_FILE, 
								$this->getUser()->getId()))
						{
							$favorite = new Favorites();
							$favorite->setObjectId($file_id);
							$favorite->setObjectType(FavoritesPeer::__TYPE_FILE);
							$favorite->setUserId($this->getUser()->getId());
							$favorite->save();
						}
					}
				}
			break;

			case "files2":
				$file_ids = $this->getRequestParameter("file_ids");

				if(sizeof($file_ids) > 0)
				{
					foreach ($file_ids as $file_id)
					{
						if(!FavoritesPeer::getFavorite($file_id, FavoritesPeer::__TYPE_FILE, 
								$this->getUser()->getId()))
						{
							$favorite = new Favorites();
							$favorite->setObjectId($file_id);
							$favorite->setObjectType(FavoritesPeer::__TYPE_FILE);
							$favorite->setUserId($this->getUser()->getId());
							$favorite->save();
						}
					}
				}
			break;
		}

		$this->getResponse()->setContentType("application/json");
		return $this->renderText(json_encode(Array("errorCode" => 0)));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDelete()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}

		switch($this->getRequestParameter("type"))
		{
			case FavoritesPeer::__TYPE_GROUP:
				$this->forward404Unless($group = GroupePeer::retrieveByPK($this->getRequestParameter("id")));

				$favorite = FavoritesPeer::getFavorite($group->getId(), FavoritesPeer::__TYPE_GROUP, 
						$this->getUser()->getId());

				if($favorite)
					$favorite->delete();
			break;

			case FavoritesPeer::__TYPE_FOLDER:
				$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("id")));

				$favorite = FavoritesPeer::getFavorite($folder->getId(), FavoritesPeer::__TYPE_FOLDER, 
						$this->getUser()->getId());

				if($favorite)
					$favorite->delete();
			break;

			case FavoritesPeer::__TYPE_FILE:
				$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter('id')));

				$favorite = FavoritesPeer::getFavorite($file->getId(), FavoritesPeer::__TYPE_FILE, 
						$this->getUser()->getId());

				if($favorite)
					$favorite->delete();
			break;

			case "files2":
				$file_ids = $this->getRequestParameter("file_ids");

				if(sizeof($file_ids) > 0)
				{
					foreach ($file_ids as $file_id)
					{
						$favorite = FavoritesPeer::getFavorite($file_id, FavoritesPeer::__TYPE_FILE, 
								$this->getUser()->getId());

						if($favorite)
							$favorite->delete();
					}
				}
			break;
		}

		$this->getResponse()->setContentType("application/json");
		return $this->renderText(json_encode(Array("errorCode" => 0)));
	}
}