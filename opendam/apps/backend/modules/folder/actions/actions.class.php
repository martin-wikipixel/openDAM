<?php
/**
 * folder actions.
 *
 * @package    media management
 * @subpackage folder
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 3335 2007-01-23 16:19:56Z fabien $
 */
class folderActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	}

	public function executeThumbnailShow(sfWebRequest $request)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Asset");
	
		$response = $this->getResponse();
		$buffer = 4096;
		$cacheDir = sfConfig::get("sf_cache_dir")."/thumbnail";
		$folderId = (int) $request->getParameter("id");
		$permalinkCode = $request->getParameter("link");

		if (!file_exists($cacheDir)) {
			@mkdir($cacheDir);
		}

		$folder = FolderPeer::retrieveByPK($folderId);

		$this->forward404Unless($folder);

		if ($this->getUser()->isAuthenticated()) {
			$albumRole = $this->getUser()->getRole($folder->getGroupeId());

			$this->forward404Unless($albumRole);

			$folderRole = $this->getUser()->getRole($folder->getGroupeId(), $folder->getId());

			$this->forward404Unless($folderRole);
		}
		else {
			$permalink = PermalinkPeer::getByLink($permalinkCode);

			$this->forward404Unless($permalink);

			switch ($permalink->getObjectType()) {
				case PermalinkPeer::__OBJECT_GROUP:
					if ($folder->getGroupeId() != $permalink->getObjectId()) {
						$this->forward404();
					}
				break;

				case PermalinkPeer::__OBJECT_FOLDER:
					if (!FolderPeer::isUnderFolder($folder->getId(), $permalink->getObjectId()) &&
							$folder->getId() != $permalink->getObjectId()) {
						$this->forward404();
					}
				break;
			}
		}

		if ($folder->exists() && is_file($folder->getRealPathname())) {
			$filePathnameToRead = $folder->getRealPathname();
			$contentType = $folder->getContentType();
		}
		else {
			$filePathnameToRead = image_path("no-access-file-200x200.png", true);
			$contentType = "image/png";
		}
	
		// set headers
		$response->setHttpHeader("Content-Type", $contentType);
	
		// send content (stream)
		$fd = fopen($filePathnameToRead, "rb");
	
		while (!feof($fd)) {
			$bytes = fread($fd, $buffer);
	
			if ($bytes !== false) {
				$response->setContent($bytes);
				$response->send();
			}
		}
	
		fclose($fd);
	
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	# SHOW FILES IN THIS FOLDER
	public function executeShow()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404If(!$roleGroup);
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));

		$group = $folder->getGroupe();

		$bread_crumbs = array("bread" => array(), "parameters" => $this->getPartial("folder/breadcrumbParameters", array("folder" => $folder)));
		$bread_crumbs["bread"][] = array("link" => url_for("@homepage"), "label" => __("Groups"));
		$bread_crumbs["bread"][] = array("link" => url_for("group/show?id=".$folder->getGroupeId()), "label" => $group->getName());
	
		FileTmpPeer::deleteByUserId($this->getUser()->getId(), $folder->getId());
		$this->getUser()->setAttribute("files_array", null);
	
		$selected_tag_ids = $this->getRequestParameter("selected_tag_ids") ? $this->getRequestParameter("selected_tag_ids") : Array();
		$selected_tag_ids = array_unique($selected_tag_ids);

		$bread = FolderPeer::getBreadCrumbNew($folder->getId());
	
		krsort($bread);
	
		foreach($bread as $case)
			$bread_crumbs["bread"][] = $case;
	
		$this->getResponse()->setSlot('bread_crumbs', $bread_crumbs);
	
		$sortTemp = null;
	
		if($this->getRequestParameter("sortTemp"))
			$sortTemp = $this->getRequestParameter("sortTemp");
	
		if($this->getRequestParameter("creation_min") && $this->getRequestParameter("creation_max"))
			$creationDateRange = Array("min" => $this->getRequestParameter("creation_min"), "max" => $this->getRequestParameter("creation_max"));
		else
			$creationDateRange = Array("min" => null, "max" => null);
	
		if($this->getRequestParameter("shooting_min") && $this->getRequestParameter("shooting_max"))
			$shootingDateRange = Array("min" => $this->getRequestParameter("shooting_min"), "max" => $this->getRequestParameter("shooting_max"));
		else
			$shootingDateRange = Array("min" => null, "max" => null);
	
		if($this->getRequestParameter("size_min") && $this->getRequestParameter("size_max"))
			$sizeRange = Array("min" => $this->getRequestParameter("size_min"), "max" => $this->getRequestParameter("size_max"));
		else
			$sizeRange = Array("min" => null, "max" => null);
	
		$perPage = 25;
	
		$preferences = $this->getUser()->getPreferences("folder/show", true, array("sort" => "creation_desc", "perPage" => 6));
		
		$files = FilePeer::getFilesInFolder(
			$folder->getId(),
			$selected_tag_ids,
			null,
			$this->getRequestParameter("added_by_me_input", 0),
			$creationDateRange,
			$shootingDateRange,
			$sizeRange,
			($sortTemp ? $sortTemp : $preferences["sort"]),
			$roleGroup <= RolePeer::__ADMIN ? array(FilePeer::__STATE_VALIDATE, FilePeer::__STATE_WAITING_VALIDATE) : array(FilePeer::__STATE_VALIDATE),
			$perPage,
			($perPage * ($this->getRequestParameter("page", 1) - 1))
		);
	
		$this->getUser()->setAttribute("params.files.folder.show", array(
			"tags" => $selected_tag_ids,
			"date" => $creationDateRange,
			"shooting" => $shootingDateRange,
			"size" => $sizeRange,
			"me" => $this->getRequestParameter("added_by_me_input", 0)
		));
	
		$folders = FolderPeer::getFoldersInGroup(
			$folder->getGroupeId(), 
			$selected_tag_ids, 
			null,
			$this->getRequestParameter("added_by_me_input", 0),
			$creationDateRange,
			($sortTemp ? $sortTemp : $preferences["sort"]),
			"N",
			$folder->getId(),
			$preferences["perPage"],
			0
		);
	
		$this->getUser()->setAttribute("params.folders.folder.show", array(
			"tags" => $selected_tag_ids,
			"date" => $creationDateRange,
			"me" => $this->getRequestParameter("added_by_me_input", 0)
		));
	
		if($roleGroup <= RolePeer::__CONTRIB)
			$this->getResponse()->setSlot("link_upload", "upload/uploadify?folder_id=".$folder->getId());
	
		$this->folder = $folder;
		$this->selected_tag_ids = $selected_tag_ids;
		$this->added_by_me_input = $this->getRequestParameter("added_by_me_input", 0);
		$this->creationDateRange = $creationDateRange;
		$this->shootingDateRange = $shootingDateRange;
		$this->sizeRange = $sizeRange;
		$this->folders = $folders["folders"];
		$this->count = $folders["count"];
		$this->files = $files["files"];
		$this->itemsToShow = $preferences["perPage"];
		$this->page = $this->getRequestParameter("page", 1);
		$this->paginateFiles = $files["count"] > ($perPage * $this->getRequestParameter("page", 1)) ? true : false;
		$this->getResponse()->setSlot("selectedActions",  $this->getPartial("folder/selectedActions", Array("folder" => $folder)));
		$this->getResponse()->setSlot("actions",  $this->getPartial("folder/breadcrumbActions", Array(
				"folder" => $folder,
				"results" => array("selected" => $preferences["perPage"], "values" => array(6 => 6, 12 => 12, 24 => 24, "all" => __("All"))),
				"sorts" => array("selected" => $preferences["sort"], "values" => array("name_asc" => __("Name ascending"), "name_desc" => __("Name descending"), "creation_asc" => __("Creation date ascending"), "creation_desc" => __("Creation date descending"), "activity_asc" => __("Last activity date ascending"), "activity_desc" => __("Last activity date descending")))
		)));
	}

	/*________________________________________________________________________________________________________________*/
	# EDIT
	public function executeEdit()
	{
		if($this->getRequestParameter('data'))
		{
			$data = $this->getRequestParameter('data');
			$id = array_key_exists("id", $data) ? $data["id"] : null;
			$group_id = array_key_exists("group_id", $data) ? $data["group_id"] : null;
			$inside = array_key_exists("inside", $data) ? $data["inside"] : null;
			$subfolder = array_key_exists("subfolder", $data) ? $data["subfolder"] : null;
		} else {
			$id = $this->getRequestParameter('id');
			$group_id = $this->getRequestParameter('group_id');
			$inside = $this->getRequestParameter('inside');
			$subfolder = $this->getRequestParameter('subfolder');
		}
	
		$this->forward404Unless($group = GroupePeer::retrieveByPk($group_id));
		$this->forward404Unless($folder = FolderPeer::retrieveByPK($id));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($group->getId()));
		$this->forward404Unless(
			($roleGroup < RolePeer::__ADMIN) ||
			($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
			($roleGroup == RolePeer::__CONTRIB && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) || $folder->getUserId() == $this->getUser()->getId())
		);
	
		$this->getResponse()->setSlot('title', $this->getRequestParameter("navigation") == "create" ? __("Create a folder") : __("Manage folder")." \"".$folder."\"");
	
		$user = UserPeer::retrieveByPk($this->getUser()->getId());
	
		$this->form = new FolderEditForm(
			array(
				'group_id' => $group->getId(),
				'subfolder' => $subfolder,
				'id' => $folder->getId(),
				'inside' => $inside,
				'redirect' => 0,
				'name' => $folder->getName(),
				'description' => $folder->getDescription()
			)
		);
	
		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if($this->form->isValid())
			{
				// save informations
				$folder->setGroupeId($this->form->getValue('group_id'));
				$folder->setName($this->form->getValue('name'));
				$folder->setDescription($this->form->getValue('description'));
	
				// save other
				if($this->form->getValue('subfolder2'))
					$sub = null;
				elseif($this->form->getValue('subfolder'))
					$sub = $this->form->getValue('subfolder');
				else
					$sub = $folder->getSubfolderId();
	
				$folder->setSubfolderId((!empty($sub) ? $sub : null));
				$folder->save();
	
				// set log
				LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-update", "2");
	
				$this->getUser()->setFlash("success", __("Folder information has updated successfully."), false);
	
				if($this->form->getValue("redirect"))
					$this->redirect(url_for("upload/uploadify?folder_id=".$folder->getId()."&navigation=create&mode=normal"));
	
				$this->uri = ($this->form->getValue("inside") || $this->form->getValue('subfolder')) ? url_for('folder/show?id='.$folder->getId()) : url_for('group/show?id='.$folder->getGroupeId());
				$this->setTemplate('thankyou');
			}
		}
	
		$this->folder = $folder;
		$this->subfolder = $subfolder;
		$this->group_id = $group_id;
	}

	/*________________________________________________________________________________________________________________*/
	public function validateEdit()
	{

	sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");

	if ($this->getRequest()->getMethod() == sfRequest::POST) {
		$valid = true;

		if ($this->getRequestParameter("author") 
			&& !UserPeer::retrieveByNames($this->getRequestParameter("author"))) {
			$this->getRequest()->setError("error", __("Author name is invalid. This named user have not found."), false);
			$valid = false;
		}
		
		return $valid;
	}

	return true;
	}

	/*________________________________________________________________________________________________________________*/
	# DELETE
	public function executeDelete()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
				($roleGroup < RolePeer::__ADMIN) ||
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
				($roleGroup == RolePeer::__CONTRIB && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) || $folder->getUserId() == $this->getUser()->getId())
		);

		$this->getResponse()->setSlot('title', __("Remove folder")." \"".$folder."\"");
	
		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$group_id = $folder->getGroupeId();
	
			try 
			{
				sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
				
				$folder->setState(FolderPeer::__STATE_DELETE);;
				$folder->save();
	
				LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-delete", "2");
	
				$c = new Criteria();
				$c->add(FilePeer::FOLDER_ID, $folder->getId());
				$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
	
				$files = FilePeer::doSelect($c);
	
				foreach($files as $file)
				{
					$file->setState(FilePeer::__STATE_DELETE);
					$file->setUpdatedAt(time());
	
					$file->save();
	
					LogPeer::setLog($this->getUser()->getId(), $file->getId(), "file-delete", "3");
				}
	
				FolderPeer::deleteArbo($folder->getId());
	
			}catch (Exception $e){}
	
			if($this->getRequestParameter('callback') == "none")
				return sfView::NONE;
	
			$this->getUser()->setFlash("success", __("The folder is deleted successfully."), false);
	
			$this->uri = 'group/show?id='.$group_id;
			$this->setTemplate('thankyou');
		}
	
		$this->folder = $folder;
	}

	/*________________________________________________________________________________________________________________*/
	# MOVE
	public function executeMove()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
				($roleGroup < RolePeer::__ADMIN) ||
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") 
				|| $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) 
				|| $folder->getUserId() == $this->getUser()->getId())) ||
				($roleGroup == RolePeer::__CONTRIB 
			&& ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, 
					RolePeer::__CONTRIB)) || $folder->getUserId() == $this->getUser()->getId())
		);

		$this->getResponse()->setSlot('title', __("Move folder")." \"".$folder."\"");
	
		if ($this->getRequest()->getMethod() == sfRequest::POST) {
			$folder->setGroupeId($this->getRequestParameter("group_id"));
			
			if($this->getRequestParameter("folder_id") != '')
				$folder->setSubfolderId($this->getRequestParameter("folder_id"));
			else
				$folder->setSubfolderId(null);
	
			$folder->save();
	
		$c = new Criteria();
		$c->add(FolderPeer::SUBFOLDER_ID, $folder->getId());
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$folders = FolderPeer::doSelect($c);
	
		foreach($folders as $folder_child)
			FolderPeer::moveSubfolder($folder_child->getId(), $this->getRequestParameter("group_id"));
	
		$files = FilePeer::retrieveByFolderId($folder->getId());
		
		foreach ($files as $file){
			$file->setGroupeId($this->getRequestParameter("group_id"));
			$file->save();
		}
	
		// set log
		LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-move", "2");
	
		$this->getUser()->setFlash("success", __("The folder is moved successfully."), false);
	
		$this->uri = "folder/show?id=".$folder->getId();
		$this->setTemplate('thankyou');
		}
	
		$this->folder = $folder;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRefreshListeFolderRight()
	{
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFolderFromGroup()
	{
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserRights()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) || ($roleGroup == RolePeer::__CONTRIB && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB) && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) || $folder->getUserId() == $this->getUser()->getId())));
	
		$this->getResponse()->setSlot('title', __("Manage access folder")." \"".$folder."\"");
	
		$this->folder = $folder;
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRevokeUser()
	{
		$this->forward404Unless($user = UserPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN 
				&& ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(), 
						ConstraintPeer::__UPDATE, RolePeer::__ADMIN) 
			|| $folder->getUserId() == $this->getUser()->getId())) 
			|| ($roleGroup == RolePeer::__CONTRIB && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB) 
			&& ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) 
					|| $folder->getUserId() == $this->getUser()->getId())));
	
		$perm = UserFolderPeer::retrieveByUserAndFolder($user->getId(), $folder->getId());
	
		if($perm)
			$perm->delete();
	
		$this->getResponse()->setContentType('application/json');
		return $this->renderText(json_encode(array("errorCode" => 0)));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeField()
	{
	if($this->getRequest()->isXmlHttpRequest())
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
		$this->forward404Unless($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) || ($roleGroup == RolePeer::__CONTRIB && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) || $folder->getUserId() == $this->getUser()->getId())));

		$value = $this->getRequestParameter('value');
		$field = $this->getRequestParameter('field');

		switch($field)
		{
			case "name":
				if(!empty($value))
					$folder->setName($value);
				else
					$value = $folder->getName();
			break;

			case "description":
				$folder->setDescription($value);

				$value = nl2br($value);
			break;
		}

		$folder->save();

		if(empty($value))
			return $this->renderText(__("Add a description."));

		return $this->renderText($value);
	}

	$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executePublicShow()
	{
	$link = $this->getRequestParameter('link');
	$this->permalink = PermalinkPeer::getByLink($link);

	if ($this->permalink) {
		$user = UserPeer::retrieveByPkNoCustomer($this->permalink->getUserId());
	
		if(ModulePeer::haveAccessModule(ModulePeer::__MOD_PERMALINK, $user->getCustomerId(), $user->getId()))
		{
			if(($this->permalink && $this->permalink->getState() == PermalinkPeer::__STATE_DISABLED) || (!$this->permalink))
				$this->redirect404();
	
			if($this->getUser()->isAuthenticated())
				$this->getUser()->setAttribute("custom_referer", url_for("folder/publicShow?link=".$this->permalink->getLink()."&pop=1"));
	
			if($this->permalink->getObjectType() == PermalinkPeer::__OBJECT_FOLDER)
			{
				if($this->permalink->getState() == PermalinkPeer::__STATE_PRIVATE && $this->getUser()->getAttribute("permalink-authenticated") != $this->permalink->getId())
				{
					$this->getRequest()->setParameter('id', $this->permalink->getId());
					$this->forward("folder", "authentication");
				}
	
				if(!$this->getRequestParameter("folder_id"))
					$this->forward404Unless($this->folder = FolderPeer::retrieveByPk($this->permalink->getObjectId()));
				else
					$this->forward404Unless($this->folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));

				$this->forward404Unless(GroupeConstraintPeer::isAllowedTo($this->folder->getGroupeId(), ConstraintPeer::__PERMALINK_FOLDER));

				if(!$this->folder->getFree())
					$this->forward404();
	
				PermalinkLogPeer::addLog($_SERVER["REMOTE_ADDR"], $this->permalink->getId(), PermalinkLogPeer::__PERMALINK);
	
				$bread = explode('|',FolderPeer::getBreadCrumbTxtPublic($this->folder->getId(), $this->permalink->getObjectId()));
				array_splice($bread, count($bread) - 1);
				krsort($bread);
				$this->breadcrumb = Array();
				foreach($bread as $case)
				{
					if(!empty($case))
					{
						$folder_bread = unserialize($case);
						$this->breadcrumb[] = $folder_bread;
					}
				}
	
				$this->customer = CustomerPeer::retrieveByPk($this->folder->getCustomerId());
	
				$c = new Criteria();
				$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
				$c->add(FolderPeer::SUBFOLDER_ID, $this->folder->getId());
				$c->add(FolderPeer::FREE, true);
				$c->addDescendingOrderByColumn(FolderPeer::NAME);
	
				$this->folders = FolderPeer::doSelect($c);
	
				$c = new Criteria();
				$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
				$c->add(FilePeer::FOLDER_ID, $this->folder->getId());
	
				$c1 = $c->getNewCriterion(FilePeer::USAGE_DISTRIBUTION_ID, UsageDistributionPeer::__UNAUTH, Criteria::NOT_EQUAL);
				$c2 = $c->getNewCriterion(FilePeer::USAGE_DISTRIBUTION_ID, null);
	
				$c1->addOr($c2);
				$c->add($c1);
	
				$c->addDescendingOrderByColumn(FilePeer::NAME);
				$c->addDescendingOrderByColumn(FilePeer::ORIGINAL);
	
				$this->files = new sfPropelPager('File', 20);
				$this->files->setCriteria($c);
				$this->files->setPage($this->getRequestParameter("page", 1));
				$this->files->setPeerMethod('doSelect');
				$this->files->init();
	
				$connection = Propel::getConnection();
	
				$query = "	SELECT sum(file.size) as size
							FROM file
							WHERE file.state = ".FilePeer::__STATE_VALIDATE."
							AND (file.usage_distribution_id != ".UsageDistributionPeer::__UNAUTH." OR file.usage_distribution_id IS NULL)
							AND file.folder_id = ".$this->folder->getId();
	
				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_ASSOC); 
				$result = $statement->fetchAll();
				$statement->closeCursor();
				$statement = null;
	
				$this->filesSize = $result[0]["size"];
	
				if($this->files->getLastPage() > 1)
				{
					if($this->getRequestParameter("page") > $this->files->getNextPage())
						$this->forward404();
				}
				else
				{
					if($this->getRequestParameter("page") >= 1)
						$this->forward404();
				}
	
				return sfView::SUCCESS;
			}
		}
		else
			$this->redirect404();
	}

	$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAuthentication()
	{
		if($this->getRequestParameter("data"))
		{
			$data = $this->getRequestParameter("data");
			$id = array_key_exists("id", $data) ? $data["id"] : null;
		} else
			$id = $this->getRequestParameter("id");

		$this->forward404Unless($permalink = PermalinkPeer::retrieveByPk($id));

		$this->form = new FolderAuthenticationForm(
			array(
				"id" => $id
			)
		);

		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);

			if($this->form->isValid())
			{
				$this->getUser()->setAttribute("permalink-authenticated", $id);

				$this->redirect("folder/publicShow?link=".$permalink->getLink());
			}
		}

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAddFolder()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');

			if($this->getRequestParameter("groupe_id"))
			{
				$group = GroupePeer::retrieveByPk($this->getRequestParameter("groupe_id"));
				$subfolder_id = null;
			}
			elseif($this->getRequestParameter("folder_id"))
			{
				$folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id"));
				$group = GroupePeer::retrieveByPk($folder->getGroupeId());
				$subfolder_id = $folder->getId();
			}

			$folder = new Folder();
			$folder->setState(FolderPeer::__STATE_ACTIVE);
			$folder->setGroupeId($group->getId());
			$folder->setName(__("New folder"));
			$folder->setDescription(__("New folder"));
			$folder->setUserId($this->getUser()->getId());

			if($subfolder_id)
				$folder->setSubfolderId($subfolder_id);

			$folder->save();

			// set log
			LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-create", "2");

			$array = array();
			$array["key"] = $folder->getId();
			$array["title"] = $folder->getName();
			$array["tooltip"] = $folder->getDescription();
			$array["isFolder"] = false;
			$array["isLazy"] = true;
			$array["addClass"] = "node-folder";
			$array["href"] = urldecode(url_for("folder/show?id=".$folder->getId()));
			$array["right"] = $this->getRequestParameter("right");

			return $this->renderText(json_encode($array));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGetSubfolders()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			$this->forward404Unless($roleGroup < RolePeer::__ADMIN 
				|| ($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") 
				|| $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) 
				|| $folder->getUserId() == $this->getUser()->getId())) || ($roleGroup == RolePeer::__CONTRIB 
					&& ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, 
					RolePeer::__CONTRIB) || $folder->getUserId() == $this->getUser()->getId())));

			$this->subfolders = array(__("Root's folder group")) + FolderPeer::getAllPathFolder($group->getId());

			return sfView::SUCCESS;
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCreate()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($group->getId()));
			$this->forward404If($roleGroup > RolePeer::__CONTRIB);

			$this->getResponse()->setContentType('application/json');

			if($folder = FolderPeer::retrieveByName($this->getRequestParameter("name")))
			{
				if($folder->getGroupeId() == $group->getId())
				{
					if($this->getRequestParameter("folder_id"))
					{
						if($folder->getSubfolderId() == $this->getRequestParameter("folder_id"))
							return $this->renderText(json_encode(array("code" => 1, "html" => "folder already exist")));
					}
					else
						return $this->renderText(json_encode(array("code" => 1, "html" => "folder already exist")));
				}
			}

			$folder = new Folder();
			$folder->setState(FolderPeer::__STATE_ACTIVE);
			$folder->setGroupeId($group->getId());
			$folder->setName($this->getRequestParameter("name"));
			$folder->setUserId($this->getUser()->getId());

			if($this->getRequestParameter("folder_id"))
			{
				$subfolder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id"));
				$folder->setSubfolderId($subfolder->getId());
			}

			$folder->save();

			LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-create", "2");

			if($this->getRequestParameter("next_step") == "true")
				return $this->renderText(json_encode(array("code" => 0, 
						"html" => url_for("upload/uploadify?folder_id=".$folder->getId()."&navigation=upload&mode=normal&no_close=1"))));
			else
				return $this->renderText(json_encode(array("code" => 0, 
						"html" => $this->getRequestParameter("folder_id") ? url_for('folder/show?id='.$folder->getId()) : url_for('group/show?id='.$folder->getGroupeId()))));
		}

		$this->redirect404();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeAddFolderUpload()
	{
		if($this->getRequestParameter("data")) {
			$data = $this->getRequestParameter("data");
			$group_id = array_key_exists("group_id", $data) ? $data["group_id"] : null;
		} else
			$group_id = $this->getRequestParameter("group_id");

		$this->forward404Unless($group = GroupePeer::retrieveByPk($group_id));
		$this->getResponse()->setSlot('title', __("Create a folder"));

		$this->form = new FolderAddFolderUploadForm(
			array(
				"group_id" => $group->getId()
			)
		);

		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);

			if($this->form->isValid())
			{
				$folder = new Folder();
				$folder->setState(FolderPeer::__STATE_ACTIVE);
				$folder->setGroupeId($group->getId());
				$folder->setName($this->form->getValue('name'));
				$folder->setUserId($this->getUser()->getId());
				$folder->save();

				LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-create", "2");

				$this->getUser()->setFlash("success", __("The folder has successfully created."), false);
				$this->redirect("upload/uploadify?folder_id=".$folder->getId()."&navigation=upload&mode=normal&first=1");
			}
		}
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAddUser()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');

			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));

			if($this->getRequestParameter("id"))
			{
				$ids = $this->getRequestParameter("id");

				foreach($ids as $id) {
					$temp = explode("-", $id);
	
					if(is_array($temp) && $temp[0] == "user" && $user = UserPeer::retrieveByPk($temp[1]))
					{
						$user_folder = UserFolderPeer::retrieveByUserAndFolder($user->getId(), $folder->getId());
	
						if(!$user_folder)
						{
							$passe = false;
	
							/*if($folder->getSubfolderId())
							{
								$users = UserFolderPeer::getUsers($folder->getSubfolderId());
	
								foreach($users as $user_)
								{
									if($user_->getState() == UserPeer::__STATE_ACTIVE)
									{
										if($user_->getId() == $user->getId())
											$passe = true;
									}
								}
							}
							else*/
							{
								$group = GroupePeer::retrieveByPk($folder->getGroupeId());
	
								if($group->getFree())
								{
									// $users = CustomerPeer::getMyUsersNoPager($group->getCustomerId());
									$passe = true;
								}
								else
								{
									$users = Array();
									$temp = UserGroupPeer::retrieveByGroupId($group->getId());
	
									foreach($temp as $user_)
									{
										$temp = $user_->getUser();
	
										if($temp && $temp->getState() == UserPeer::__STATE_ACTIVE)
										{
											if($temp->getId() == $user->getId())
												$passe = true;
										}
									}
								}
							}
	
							if($passe == true)
							{
								$user_folder = new UserFolder();
								$user_folder->setUserId($user->getId());
								$user_folder->setFolderId($folder->getId());
								$user_folder->setRole(RolePeer::__READER);
	
								$user_folder->save();
							}
							else
								return $this->renderText(json_encode(array("errorCode" => 1, "message" => "not allowed to add this user")));
						}

					return $this->renderText(json_encode(array("errorCode" => 0, "message" => "success")));
					}
					elseif(is_array($temp) && $temp[0] == "unit" && $temp[1] == "everybody") {
						$folder->setFree(true);
						$folder->save();
	
						UserFolderPeer::deleteByFolderId($folder->getId());
	
						return $this->renderText(json_encode(array("errorCode" => 0, "message" => "success")));
					}
					else
						return $this->renderText(json_encode(array("errorCode" => 2, "message" => "user and unit not found (".$this->getRequestParameter("id").")")));
				}
			}
			else
				return $this->renderText(json_encode(array("errorCode" => 2, "message" => "user and unit not found (".$this->getRequestParameter("id").")")));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeImportUserFolder()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));
			$this->forward404Unless($import_folder = FolderPeer::retrieveByPk($this->getRequestParameter("import")));

			$this->getResponse()->setContentType('application/json');

			if($this->getRequestParameter("force") == "true")
			{
				$user_folders = UserFolderPeer::retrieveByFolderId($folder->getId());
				foreach($user_folders as $user_folder)
					$user_folder->delete();

				$user_folders = UserFolderPeer::retrieveByFolderId($import_folder->getId());
				foreach($user_folders as $user_folder)
				{
					$user_folder_new = new UserFolder();
					$user_folder_new->setUserId($user_folder->getUserId());
					$user_folder_new->setFolderId($folder->getId());
					$user_folder_new->setRole($user_folder->getRole());

					$user_folder_new->save();
				}

				return $this->renderText(json_encode(array("errorCode" => 0, "message" => "success")));
			}
			else
			{
				$existing = array();
				$user_folders = UserFolderPeer::retrieveByFolderId($folder->getId());
				foreach($user_folders as $user_folder)
					$existing[$user_folder->getId()] = $user_folder->getUserId();

				$user_folders = UserGroupPeer::retrieveByFolderId($import_folder->getId());
				foreach($user_folders as $user_folder)
				{
					if($key = array_search($user_folder->getUserId(), $existing))
					{
						if($this->getRequestParameter("overwrite") == "true")
						{
							UserFolderPeer::retrieveByPk($key)->delete();

							$user_folder_new = new UserFolder();
							$user_folder_new->setUserId($user_folder->getUserId());
							$user_folder_new->setFolderId($folder->getId());
							$user_folder_new->setRole($user_folder->getRole());

							$user_folder_new->save();
						}
					}
					else
					{
						$user_folder_new = new UserFolder();
						$user_folder_new->setUserId($user_folder->getUserId());
						$user_folder_new->setFolderId($folder->getId());
						$user_folder_new->setRole($user_folder->getRole());

						$user_folder_new->save();
					}
				}

				return $this->renderText(json_encode(array("errorCode" => 0, "message" => "success")));
			}
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDefault()
	{
		if($this->getRequestParameter('data'))
		{
			$data = $this->getRequestParameter('data');
			$id = array_key_exists("id", $data) ? $data["id"] : null;
		} else {
			$id = $this->getRequestParameter('id');
		}

		$this->forward404Unless($folder = FolderPeer::retrieveByPK($id));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
				($roleGroup < RolePeer::__ADMIN) ||
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
				($roleGroup == RolePeer::__CONTRIB && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) || $folder->getUserId() == $this->getUser()->getId())
		);

		$this->getResponse()->setSlot('title', __("Manage folder")." \"".$folder."\"");

		$this->form = new FolderDefaultForm(
			array(
				'id' => $folder->getId(),
				'address' => __("Address, City, Region")
			)
		);

		$this->lat = $folder->getLat();
		$this->lng = $folder->getLng();

		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);

			if($this->form->isValid())
			{
				$distribution = $this->getRequestParameter("distribution");
				$licence = $this->getRequestParameter("licence");
				$use = $this->getRequestParameter("us_");
				$creative_commons = $this->getRequestParameter("creative_commons_select");

				$folder->setUsageDistributionId($distribution > -1 ? $distribution : null);
				$folder->setUsageUseId($use > -1 ? $use : null);
				$folder->setLicenceId($licence > -1 ? $licence : null);

				if($licence == LicencePeer::__CREATIVE_COMMONS)
					$folder->setCreativeCommonsId($creative_commons > -1 ? $creative_commons : null);

				$folder->setLat($this->getRequestParameter("lat"));
				$folder->setLng($this->getRequestParameter("lng"));

				$folder->save();

				GeolocationPeer::saveGeolocation($folder, GeolocationPeer::__TYPE_FOLDER);

				if($distribution == UsageDistributionPeer::__AUTH)
				{
					$limitations = UsageLimitationPeer::getLimitations();

					foreach($limitations as $limitation)
					{
						if($file_right = FileRightPeer::retrieveByTypeAndLimitation($folder->getId(), 2, $limitation->getId()))
							$file_right->delete();

						$value = $this->getRequestParameter("limitation_".$limitation->getId());

						if(!empty($value))
						{
							$file_right = new FileRight();
							$file_right->setObjectId($folder->getId());
							$file_right->setType(2);
							$file_right->setUsageLimitationId($limitation->getId());
							$file_right->setValue($value);

							$file_right->save();
						}
					}
				}

				// save tags
				FileTagPeer::deletByTypeFileId(2, $folder->getId());
				$tags_name = $this->getRequestParameter("tags_input") ? explode("|", $this->getRequestParameter("tags_input")) : Array();

				foreach ($tags_name as $tag_name)
				{
					if(!empty($tag_name))
					{
						$tag = TagPeer::retrieveByTitle($tag_name);

						if(!$tag)
						{
							$tag = new Tag();
							$tag->setTitle($tag_name);
							$tag->setCustomerId($this->getUser()->getCustomerId());
							$tag->save();
						}

						if(!FileTagPeer::getFileTag(2, $folder->getId(), $tag->getId()))
						{
							$file_tag = new FileTag();
							$file_tag->setType(2);
							$file_tag->setFileId($folder->getId());
							$file_tag->setTagId($tag->getId());
							$file_tag->save();
						}
					}
				}

				foreach($this->getRequest()->getParameterHolder()->getAll() as $key => $value)
				{
					if(preg_match('/field_/', $key))
					{
						$temp = explode("_", $key);
						$field_id = $temp[1];

						$this->forward404Unless($field = FieldPeer::retrieveByPk($field_id));

						switch($field->getType())
						{
							case FieldPeer::__TYPE_BOOLEAN: $val = ($value == "on" ? 1 : 0); break;
							case FieldPeer::__TYPE_SELECT: $val = (empty($value) ? "" : $value); break;
							default: $val = $value; break;
						}

						if(!empty($val))
						{
							if($content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $folder->getId(), FieldContentPeer::__FOLDER))
								$content->setValue($val);
							else
							{
								$content = new FieldContent();
								$content->setFieldId($field->getId());
								$content->setObjectId($folder->getId());
								$content->setObjectType(FieldContentPeer::__FOLDER);
								$content->setValue($val);
							}

							$content->save();
						}
					}
				}

				LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-update", "2");

				if($this->getRequestParameter("recurs") == "on")
				{
					$files = FilePeer::retrieveByFolderIdRecursive($folder->getId());

					foreach($files as $file)
					{
						$file->setUsageDistributionId($distribution > -1 ? $distribution : null);
						$file->setUsageUseId($use > -1 ? $use : null);
						$file->setLicenceId($licence > -1 ? $licence : null);

						if($licence == LicencePeer::__CREATIVE_COMMONS)
							$file->setCreativeCommonsId($creative_commons > -1 ? $creative_commons : null);

						$file->setLat($this->getRequestParameter("lat"));
						$file->setLng($this->getRequestParameter("lng"));

						$file->save();

						GeolocationPeer::saveGeolocation($file, GeolocationPeer::__TYPE_FILE);

						if($distribution == UsageDistributionPeer::__AUTH)
						{
							$limitations = UsageLimitationPeer::getLimitations();

							foreach($limitations as $limitation)
							{
								if($file_right = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $limitation->getId()))
									$file_right->delete();

								$value = $this->getRequestParameter("limitation_".$limitation->getId());

								if(!empty($value))
								{
									$file_right = new FileRight();
									$file_right->setObjectId($file->getId());
									$file_right->setType(3);
									$file_right->setUsageLimitationId($limitation->getId());
									$file_right->setValue($value);

									$file_right->save();
								}
							}
						}

						$tags_name = $this->getRequestParameter("tags_input") ? explode("|", $this->getRequestParameter("tags_input")) : Array();

						foreach ($tags_name as $tag_name)
						{
							if(!empty($tag_name))
							{
								$tag = TagPeer::retrieveByTitle($tag_name);

								if(!$tag)
								{
									$tag = new Tag();
									$tag->setTitle($tag_name);
									$tag->setCustomerId($this->getUser()->getCustomerId());
									$tag->save();
								}

								if(!FileTagPeer::getFileTag(3, $file->getId(), $tag->getId()))
								{
									$file_tag = new FileTag();
									$file_tag->setTagId($tag->getId());
									$file_tag->setFileId($file->getId());
									$file_tag->setType(3);
									$file_tag->save();
								}
							}
						}

						if(!empty($tags_name))
							myTools::addTags($file, false);

						foreach($this->getRequest()->getParameterHolder()->getAll() as $key => $value)
						{
							if(preg_match('/field_/', $key))
							{
								$temp = explode("_", $key);
								$field_id = $temp[1];

								$this->forward404Unless($field = FieldPeer::retrieveByPk($field_id));

								switch($field->getType())
								{
									case FieldPeer::__TYPE_BOOLEAN: $val = ($value == "on" ? 1 : 0); break;
									case FieldPeer::__TYPE_SELECT: $val = (empty($value) ? "" : $value); break;
									default: $val = $value; break;
								}

								if(!empty($val))
								{
									if($content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $file->getId(), FieldContentPeer::__FILE))
										$content->setValue($val);
									else
									{
										$content = new FieldContent();
										$content->setFieldId($field->getId());
										$content->setObjectId($file->getId());
										$content->setObjectType(FieldContentPeer::__FILE);
										$content->setValue($val);
									}

									$content->save();
								}
							}
						}

						LogPeer::setLog($this->getUser()->getId(), $file->getId(), "file-edit", "3");
					}
				}

				$this->getUser()->setFlash("success", __("Folder information has updated successfully."), true);

				$this->uri = url_for('folder/show?id='.$folder->getId());
				$this->setTemplate('thankyou');
			}
		}

		$this->folder = $folder;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeManageUsers()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
				($roleGroup < RolePeer::__ADMIN) ||
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
				($roleGroup == RolePeer::__CONTRIB && (($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB)) || ($folder->getUserId() == $this->getUser()->getId() && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB))))
		);
		
		$this->getResponse()->setSlot('title', __("Manage folder")." \"".$folder."\"");

		$this->folder = $folder;
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeInviteUsers()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
			($roleGroup < RolePeer::__ADMIN) ||
			($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
			($roleGroup == RolePeer::__CONTRIB && (($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB)) || ($folder->getUserId() == $this->getUser()->getId() && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB))))
		);

		$this->getResponse()->setSlot('title', __("Manage folder")." \"".$folder."\"");

		$this->folder = $folder;
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeImportUsers()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
				($roleGroup < RolePeer::__ADMIN) ||
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
				($roleGroup == RolePeer::__CONTRIB && (($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB)) || ($folder->getUserId() == $this->getUser()->getId() && $this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__USER, RolePeer::__CONTRIB))))
		);

		$this->getResponse()->setSlot('title', __("Manage folder")." \"".$folder."\"");

		$this->folder = $folder;
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThumbnail()
	{
		if($this->getRequestParameter('data'))
		{
			$data = $this->getRequestParameter('data');
			$id = array_key_exists("id", $data) ? $data["id"] : null;
			$step = array_key_exists("step", $data) ? $data["step"] : null;
		} else {
			$id = $this->getRequestParameter('id');
			$step = $this->getRequestParameter('step');
		}

		$this->forward404Unless($folder = FolderPeer::retrieveByPK($id));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
				($roleGroup < RolePeer::__ADMIN) ||
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
				($roleGroup == RolePeer::__CONTRIB && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) || $folder->getUserId() == $this->getUser()->getId())
		);

		
		$this->form = new FolderThumbnailForm(
			array(
				"id" => $folder->getId(),
				"uploaded_thumbnail_name" => $folder->getThumbnail(),
				"is_upload" => 0
			)
		);

		$this->getResponse()->setSlot('title', __("Manage folder")." \"".$folder."\"");

		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'), $this->getRequest()->getFiles('data'));
			$this->getResponse()->setSlot("form", $this->form);

			if($this->form->isValid())
			{
				$path = $folder->getRealPath().DIRECTORY_SEPARATOR;

				// save thumbnail_image
				if ($this->form->getValue("is_upload") && $uploaded_thumbnail_name = $this->form->getValue("uploaded_thumbnail_name"))
				{
					$ext = myTools::getFileExtension($uploaded_thumbnail_name);
					$k = time();

					$size = getimagesize($path.$uploaded_thumbnail_name);

					$cropped_web = $k."_web.".$ext;

					while(file_exists($path.$cropped_web))
					{
						$k++;
						$cropped_web = $k."_web.".$ext;
					}

					@rename($path.$uploaded_thumbnail_name, $path.$cropped_web);
					@unlink($path.$folder->getThumbnail());

					$folder->setThumbnail($cropped_web);
					$folder->setDiskId($this->getUser()->getDisk()->getId());
					$folder->save();
				}

				$this->uri = 'folder/show?id='.$folder->getId();
				$this->setTemplate("thankyouUri");
			}
		}

		$this->folder = $folder;
		$this->folders = FolderPeer::getFolderInArray(0, $folder->getGroupeId());
		$this->files = FilePeer::retrieveByGroupId($folder->getGroupeId());

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeGetFilesThumb()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));

			if($this->getRequestParameter("folder_id") == "all")
				return $this->renderPartial("folder/filesThumb", array("files" => FilePeer::retrieveByGroupId($group->getId())));
			else
			{
				$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));
				return $this->renderPartial("folder/filesThumb", array("files" => FilePeer::retrieveByFolderId($folder->getId())));
			}
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThumbnailUploadFromWikipixel()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
		$this->form = new GroupThumbnailForm();
		
		@mkdir(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/", 0777, true);
		@copy(sfConfig::get("app_path_upload_dir")."/".$file->getDisk()->getPath()."/cust-".$file->getCustomerId()."/folder-".$file->getFolderId()."/".$file->getOriginal(), sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/".$file->getOriginal());
		@chmod(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/".$file->getOriginal(), 0666);
		$size = getimagesize(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/".$file->getOriginal());

		$this->thumbnail = $file->getOriginal();

		$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);

		$this->new_width = $new["width"];
		$this->new_height = $new["height"];

		$this->form = new FolderThumbnailForm(
			array(
				"uploaded_thumbnail_name" => $file->getOriginal(),
				"is_upload" => 1,
				"width" => $this->new_width,
				"height" => $this->new_height
			)
		);

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeThumbnailUpload()
	{
		$this->form = new FolderThumbnailForm();

		$this->form->bind($this->getRequestParameter('data'), $this->getRequest()->getFiles('data'));

		if($this->form->isValid())
		{
			if($thumbnail = $this->form->getValue('thumbnail'))
			{
				@mkdir(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/", 0777, true);
				$thumbnail->save(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/".$thumbnail->getOriginalName());
				@chmod(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/".$thumbnail->getOriginalName(), 0666);
				$size = getimagesize(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/folders/".$thumbnail->getOriginalName());

				$this->thumbnail = $thumbnail->getOriginalName();

				if($size[0] < 220 || $size[1] < 220)
				{
					$this->header_img = null;
					$this->form->getErrorSchema()->addError(new sfValidatorError(new sfValidatorString(), __("The image must be at least %1%x%2% pixels.", Array("%1%" => 220, "%2%" => 220))), "error_name");
					return sfView::SUCCESS;
				}

				$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);

				$this->new_width = $new["width"];
				$this->new_height = $new["height"];

				$this->form = new FolderThumbnailForm(
					array(
						"uploaded_thumbnail_name" => $thumbnail->getOriginalName(),
						"is_upload" => 1,
						"width" => $this->new_width,
						"height" => $this->new_height
					)
				);

				return sfView::SUCCESS;
			}

			$this->thumbnail = $this->form->getValue("uploaded_thumbnail_name");
		}

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRecursive()
	{
		if($this->getRequestParameter('data'))
		{
			$data = $this->getRequestParameter('data');
			$id = array_key_exists("id", $data) ? $data["id"] : null;
		} else {
			$id = $this->getRequestParameter('id');
		}

		$this->forward404Unless($folder = FolderPeer::retrieveByPK($id));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless(
				($roleGroup < RolePeer::__ADMIN) ||
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) ||
				($roleGroup == RolePeer::__CONTRIB && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) || $folder->getUserId() == $this->getUser()->getId())
		);
		
		$this->getResponse()->setSlot('title', __("Manage folder")." \"".$folder."\"");

		$this->form = new FolderDefaultForm(
			array(
				'id' => $folder->getId(),
				'address' => __("Address, City, Region")
			)
		);

		$this->lat = $folder->getLat();
		$this->lng = $folder->getLng();

		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);

			if($this->form->isValid())
			{
				$distribution = $this->getRequestParameter("distribution");
				$licence = $this->getRequestParameter("licence");
				$use = $this->getRequestParameter("us_");
				$commercial = $this->getRequestParameter("commercial");
				$creative_commons = $this->getRequestParameter("creative_commons_select");

				$files = FilePeer::retrieveByFolderIdRecursive($folder->getId());

				foreach($files as $file)
				{
					$file->setUsageDistributionId($distribution > -1 ? $distribution : null);
					$file->setUsageUseId($use > -1 ? $use : null);
					$file->setLicenceId($licence > -1 ? $licence : null);

					if($licence == LicencePeer::__CREATIVE_COMMONS)
						$file->setCreativeCommonsId($creative_commons > -1 ? $creative_commons : null);

					$file->setLat($this->getRequestParameter("lat"));
					$file->setLng($this->getRequestParameter("lng"));

					$file->save();

					GeolocationPeer::saveGeolocation($file, GeolocationPeer::__TYPE_FILE);

					if($distribution == UsageDistributionPeer::__AUTH)
					{
						$limitations = UsageLimitationPeer::getLimitations();

						foreach($limitations as $limitation)
						{
							if($file_right = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $limitation->getId()))
								$file_right->delete();

							$value = $this->getRequestParameter("limitation_".$limitation->getId());

							if(!empty($value))
							{
								$file_right = new FileRight();
								$file_right->setObjectId($file->getId());
								$file_right->setType(3);
								$file_right->setUsageLimitationId($limitation->getId());
								$file_right->setValue($value);

								$file_right->save();
							}
						}
					}

					$tags_name = $this->getRequestParameter("tags_input") ? explode("|", $this->getRequestParameter("tags_input")) : Array();

					foreach ($tags_name as $tag_name)
					{
						if(!empty($tag_name))
						{
							$tag = TagPeer::retrieveByTitle($tag_name);

							if(!$tag)
							{
								$tag = new Tag();
								$tag->setTitle($tag_name);
								$tag->setCustomerId($this->getUser()->getCustomerId());
								$tag->save();
							}

							if(!FileTagPeer::getFileTag(3, $file->getId(), $tag->getId()))
							{
								$file_tag = new FileTag();
								$file_tag->setTagId($tag->getId());
								$file_tag->setFileId($file->getId());
								$file_tag->setType(3);
								$file_tag->save();
							}
						}
					}

					if(!empty($tags_name))
						myTools::addTags($file, false);

					foreach($this->getRequest()->getParameterHolder()->getAll() as $key => $value)
					{
						if(preg_match('/field_/', $key))
						{
							$temp = explode("_", $key);
							$field_id = $temp[1];

							$this->forward404Unless($field = FieldPeer::retrieveByPk($field_id));

							switch($field->getType())
							{
								case FieldPeer::__TYPE_BOOLEAN: $val = ($value == "on" ? 1 : 0); break;
								case FieldPeer::__TYPE_SELECT: $val = (empty($value) ? "" : $value); break;
								default: $val = $value; break;
							}

							if(!empty($val))
							{
								if($content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $file->getId(), FieldContentPeer::__FILE))
									$content->setValue($val);
								else
								{
									$content = new FieldContent();
									$content->setFieldId($field->getId());
									$content->setObjectId($file->getId());
									$content->setObjectType(FieldContentPeer::__FILE);
									$content->setValue($val);
								}

								$content->save();
							}
						}
					}

					LogPeer::setLog($this->getUser()->getId(), $file->getId(), "file-edit", "3");
				}

				if($this->getRequestParameter("recurs") == "on")
				{
					$folder->setUsageDistributionId($distribution > -1 ? $distribution : null);
					$folder->setUsageUseId($use > -1 ? $use : null);
					$folder->setLicenceId($licence > -1 ? $licence : null);

					if($licence == LicencePeer::__CREATIVE_COMMONS)
						$folder->setCreativeCommonsId($creative_commons > -1 ? $creative_commons : null);

					$folder->setLat($this->getRequestParameter("lat"));
					$folder->setLng($this->getRequestParameter("lng"));

					$folder->save();

					GeolocationPeer::saveGeolocation($folder, GeolocationPeer::__TYPE_FOLDER);

					if($distribution == UsageDistributionPeer::__AUTH)
					{
						$limitations = UsageLimitationPeer::getLimitations();

						foreach($limitations as $limitation)
						{
							if($file_right = FileRightPeer::retrieveByTypeAndLimitation($folder->getId(), 2, $limitation->getId()))
								$file_right->delete();

							$value = $this->getRequestParameter("limitation_".$limitation->getId());

							if(!empty($value))
							{
								$file_right = new FileRight();
								$file_right->setObjectId($folder->getId());
								$file_right->setType(2);
								$file_right->setUsageLimitationId($limitation->getId());
								$file_right->setValue($value);

								$file_right->save();
							}
						}
					}

					// save tags
					FileTagPeer::deletByTypeFileId(2, $folder->getId());
					$tags_name = $this->getRequestParameter("tags_input") ? explode("|", $this->getRequestParameter("tags_input")) : Array();

					foreach ($tags_name as $tag_name)
					{
						if(!empty($tag_name))
						{
							$tag = TagPeer::retrieveByTitle($tag_name);

							if(!$tag)
							{
								$tag = new Tag();
								$tag->setTitle($tag_name);
								$tag->setCustomerId($this->getUser()->getCustomerId());
								$tag->save();
							}

							if(!FileTagPeer::getFileTag(2, $folder->getId(), $tag->getId()))
							{
								$file_tag = new FileTag();
								$file_tag->setType(2);
								$file_tag->setFileId($folder->getId());
								$file_tag->setTagId($tag->getId());
								$file_tag->save();
							}
						}
					}

					foreach($this->getRequest()->getParameterHolder()->getAll() as $key => $value)
					{
						if(preg_match('/field_/', $key))
						{
							$temp = explode("_", $key);
							$field_id = $temp[1];

							$this->forward404Unless($field = FieldPeer::retrieveByPk($field_id));

							switch($field->getType())
							{
								case FieldPeer::__TYPE_BOOLEAN: $val = ($value == "on" ? 1 : 0); break;
								case FieldPeer::__TYPE_SELECT: $val = (empty($value) ? "" : $value); break;
								default: $val = $value; break;
							}

							if(!empty($val))
							{
								if($content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $folder->getId(), FieldContentPeer::__FOLDER))
									$content->setValue($val);
								else
								{
									$content = new FieldContent();
									$content->setFieldId($field->getId());
									$content->setObjectId($folder->getId());
									$content->setObjectType(FieldContentPeer::__FOLDER);
									$content->setValue($val);
								}

								$content->save();
							}
						}
					}

					LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "folder-update", "2");
				}

				$this->getUser()->setFlash("success", __("Recursively change successfully applied."), true);

				$this->uri = url_for('folder/show?id='.$folder->getId());
				$this->setTemplate('thankyou');
			}
		}

		$this->folder = $folder;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeMoveDnd()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('from')));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			$this->forward404Unless($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) || $folder->getUserId() == $this->getUser()->getId())) || ($roleGroup == RolePeer::__CONTRIB && ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) || $folder->getUserId() == $this->getUser()->getId())));
			$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
			$this->forward404Unless($to = FolderPeer::retrieveByPk($this->getRequestParameter('to')));
			$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));

			$this->getResponse()->setContentType('application/json');

			$folder->setSubfolderId($to->getId());

			$folder->save();

			return $this->renderText(json_encode(Array("code" => 0, "msg" => "Success")));
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUpdateName()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			$this->forward404Unless($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN 
				&& ($this->getUser()->hasCredential("admin") 
				|| $this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE, RolePeer::__ADMIN) 
				|| $folder->getUserId() == $this->getUser()->getId())) || ($roleGroup == RolePeer::__CONTRIB 
				&& ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, 
					RolePeer::__CONTRIB) || $folder->getUserId() == $this->getUser()->getId())));

			$value = $this->getRequestParameter('value');
			$field = $this->getRequestParameter('field');

			switch($field)
			{
				case "name":
					if(!empty($value))
						$folder->setName($value);
					else
						$value = $folder->getName();
				break;
			}

			$folder->save();

			return $this->renderText($value);
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeComment()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($roleGroup < RolePeer::__ADMIN || ($roleGroup == RolePeer::__ADMIN 
			&& ($this->getUser()->hasCredential("admin") || $this->getUser()->getConstraint($folder->getGroupeId(), 
					ConstraintPeer::__UPDATE, RolePeer::__ADMIN) 
			|| $folder->getUserId() == $this->getUser()->getId())) || ($roleGroup == RolePeer::__CONTRIB 
			&& ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, 
					RolePeer::__CONTRIB) || $folder->getUserId() == $this->getUser()->getId())));
		
		$this->forward404Unless($permalink = PermalinkPeer::getByObjectId($folder->getId(), 
				PermalinkPeer::__TYPE_CUSTOM, PermalinkPeer::__OBJECT_FOLDER));

		$this->getResponse()->setSlot('title', __("List of comments of folder \"%1%\"", Array("%1%" => $folder)));

		$this->comments = PermalinkCommentPeer::retrieveByPermalinkId($permalink->getId());

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDeleteComment()
	{
		$this->forward404Unless($comment = PermalinkCommentPeer::retrieveByPK($this->getRequestParameter("id")));
		$comment->delete();

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeEditComment()
	{
		$this->forward404Unless($comment = PermalinkCommentPeer::retrieveByPK($this->getRequestParameter("id")));

		$this->getResponse()->setContentType('application/json');

		$formating = new Formating();
		$comment->setComment($formating->force_balance_tags(nl2br($this->getRequestParameter("comment"))));
		$comment->save();

		return $this->renderText(json_encode(Array("html" => $comment->getComment(), "js" => 
				$this->getRequestParameter("comment"))));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeShare()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
			$this->forward404If($roleGroup > RolePeer::__CONTRIB);

			return $this->renderPartial("folder/share", Array("folder" => $folder));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadComments()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($permalink = PermalinkPeer::getByLink($this->getRequestParameter("permalink")));

			return $this->renderComponent("folder", "publicComment", Array("permalink" => $permalink));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executePostComment()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($permalink = PermalinkPeer::getByLink($this->getRequestParameter("permalink")));

			switch($permalink->getObjectType())
			{
				case PermalinkPeer::__OBJECT_FOLDER:
					$this->forward404Unless($folder = FolderPeer::retrieveByPk($permalink->getObjectId()));
				break;

				case PermalinkPeer::__OBJECT_GROUP:
					$this->forward404Unless($group = GroupePeer::retrieveByPkNoCustomer($permalink->getObjectId()));
				break;
			}

			$this->getResponse()->setContentType('application/json');

			if(!filter_var($this->getRequestParameter("email"), FILTER_VALIDATE_EMAIL))
				return $this->renderText(json_encode(Array("errorCode" => 1)));
			else
			{
				$comment = new PermalinkComment();
				$comment->setPermalinkId($permalink->getId());
				$comment->setEmail($this->getRequestParameter("email"));
				$comment->setComment($this->getRequestParameter("comment"));

				$comment->save();

				$users = $permalink->getUsersNotification(PermalinkNotificationPeer::__ADD_COMMENT);

				foreach($users as $user)
				{
					switch($permalink->getObjectType())
					{
						case PermalinkPeer::__OBJECT_FOLDER:
						{
							$search = Array("**FOLDER_NAME**", "**COMMENT_EMAIL**", "**COMMENT**");
							$replace = Array($folder, $this->getRequestParameter("email"), nl2br($this->getRequestParameter("comment")));

							$subject = __("New comment about %1% folder", array("%1%" => $folder));

							$email = new myMailer("folder_comment", "[wikiPixel] ".$subject);
							$email->setTo(array($user->getEmail()));
							$email->setFrom(Array("no-reply@wikipixel.com" => "no-reply@wikipixel.com"));
							$email->compose($search, $replace);
							$email->send();
						}
						break;

						case PermalinkPeer::__OBJECT_GROUP:
						{
							$search = Array("**ALBUM_NAME**", "**COMMENT_EMAIL**", "**COMMENT**");
							$replace = Array($group->getName(), $this->getRequestParameter("email"), nl2br($this->getRequestParameter("comment")));

							$subject = __("New comment about %1% main folder", array("%1%" => $group));

							$email = new myMailer("group_comment", "[wikiPixel] ".$subject);
							$email->setTo(array($user->getEmail()));
							$email->setFrom(Array("no-reply@wikipixel.com" => "no-reply@wikipixel.com"));
							$email->compose($search, $replace);
							$email->send();
						}
						break;
					}
				}

				return $this->renderText(json_encode(Array("errorCode" => 0)));
			}
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSlideshowPublic()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($this->permalink = PermalinkPeer::getByLink($this->getRequestParameter("permalink")));
			$this->forward404Unless($this->folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));

			$c = new Criteria();
			$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
			$c->add(FilePeer::FOLDER_ID, $this->folder->getId());
			$c->addDescendingOrderByColumn(FilePeer::NAME);
			$c->addDescendingOrderByColumn(FilePeer::ORIGINAL);

			$this->files = FilePeer::doSelect($c);
			$this->start = $this->getRequestParameter("start", 0);
			$this->countFile = Array();
			$this->height = $this->getRequestParameter("height");
			$this->width = $this->getRequestParameter("width");

			return sfView::SUCCESS;
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executePublicLogin()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');

			$username = $this->getRequestParameter("login");
			$password = $this->getRequestParameter("password");

			$user = UserPeer::retrieveByEmail($username);

			if($user && $user->getState() == UserPeer::__STATE_ACTIVE)
			{
				if($user->getPassword() == md5($password))
				{
					$this->getContext()->getUser()->signIn($user);

					return $this->renderText(json_encode(Array("errorCode" => 0, "label" => "Authentication success.")));
				}
				else
					return $this->renderText(json_encode(Array("errorCode" => 1, "label" => "Authentication failed.")));
			}
			else
				return $this->renderText(json_encode(Array("errorCode" => 1, "label" => "Authentication failed.")));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadSidebar()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('id')));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));

			$return = Array();
			$return["html"] = $this->getComponent("folder", "sidebar", Array("folder" => $folder, 
				"tagsSelected" => TagPeer::retrieveByPks($this->getRequestParameter("selected_tag_ids")), 
				"addedByMe" => $this->getRequestParameter("added_by_me_input"), 
				"creationMin" => $this->getRequestParameter("creation_min"), 
				"creationMax" => $this->getRequestParameter("creation_max"), 
				"shootingMin" => $this->getRequestParameter("shooting_min"), 
				"shootingMax" => $this->getRequestParameter("shooting_max"), 
				"sizeMin" => $this->getRequestParameter("size_min"), 
				"sizeMax" => $this->getRequestParameter("size_max")));

			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode($return));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadFolders()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));

			$page = $this->getRequestParameter("page");
			$perPage = $this->getRequestParameter("onPage");
			$sort = $this->getRequestParameter("sort");

			if($this->getRequestParameter("folder_id")) {
				$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id")));
				$params = $this->getUser()->getAttribute("params.folders.folder.show");
				$this->getUser()->savePreferences("folder/show", $sort, $perPage);
			}
			else {
				$folder = null;
				$params = $this->getUser()->getAttribute("params.groups.group.show");
				$this->getUser()->savePreferences("group/show", $sort, $perPage);
			}


			$temp = FolderPeer::getFoldersInGroup(
				$group->getId(),
				$params["tags"],
				null,
				$params["me"],
				$params["date"],
				$sort,
				"N",
				$folder ? $folder->getId() : null,
				$perPage,
				($perPage * ($page - 1))
			);

			$return = Array();
			$return["folders"] = "";
			$return["rightclick"] = "";
	
			foreach($temp["folders"] as $folder)
			{
				$return["folders"] .= $this->getPartial("folder/grid", Array("folder" => $folder));
			}
	
			$return["index"] = $temp["count"] > ($page * $perPage) ? ($page * $perPage) : 0;
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode($return));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDeleteCredential()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
			$this->forward404If($roleGroup > RolePeer::__CONTRIB);

			$folder->setFree(false);
			$folder->save();

			$permalink = PermalinkPeer::getByObjectId($folder->getId(), PermalinkPeer::__TYPE_CUSTOM, 
					PermalinkPeer::__OBJECT_FOLDER);

			if($permalink) {
				PermalinkPeer::deletByUserIdAndObjectId($this->getUser()->getId(), $folder->getId(), 
				PermalinkPeer::__OBJECT_FOLDER);
			}

			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array("errorCode" => 0)));
		}
		
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGetFolders()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($sf_user->isAdmin());
			$this->forward404Unless($group = GroupePeer::retrieveByPKNoCustomer($this->getRequestParameter("group")));
			$this->forward404Unless($user = UserPeer::retrieveByPK($this->getRequestParameter("user")));
	
			$perPage = $this->getRequestParameter("perPage");

			switch($this->getRequestParameter("type")) {
				default:
				case "access":
					$type = "access";
					$folders = FolderPeer::getAccessFolders($group->getId(), $user->getId(), null, 1, $perPage, "pager");
					break;
			
				case "noaccess":
					$type = "noaccess";
					$folders = FolderPeer::getNoAccessFolders($group->getId(), $user->getId(), null, 1, $perPage, "pager");
					break;
			}

			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array(
				"errorCode" => 0,
				"html" => $this->getComponent("user", "rightFoldersUser", array("folders_pager" => $folders, 
						"group" => $group, "user" => $user, "perPage" => $perPage, "type" => $type))
			)));
		}
	
		$this->forward404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAddFolderRight()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($sf_user->isAdmin());
			$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("id")));
			$this->forward404Unless($user = UserPeer::retrieveByPK($this->getRequestParameter("user")));

			$userFolder = new UserFolder();
			$userFolder->setUserId($user->getId());
			$userFolder->setFolderId($folder->getId());
			$userFolder->setRole(RolePeer::__READER);
			$userFolder->save();

			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array(
					"errorCode" => 0
			)));
		}

		$this->forward404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRightUserList(sfWebRequest $request)
	{
		$folderId = $request->getParameter("folder");
		$folder = FolderPeer::retrieveByPK($folderId);
		$suser = $this->getUser()->getInstance();

		$this->forward404Unless($folder);

		$album = GroupePeer::retrieveByPK($folder->getGroupeId());

		$this->forward404Unless($album);

		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404Unless($this->getUser()->getRole($album->getId(), $folder->getId()));

		if ($roleAlbum > RolePeer::__CONTRIB) {
			$this->forward404();
		}
		elseif ($roleAlbum == RolePeer::__ADMIN) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE, RolePeer::__ADMIN);

			if (!$this->getUser()->isAdmin() && !$updateConstraint &&
					$folder->getUserId() != $this->getUser()->getId()) {
				$this->forward404();
			}
		}
		elseif ($roleAlbum == RolePeer::__CONTRIB) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB);

			$manageUserConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__USER, RolePeer::__CONTRIB);

			if (!($updateConstraint && $manageUserConstraint) && !($folder->getUserId() == $this->getUser()->getId() &&
					$manageUserConstraint)) {
				$this->forward404();
			}
		}

		$keyword = $request->getParameter("keyword", "");
		$letter = $request->getParameter("letter", "");
		$page = (int)$request->getParameter("page", 1);
		$state = $request->getParameter("state", "");
		$role = $request->getParameter("role", "");
		$itemPerPage = 10;
		$customerId = $this->getUser()->getCustomerId();

		$users = FolderPeer::getUsersPager($page, $itemPerPage,
				array(
						"albumId"		=> $album->getId(),
						"folderId"		=> $folder->getId(),
						"customerId"	=> $customerId,
						"keyword"		=> $keyword,
						"userStates"	=> array(UserPeer::__STATE_ACTIVE),
						"state"			=> $state,
						"roleState"		=> $role,
						"letter"		=> $letter
				), array(UserPeer::EMAIL => "asc"));

		$letters = FolderPeer::getLettersOfUsersPager(
				array(
						"albumId"		=> $album->getId(),
						"folderId"		=> $folder->getId(),
						"customerId"	=> $customerId,
						"keyword"		=> $keyword,
						"userStates"	=> array(UserPeer::__STATE_ACTIVE),
						"state"			=> $state,
						"roleState"		=> $role
				));

		$this->keyword = $keyword;
		$this->currentLetter = $letter;
		$this->currentState = $state;
		$this->currentRole = $role;
		$this->folder = $folder;
		$this->page = $page;

		$this->users = $users;
		$this->letters = $letters;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRightUserUpdate(sfWebRequest $request)
	{
		$folderId = $request->getParameter("folder");
		$userId = $request->getParameter("user");
		$role = $request->getParameter("role");
		$suser = $this->getUser()->getInstance();

		$folder = FolderPeer::retrieveByPK($folderId);
		$user = UserPeer::retrieveByPK($userId);

		$this->forward404Unless($folder);
		$this->forward404Unless($user);

		$album = GroupePeer::retrieveByPK($folder->getGroupeId());

		$this->forward404Unless($album);

		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404Unless($this->getUser()->getRole($album->getId(), $folder->getId()));

		if ($roleAlbum > RolePeer::__CONTRIB) {
			$this->forward404();
		}
		elseif ($roleAlbum == RolePeer::__ADMIN) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE, RolePeer::__ADMIN);

			if (!$this->getUser()->isAdmin() && !$updateConstraint &&
			$folder->getUserId() != $this->getUser()->getId()) {
				$this->forward404();
			}
		}
		elseif ($roleAlbum == RolePeer::__CONTRIB) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB);

			$manageUserConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__USER, RolePeer::__CONTRIB);

			if (!($updateConstraint && $manageUserConstraint) && !($folder->getUserId() == $this->getUser()->getId() &&
					$manageUserConstraint)) {
				$this->forward404();
			}
		}

		$userFolder = UserFolderPeer::retrieveByUserAndFolder($user->getId(), $folder->getId());

		if (($folder->getFree() && !$role) || (!$folder->getFree() && $role)) {
			if (!$userFolder) {
				$userFolder = new UserFolder();
				$userFolder->setFolderId($folder->getId());
				$userFolder->setUserId($user->getId());
			}

			$userFolder->setRole("");
			$userFolder->save();
		}
		else {
			if ($userFolder) {
				$userFolder->delete();
			}
		}

		$request = RequestPeer::getRequestFolder($folder->getId(), $user->getId());

		if ($request) {
			$request->delete();
			// TODO: Envoi de notification à l'utilisateur
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRightEverybodyUpdate(sfWebRequest $request)
	{
		$folderId = $request->getParameter("folder");
		$role = $request->getParameter("role");
		$suser = $this->getUser()->getInstance();

		$folder = FolderPeer::retrieveByPK($folderId);

		$this->forward404Unless($folder);

		$album = GroupePeer::retrieveByPK($folder->getGroupeId());

		$this->forward404Unless($album);

		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404Unless($this->getUser()->getRole($album->getId(), $folder->getId()));

		if ($roleAlbum > RolePeer::__CONTRIB) {
			$this->forward404();
		}
		elseif ($roleAlbum == RolePeer::__ADMIN) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE, RolePeer::__ADMIN);

			if (!$this->getUser()->isAdmin() && !$updateConstraint &&
			$folder->getUserId() != $this->getUser()->getId()) {
				$this->forward404();
			}
		}
		elseif ($roleAlbum == RolePeer::__CONTRIB) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB);

			$manageUserConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__USER, RolePeer::__CONTRIB);

			if (!($updateConstraint && $manageUserConstraint) && !($folder->getUserId() == $this->getUser()->getId() &&
					$manageUserConstraint)) {
				$this->forward404();
			}
		}

		if ($role != 1) {
			$folder->setFree(false);
		}
		else {
			$folder->setFree(true);
		}

		$folder->save();

		$userFolders = UserFolderPeer::retrieveByFolderId($folder->getId());

		foreach ($userFolders as $userFolder) {
			$userFolder->delete();
		}

		if ($role == 1) {
			$requests = RequestPeer::retrieveByFolderRequest($folder->getId());

			foreach ($requests as $request) {
				$request->delete();
			}
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRightGroupList(sfWebRequest $request)
	{
		$folderId = $request->getParameter("folder");
		$folder = FolderPeer::retrieveByPK($folderId);
		$suser = $this->getUser()->getInstance();

		$this->forward404Unless($folder);

		$album = GroupePeer::retrieveByPK($folder->getGroupeId());

		$this->forward404Unless($album);

		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404Unless($this->getUser()->getRole($album->getId(), $folder->getId()));

		if ($roleAlbum > RolePeer::__CONTRIB) {
			$this->forward404();
		}
		elseif ($roleAlbum == RolePeer::__ADMIN) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE, RolePeer::__ADMIN);

			if (!$this->getUser()->isAdmin() && !$updateConstraint &&
			$folder->getUserId() != $this->getUser()->getId()) {
				$this->forward404();
			}
		}
		elseif ($roleAlbum == RolePeer::__CONTRIB) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB);

			$manageUserConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__USER, RolePeer::__CONTRIB);

			if (!($updateConstraint && $manageUserConstraint) && !($folder->getUserId() == $this->getUser()->getId() &&
					$manageUserConstraint)) {
				$this->forward404();
			}
		}

		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 10;
		$orderBy = $request->getParameter("orderBy", array("title_asc"));
		$letter = $request->getParameter("letter", "");
		$keyword = $request->getParameter("keyword", "");
		$role = $request->getParameter("role");
		$state = $request->getParameter("state", "");
		$customerId = $this->getUser()->getCustomerId();

		$groups = UnitFolderPeer::getPager($page, $itemPerPage,
				array(
						"albumId"		=> $album->getId(),
						"folderId"		=> $folder->getId(),
						"keyword"		=> $keyword,
						"customerId"	=> $customerId,
						"state"			=> $state,
						"letter"		=> $letter
				), $orderBy);

		$letters = UnitFolderPeer::getLettersOfPager(
				array(
						"albumId"		=> $album->getId(),
						"folderId"		=> $folder->getId(),
						"keyword"		=> $keyword,
						"customerId"	=> $customerId,
						"state"			=> $state
				));

		$this->keyword = $keyword;
		$this->folder = $folder;
		$this->groups = $groups;
		$this->letters = $letters;
		$this->currentState = $state;
		$this->currentLetter = $letter;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRightGroupUpdate(sfWebRequest $request)
	{
		$folderId = $request->getParameter("folder");
		$groupId = $request->getParameter("group");
		$role = $request->getParameter("role");
		$suser = $this->getUser()->getInstance();

		$folder = FolderPeer::retrieveByPK($folderId);
		$group = UnitPeer::retrieveByPK($groupId);

		$this->forward404Unless($folder);
		$this->forward404Unless($group);

		$album = GroupePeer::retrieveByPK($folder->getGroupeId());

		$this->forward404Unless($album);

		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404Unless($this->getUser()->getRole($album->getId(), $folder->getId()));

		if ($roleAlbum > RolePeer::__CONTRIB) {
			$this->forward404();
		}
		elseif ($roleAlbum == RolePeer::__ADMIN) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE, RolePeer::__ADMIN);

			if (!$this->getUser()->isAdmin() && !$updateConstraint &&
			$folder->getUserId() != $this->getUser()->getId()) {
				$this->forward404();
			}
		}
		elseif ($roleAlbum == RolePeer::__CONTRIB) {
			$updateConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB);

			$manageUserConstraint = $this->getUser()->getConstraint($album->getId(),
					ConstraintPeer::__USER, RolePeer::__CONTRIB);

			if (!($updateConstraint && $manageUserConstraint) && !($folder->getUserId() == $this->getUser()->getId() &&
					$manageUserConstraint)) {
				$this->forward404();
			}
		}

		$unitFolder = UnitFolderPeer::retrieveByUnitIdAndFolderId($group->getId(), $folder->getId());

		if ($role < 0) {
			if ($unitFolder) {
				$unitFolder->delete();
			}
		}
		else {
			if ($role < 1) {
				$role = "";
			}
			else {
				$role = RolePeer::__READER;
			}

			if (!$unitFolder) {
				$unitFolder = new UnitFolder();
				$unitFolder->setUnitId($group->getId());
				$unitFolder->setFolderId($folder->getId());
			}

			$unitFolder->setRole($role);
			$unitFolder->save();
		}

		return sfView::NONE;
	}
}
