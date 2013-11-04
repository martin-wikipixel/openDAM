<?php

/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class mapActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
		$this->getResponse()->setSlot('title', __("See on map"));
	}

	/*________________________________________________________________________________________________________________*/
	# LOAD MAP WITH FOLDERS
	public function executeFolder()
	{
		// checks
		$this->forward404Unless($group = GroupePeer::retrieveByPK($this->getRequestParameter("group_id")));
		$this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter("group_id"), "group"));
	
		//check user rights
		$this->forward404Unless(
			(UserGroupPeer::getRole($this->getUser()->getId(), $group->getId())) || // user has group rights
			$this->getUser()->isAdmin()
		);
	
		$folder_ids = FolderPeer::retrieveByGroupIdInArray($group->getId());
	
		if(!sizeof($folder_ids))
		{
			// no map
			$this->redirect("map/nomap?type=group");
		}
	
		$this->getUser()->setAttribute("folder_ids", $folder_ids);
	
		$this->bound = FolderPeer::getBounds($folder_ids);
	
		if(!$this->bound)
			$this->redirect("map/nomap?type=group");
	
		$this->type = "folder";
	
		$response = $this->getResponse();
	
		$this->setTemplate("map");
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	# LOAD MAP WITH SINGLE FILE
	public function executeSingleFile()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter('file_id')));
		$this->forward404Unless($this->getUser()->getRole($file->getGroupeId(), $file->getFolderId()));
	
		// load map
		$response = $this->getResponse();
	
		$this->file = $file;
	}

	/*________________________________________________________________________________________________________________*/
	# LOAD MAP WITH FILES
	public function executeFile()
	{
		$group_id = 0;

		if ($group_id = $this->getRequestParameter("group_id")) {
			$this->forward404Unless($group = GroupePeer::retrieveByPK($this->getRequestParameter("group_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($group->getId()));
			// $this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter("group_id"), "group"));
			$kind = "group";
		}
		else {
			$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id")));
			// $this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter("folder_id"), "folder"));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			$group_id = $folder->getGroupeId();
			$kind = "folder";
		}
	
		if($this->getRequestParameter("selected") == "true")
			 $kind = "file";

		//check user rights
		/* $this->forward404Unless(
			(UserGroupPeer::getRole($this->getUser()->getId(), $group_id)) || // user has group rights
			$this->getUser()->isAdmin()
		); */
	
		$file_ids = array();
	
		if ($this->getRequestParameter("group_id")) {
			$file_ids = FilePeer::checkMapFiles($group_id, 0);
		}
		else {
			$file_ids = $this->getRequestParameter("file_ids");
			
			if (!sizeof($file_ids)) {
				$file_ids = FilePeer::checkMapFiles(0, $folder->getId());
			}
		}
	
		$this->getUser()->setAttribute("file_ids", $file_ids);
	
		if (!sizeof($file_ids)) {
			// no map
			$this->redirect("map/nomap?type=".$kind);
		}
	
		$this->bound = FilePeer::getBounds($file_ids);
	
		if (empty($this->bound["max"]["lat"]) && empty($this->bound["max"]["long"]) 
			&& empty($this->bound["min"]["lat"]) && empty($this->bound["min"]["long"]))
			$this->redirect("map/nomap?type=".$kind);
	
		$this->type = "file";
	
		$this->setTemplate("map");
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	# SHOW FOLDERS ON MAP
	public function executeFolders()
	{
		$this->folders = FolderPeer::getMapFolders(
			0, 
			$this->getUser()->getAttribute("folder_ids"), 
			$this->getRequestParameter('southLat'),
			$this->getRequestParameter('northLat'),
			$this->getRequestParameter('southLng'),
			$this->getRequestParameter('northLng')
		);
		echo sizeof($this->folders)." ".__("folders.");
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	# SHOW FILES ON MAP
	public function executeFiles()
	{
		$this->files = FilePeer::getMapFiles(
			0, 
			$this->getUser()->getAttribute("file_ids"),
			$this->getRequestParameter('southLat'),
			$this->getRequestParameter('northLat'),
			$this->getRequestParameter('southLng'),
			$this->getRequestParameter('northLng')
		);
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeNomap()
	{
		$this->type = $this->getRequestParameter("type");
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSearchLocation()
	{
		$this->getResponse()->setContentType('application/json');
		
		$url = "http://nominatim.openstreetmap.org/search/?format=".$this->getRequestParameter("format").
			"&bounded=1&q=".$this->getRequestParameter("address");

		$content = file_get_contents($url);

		return $this->renderText($content);
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFilesSelected()
	{
		if ($this->getRequestParameter("selected") == "true")
			$kind = "file";

		$file_ids = $this->getRequestParameter("file_ids");
		$this->getUser()->setAttribute("file_ids", $file_ids);
	
		if (!sizeof($file_ids))
			$this->redirect("map/nomap?type=".$kind);
	
		$this->bound = FilePeer::getBounds($file_ids);

		if (empty($this->bound["max"]["lat"]) && empty($this->bound["max"]["long"]) 
			&& empty($this->bound["min"]["lat"]) && empty($this->bound["min"]["long"]))
			$this->redirect("map/nomap?type=".$kind);

		$this->type = $kind;

		$this->setTemplate("map");

		return sfView::SUCCESS;
	}
}