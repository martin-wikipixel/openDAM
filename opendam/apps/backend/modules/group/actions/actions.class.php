<?php
/**
 * group actions.
 *
 * @package    media management
 * @subpackage group
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 3335 2007-01-23 16:19:56Z fabien $
 */
class groupActions extends sfActions
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
		$albumId = (int) $request->getParameter("id");

		if (!file_exists($cacheDir)) {
			@mkdir($cacheDir);
		}

		$album = GroupePeer::retrieveByPK($albumId);

		$this->forward404Unless($album);

		if ($this->getUser()->isAuthenticated()) {
			$albumRole = $this->getUser()->getRole($album->getId());

			$this->forward404Unless($albumRole);
		}
		else {
			$this->forward404();
		}

		if ($album->exists() && is_file($album->getPathname())) {
			$filePathnameToRead = $album->getPathname();
			$contentType = $album->getContentType();
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
	public function executeManageGroup2()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter('group_id1')));
			$this->forward404Unless(UserPeer::isAllowed($group->getId(), "group"));
	
			$this->groups = GroupePeer::getGroupsInArray3(0, $group->getId());
			return sfView::SUCCESS;
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeMerge()
	{
		if($this->getRequestParameter('data'))
		{
			$data = $this->getRequestParameter('data');
			$group_from = array_key_exists("group_from", $data) ? $data["group_from"] : null;
		} else
			$group_from = $this->getRequestParameter('group_from');

		$this->forward404Unless($groupFrom = GroupePeer::retrieveByPk($group_from));
		$this->forward404Unless($role = $this->getUser()->getRole($groupFrom->getId()));

		$this->forward404Unless($role <= RolePeer::__ADMIN && ($this->getUser()->isAdmin() && $this->getUser()->getCustomerId() == $groupFrom->getCustomerId()));

		$this->getResponse()->setSlot('title', __("Administration"));
	
		$this->form = new GroupMergeForm(
			array(
				"group_from" => $groupFrom->getId()
			)
		);

		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if($this->form->isValid())
			{
				$this->forward404Unless($groupTo = GroupePeer::retrieveByPK($this->form->getValue("group_to")));
				$this->forward404Unless(UserPeer::isAllowed($groupTo->getId(), "group"));
	
				// change folders and files groupe_id
				$folders = FolderPeer::retrieveByGroupId($groupFrom->getId());
				foreach ($folders as $folder)
				{
					$folder->setGroupeId($groupTo->getId());
					$folder->save();
	
					$files = FilePeer::retrieveByFolderId($folder->getId());
					foreach ($files as $file)
					{
						$file->setGroupeId($groupTo->getId());
						$file->save();
					}
				}
	
				// save user rights
				if($this->getRequestParameter("rights") == "yes")
				{
					$user_groups = UserGroupPeer::retrieveByGroupId($groupFrom->getId());
					foreach ($user_groups as $user_group)
					{
						if(UserGroupPeer::getUserGroup($groupTo->getId(), $user_group->getUserId()))
							$user_group->delete();
						else
						{
							$user_group->setGroupeId($groupTo->getId());
							$user_group->save();
						}
					}
				}
	
				$this->getUser()->setFlash("success", __("The group")." \"".$groupFrom."\" ".__("was successfully merged to")." \"".$groupTo."\".", false);
	
				try {
					@unlink($groupFrom->getPathname());
				}catch (Exception $e){}
			}
		}
	
		$this->group = $groupFrom;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRemove()
	{
		$this->forward404Unless($group = GroupePeer::retrieveByPK($this->getRequestParameter("id")));
		$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));

		$this->forward404Unless($role <= RolePeer::__ADMIN && ($this->getUser()->isAdmin() && $this->getUser()->getCustomerId() == $group->getCustomerId()));

	
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
	
		$this->getResponse()->setSlot('title', __("Remove main folder")." \"".$group."\"");
	
		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			GroupePeer::deleteAlbum($group);
	
			$this->getUser()->setFlash("success", __("The group")." \"".$group."\" ".__("was successfully deleted."), true);
	
			if($this->getRequestParameter("iframe") == 1)
			{
				$this->uri = '/';
				$this->setTemplate("thankyouUri");
				return sfView::SUCCESS;
			}
	
			$this->redirect("@homepage");
		}
	
		$this->group = $group;
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeShow()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$this->forward404Unless($group = GroupePeer::retrieveByPKNoCustomer($this->getRequestParameter('id')));
		$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));
		$this->forward404If(!$role);

		
			$path = array(
					array(
							"link"	=> path("@homepage"),
							"label"	=> __("Groups")
					),
					array(
							"link"	=> path("@album_show", array("id" => $group->getId())),
							"label"	=> $group->getName()
					)
			);


		$bread_crumbs = array(
				"bread" => $path,
				"parameters" => $this->getPartial("group/breadcrumbParameters", array("group" => $group))
		);

		if ($role <= RolePeer::__CONTRIB && $group->getFirstFolder()) {
			$this->getResponse()->setSlot("link_upload", "upload/uploadify?folder_id=".$group->getFirstFolder()->getId());
		}
	
		$selected_tag_ids = $this->getRequestParameter("selected_tag_ids") ? $this->getRequestParameter("selected_tag_ids") : Array();
		$selected_tag_ids = array_unique($selected_tag_ids);

		$this->getResponse()->setSlot('bread_crumbs', $bread_crumbs);
	
		if($this->getRequestParameter("min_range") && $this->getRequestParameter("max_range"))
			$dateRange = Array("min" => $this->getRequestParameter("min_range"), "max" => $this->getRequestParameter("max_range"));
		else
			$dateRange = Array("min" => null, "max" => null);
	
		$this->getUser()->setAttribute("params.groups.group.show", array(
			"tags" => $selected_tag_ids,
			"date" => $dateRange,
			"me" => $this->getRequestParameter("added_by_me_input", 0)
		));

		$preferences = $this->getUser()->getPreferences("group/show", true, array("sort" => "creation_desc", "perPage" => 6));

		$folders = FolderPeer::getFoldersInGroup(
			$group->getId(), 
			$selected_tag_ids, 
			null,
			$this->getRequestParameter("added_by_me_input", 0),
			$dateRange,
			$preferences["sort"],
			"N",
			null,
			$preferences["perPage"],
			0
		);

		$this->group = $group;
		$this->selected_tag_ids = $selected_tag_ids;
		$this->added_by_me_input = $this->getRequestParameter("added_by_me_input", 0);
		$this->dateRange = $dateRange;
		$this->folders = $folders["folders"];
		$this->count = $folders["count"];
		$this->itemsToShow = $preferences["perPage"];
	
		$this->getResponse()->setSlot("actions",  $this->getPartial("group/breadcrumbActions", Array(
			"group" => $group,
			"results" => array("selected" => $preferences["perPage"], "values" => array(6 => 6, 12 => 12, 24 => 24, "all" => __("All"))),
			"sorts" => array("selected" => $preferences["sort"], "values" => array("name_asc" => __("Name ascending"), "name_desc" => __("Name descending"), "creation_asc" => __("Creation date ascending"), "creation_desc" => __("Creation date descending"), "activity_asc" => __("Last activity date ascending"), "activity_desc" => __("Last activity date descending")))
		)));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeStep1()
	{
		if($this->getRequestParameter('data'))
		{
			$data = $this->getRequestParameter('data');
			$id = array_key_exists("id", $data) ? $data["id"] : null;
		} else
			$id = $this->getRequestParameter('id');
	
		$this->forward404Unless($group = GroupePeer::retrieveByPk($id));
		$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		

		$this->getResponse()->setSlot('title', ($this->getRequestParameter("navigation") == "create" || $group->isNew()) ? __("Create a group") : __("Manage group")." \"".$group."\"");

		$this->form = new GroupStep1Form(
			array(
				"id" => $group->getId(),
				"redirect" => 0,
				"name" => $group->getName(),
				"description" => $group->getDescription()
			)
		);

		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'), $this->getRequest()->getFiles('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if($this->form->isValid())
			{
				$isNew = false;
	
				if($group->isNew())
					$isNew = true;
	
				// save informations
				$group->setName($this->form->getValue('name'));
				$group->setDescription($this->form->getValue('description'));
				$group->setUserId($this->getUser()->getId());
	
				$group->save();
	
				if($isNew)
					LogPeer::setLog($this->getUser()->getId(), $group->getId(), "group-create", "1");
				else
					LogPeer::setLog($this->getUser()->getId(), $group->getId(), "group-update", "1");
	
				if($this->form->getValue("redirect") == 2)
				{
					$this->setTemplate("thankyou");
					return sfView::SUCCESS;
				}elseif($this->form->getValue("redirect") == 1)
					$this->redirect('@group_step2?id='.$group->getId().'&step=create');
	
				$this->redirect('group/show?id='.$group->getId());
			}
		}

		$this->group = $group;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThumbnailUpload()
	{
		$this->form = new GroupThumbnailForm();
	
		$this->form->bind($this->getRequestParameter('data'), $this->getRequest()->getFiles('data'));
	
		if($this->form->isValid())
		{
			if($thumbnail = $this->form->getValue('thumbnail'))
			{
				@mkdir(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/", 0777, true);
				$thumbnail->save(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/".$thumbnail->getOriginalName());
				@chmod(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/".$thumbnail->getOriginalName(), 0666);
				$size = getimagesize(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/".$thumbnail->getOriginalName());
	
				$this->thumbnail = $thumbnail->getOriginalName();
	
				if($size[0] > $size[1])
					$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);
				else
					$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);
	
				$this->new_width = $new["width"];
				$this->new_height = $new["height"];
	
				$this->form = new GroupThumbnailForm(
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
	public function executeChangeRight()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($userGroup = UserGroupPeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($role = RolePeer::retrieveByPK($this->getRequestParameter("role")));
	
			$userGroup->setRole($role->getId());
			$userGroup->save();
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array("errorCode" => 0)));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRevoke()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($userGroup = UserGroupPeer::retrieveByPk($this->getRequestParameter("id")));
		
			$userGroup->delete();
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array("errorCode" => 0)));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	protected function sendInvitation($user, $groupe, $right, $free = false)
	{
		$roles = array(RolePeer::__ADMIN =>__("Manager"), RolePeer::__CONTRIB => __("Contributor"), RolePeer::__READER => __("Reader"));
		$from = UserPeer::retrieveByPk($this->getUser()->getId());
	
		$search = Array("**FROM_USER**", "**URL**", "**ALBUM_NAME**", "**ROLE**");
		$replace = Array($from->getFullname(), url_for("@group_show?id=".$groupe->getId(), true), $groupe->getName(), $roles[$right]);
	
		$email = new myMailer("invitation_free_send", "[wikiPixel] ".$from->getFullname()." ".__("share files with you via Wikipixel"));
		$email->setTo(Array($user->getEmail() => $user->getEmail()));
		$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getOwnFromEmail($this->getUser()->getCustomerId())));
		$email->compose($search, $replace);
		$email->send();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeImportUserGroup()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
			$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));
			$this->forward404If(!$role || $role > RolePeer::__ADMIN);
			$this->forward404Unless($import_group = GroupePeer::retrieveByPk($this->getRequestParameter("import_group")));

			$totalImport = $this->getRequestParameter("total");
			$collision = $this->getRequestParameter("collision");

			$group->setFree($import_group->getFree());
			$group->getFreeCredential($import_group->getFreeCredential());
			$group->save();

			if($totalImport == "true") {
				$user_groups = UserGroupPeer::retrieveByGroupId($group->getId());

				foreach ($user_groups as $user_group) {
					$user_group->delete();
				}

				$userGroups = UserGroupPeer::retrieveByGroupId($import_group->getId());

				foreach ($userGroups as $userGroup) {
					$newUserGroup = new UserGroup();
					$newUserGroup->setUserId($userGroup->getUserId());
					$newUserGroup->setGroupeId($group->getId());
					$newUserGroup->setRole($userGroup->getRole());
					$newUserGroup->setState(UserGroupPeer::__STATE_ACTIVE);
					$newUserGroup->save();
				}
			}
			else {
				$existing = array();
				$userGroups = UserGroupPeer::retrieveByGroupId($group->getId());

				foreach ($userGroups as $userGroup) {
					$existing[$userGroup->getId()] = $userGroup->getUserId();
				}

				$userGroup = UserGroupPeer::retrieveByGroupId($import_group->getId());

				foreach ($userGroup as $userGroup) {
					if ($key = array_search($userGroup->getUserId(), $existing)) {
						if ($collision == "true") {
							UserGroupPeer::retrieveByPk($key)->delete();

							$newUserGroup = new UserGroup();
							$newUserGroup->setUserId($userGroup->getUserId());
							$newUserGroup->setGroupeId($group->getId());
							$newUserGroup->setRole($userGroup->getRole());
							$newUserGroup->setState(UserGroupPeer::__STATE_ACTIVE);
							$newUserGroup->save();
						}
					}
					else {
						$newUserGroup = new UserGroup();
						$newUserGroup->setUserId($userGroup->getUserId());
						$newUserGroup->setGroupeId($group->getId());
						$newUserGroup->setRole($userGroup->getRole());
						$newUserGroup->setState(UserGroupPeer::__STATE_ACTIVE);
						$newUserGroup->save();
					}
				}
			}

			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array("errorCode" => 0, "access" => $group->getFree() ? $group->getFreeCredential() : 0, "html" => $this->getComponent("group", "detailsUserGroup", array("group" => $group, "type" => $group->getFree() ? "free" : "managed")))));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter('id')));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($group->getId()));
			$this->forward404If($roleGroup > RolePeer::__ADMIN);

			$value = $this->getRequestParameter('value');
			$field = $this->getRequestParameter('field');
	
			switch($field)
			{
				case "name":
					if(!empty($value))
						$group->setName($value);
					else
						$value = $group->getName();
				break;
	
				case "description":
					$group->setDescription($value);
				break;
			}
	
			$group->save();
	
			if(empty($value))
				return $this->renderText('<span style="cursor: pointer;" class="nc">'.__("Add a description.").'</span>');
	
			return $this->renderText($value);
		}

		$this->redirect404();
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

		$this->forward404Unless($group = GroupePeer::retrieveByPK($id));
		$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		$this->form = new GroupThumbnailForm(
			array(
				"id" => $group->getId(),
				"step" => $step,
				"uploaded_thumbnail_name" => $group->getThumbnail(),
				"is_upload" => 0
			)
		);

		$this->getResponse()->setSlot('title', __("Manage group")." \"".$group."\"");
	
		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'), $this->getRequest()->getFiles('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if($this->form->isValid())
			{
				$path = sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$group->getCustomerId()."/groups/";
	
				// save thumbnail_image
				if ($this->form->getValue("is_upload") && $uploaded_thumbnail_name = $this->form->getValue("uploaded_thumbnail_name"))
				{
					$x1 = $this->form->getValue("x1");
					$y1 = $this->form->getValue("y1");
					$x2 = $this->form->getValue("x2");
					$y2 = $this->form->getValue("y2");
					$w = $this->form->getValue("w");
					$h = $this->form->getValue("h");
	
					$ext = myTools::getFileExtension($uploaded_thumbnail_name);
					$k = time();
					$cropped = $k.".".$ext;
	
					while(file_exists($path.$cropped))
					{
						$k++;
						$cropped = $k.".".$ext;
					}
	
					/*$size = getimagesize($path.$uploaded_thumbnail_name);
	
					if($size[0] != $this->form->getValue("width") || $size[1] != $this->form->getValue("height"))
						imageTools::resizeImage($path.$uploaded_thumbnail_name, $this->form->getValue("width"), $this->form->getValue("height"), 1);
	
					$scale = 220/$w;*/
					try {
						/*imageTools::resizeThumbnailImage($path.$cropped, $path.$uploaded_thumbnail_name, $w, $h, $x1, $y1, $scale, $this->form->getValue("width"), $this->form->getValue("height"));*/
	
						@copy($path.$uploaded_thumbnail_name, $path.$cropped);
						@unlink($path.$group->getThumbnail());
						@unlink($path.$uploaded_thumbnail_name);
					}catch (Exception $e){}
	
					$group->setDiskId($this->getUser()->getDisk()->getId());
					$group->setThumbnail($cropped);
				}
	
				$group->save();
	
				$this->uri = 'group/show?id='.$group->getId();
				$this->setTemplate("thankyouUri");
			}
		}
	
		$this->group = $group;
		$this->navigation = $step;
		$this->folders = FolderPeer::getFolderInArray(0, $group->getId());
		$this->files = FilePeer::retrieveByGroupId($group->getId());
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRightConstraintList(sfWebRequest $request)
	{
		$albumId = $request->getParameter("album");
		$type = $request->getParameter("type");

		$this->forward404Unless($album = GroupePeer::retrieveByPK($albumId));
		$this->forward404Unless($role = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		$constraints = array(
				""					=> array (
					"label"			=> __("Global constraints"),
					"constraints"	=> ConstraintPeer::retrieveByRoleId()
					),
				RolePeer::__ADMIN	=> array (
					"label"			=> __("Managers constraints"),
					"constraints"	=> ConstraintPeer::retrieveByRoleId(RolePeer::__ADMIN)
					),
				RolePeer::__CONTRIB	=> array (
					"label"			=> __("Contributors constraints"),
					"constraints"	=> ConstraintPeer::retrieveByRoleId(RolePeer::__CONTRIB)
					),
				RolePeer::__READER	=> array (
					"label"			=> __("Readers constraints"),
					"constraints"	=> ConstraintPeer::retrieveByRoleId(RolePeer::__READER)
					)
		);

		$values = GroupeConstraintPeer::retrieveByRoleId($album->getId(), $type);

		$this->values = $values;
		$this->constraints = $constraints;
		$this->album = $album;
		$this->type = $type;

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeRightConstraintUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$albumId = $request->getParameter("album");
		$constraintId = $request->getParameter("constraint");
		$delete = $request->getParameter("delete");

		$album = GroupePeer::retrieveByPK($albumId);
		$constraint = ConstraintPeer::retrieveByPK($constraintId);

		$this->forward404Unless($album);
		$this->forward404Unless($constraint);

		$this->forward404Unless($role = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		$albumConstraint = GroupeConstraintPeer::retrieveByGroupeIdAndConstraintId($album->getId(),
				$constraint->getId());

		if (!$delete) {
			if (!$albumConstraint) {
				$albumConstraint = new GroupeConstraint();
				$albumConstraint->setGroupeId($album->getId());
				$albumConstraint->setConstraintId($constraint->getId());
				$albumConstraint->save();
			}
		}
		else {
			if ($albumConstraint) {
				$albumConstraint->delete();
			}
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCreate()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($this->getUser()->isAdmin());
	
			$this->getResponse()->setContentType('application/json');
	
			if($groupe = GroupePeer::retrieveByName($this->getRequestParameter("name")))
				return $this->renderText(json_encode(array("code" => 1, "html" => "group already exist")));
			else
			{
				$group = new Groupe();
				$group->setCustomerId($this->getUser()->getCustomerId());
				$group->setState(GroupePeer::__STATE_ACTIVE);
				$group->setType(GroupePeer::__TYPE_PROD);
				//$group->setFree($this->getRequestParameter("access") == "free" ? true : false);
				$group->setFree(false);

				$group->setName($this->getRequestParameter("name"));
				$group->setUserId($this->getUser()->getId());
				$group->setFreeCredential($this->getRequestParameter("access") == "free" ? $this->getRequestParameter("role") : null);
	
				$group->save();
	
				LogPeer::setLog($this->getUser()->getId(), $group->getId(), "group-create", "1");
	
				if($this->getRequestParameter("next_step") == "true")
					return $this->renderText(json_encode(array("code" => 0, "html" => $this->getComponent("folder", "speedStep", array("subfolder" => null, "group_id" => $group->getId())))));
				else
					return $this->renderText(json_encode(array("code" => 0, "html" => "group/show?id=".$group->getId())));
			}
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSpeedStep()
	{
		if($this->getRequest()->isXmlHttpRequest())
			return $this->renderPartial("group/speedStep", array("next_step" => $this->getRequestParameter("next_step")));
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGetFilesThumb()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
	
			if($this->getRequestParameter("folder_id") == "all")
				return $this->renderPartial("group/filesThumb", array("files" => FilePeer::retrieveByGroupId($group->getId())));
			else
			{
				$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));
				return $this->renderPartial("group/filesThumb", array("files" => FilePeer::retrieveByFolderId($folder->getId())));
			}
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThumbnailUploadFromWikipixel()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
		$this->form = new GroupThumbnailForm();
	
		@mkdir(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/", 0777, true);
		@copy($file->getPathname(), sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/".$file->getOriginal());
		@chmod(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/".$file->getOriginal(), 0666);
		$size = getimagesize(sfConfig::get("app_path_upload_dir")."/".$this->getUser()->getDisk()->getPath()."/cust-".$this->getUser()->getCustomerId()."/groups/".$file->getOriginal());

		$this->thumbnail = $file->getOriginal();

		if($size[0] > $size[1])
			$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);
		else
			$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);

		$this->new_width = $new["width"];
		$this->new_height = $new["height"];

		$this->form = new GroupThumbnailForm(
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
	public function executeTags()
	{
		$this->forward404Unless($this->group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($role = $this->getUser()->getRole($this->group->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		$this->getResponse()->setSlot('title', __("Manage group")." \"".$this->group."\"");
	
		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			FileTagPeer::deletByTypeFileId(1, $this->group->getId());
			$tags_name = $this->getRequestParameter("tags_input") ? explode("|", $this->getRequestParameter("tags_input")) : Array();
	
			$folders = FolderPeer::retrieveByGroupId($this->group->getId());
			$files = FilePeer::retrieveByGroupId($this->group->getId());
	
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
	
					if(!FileTagPeer::getFileTag(1, $this->group->getId(), $tag->getId()))
					{
						$file_tag = new FileTag();
						$file_tag->setType(1);
						$file_tag->setFileId($this->group->getId());
						$file_tag->setTagId($tag->getId());
						$file_tag->save();
					}
	
					foreach ($folders as $folder)
					{
						if(!FileTagPeer::getFileTag(2, $folder->getId(), $tag->getId()))
						{
							$file_tag = new FileTag();
							$file_tag->setTagId($tag->getId());
							$file_tag->setFileId($folder->getId());
							$file_tag->setType(2);
							$file_tag->save();
						}
					}
	
					foreach ($files as $file)
					{
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
			}
	
			if(!empty($tag_ids) && $files)
			{
				foreach ($files as $file)
					myTools::addTags($file, false);
			}
	
			$this->getUser()->setFlash("success", __("Tags were updated successfully."), false);
		}
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFields()
	{
		$this->forward404Unless($this->group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($role = $this->getUser()->getRole($this->group->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);
	
		$this->getResponse()->setSlot('title', __("Manage group")." \"".$this->group."\"");
	
		$this->types = FieldPeer::getTypes();
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeReturn(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$this->forward404Unless($album = GroupePeer::retrieveByPk($this->getRequestParameter("album")));
		$this->forward404Unless($user = UserPeer::retrieveByPk($this->getRequestParameter("user")));
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);

		$object = UserGroupPeer::retrieveByUserAndGroup($user->getId(), $album->getId());

		if (!$object) {
			$object = UnitGroupPeer::retrieveMinRoleByGroupIdAndUserId($album->getId(), $user->getId());
		}

		if(!$album->getFree() || ($object && $album->getFree() && $object->getRole() == RolePeer::__ADMIN)) {
			$this->sendInvitation($user, $album, $object->getRole());
		}
		elseif($album->getFree()) {
			$this->sendInvitation($user, $album, $album->getFreeCredential(), true);
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAddField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($groupe = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
	
			$this->getResponse()->setContentType('application/json');
	
			if(FieldPeer::retrieveByNameAndGroupId($this->getRequestParameter("name"), $groupe->getId()))
				return $this->renderText(json_encode(array("errorCode" => 1, "message" => "already exists")));
	
			parse_str($this->getRequestParameter("values"), $values);
	
			$field = new Field();
			$field->setGroupeId($groupe->getId());
			$field->setType($this->getRequestParameter("type"));
			$field->setName($this->getRequestParameter("name"));
	
			if($this->getRequestParameter("type") == FieldPeer::__TYPE_SELECT && !empty($values))
				$field->setValues(base64_encode(serialize($values)));
			else
				$field->setValues(null);
	
			$field->save();
	
			return $this->renderText(json_encode(array("errorCode" => 0, "message" => $this->getComponent("group", "listFields", array("group_id" => $groupe->getId())))));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRemoveField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($groupe = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
			$this->forward404Unless($field = FieldPeer::retrieveByPk($this->getRequestParameter("id")));
	
			$field->delete();
	
			return $this->renderText(json_encode(array("errorCode" => 0, "message" => $this->getComponent("group", "listFields", array("group_id" => $groupe->getId())))));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFieldsOfField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($field = FieldPeer::retrieveByPk($this->getRequestParameter('id')));
	
			$value = $this->getRequestParameter('value');
			$fieldOfField = $this->getRequestParameter('field');
	
			switch($fieldOfField)
			{
				case "name":
					if(empty($value))
						$value = $field->getName();
	
					if($fieldName = FieldPeer::retrieveByNameAndGroupId($value, $field->getGroupeId()))
					{
						if($fieldName->getId() != $field->getId())
							$value = $field->getName();
					}
	
					$field->setName($value);
	
					$field->save();
	
					return $this->renderText($value);
				break;
	
				case "type":
					$field->setType($value);
	
					$field->save();
	
					$contents = FieldContentPeer::retrieveByFieldId($field->getId());
	
					foreach($contents as $content)
						$content->delete();
	
					$types = FieldPeer::getTypes();
	
					return $this->renderText($types[$value]);
				break;
	
				case "values":
					parse_str($this->getRequestParameter("value"), $values);
	
					$old_values = unserialize(base64_decode($field->getValues()));
	
					if($old_values)
					{
						foreach($old_values as $old_value)
						{
							if(!in_array($old_value, $values))
							{
								$contents = FieldContentPeer::retrieveByFieldIdAndValue($field->getId(), $old_value);
	
								foreach($contents as $content)
									$content->delete();
							}
						}
					}
	
					$field->setValues(base64_encode(serialize($values)));
	
					$field->save();
	
					return $this->renderText(implode("<br />", unserialize(base64_decode($field->getValues()))));
				break;
			}
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadFieldType()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($field = FieldPeer::retrieveByPk($this->getRequestParameter('id')));
	
			$this->getResponse()->setContentType('application/json');
	
			$types = FieldPeer::getTypes();
	
			$array = Array();
	
			foreach($types as $key => $value)
				$array[$key] = $value;
	
			$array["selected"] = $field->getType();
	
			return $this->renderText(json_encode($array));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadFieldValue()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			if($this->getRequestParameter("id"))
			{
				$this->forward404Unless($field = FieldPeer::retrieveByPk($this->getRequestParameter('id')));
				$values = unserialize(base64_decode($field->getValues()));
			}
			else
			{
				$field = null;
				$values = null;
			}
	
			if($this->getRequestParameter("values"))
				parse_str($this->getRequestParameter("values"), $values);
	
			return $this->renderPartial("group/fieldValues", array("field" => $field, "values" => $values));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeManageTags()
	{
		$this->forward404Unless($this->group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($role = $this->getUser()->getRole($this->group->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		if($this->getRequestParameter("manage_tag_session") == "start")
		{
			$this->getUser()->setAttribute("manage_tag_keyword", "");
			$this->getUser()->setAttribute("manage_tag_sort", "name_asc");
			$this->getUser()->setAttribute("manage_tag_l", '');
		}
		else
		{
			if($this->getRequestParameter("is_search"))
				$this->getUser()->setAttribute("manage_tag_keyword", $this->getRequestParameter("keyword"));
	
			if($this->getRequestParameter("sort"))
				$this->getUser()->setAttribute("manage_tag_sort", $this->getRequestParameter("sort"));
	
			if($this->getRequestParameter("l"))
				$this->getUser()->setAttribute("manage_tag_l", $this->getRequestParameter("l"));
		}
	
		$this->getResponse()->setSlot('title', __("Manage group")." \"".$this->group."\"");
	
		$this->l = $this->getUser()->getAttribute("manage_tag_l");
	
		$this->letters = TagPeer::getLetters();
	
		$this->tag_pager = TagPeer::getTagPager($this->getUser()->getAttribute("manage_tag_keyword"), $this->getUser()->getAttribute("manage_tag_sort"), $this->getRequestParameter("page"), $this->l, $this->group->getId());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadExpiration()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($user_right = UserGroupPeer::retrieveByPk($this->getRequestParameter("id")));
	
			return $this->renderPartial("group/expiration", array("user_right" => $user_right));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSaveExpiration()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($user_right = UserGroupPeer::retrieveByPk($this->getRequestParameter("id")));
	
			if($this->getRequestParameter("type") == "unlimited")
				$user_right->setExpiration(null);
			else
			{
				$expiration = $this->getRequestParameter("expiration");
				$temp = explode(" ", $expiration);
				$temp1 = explode("/", $temp[0]);
	
				$user_right->setExpiration($temp1[2]."-".$temp1[1]."-".$temp1[0]." ".$temp[1]);
			}
	
			$user_right->save();
	
			return sfView::NONE;
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadHomeGroups()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$page = $this->getRequestParameter("page");
			$perPage = $this->getRequestParameter("onPage");
			$sort = $this->getRequestParameter("sort");
	
			$temp = GroupePeer::getHomeGroups($perPage, ($perPage * ($page - 1)), $sort);
			$this->getUser()->savePreferences("group/home", $sort, $perPage);
	
			$return = Array();
			$return["groups"] = "";
			$return["rightclick"] = "";
	
			foreach($temp["groups"] as $group)
			{
				$return["groups"] .= $this->getPartial("group/grid", Array("group" => $group));
				// $return["rightclick"] .= $this->getPartial("group/rightClick", Array("group" => $group));
			}
	
			$return["index"] = $temp["count"] > ($page * $perPage) ? ($page * $perPage) : 0;
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode($return));
		}
	
		$this->redirect404();
	}

	
	/*________________________________________________________________________________________________________________*/
	public function executeLoadHomeGroupsPrivate()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$page = $this->getRequestParameter("page");
			$perPage = $this->getRequestParameter("onPage");
			$sort = $this->getRequestParameter("sort");
	
			$temp = GroupePeer::getHomeGroupsPrivate($perPage, ($perPage * ($page - 1)), $sort);

			$this->getUser()->savePreferences("group/private", $sort, $perPage);
	
			$return = Array();
			$return["groups"] = "";
			$return["rightclick"] = "";
	
			foreach($temp["groups"] as $group)
			{
				$return["groups"] .= $this->getPartial("group/grid", Array("group" => $group));
				// $return["rightclick"] .= $this->getPartial("group/rightClick", Array("group" => $group));
			}
	
			$return["index"] = $temp["count"] > ($page * $perPage) ? ($page * $perPage) : 0;
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode($return));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadSidebar()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));
			$this->forward404If(!$role);

			$return = Array();
			$return["html"] = $this->getComponent("group", "sidebar", Array("group" => $group, "tagsSelected" => TagPeer::retrieveByPks($this->getRequestParameter("selected_tag_ids")), "addedByMe" => $this->getRequestParameter("added_by_me_input"), "min" => $this->getRequestParameter("min"), "max" => $this->getRequestParameter("max")));
	
			return $this->renderText(json_encode($return));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeShare()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter('id')));
			$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));
			$this->forward404If(!$role || $role > RolePeer::__ADMIN);

			return $this->renderPartial("group/share", Array("group" => $group));
		}

		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executePublicShow()
	{
		$link = $this->getRequestParameter('link');
		$this->permalink = PermalinkPeer::getByLink($link);
		$user = UserPeer::retrieveByPkNoCustomer($this->permalink->getUserId());

		if(ModulePeer::haveAccessModule(ModulePeer::__MOD_PERMALINK, $user->getCustomerId(), $user->getId()))
		{
			if(($this->permalink && $this->permalink->getState() == PermalinkPeer::__STATE_DISABLED) || (!$this->permalink))
				$this->redirect404();

			if($this->permalink->getObjectType() == PermalinkPeer::__OBJECT_GROUP)
			{
				if($this->permalink->getState() == PermalinkPeer::__STATE_PRIVATE && $this->getUser()->getAttribute("group-permalink-authenticated") != $this->permalink->getId())
				{
					$this->getRequest()->setParameter('id', $this->permalink->getId());
					$this->forward("group", "authentication");
				}

				$this->forward404Unless($this->group = GroupePeer::retrieveByPKNoCustomer($this->permalink->getObjectId()));

				if(!$this->getRequestParameter("folder_id"))
				{
					$this->folder = null;

					$this->breadcrumb = Array();
					$this->breadcrumb[] = $this->group->getName();

					$c = new Criteria();
					$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
					$c->add(FolderPeer::SUBFOLDER_ID, null);
					$c->add(FolderPeer::GROUPE_ID, $this->group->getId());
					$c->addDescendingOrderByColumn(FolderPeer::NAME);

					$this->folders = FolderPeer::doSelect($c);

					$this->files = null;
				}
				else
				{
					$this->forward404Unless($this->folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));

					$bread = explode('|',FolderPeer::getBreadCrumbTxtPublic($this->folder->getId(), $this->permalink->getObjectId()));
					array_splice($bread, count($bread) - 1);
					krsort($bread);
					$this->breadcrumb = Array();
					$this->breadcrumb[] = $this->group;
					foreach($bread as $case)
					{
						if(!empty($case))
						{
							$folder_bread = unserialize($case);
							$this->breadcrumb[] = $folder_bread;
						}
					}

					$c = new Criteria();
					$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
					$c->add(FolderPeer::SUBFOLDER_ID, $this->folder->getId());
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
				}

				PermalinkLogPeer::addLog($_SERVER["REMOTE_ADDR"], $this->permalink->getId(), PermalinkLogPeer::__PERMALINK);


				$this->customer = CustomerPeer::retrieveByPk($this->group->getCustomerId());

				return sfView::SUCCESS;
			}
		}
		else
			$this->redirect404();

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

		$this->form = new GroupAuthenticationForm(
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
				$this->getUser()->setAttribute("group-permalink-authenticated", $id);

				$this->redirect("group/publicShow?link=".$permalink->getLink());
			}
		}

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeComment()
	{
		$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($role = $this->getUser()->getRole($group->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		$this->forward404Unless($permalink = PermalinkPeer::getByObjectId($group->getId(), PermalinkPeer::__TYPE_CUSTOM, PermalinkPeer::__OBJECT_GROUP));

		$this->getResponse()->setSlot('title', __("List of comments of main folder \"%1%\"", Array("%1%" => $group)));

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

		return $this->renderText(json_encode(Array("html" => $comment->getComment(), "js" => $this->getRequestParameter("comment"))));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeChangeCredential()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($role = RolePeer::retrieveByPK($this->getRequestParameter("role")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($group->getId()));
			$this->forward404If($roleGroup > RolePeer::__ADMIN);

			$group->setFree(true);
			$group->setFreeCredential($role->getId());
			$group->save();

			UserGroupPeer::updateToFreeAccess($group->getId());

			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array(
				"errorCode" => 0,
				"html" => $this->getComponent("group", "detailsUserGroup", array("group" => $group, "type" => "free"))
			)));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDeleteCredential()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($group->getId()));
			$this->forward404If($roleGroup > RolePeer::__ADMIN);

			$group->setFree(false);
			$group->setFreeCredential(null);
			$group->save();
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(array(
				"errorCode" => 0,
				"html" => $this->getComponent("group", "detailsUserGroup", array("group" => $group, "type" => "managed"))
			)));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeWaiting()
	{
		$this->forward404Unless($this->group = GroupePeer::retrieveByPK($this->getRequestParameter("id")));
		$this->forward404Unless($role = $this->getUser()->getRole($this->group->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		$this->getResponse()->setSlot('title', __("Manage group")." \"".$this->group."\"");

		if ($this->getRequestParameter("sort")) {
			$this->getUser()->setAttribute("waiting_sort", $this->getRequestParameter("sort"));
		}

		if ($this->getRequestParameter("state")) {
			$this->getUser()->setAttribute("waiting_state", $this->getRequestParameter("state"));
		}

		if ($this->getRequestParameter("keyword")) {
			$this->getUser()->setAttribute("waiting_keyword", $this->getRequestParameter("keyword"));
		}

		if (!$this->getUser()->getAttribute("waiting_sort")) {
			$this->getUser()->setAttribute("waiting_sort", "name_asc");
		}

		if (!$this->getUser()->getAttribute("waiting_state")) {
			$this->getUser()->setAttribute("waiting_state", FileWaitingPeer::__STATE_WAITING_VALIDATE);
		}

		if (!$this->getUser()->getAttribute("waiting_keyword")) {
			$this->getUser()->setAttribute("waiting_keyword", "");
		}

		$this->sort = $this->getUser()->getAttribute("waiting_sort");
		$this->state = $this->getUser()->getAttribute("waiting_state");
		$this->keyword = $this->getUser()->getAttribute("waiting_keyword");
		$this->pager = FileWaitingPeer::getWaintingFilesPager($this->group->getId(), $this->getUser()->getAttribute("waiting_keyword"), $this->getUser()->getAttribute("waiting_state"), $this->getUser()->getAttribute("waiting_sort"));

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeRightUser(sfWebRequest $request)
	{
		$this->forward404Unless(
				$this->getUser()->isAdmin());
		$this->forward404Unless($album = GroupePeer::retrieveByPK($request->getParameter("album")));

		$user = UserPeer::retrieveByPKNoCustomer($request->getParameter("id"));

		if ($user) {
			// checks rights
			if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}

			$keyword = $request->getParameter("keyword", "");
			$letter = $request->getParameter("letter", "");
			$page = (int)$request->getParameter("page", 1);
			$itemPerPage = 10;
			$letters = array();

			$albums = GroupePeer::getAlbumsHaveAccessForUserPager($page, $itemPerPage,
					array(
						"customerId" 	=> $user->getCustomerId(),
						"userId" 		=> $user->getId()
					), array(GroupePeer::NAME => "asc"));
		
			$this->albums = $albums;
			$this->keyword = $keyword;
			$this->roles = RolePeer::getRoles();
			$this->currentLetter = $letter;
			$this->letters = $letters;
			$this->csrfToken = $this->getUser()->getCsrfToken();
		}

		$this->album = $album;
		$this->user = $user;

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeRightGroup(sfWebRequest $request)
	{
		$this->forward404Unless(
				$this->getUser()->isAdmin());
		$this->forward404Unless($album = GroupePeer::retrieveByPK($request->getParameter("album")));

		$group = UnitPeer::retrieveByPk($request->getParameter("id"));
	
		if ($group) {
			// checks rights
			if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}

			$keyword = $request->getParameter("keyword", "");
			$letter = $request->getParameter("letter", "");
			$page = (int)$request->getParameter("page", 1);
			$itemPerPage = 10;

			$rights = UnitGroupPeer::getPager($page, $itemPerPage,
					array(
						"keyword"		=> $keyword,
						"letter"		=> $letter,
						"groupId"		=> $group->getId()
			), array(UnitPeer::TITLE => "asc"));

			$letters = UnitGroupPeer::getLettersPager(
					array(
						"keyword"		=> $keyword,
						"groupId"		=> $group->getId()
			));

			$this->keyword = $keyword;
			$this->roles = RolePeer::getRoles();
			$this->rights = $rights;
			$this->currentLetter = $letter;
			$this->letters = $letters;
		}

		$this->album = $album;
		$this->group = $group;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param sfWebRequest $request
	 * @return string
	 */
	public function executeRightUserList(sfWebRequest $request)
	{
		$this->forward404Unless($album = GroupePeer::retrieveByPK($request->getParameter("album")));
		$this->forward404Unless($role = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);

		$keyword = $request->getParameter("keyword", "");
		$letter = $request->getParameter("letter", "");
		$page = (int)$request->getParameter("page", 1);
		$role = $request->getParameter("role", "");
		$state = $request->getParameter("state", "");
		$itemPerPage = 10;
		$customerId = $this->getUser()->getCustomerId();

		$users = GroupePeer::getUsersPager($page, $itemPerPage,
				array(
					"albumId"		=> $album->getId(),
					"customerId"	=> $customerId,
					"keyword"		=> $keyword,
					"userStates"	=> array(UserPeer::__STATE_ACTIVE),
					"role"			=> $role,
					"roleState"		=> $state,
					"letter"		=> $letter
				), array(UserPeer::EMAIL => "asc"));

		$letters = GroupePeer::getLettersOfUsersPager(
				array(
					"albumId"		=> $album->getId(),
					"customerId"	=> $customerId,
					"keyword"		=> $keyword,
					"userStates"	=> array(UserPeer::__STATE_ACTIVE),
					"role"			=> $role,
					"roleState"		=> $state));

		$this->keyword = $keyword;
		$this->currentLetter = $letter;
		$this->currentRole = $role;
		$this->currentState = $state;
		$this->roles = RolePeer::getRoles();
		$this->album = $album;
		$this->page = $page;

		$this->users = $users;
		$this->letters = $letters;

		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param sfWebRequest $request
	 */
	public function executeRightUserDelete(sfWebRequest $request)
	{
		$this->forward404Unless($album = GroupePeer::retrieveByPK($request->getParameter("album")));
		$this->forward404Unless($user = UserPeer::retrieveByPK($request->getParameter("user")));
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);


		//check csrf token
		SecurityUtils::checkCsrfToken();

		$userAlbum = UserGroupPeer::retrieveByUserAndGroup($user->getId(), $album->getId());

		if ($userAlbum) {
			// check rights
			if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}
	
			if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}

			$userAlbum->delete();
		}
		else {
			$access = RequestPeer::getRequest($album->getId(), $user->getId());

			if ($access) {
				$access->delete();
			}
			else {
				$this->forward404();
			}
		}

		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public function executeRightUserUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$this->forward404Unless($album = GroupePeer::retrieveByPK($request->getParameter("album")));
		$this->forward404Unless($user = UserPeer::retrieveByPK($request->getParameter("user")));
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);

		if ($request->getParameter("role") == "") {
			$userAlbum = UserGroupPeer::retrieveByUserAndGroup($user->getId(), $album->getId());

			if ($userAlbum) {
				$userAlbum->delete();
			}
		}
		else {
			$role = RolePeer::retrieveByPK($request->getParameter("role"));
			$this->forward404Unless($role);
	
			$userAlbum = UserGroupPeer::retrieveByUserAndGroup($user->getId(), $album->getId());
	
			if (!$userAlbum) {
				$userAlbum = new UserGroup();
				$userAlbum->setUserId($user->getId());
				$userAlbum->setGroupeId($album->getId());
				$userAlbum->setState(UserGroupPeer::__STATE_ACTIVE);
			}

			// check rights
			
			if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}

			if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}

			// update
			$userAlbum->setRole($role->getId());
			$userAlbum->save();
		}

		$request = RequestPeer::getRequest($album->getId(), $user->getId());

		if ($request) {
			$request->delete();
			// TODO: Envoi de notification à l'utilisateur
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public function executeRightGroupList(sfWebRequest $request)
	{
		$this->forward404Unless($album = GroupePeer::retrieveByPK($request->getParameter("album")));
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);

		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 10;
		$orderBy = $request->getParameter("orderBy", array("title_asc"));
		$letter = $request->getParameter("letter", "");
		$keyword = $request->getParameter("keyword", "");
		$role = $request->getParameter("role");
		$customerId = $this->getUser()->getCustomerId();

		$groups = UnitPeer::getPager($page, $itemPerPage,
				array(
					"albumId"		=> $album->getId(),
					"keyword"		=> $keyword,
					"customerId"	=> $customerId,
					"role"			=> $role,
					"letter"		=> $letter,
					"roleState"		=> ""
				), $orderBy);

		$letters = UnitPeer::getLettersOfPager(
			array(
				"albumId"		=> $album->getId(),
				"keyword"		=> $keyword,
				"customerId"	=> $customerId,
				"role"			=> $role,
				"roleState"		=> ""
			));

		$this->album = $album;
		$this->groups = $groups;
		$this->letters = $letters;
		$this->orderBy = $orderBy;
		$this->keyword = $keyword;
		$this->currentLetter = $letter;
		$this->currentRole = $role;
		$this->roles = RolePeer::getRoles();

		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public function executeRightGroupUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$group = UnitPeer::retrieveByPK($request->getParameter("group"));
		$role = RolePeer::retrieveByPK($request->getParameter("role"));

		$this->forward404Unless($album);
		$this->forward404Unless($group);
		$this->forward404Unless($role);

		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);

		// check rights
		
		// on vérifie que le groupe appartient au même client
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		if (!RightUtils::canUpdateAlbum($album)) {
			$this->forward404();
		}

		$albumGroup = UnitGroupPeer::retrieveByUnitIdAndGroupeId($group->getId(), $album->getId());

		if (!$albumGroup) {
			// add
			$albumGroup = new UnitGroup();
		
			$albumGroup->setUnit($group);
			$albumGroup->setGroupe($album);
			$albumGroup->setRole($role->getId());
		
			$albumGroup->save();
		}
		else {
			// update
			$albumGroup->setRole($role->getId());
			
			$albumGroup->save();
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public function executeRightGroupDelete(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$group = UnitPeer::retrieveByPK($request->getParameter("group"));
		
		$this->forward404Unless($album);
		$this->forward404Unless($group);
		
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);

		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		if (!RightUtils::canUpdateAlbum($album)) {
			$this->forward404();
		}

		$albumGroup = UnitGroupPeer::retrieveByUnitIdAndGroupeId($group->getId(), $album->getId());

		if ($albumGroup) {
			$albumGroup->delete();
		}

		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public function executeRightEverybodyUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);

		
		// check rights
		$this->forward404If(!$this->getUser()->isAdmin());
	
		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$roleId = $request->getParameter("role");
		
		// on ne peut pas affecter none à everybody
		if ($roleId == "" || $roleId == RolePeer::__NONE) {
			$album->setFree(false);
			$album->setFreeCredential(null);
			$album->save();
		}
		else {
			$role = RolePeer::retrieveByPK($roleId);
			$this->forward404Unless($role || $role->getId() <= RolePeer::__ADMIN);

			$album->setFree(true);
			$album->setFreeCredential($role->getId());
			$album->save();
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadNew(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$this->forward404Unless($this->getUser()->isAdmin());

		return $this->renderText(json_encode(array("html" => $this->getPartial("group/gridBlank"))));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeNew(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$albumName = $request->getParameter("name");

		$this->forward404Unless($this->getUser()->isAdmin());

		try {
			$album = GroupePeer::retrieveByName($albumName);
	
			if (!$album) {

				if (!$albumName) {
					$errorCode = 2;
				}
				else {
					$album = new Groupe();
					$album->setCustomerId($this->getUser()->getCustomerId());
					$album->setState(GroupePeer::__STATE_ACTIVE);
					$album->setType(GroupePeer::__TYPE_PROD);
					$album->setFree(false);
					$album->setName($albumName);
					$album->setUserId($this->getUser()->getId());
					$album->save();
		
					LogPeer::setLog($this->getUser()->getId(), $album->getId(), "group-create", "1");
	
					$errorCode = 0;
					$albumGrid = $this->getPartial("group/grid", array("group" => $album));
				}
			}
			else {
				$errorCode = 3;
				$albumGrid = null;
			}
		}
		catch (Exception $e) {
			$errorCode = 1;
			$albumGrid = null;
		}

		$this->getResponse()->setContentType('application/json');
		echo json_encode(array("errorCode" => $errorCode, "album" => $albumGrid));

		return sfView::NONE;
	}
}
