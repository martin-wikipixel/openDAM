<?php

class folderComponents extends sfComponents
{
	/*________________________________________________________________________________________________________________*/
	public function executeSpeedStep()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	
		$this->folder_selected = 0;
		$this->group_selected = $this->group_id;
	
		$this->subfolders = null;
		//$this->groups = GroupePeer::getGroupsInArray($this->getUser()->getId());
	
		if($this->subfolder)
		{
			//$folder = FolderPeer::retrieveByPk($this->subfolder);
			//$this->subfolders = array(__("Root's folder group")) + FolderPeer::getAllPathFolder($folder->getGroupeId());
			$this->folder_selected = $this->subfolder;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDetailsUser()
	{
		if($this->getRequestParameter("is_search"))
			$this->getUser()->setAttribute("folder_user_right_keyword", $this->getRequestParameter("keyword"));
	
		if($this->getRequestParameter("role"))
			$this->getUser()->setAttribute("folder_user_right_role", $this->getRequestParameter("role"));
	
		$this->roleGroup = $this->getUser()->getRole($this->folder->getGroupeId());
		$this->roleFolder = $this->getUser()->getRole($this->folder->getGroupeId(), $this->folder->getId());
		$this->roles = array(RolePeer::__ADMIN =>__("Administration"), RolePeer::__CONTRIB => __("Writing"), 
				RolePeer::__READER => __("Reading"));
		$this->role = $this->getUser()->getAttribute("folder_user_right_role");
	
		$users = new Criteria();
		$users->add(UserFolderPeer::FOLDER_ID, $this->folder->getId());
		$users->add(UserFolderPeer::USER_ID, $this->getUser()->getAttribute("folder_user_right_keyword"), Criteria::LIKE);
		
		if(!empty($this->role) && $this->role != "all")
			$users->add(UserFolderPeer::ROLE, $this->role);
		else
			$users->add(UserFolderPeer::ROLE, "");
		
		$user_pager = new sfPropelPager('UserFolder', 10);
		$user_pager->setCriteria($users);
		$user_pager->setPage($this->getRequestParameter("page", 1));
		$user_pager->setPeerMethod('getAllUsers');
		$user_pager->setPeerCountMethod('getCountAllUsers');
		$user_pager->init();
	
		$this->pager = $user_pager;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDnd()
	{
		$key = new UniqueKey();
		$key->setUserId($this->getUser()->getId());
		$key->setCreatedAt(time());
		$key->setExpiredAt(time());
		$key->setIp(@$_SERVER['REMOTE_ADDR']);
		$key->setUri(@$_SERVER['REQUEST_URI']);
		$key->setReferer(@$_SERVER['REFERER']);
	
		$key->save();
	
		$this->key_id = $key->getId();
	}

	/*________________________________________________________________________________________________________________*/
	public function executePublicComment()
	{
		$this->comments = PermalinkCommentPeer::retrieveByPermalinkId($this->permalink->getId());
	}

	/*________________________________________________________________________________________________________________*/
	/*public function executePublicSignup()
	{
		$c = new Criteria();
		$c->addJoin(CountryI18nPeer::ID, CountryPeer::ID);
		$c->add(CountryI18nPeer::CULTURE, $this->getUser()->getCulture());
		$c->addAscendingOrderByColumn(CountryI18nPeer::TITLE);
	
		$default = CountryPeer::retrieveByTitle("France");
		$this->countrys = CountryPeer::doSelect($c);
		$this->countryDefault = $default->getId();
		$this->prefix = $default->getPhoneCode();
	}*/

	/*________________________________________________________________________________________________________________*/
	public function executeSidebar()
	{
		$this->tags =$this->folder->getTagsInside(20, 0);
		$this->creationDateRange = FilePeer::getDateRange($this->folder->getGroupeId(), $this->folder->getId());
		$this->shootingDateRange = FilePeer::getShootingDateRange($this->folder->getId());
		$this->sizeRange = FilePeer::getSizeRange($this->folder->getId());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLineDetailsUserFolder()
	{
		$this->group = GroupePeer::retrieveByPk($this->folder->getGroupeId());
		$this->roles = array(RolePeer::__ADMIN =>__("Administration"), RolePeer::__CONTRIB => __("Writing"), RolePeer::__READER => __("Reading"));
	}
}