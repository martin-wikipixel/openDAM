<?php

class uploadComponents extends sfComponents
{
	/*________________________________________________________________________________________________________________*/
	public function executeSelectFolder()
	{
		$this->url = null;
		$params = Array();
	
		foreach(sfContext::getInstance()->getRequest()->getParameterHolder()->getAll() as $key => $value)
			$params[$key] = $value;
	
		$group = $this->folder->getGroupe();
		$this->folders = FolderPeer::getUploadFoldersPath($group->getId(), $this->getUser()->getId());
		$this->folderId = $this->folder->getId();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSelectGroupFolder()
	{
		$this->url = null;
		$params = Array();
	
		foreach(sfContext::getInstance()->getRequest()->getParameterHolder()->getAll() as $key => $value)
			$params[$key] = $value;
	
		$group = $this->folder->getGroupe();
	
		$this->groups = GroupePeer::getUploadGroups($this->getUser()->getId());
	
		$this->folders = FolderPeer::getUploadFoldersPath($group->getId(), $this->getUser()->getId());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeSidebar()
	{
		$params = Array();
		foreach($this->getRequest()->getParameterHolder()->getAll() as $key => $value)
		{
			if(!in_array($key, Array('module', 'action')))
				$params[] = $key."=".$value;
		}
		
		$this->links = Array();
		$this->links["single"] = url_for("upload/uploadify?".implode("&", $params)."&provider=single");
		$this->links["bulk"] = url_for("upload/uploadify?".implode("&", $params)."&provider=bulk");
		
	}
}