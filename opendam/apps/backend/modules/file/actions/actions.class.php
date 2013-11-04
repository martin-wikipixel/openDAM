<?php

/**
 * file actions.
 *
 * @packagemedia management
 * @subpackage file
 * @author Your name here
 * @versionSVN: $Id: actions.class.php 3335 2007-01-23 16:19:56Z fabien $
 */
class fileActions extends sfActions
{
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}
	
	/**
	 * Génère une thumbnail pour une image.
	 *
	 * @param sfWebRequest $request
	 * @return string
	 */
	public function executeThumbnail(sfWebRequest $request)
	{
		$response = $this->getResponse();
		$buffer = 4096;
		$cacheDir = sfConfig::get("sf_cache_dir")."/thumbnail";
		$fileId = (int) $request->getParameter("id");
		$permalinkCode = $request->getParameter("link");
		$format = $request->getParameter("format");

		if (!file_exists($cacheDir)) {
			@mkdir($cacheDir);
		}

		$file = FilePeer::retrieveByPK($fileId);

		$this->forward404Unless($file);

		if ($this->getUser()->isAuthenticated()) {
			$albumRole = $this->getUser()->getRole($file->getGroupeId());

			$this->forward404Unless($albumRole);

			$folderRole = $this->getUser()->getRole($file->getGroupeId(), $file->getFolderId());

			$this->forward404Unless($folderRole);
		}
		else {
			$permalink = PermalinkPeer::getByLink($permalinkCode);

			$this->forward404Unless($permalink);

			switch ($permalink->getObjectType()) {
				case PermalinkPeer::__OBJECT_GROUP:
					if ($file->getGroupeId() != $permalink->getObjectId()) {
						$this->forward404();
					}
				break;

				case PermalinkPeer::__OBJECT_FOLDER:
					if (!FilePeer::isUnderFolder($file->getId(), $permalink->getObjectId())) {
						$this->forward404();
					}
				break;
			}
		}

		switch ($format) {
			case "100":
				$filename = $file->getThumb100();
			break;
			
			case "200":
				$filename = $file->getThumb200();
			break;

			case "400":
				$filename = $file->getThumb400();
			break;

			case "400w":
				$filename = $file->getThumb400W();
			break;

			case "web":
				$filename = $file->getWeb();
			break;

			case "mob":
				$filename = $file->getThumbMob();
			break;

			case "mobw":
				$filename = $file->getThumbMobW();
			break;

			case "tab":
				$filename = $file->getThumbTab();
			break;

			case "tabw":
				$filename = $file->getThumbTabW();
			break;

			case "original":
				$filename = $file->getOriginal();
			break;

			case "poster":
				$filename = $file->getFileName().".poster.jpeg";
			break;

			case "mp4":
				if ($file->getType() != FilePeer::__TYPE_VIDEO) {
					$this->forward404();
				}

				$filename = $file->getOriginal()."ToMp4.mp4";
			break;

			case "webm":
				if ($file->getType() != FilePeer::__TYPE_VIDEO) {
					$this->forward404();
				}

				$filename = $file->getOriginal()."ToWebm.webm";
			break;

			case "mp3":
				if ($file->getType() != FilePeer::__TYPE_AUDIO) {
					$this->forward404();
				}
			
				$filename = $file->getOriginal()."ToMp3.mp3";
				break;
			
			case "wav":
				if ($file->getType() != FilePeer::__TYPE_AUDIO) {
					$this->forward404();
				}
			
				$filename = $file->getOriginal()."ToWav.wav";
				break;

			default:
				$this->forward404();
			break;
		}

		$filePathnameToRead = $file->getPath().DIRECTORY_SEPARATOR.$filename;

		// set headers
		$response->setHttpHeader("Content-Type", $file->getContentTypeForThumbnail($filePathnameToRead));

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

	# file/moveSelectedSuccess
	public function executeObserveGroupId()
	{
		$this->setLayout(false);
		return sfView::SUCCESS;
	}
	
	public function executeShow()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	
		$id = $this->getRequestParameter("id");
		$index = $this->getRequestParameter('index');
		$bread_crumbs = Array();
	
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter('id')));
		$folder = $file->getFolder();
		
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));

		$bread_crumbs[] = array(
				"link" => url_for("@homepage"),
				"label" => __("Groups")
		);

		$bread_crumbs[] = array(
				"link" => url_for("group/show?id=".$folder->getGroupeId()),
				"label" => $folder->getGroupe()->getName()
		);
	
		if ($file->getState() == FilePeer::__STATE_WAITING_VALIDATE) {
			$files_array = array();
		}
		else {
			$this->forward404If($file->getState() != FilePeer::__STATE_VALIDATE); 
			$files_array = FilePeer::retrieveByFolderIdInArray($folder->getId());
			$this->forward404Unless(sizeof($files_array));
			
			if($id) {
				$index = array_search($file, $files_array);
			}
	
			$this->forward404If(($index > sizeof($files_array)) || !sizeof($files_array[$index]));
		}

		$this->getResponse()->setSlot("link_upload", "upload/uploadify?folder_id=".$folder->getId());
		$this->getResponse()->setSlot("actions",$this->getPartial("file/breadcrumbActions", Array("file" => $file)));
	
		$bread = FolderPeer::getBreadCrumbNew($folder->getId());
	
		krsort($bread);
	
		foreach($bread as $case)
			$bread_crumbs[] = $case;
	
		$bread_crumbs[] = Array("link" => url_for("file/show?id=".$file->getId()."&folder_id=".$folder->getId()), "label" => (strlen($file) > 40 ? myTools::utf8_substr(myTools::longword_break_old($file, 20), 0, 40)."..." : $file));
	
		$this->getResponse()->setSlot('bread_crumbs', $bread_crumbs);
	
		$role = false;
	
		if ($roleGroup < RolePeer::__ADMIN) {
			$role = true;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$role = true;
			}
			elseif ($file->getUserId() == $this->getUser()->getId()) {
				$role = true;
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB)
		{
			if ($this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$role = true;
			}
			elseif ($file->getUserId() == $this->getUser()->getId()) {
				$role = true;
			}
		}
	
		$this->roleGroup = $roleGroup;
		$this->role = $role;
		$this->file = $file;
		$this->folder = $folder;
		$this->index = $index;
		$this->files_array = $files_array;
	
		$this->licences = LicencePeer::getLicenceInArray();
	
		return sfView::SUCCESS;
	}

	public function executeSaveSelected()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			if($this->getRequest()->getMethod() == sfRequest::POST)
			{
				$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
				$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
				$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
				$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
				if ($roleGroup < RolePeer::__ADMIN) {
					;
				}
				elseif ($roleGroup == RolePeer::__ADMIN) {
					if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
						$this->forward404();
					}
				}
				elseif ($roleGroup == RolePeer::__CONTRIB) {
					if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
						$this->forward404();
					}
				}
				else {
					$this->forward404();
				}
	
				$limitations = UsageLimitationPeer::getLimitations();
	
				$licence = $this->getRequestParameter("licence_".$file->getId());
				$creative_commons = $this->getRequestParameter("creative_commons_select_".$file->getId());
				$use = $this->getRequestParameter("use_".$file->getId());
				$distribution = $this->getRequestParameter("distribution_".$file->getId());
				$created_at = $this->getRequestParameter("created_at_".$file->getId());
				$path = $file->getPathname();
	
				$file->setName($this->getRequestParameter("name_".$file->getId()));
				$file->setDescription($this->getRequestParameter("description_".$file->getId()));
	
				$file->setUsageDistributionId($distribution > -1 ? $distribution : null);
				$file->setUsageUseId($use > -1 ? $use : null);
				$file->setLicenceId($licence > -1 ? $licence : null);
	
				if($licence == LicencePeer::__CREATIVE_COMMONS)
					$file->setCreativeCommonsId($creative_commons > -1 ? $creative_commons : null);

				$file->save();

				if($distribution == UsageDistributionPeer::__AUTH)
				{
					foreach($limitations as $limitation)
					{
						if($file_right = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $limitation->getId()))
							$file_right->delete();
	
						$value = $this->getRequestParameter("limitation_".$limitation->getId()."_".$file->getId());
	
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
	
				if($this->getRequestParameter("author_".$file->getId()) != "" && $this->getRequestParameter("author_".$file->getId()) != __("To inform"))
				{
					if(!$exif = ExifPeer::getTag("Author", $file->getId()))
					{
						$exif = new Exif();
						$exif->setTitle("Author");
						$exif->setValue($this->getRequestParameter("author_".$file->getId()));
						$exif->setFileId($file->getId());
						$exif->setCreatedAt(time());
					} else
						$exif->setValue($this->getRequestParameter("author_".$file->getId()));
	
					$exif->save();
	
					ExifPeer::writeExif(array("Artist" => $this->getRequestParameter("author_".$file->getId()), "Author" => $this->getRequestParameter("author_".$file->getId())), $path);
	
					if(!$iptc = IptcPeer::getTag("Writer/Editor", $file->getId()))
					{
						$iptc = new Iptc();
						$iptc->setTitle("Writer/Editor");
						$iptc->setValue($this->getRequestParameter("author_".$file->getId()));
						$iptc->setFileId($file->getId());
						$iptc->setCreatedAt(time());
					} else
						$iptc->setValue($this->getRequestParameter("author_".$file->getId()));
	
					$iptc->save();
	
					if(in_array($file->getExtention(), array("jpg", "jpeg")))
						IptcPeer::writeIptc(array('2#122' => $this->getRequestParameter("author_".$file->getId())), $path);
				}
	
				if(!empty($created_at["year"]) && !empty($created_at["month"]) && !empty($created_at["day"]))
				{
					if($file->getType() == FilePeer::__TYPE_PHOTO)
					{
						if(!$exif = ExifPeer::getTag("DateTimeOriginal", $file->getId()))
						{
							$exif = new Exif();
							$exif->setTitle("DateTimeOriginal");
							$exif->setValue($created_at["year"]."-".$created_at["month"]."-".$created_at["day"]." ".$this->getRequestParameter("created_at_".$file->getId()."_hour").":".$this->getRequestParameter("created_at_".$file->getId()."_minute").":".$this->getRequestParameter("created_at_".$file->getId()."_second"));
							$exif->setFileId($file->getId());
							$exif->setCreatedAt(time());
						} else
							$exif->setValue($created_at["year"]."-".$created_at["month"]."-".$created_at["day"]." ".$this->getRequestParameter("created_at_".$file->getId()."_hour").":".$this->getRequestParameter("created_at_".$file->getId()."_minute").":".$this->getRequestParameter("created_at_".$file->getId()."_second"));
	
						$exif->save();
	
						ExifPeer::writeExif(array("DateTimeOriginal" => $created_at["year"]."-".$created_at["month"]."-".$created_at["day"]." ".$this->getRequestParameter("created_at_".$file->getId()."_hour").":".$this->getRequestParameter("created_at_".$file->getId()."_minute").":".$this->getRequestParameter("created_at_".$file->getId()."_second")), $path);
	
						if(!$iptc = IptcPeer::getTag("Date Created", $file->getId()))
						{
							$iptc = new Iptc();
							$iptc->setTitle("Date Created");
							$iptc->setValue($created_at["year"]."-".$created_at["month"]."-".$created_at["day"]." ".$this->getRequestParameter("created_at_".$file->getId()."_hour").":".$this->getRequestParameter("created_at_".$file->getId()."_minute").":".$this->getRequestParameter("created_at_".$file->getId()."_second"));
							$iptc->setFileId($file->getId());
							$iptc->setCreatedAt(time());
						} else
							$iptc->setValue($created_at["year"]."-".$created_at["month"]."-".$created_at["day"]." ".$this->getRequestParameter("created_at_".$file->getId()."_hour").":".$this->getRequestParameter("created_at_".$file->getId()."_minute").":".$this->getRequestParameter("created_at_".$file->getId()."_second"));
	
						$iptc->save();
	
						IptcPeer::writeIptc(array('2#055' => $created_at["year"]."-".$created_at["month"]."-".$created_at["day"]." ".$this->getRequestParameter("created_at_".$file->getId()."_hour").":".$this->getRequestParameter("created_at_".$file->getId()."_minute").":".$this->getRequestParameter("created_at_".$file->getId()."_second")), $path);
					}
				}
	
				FileTagPeer::deletByTypeFileId(3, $file->getId());
				$tags_name = $this->getRequestParameter("tags_input_".$file->getId()) ? explode("|", $this->getRequestParameter("tags_input_".$file->getId())) : Array();
	
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
							$file_tag->setType(3);
							$file_tag->setFileId($file->getId());
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
	
				if($this->getRequestParameter("navigation") == "upload" && $this->getUser()->haveAccessModule(ModulePeer::__MOD_APPROVAL) && !$this->getUser()->isAdmin())
					$this->getUser()->setFlash("success", "The files informations has successfully saved. The files will be available after validation.", true);
				else
					$this->getUser()->setFlash("success", "The files informations has successfully saved.", true);
	
				return $this->renderText('');
			}
		}
	
		$this->redirect404();
	}

	# EDIT SELECTED, called form folder/_buttons(selected files), upload/option
	public function executeEditSelected()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		if($this->getRequestParameter("first_call1") == 1)
		{
			if($this->getRequestParameter("navigation"))
			{
				$fileTmpsArray = FileTmpPeer::retrieveByUserIdInArray($this->getUser()->getId(), $folder->getId());
				$this->getUser()->setAttribute("files_array", $fileTmpsArray);
				FileTmpPeer::deleteByUserId($this->getUser()->getId(), $folder->getId());
			}
			else
			{
				$file_ids = $this->getRequestParameter("file_ids") ? $this->getRequestParameter("file_ids") : array();

				$files_array = array();
				$i = 1;
				foreach ($file_ids as $file_id)
					$files_array[$i++] = $file_id;
	
				$this->getUser()->setAttribute("files_array", $files_array);
			}
		}
	
		$files_array = $this->getUser()->getAttribute("files_array");
	
		if($this->getRequestParameter("navigation") == "create")
			$this->getResponse()->setSlot('title', __("Create a folder"));
		elseif($this->getRequestParameter("navigation") == "upload")
			$this->getResponse()->setSlot('title', __("Upload files"));
		else
			$this->getResponse()->setSlot('title', __("Edit files informations"));
	
		$this->navigation = $this->getRequestParameter("navigation");
		$this->folder = $folder;
		$this->files_array = $files_array;
		$this->presets = PresetPeer::retrieveByCustomerId($this->getUser()->getCustomerId());
	
		return sfView::SUCCESS;
	}
	
	# DELETE SELECTED
	public function executeDeleteSelected()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		$file_ids = $this->getRequestParameter("file_ids") ? $this->getRequestParameter("file_ids") : array();
		if(!sizeof($file_ids))
		{
			$this->getUser()->setFlash("warning", __("Please select the files to remove."), true);
			$this->redirect('folder/show?id='.$this->getRequestParameter("folder_id"));
		}
	
		if($this->getUser()->haveAccessModule(ModulePeer::__MOD_APPROVAL) && !$this->getUser()->isAdmin())
		{
			$this->getResponse()->setSlot('title', __("Request files deletion"));
			$this->deleteOnDemand = true;
			
			$this->form = new FileDeleteSelectedForm();
		}
		else
		{
			$this->getResponse()->setSlot('title', __("Remove selected files"));
			$this->deleteOnDemand = false;
		}
	
		if ($this->getRequest()->getMethod() == sfRequest::POST)
		{
			if($this->deleteOnDemand == true)
			{
				$this->form->bind($this->getRequestParameter('data'));
				$this->getResponse()->setSlot("form", $this->form);
	
				if($this->form->isValid())
				{
					foreach ($file_ids as $file_id)
					{
						$file = FilePeer::retrieveByPk($file_id);
	
						if(!FileWaitingPeer::haveWaitingFile($this->getUser()->getId(), $file->getId(), FileWaitingPeer::__STATE_WAITING_DELETE))
						{
							$fileWaiting = new FileWaiting();
							$fileWaiting->setFileId($file->getId());
							$fileWaiting->setUserId($this->getUser()->getId());
							$fileWaiting->setState(FileWaitingPeer::__STATE_WAITING_DELETE);
							$fileWaiting->setCause($this->form->getValue("reason"));
	
							$fileWaiting->save();
	
							$to = Array();
	
							$admins = UserPeer::retrieveByRoleIds(array(RolePeer::__ADMIN));
							foreach ($admins as $admin)
							{
								if($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
									$to[$admin->getEmail()] = $admin->getEmail();
							}
	
							$validators = ValidatorUserGroupPeer::retrieveByGroupeId($file->getGroupeId());
	
							foreach ($validators as $validator)
							{
								$user = UserPeer::retrieveByPk($validator->getUserId());
								$to[$user->getEmail()] = $user->getEmail();
							}
	
							sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
							$search = Array("**URL_FILE**", "**FILE_NAME**", "**USER**", "**URL**");
							$replace = Array(url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true), $file->getName(), $this->getUser()->getInstance()->getFullname(), $_SERVER["SERVER_NAME"], url_for("group/waiting?id=".$file->getGroupeId(), true));
	
							$email = new myMailer("request_delete_file", "[wikiPixel] ".__("Request for delete file")." \"".$file."\"");
							$email->setTo($to);
							$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
							$email->compose($search, $replace);
							$email->send();
						}
					}
	
					$this->getUser()->setFlash("success", "Your request to delete files was registered successfully.", true);
				}
			}
			else
			{
				foreach ($file_ids as $file_id)
				{
					if($file = FilePeer::retrieveByPK($file_id))
					{
						$file->setState(FilePeer::__STATE_DELETE);
						$file->setUpdatedAt(time());
	
						$file->save();
	
						$fileWaiting = new FileWaiting();
						$fileWaiting->setFileId($file->getId());
						$fileWaiting->setUserId($this->getUser()->getId());
						$fileWaiting->setState(FileWaitingPeer::__STATE_DELETE);
	
						$fileWaiting->save();
					}
				}
	
				$this->getUser()->setFlash("success", "The selected files removed successfully.", false);
			}
	
			$this->uri = 'folder/show?id='.$folder->getId();
			$this->setTemplate("thankyou");
		}
	
	$this->folder = $folder;
	$this->file_ids = $file_ids;
	return sfView::SUCCESS;
	}
	
	# DELETE SINGLE
	public function executeDeleteSingle()
	{
		if($this->getRequestParameter("data")) {
			$data = $this->getRequestParameter("data");
			$id = array_key_exists("id", $data) ? $data["id"] : null;
			$folder_id = array_key_exists("folder_id", $data) ? $data["folder_id"] : null;
		} else {
			$id = $this->getRequestParameter("id");
			$folder_id = $this->getRequestParameter("folder_id");
		}
	
		$this->getResponse()->setSlot('title', __("Remove file"));
	
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($folder_id));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		if($this->getUser()->haveAccessModule(ModulePeer::__MOD_APPROVAL) && !$this->getUser()->isAdmin())
		{
			$this->getResponse()->setSlot('title', __("Request file deletion"));
			$this->deleteOnDemand = true;
			
			$this->form = new FileDeleteSingleForm(
				array(
					"id" => $id,
					"folder_id" => $folder_id
				)
			);
		}
		else
		{
			$this->getResponse()->setSlot('title', __("Remove file"));
			$this->deleteOnDemand = false;
		}
	
	if ($this->getRequest()->getMethod() == sfRequest::POST)
	{
			if($this->deleteOnDemand == true)
			{
				$this->form->bind($this->getRequestParameter('data'));
				$this->getResponse()->setSlot("form", $this->form);
	
				if($this->form->isValid())
				{
					$file = FilePeer::retrieveByPk($id);
	
					if(!FileWaitingPeer::haveWaitingFile($this->getUser()->getId(), $file->getId(), FileWaitingPeer::__STATE_WAITING_DELETE))
					{
						$fileWaiting = new FileWaiting();
						$fileWaiting->setFileId($file->getId());
						$fileWaiting->setUserId($this->getUser()->getId());
						$fileWaiting->setState(FileWaitingPeer::__STATE_WAITING_DELETE);
						$fileWaiting->setCause($this->form->getValue("reason"));
	
						$fileWaiting->save();
	
						$to = Array();
	
						$admins = UserPeer::retrieveByRoleIds(array(RolePeer::__ADMIN));
						foreach ($admins as $admin)
						{
							if($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
								$to[$admin->getEmail()] = $admin->getEmail();
						}
	
						$validators = ValidatorUserGroupPeer::retrieveByGroupeId($file->getGroupeId());
	
						foreach ($validators as $validator)
						{
							$user = UserPeer::retrieveByPk($validator->getUserId());
							$to[$user->getEmail()] = $user->getEmail();
						}

						sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
						$search = Array("**URL_FILE**", "**FILE_NAME**", "**USER**", "**URL**");
						$replace = Array(url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true), $file->getName(), $this->getUser()->getInstance()->getFullname(), $_SERVER["SERVER_NAME"], url_for("group/waiting?id=".$file->getGroupeId(), true));

						$email = new myMailer("request_delete_file", "[wikiPixel] ".__("Request for delete file")." \"".$file."\"");
						$email->setTo($to);
						$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
						$email->compose($search, $replace);
						$email->send();
					}
	
					$this->getUser()->setFlash("success", "Your request to delete file was registered successfully.", true);
				}
			}
			else
			{
	
				if($file = FilePeer::retrieveByPK($id))
				{
					$file->setState(FilePeer::__STATE_DELETE);
					$file->setUpdatedAt(time());
	
					$file->save();
	
					$fileWaiting = new FileWaiting();
					$fileWaiting->setFileId($file->getId());
					$fileWaiting->setUserId($this->getUser()->getId());
					$fileWaiting->setState(FileWaitingPeer::__STATE_DELETE);
	
					$fileWaiting->save();
				}
	
				$this->getUser()->setFlash("success", "File removed successfully.", false);
			}
	
	
			$this->uri = 'folder/show?id='.$folder->getId();
			$this->setTemplate("thankyou");
		}
	
		$this->folder = $folder;
		$this->id = $id;
	
		return sfView::SUCCESS;
	}
	
	# MOVE SELECTED
	public function executeMoveSelected()
	{
	$this->getResponse()->setSlot('title', __("Move selected files"));
	
	$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
	$file_ids = $this->getRequestParameter("file_ids") ? $this->getRequestParameter("file_ids") : array();
	if(!sizeof($file_ids))
	{
	$this->getUser()->setFlash("warning", __("Please select the files to move."), false);
	$this->redirect('folder/show?id='.$this->getRequestParameter("folder_id"));
	}
	
	//bread_crumbs
	$bread_crumbs = array();
	$bread_crumbs[] = "<a style='z-index:30;' href='/group/show?id=".$folder->getGroupeId()."'>".$folder->getGroupe()."</a>";
	$bread_crumbs[] = "<a style='z-index:25;' href='/folder/show?id=".$folder->getId()."'>".$folder."</a>";
	
		$this->getResponse()->setSlot('bread_crumbs', $bread_crumbs);
	
	if ($this->getRequest()->getMethod() == sfRequest::POST)
	{
	$this->forward404Unless($folder1 = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id1")));
	
	foreach ($file_ids as $file_id)
	{
	if($file = FilePeer::retrieveByPK($file_id))
	{
	$path = $folder->getPathToSave().DIRECTORY_SEPARATOR;
	$path_to = $folder1->getPathToSave().DIRECTORY_SEPARATOR;
	
			@mkdir($path_to, 0777, true);
	
	$file->setGroupeId($folder1->getGroupeId());
	$file->setFolderId($folder1->getId());
	$file->save();
	
	try
	{
				if(file_exists($path.$file->getFileName().".poster.jpeg"))
					copy($path.$file->getFileName().".poster.jpeg", $path_to.$file->getFileName().".poster.jpeg");
	
				if(file_exists($path.$file->getFileName().".flv"))
					copy($path.$file->getFileName().".flv", $path_to.$file->getFileName().".flv");
	
	copy($path.$file->getOriginal(), $path_to.$file->getOriginal());
	copy($path.$file->getWeb(), $path_to.$file->getWeb());
	copy($path.$file->getThumb100(), $path_to.$file->getThumb100());
	copy($path.$file->getThumb200(), $path_to.$file->getThumb200());
	
	// @unlink($path.$file->getThumb100());
	// @unlink($path.$file->getThumb200());
	// @unlink($path.$file->getWeb());
	// @unlink($path.$file->getOriginal());
				// @unlink($path.$file->getFileName().".poster.jpeg");
				// @unlink($path.$file->getFileName().".flv");
	}catch (Exception $e)
	{
	
	}
	}
	}
	
	$this->getUser()->setFlash("success", __("The selected files moved successfully."), false);
	//return $this->redirect('folder/show?id='.$this->getRequestParameter("folder_id1"));
	$this->uri = 'folder/show?id='.$this->getRequestParameter("folder_id1");
	$this->setTemplate("thankyou");
	}
	
	$this->folder = $folder;
	$this->file_ids = $file_ids;
	return sfView::SUCCESS;
	}

	public function executeCopySelected()
	{
	$this->getResponse()->setSlot('title', __("Copy selected files"));
	
	$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			;
		}
		else {
			$this->forward404();
		}
	
	$file_ids = $this->getRequestParameter("file_ids") ? $this->getRequestParameter("file_ids") : array();
	if(!sizeof($file_ids))
	{
	$this->getUser()->setFlash("warning", __("Please select the files to move."), false);
	$this->redirect('folder/show?id='.$this->getRequestParameter("folder_id"));
	}
	
	//bread_crumbs
	$bread_crumbs = array();
	$bread_crumbs[] = "<a style='z-index:30;' href='/group/show?id=".$folder->getGroupeId()."'>".$folder->getGroupe()."</a>";
	$bread_crumbs[] = "<a style='z-index:25;' href='/folder/show?id=".$folder->getId()."'>".$folder."</a>";
	
		$this->getResponse()->setSlot('bread_crumbs', $bread_crumbs);
	
	if ($this->getRequest()->getMethod() == sfRequest::POST)
	{
	$this->forward404Unless($folder1 = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id1")));
	
	foreach ($file_ids as $file_id)
	{
	if($file = FilePeer::retrieveByPK($file_id))
	{
	$path = $folder->getPathToSave().DIRECTORY_SEPARATOR;
	$path_to = $folder1->getPathToSave().DIRECTORY_SEPARATOR;
	
			@mkdir($path_to, 0777, true);
	
			$new_file = $file->copy(true);
	
			$new_file->setGroupeId($folder1->getGroupeId());
			$new_file->setFolderId($folder1->getId());
			$new_file->setCreatedAt(date("Y-m-d H:i:s"));
			$new_file->save();
	
			$tags = FileTagPeer::retrieveByFileIdType(FileTagPeer::__TYPE_FILE, $file->getId());
	
			foreach($tags as $tag)
			{
				$new_tag = $tag->copy(true);
				$new_tag->setFileId($new_file->getId());
	
				$new_tag->save();
			}
	
			$fields = FieldContentPeer::retrieveByObjectIdAndObjectType($file->getId(), FieldContentPeer::__FILE);
	
			foreach($fields as $field)
			{
				$new_field = $field->copy(true);
				$new_field->setObjectId($new_file->getId());
	
				$new_field->save();
			}
	
	try
	{
	@copy($path.$file->getOriginal(), $path_to.$file->getFileName().".poster.jpeg");
	@copy($path.$file->getOriginal(), $path_to.$file->getFileName().".flv");
	copy($path.$file->getOriginal(), $path_to.$file->getOriginal());
	copy($path.$file->getWeb(), $path_to.$file->getWeb());
	copy($path.$file->getThumb100(), $path_to.$file->getThumb100());
	copy($path.$file->getThumb200(), $path_to.$file->getThumb200());
	
	}catch (Exception $e)
	{
	
	}
	}
	}
	
	$this->getUser()->setFlash("success", __("The selected files copied successfully."), false);
	//return $this->redirect('folder/show?id='.$this->getRequestParameter("folder_id1"));
	$this->uri = 'folder/show?id='.$this->getRequestParameter("folder_id1");
	$this->setTemplate("thankyou");
	}
	
	$this->folder = $folder;
	$this->file_ids = $file_ids;
	return sfView::SUCCESS;
	}
	
	public function executeMove()
	{
	$this->getResponse()->setSlot('title', __("Move file"));
	
	$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
	$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
		
		$this->min = null;
	
		if($this->getRequestParameter('min'))
			$this->min = $this->getRequestParameter('min');
	
	if ($this->getRequest()->getMethod() == sfRequest::POST)
	{
	$this->forward404Unless($folder1 = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id1")));
		
		$path = $folder->getPathToSave().DIRECTORY_SEPARATOR;
		$path_to = $folder1->getPathToSave().DIRECTORY_SEPARATOR;
	
		@mkdir($path_to, 0777, true);
	
		$file->setGroupeId($folder1->getGroupeId());
		$file->setFolderId($folder1->getId());
		$file->save();
	
		try
		{
			if(file_exists($path.$file->getFileName().".poster.jpeg"))
				copy($path.$file->getFileName().".poster.jpeg", $path_to.$file->getFileName().".poster.jpeg");
	
			if(file_exists($path.$file->getFileName().".flv"))
				copy($path.$file->getFileName().".flv", $path_to.$file->getFileName().".flv");
	
			copy($path.$file->getOriginal(), $path_to.$file->getOriginal());
			copy($path.$file->getWeb(), $path_to.$file->getWeb());
			copy($path.$file->getThumb200(), $path_to.$file->getThumb200());
			copy($path.$file->getThumb100(), $path_to.$file->getThumb100());
	
			// @unlink($path.$file->getThumb100());
			// @unlink($path.$file->getThumb200());
			// @unlink($path.$file->getWeb());
			// @unlink($path.$file->getOriginal());
			// @unlink($path.$file->getFileName().".poster.jpeg");
			// @unlink($path.$file->getFileName().".flv");
	
		}catch (Exception $e)
		{
	
		}
	
	$this->getUser()->setFlash("success", __("File moved successfully."), false);
	
	$this->uri = 'folder/show?id='.$this->getRequestParameter("folder_id1");
	$this->setTemplate("thankyou");
	}
	
		$this->file = $file;
	$this->folder = $folder;
	return sfView::SUCCESS;
	}

	public function executeCopy()
	{
	$this->getResponse()->setSlot('title', __("Copy file"));
	
	$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter('id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			;
		}
		else {
			$this->forward404();
		}
	
		
	if ($this->getRequest()->getMethod() == sfRequest::POST)
	{
	$this->forward404Unless($folder1 = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id1")));
	
		$file_id = $this->getRequestParameter("file_id");
		$path = $folder->getPathToSave().DIRECTORY_SEPARATOR;
		$path_to = $folder1->getPathToSave().DIRECTORY_SEPARATOR;
	
		@mkdir($path_to, 0777, true);
	
		$new_file = $file->copy(true);
	
		$new_file->setGroupeId($folder1->getGroupeId());
		$new_file->setFolderId($folder1->getId());
		$new_file->setCreatedAt(date("Y-m-d H:i:s"));
		$new_file->save();
	
		$tags = FileTagPeer::retrieveByFileIdType(FileTagPeer::__TYPE_FILE, $file->getId());
	
		foreach($tags as $tag)
		{
			$new_tag = $tag->copy(true);
			$new_tag->setFileId($new_file->getId());
	
			$new_tag->save();
		}
	
		$fields = FieldContentPeer::retrieveByObjectIdAndObjectType($file->getId(), FieldContentPeer::__FILE);
	
		foreach($fields as $field)
		{
			$new_field = $field->copy(true);
			$new_field->setObjectId($new_file->getId());
	
			$new_field->save();
		}
	
		try
		{
			@copy($path.$file->getOriginal(), $path_to.$file->getFileName().".poster.jpeg");
			@copy($path.$file->getOriginal(), $path_to.$file->getFileName().".flv");
			copy($path.$file->getOriginal(), $path_to.$file->getOriginal());
			copy($path.$file->getWeb(), $path_to.$file->getWeb());
			copy($path.$file->getThumb200(), $path_to.$file->getThumb200());
			copy($path.$file->getThumb100(), $path_to.$file->getThumb100());
	
		}catch (Exception $e)
		{
	
		}
	
	$this->getUser()->setFlash("success", __("File copied successfully."), false);
	
	$this->uri = 'folder/show?id='.$this->getRequestParameter("folder_id1");
	$this->setTemplate("thankyou");
	}
	
		$this->file = $file;
	$this->folder = $folder;
	return sfView::SUCCESS;
	}

	# EDIT ALL, called from upload/option
	public function executeEditAll()
	{
	$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
	if($this->getRequestParameter("navigation") == "create")
	{
	$this->getResponse()->setSlot('title', __("Create a folder"));
	}else
	{
	$this->getResponse()->setSlot('title', __("Upload files"));
	}
	
	if ($this->getRequest()->getMethod() == sfRequest::POST)
	{
	$fileTmps = FileTmpPeer::retrieveByUserIdFolderId($this->getUser()->getId(), $folder->getId());
	$file_ids = array();
	// save each file
	foreach ($fileTmps as $fileTmp)
	{
	if($file = FilePeer::retrieveByPK($fileTmp->getFileId()))
	{
	$this->saveFile($file);
	$file_ids[] = $file->getId();
	}
	}
	
		if($this->getUser()->haveAccessModule(ModulePeer::__MOD_APPROVAL) && !$this->getUser()->isAdmin())
			$this->getUser()->setFlash("success", __("The files informations has successfully saved."), true);
		else
			$this->getUser()->setFlash("success", __("The files informations has successfully saved."), true);
	
	LogPeer::setLog($this->getUser()->getId(), 0, "files-edit", "3", $file_ids);
	
	$this->uri = 'folder/show?id='.$folder->getId();
	$this->setTemplate('thankyou');
	}
	
	$this->file = new File();
	$this->folder = $folder;
	return sfView::SUCCESS;
	}
	
	# DELETE
	public function executeDelete()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter("id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($file->getGroupeId(), $file->getFolderId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
		$folder_id = $file->getFolderId();
	
		$file->setState(FilePeer::__STATE_DELETE);
		$file->setUpdatedAt(time());
	
		$file->save();
	
		LogPeer::setLog($this->getUser()->getId(), $file->getId(), "file-delete", "3");
	
		if($this->getRequestParameter("ajax") == "true")
			return sfView::NONE;
	
		$this->getUser()->setFlash("success", "The file removed successfully.", true);
	
		if($this->getRequestParameter("current_module") == "public")
			$this->redirect('public/home');
		elseif($this->getRequestParameter("current_module") == "favorite")
			$this->redirect('favorite/list');
		else
			$this->redirect('folder/show?id='.$folder_id);
	}

	protected function saveFile($file)
	{
	// save informations
	$file->setName($this->getRequestParameter('name'));
	$file->setDescription($this->getRequestParameter('description'));
	$file->setFolderId($file->getFolderId());
	$file->setGroupeId($file->getFolder()->getGroupeId());
	
		if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
		{
			if($this->getRequestParameter("cover_image"))
			{
				FilePeer::updateFolderCover($file->getFolderId());
				$file->setFolderCover(1);
			}
			else
			{
				$file->setFolderCover(0);
			}
		}
	
	if($this->getUser()->isAdmin() || (UserGroupPeer::getRole($this->getUser()->getId(), $file->getGroupeId()) == RolePeer::__ADMIN) || ($file->getUserId() == $this->getUser()->getId()))
	{
	if($author_id = UserPeer::retrieveByPk($this->getRequestParameter("author")))
	{
	$file->setUserId($author_id->getId());
	}else
	{
	$file->setUserId($file->getUserId() ? $file->getUserId() : $this->getUser()->getId());
	}
	
	if($created_at = $this->getRequestParameter('created_at'))
	{
		$hour_created_at = $this->getRequestParameter('hour_created_at');
			$minute_created_at = $this->getRequestParameter('minute_created_at');
			$second_created_at = $this->getRequestParameter('second_created_at');
	
	$file->setCreatedAt($created_at["year"]."-".$created_at["month"]."-".$created_at["day"]." ".$hour_created_at.":".$minute_created_at.":".$second_created_at);
	}else{
	$file->setCreatedAt($file->getCreatedAt() ? $file->getCreatedAt() : date("Y-m-d"));
	}
	}else{
	 if(!$file->getCreatedAt()) $file->setCreatedAt(date("Y-m-d"));
	 if(!$file->getUserId()) $file->setUserId($this->getUser()->getId());
	}
	
	// save location
	$file->setLat($this->getRequestParameter('lat'));
	$file->setLng($this->getRequestParameter('lng'));
	
		GeolocationPeer::saveGeolocation($file, GeolocationPeer::__TYPE_FILE);
	
	$file->save();
	
	// save tags
	FileTagPeer::deletByTypeFileId(3, $file->getId());// 3-file
	$tag_ids = $this->getRequestParameter("selected_tag_ids") ? $this->getRequestParameter("selected_tag_ids") : array();
	foreach ($tag_ids as $tag_id)
	{
	if(!FileTagPeer::getFileTag(3, $file->getId(), $tag_id))
	{
	$file_tag = new FileTag();
	$file_tag->setType(3);
	$file_tag->setFileId($file->getId());
	$file_tag->setTagId($tag_id);
	$file_tag->save();
	}
	}
	
	return $file;
	}

	public function executeSendFileForm()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
	
		if($this->getRequestParameter("data")) {
			$data = $this->getRequestParameter("data");
			$file_id = array_key_exists("file_id", $data) ? $data["file_id"] : null;
		} else
			$file_id = $this->getRequestParameter("file_id");
	
		$this->forward404Unless($file = FilePeer::retrieveByPK($file_id));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($file->getGroupeId(), $file->getFolderId()));
	
		if($this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__PERMALINK_FILE) && $this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__SHARE, RolePeer::__READER)) {
			if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		$user = UserPeer::retrieveByPk($this->getUser()->getId());
	
		$this->getResponse()->setSlot('title', __('Send by email').' : '.$file->getName());
	
	
		$message = __("Filename:")." ".$file."\n";
		$message .= __("File size:")." ".MyTools::getSize($file->getSize());
	
		$this->form = new FileSendFileForm(
			array(
				"file_id" => $file_id,
				"message" => $message,
				"receivers" => __("Write one or more email addresses separated by commas")
			)
		);
	
		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if ($this->form->isValid())
			{
				if(!$permalink = PermalinkPeer::getByObjectId($file->getId(), PermalinkPeer::__TYPE_ORIGINAL, PermalinkPeer::__OBJECT_FILE))
				{
					$end_at = time()+(604800);
					$link = PermalinkPeer::getUrl();
					$qrcode = PermalinkPeer::buildQrCode($file->getId(), $link, PermalinkPeer::__OBJECT_FILE);
	
					$permalink = new Permalink();
					$permalink->setType(PermalinkPeer::__TYPE_ORIGINAL);
					$permalink->setObjectId($file->getId());
					$permalink->setObjectType(PermalinkPeer::__OBJECT_FILE);
					$permalink->setUserId($this->getUser()->getId());
					$permalink->setEndAt(date("Y-m-d H:i:s", $end_at));
					$permalink->setLink($link);
					$permalink->setQrcode($qrcode);
					$permalink->setAllowComments(false);
					$permalink->setState(PermalinkPeer::__STATE_PUBLIC);
	
					$permalink->save();
				}
				else
					$link = $permalink->getLink();
	
				$emails = explode(',', $this->form->getValue("receivers"));
				$emails_clear = array();
	
				foreach ($emails as $email)
				{
					$emails_clear[] = trim($email);
				}
				
				sfContext::getInstance()->getConfiguration()->loadHelpers('Url');
	
				$search = Array("**MESSAGE**", "**DOWNLOAD_LINK**");
				$replace = Array(nl2br($this->form->getValue('message')),__("To download file").' <a href="'.url_for("@permalink_show?link=".$link, true).'">'.__("click here").'</a>.');
	
				$subject = !sizeof($this->form->getValue('subject')) ? $this->form->getValue("sender")." ".__("send to you file") : $this->form->getValue('subject');
				
				$email = new myMailer("send_file", "[wikiPixel] ".$subject);
				$email->setTo($emails_clear);
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getOwnFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
	
				$this->getUser()->setFlash("success", __("Email sent successfully."), false);
	
				LogPeer::setLog($this->getUser()->getId(), $file->getId(), "file-email", "3");
	
				$this->uri = 'file/show?id='.$file->getId()."&folder_id=".$file->getFolderId();
				$this->setTemplate('thankyou');
			}
		}
	
		$this->file = $file;
		$this->user = $user;
	}
	
	public function executeShowFromMap()
	{
		if (!$this->getUser()->haveAccessModule(ModulePeer::__MOD_GEOLOCALISATION)) {
			$this->forward404();
		}
	
	$this->uri = "file/show?id=".$this->getRequestParameter("id")."&folder_id=".$this->getRequestParameter("folder_id");
		$this->setTemplate('thankyou'); 
	}
	
	public function executeLogprint()
	{
		LogPeer::setLog($this->getUser()->getId(), $this->getRequestParameter("id"), "file-print", "3");
		return $this->renderText('');
	}

	public function executeRestore()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter("id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($file->getGroupeId(), $file->getFolderId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		$this->getResponse()->setSlot('title', __("Restore a file version of")." \"".$file."\"");
	
		if ($this->getRequest()->getMethod() == sfRequest::POST)
	{
			if(file_exists(sfConfig::get('app_path_upload_dir').'/'.$file->getDisk()->getPath().'/cust-'.$file->getCustomerId().'/folder-'.$file->getFolderId().'/DATET-'.$this->getRequestParameter("version").$file->getOriginal())) {
				$path = $file->getPath().DIRECTORY_SEPARATOR;
				$original = $file->getOriginal();
				$ext = myTools::getFileExtension($original);
				$name =myTools::getFileNameFile($original);
				$mime = ($ext == "jpg") ? "jpeg" : $ext;
				$mime = strtolower($mime);
				$temp = sfConfig::get('app_path_upload_dir').'/'.$file->getDisk()->getPath().'/cust-'.$file->getCustomerId().'/folder-'.$file->getFolderId().'/DATET-'.$this->getRequestParameter("version").$file->getOriginal();
	
				// create web resolution
				$web = $name."_thumb.".$ext;
				$dimension = getimagesize($temp);
				$thumbnail = new sfThumbnail(720, $dimension[1], true, false, 75, 'sfImageMagickAdapter');
				$thumbnail->loadFile($temp);
				$thumbnail->save($path.$web, 'image/'.$mime);
				unset($thumbnail);
	
				if($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($pathF.$web, $file->getCustomerId());
				}
	
				// create thumbnail 200
				$thumb200 = $name."_thumb200.".$ext;
				$thumbnail = new sfThumbnail(200, 200, true, false, 100, 'sfImageMagickAdapter');
				$thumbnail->loadFile($temp);
				$thumbnail->save($path.$thumb200, 'image/'.$mime);
				unset($thumbnail);
	
				// create thumbnail 100
				$thumb100 = $name."_thumb100.".$ext;
				$thumbnail = new sfThumbnail(100, 100, true, false, 100, 'sfImageMagickAdapter');
				$thumbnail->loadFile($temp);
				$thumbnail->save($path.$thumb100, 'image/'.$mime);
				unset($thumbnail);
	
				//Rename actual version
				@rename($path.$original, $path."DATET-".time().$original);
	
				//Restore the oldest one
				@rename($temp, $path.$original);
	
				$this->uri = "file/show?id=".$file->getId()."&folder_id=".$file->getFolderId();
				$this->setTemplate('thankyou');
			}
		}
	
		$this->file = $file;
	}

	public function executeViewVersion()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter("file_id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($file->getGroupeId(), $file->getFolderId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		if(file_exists(sfConfig::get('app_path_upload_dir').'/'.$file->getDisk()->getPath().'/cust-'.$file->getCustomerId().'/folder-'.$file->getFolderId().'/DATET-'.$this->getRequestParameter("version").$file->getOriginal())) {
			$path = $file->getPath().DIRECTORY_SEPARATOR;
			$original = "DATET-".$this->getRequestParameter("version").$file->getOriginal();
			$ext = myTools::getFileExtension($original);
			$name =myTools::getFileNameFile($original);
			$mime = ($ext == "jpg") ? "jpeg" : $ext;
			$mime = strtolower($mime);
			$save = time().".".$ext;
	
			$dimension = getimagesize($path.$original);
			$thumbnail = new sfThumbnail(720, $dimension[1], true, false, 75, 'sfImageMagickAdapter');
			$thumbnail->loadFile($path.$original);
			$thumbnail->save(sfConfig::get('app_path_temp_dir')."/".$save, 'image/'.$mime);
			$this->thumb = "/".sfConfig::get('app_path_temp_dir_name')."/".$save;
			unset($thumbnail);
		} else {
			sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
			$this->thumb = path("@file_thumbnail", array("id" => $file->getId(), "format" => web));
		}
	
		return sfView::SUCCESS;
	}

	public function executeDeleteVersion()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter("file_id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
		$this->forward404Unless($this->getUser()->getRole($file->getGroupeId(), $file->getFolderId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		if(file_exists(sfConfig::get('app_path_upload_dir').'/'.$file->getDisk()->getPath().'/cust-'.$file->getCustomerId().'/folder-'.$file->getFolderId().'/DATET-'.$this->getRequestParameter("version").$file->getOriginal())) {
			@unlink(sfConfig::get('app_path_upload_dir').'/'.$file->getDisk()->getPath().'/cust-'.$file->getCustomerId().'/folder-'.$file->getFolderId().'/DATET-'.$this->getRequestParameter("version").$file->getOriginal());
		}
	
		$this->file = $file;
	
		return sfView::SUCCESS;
	}

	public function executeSavePicture()
	{
		$tempName = $this->getRequestParameter("tempname");
		$tempPath = sfConfig::get("app_path_temp_dir")."/";
		$name = substr($this->getRequestParameter("newname"), (strrpos($this->getRequestParameter("newname"), "/") + 1));
		$path = str_replace("/".sfConfig::get("app_path_upload_dir_name"), sfConfig::get("app_path_upload_dir"), $this->getRequestParameter("newname"));
		$path = substr($path, 0, (strrpos($path, "/") + 1));
	
		//Rename original file
		@rename($path.$name, $path."DATET-".time().$name);
	
		//Copy the new file
		@rename($tempPath.$tempName, $path.$name);
	
		$ext = strtolower(myTools::getFileExtension($name));
		$filename =myTools::getFileNameFile($name);
		$mime = ($ext == "jpg") ? "jpeg" : $ext;
		$mime = strtolower($mime);
	
		$thumbMobW = null;
		$thumbTabW = null;
		$thumb400W = null;
	
		$web = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb");
		$thumb100 = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_100");
		$thumb200 = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_200");
		$thumbMob = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_mob");
		$thumbTab = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_tab");
		$thumb400 = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_400");
	
		if($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
		{
			imageTools::writeWatermark($path.$web);
	
			$thumbMobW = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_mob_w");
			imageTools::writeWatermarkThumb($path.$thumbMobW);
	
			$thumbTabW = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_tab_w");
			imageTools::writeWatermarkThumb($path.$thumbTabW);
	
			$thumb400W = imageTools::createThumbnail($filename, $ext, $path.$name, $path, $mime, "thumb_400_w");
			imageTools::writeWatermarkThumb($path.$thumb400W);
		}
	
		$d = dir($tempPath);
		while($name = $d->read())
		{
			if ($name=="." || $name==".." || @getimagesize($tempPath.$name)==false)
				continue;
	
			$lastmod = filemtime($tempPath.$name);
	
			if ($lastmod < time()-24*60*60)
				unlink($tempPath.$name);
		}
	
		$d->close();
	
		return $this->renderText('{"status":"ok"}');
	}

	public function executeField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$value = $this->getRequestParameter('value');
			$field = $this->getRequestParameter('field');
	
			$temp = explode("_", $field);
			if(is_array($temp)&& array_key_exists(1, $temp))
			{
				$field = $temp[0];
				$id = $temp[1];
	
				$this->forward404Unless($content = FieldContentPeer::retrieveByPk($id));
			}
	
			switch($field)
			{
				case 'field':
					$content = FieldContentPeer::retrieveByPk($id);
					$field = FieldPeer::retrieveByPk($content->getFieldId());
	
					switch($field->getFieldType())
					{
						case "Boolean field":
							$content->setContent($value == 0 ? "" : "check");
							$content->save();
	
							return $this->renderText($content->getContent() ? __("Yes") : __("No"));
						break;
	
						case "Multiple choice":
							$content->setContent($value);
							$content->save();
	
							$choice = FieldChoicePeer::retrieveByPk($value);
							return $this->renderText($this->getUser()->getCulture() == "fr" ? $choice->getNameFr() : $choice->getName());
						break;
	
						default:
							$content->setContent($value);
							$content->save();
	
							return $this->renderText($value);
						break;
					}
				break;
	
				case 'licence':
					$file->setLicenceId(empty($value) ? null : $value);
					$file->save();
	
					return $this->renderText($value);
				break;
	
				case 'creativecommons':
					$file->setCreativeCommonsId(empty($value) ? null : $value);
					$file->save();
	
					return $this->renderText($value);
				break;
	
				case 'use':
					$file->setUsageUseId(empty($value) ? null : $value);
					$file->save();
	
					$this->getResponse()->setContentType('application/json');
	
					if(empty($value))
						return $this->renderText("0");
					else
						return $this->renderText($value);
				break;
	
				case 'commercial':
					$file->setUsageCommercialId(empty($value) ? null : $value);
					$file->save();
	
					if(empty($value))
						return $this->renderText("0");
					else
						return $this->renderText($value);
				break;
	
				case 'distribution':
					$file->setUsageDistributionId(empty($value) ? null : $value);
					$file->save();
	
					FileRightPeer::deleteByType($file->getId(), 3);
	
					$this->getResponse()->setContentType('application/json');
	
					if(empty($value))
						return $this->renderText("0");
					else
						return $this->renderText($value);
				break;
	
				case 'constraint':
					$file->setUsageConstraintId(empty($value) ? null : $value);
					$file->save();
	
					if($value != UsageConstraintPeer::__EXTERNAL)
						FileRightPeer::deleteByType($file->getId(), 3);
	
					if(empty($value))
						return $this->renderText("0");
					else
						return $this->renderText($value);
				break;
	
				case 'name':
					$file->setName($value);
					$file->save();
	
					$path = $file->getPathname();
	
					if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_EXIF) && $file->getType() == FilePeer::__TYPE_PHOTO)
					{
						if(!$exif = ExifPeer::getTag("ImageDescription", $file->getId()))
						{
							$exif = new Exif();
							$exif->setTitle("ImageDescription");
							$exif->setValue($value);
							$exif->setFileId($file->getId());
							$exif->setCreatedAt(time());
						}
						else
							$exif->setValue($value);
	
						$exif->save();
	
						ExifPeer::writeExif(array("title" => $value), $path);
					}
	
					if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_IPTC) && $file->getType() == FilePeer::__TYPE_PHOTO)
					{
						if(!$iptc = IptcPeer::getTag("Title", $file->getId()))
						{
							$iptc = new Iptc();
							$iptc->setTitle("Title");
							$iptc->setValue($value);
							$iptc->setFileId($file->getId());
							$iptc->setCreatedAt(time());
						}
						else
							$iptc->setValue($value);
	
						$iptc->save();
	
						IptcPeer::writeIptc(array('2#005' => $value), $path);
					}
	
					return $this->renderText(myTools::longword_break_old($value, 22));
				break;
	
				case 'description':
					$file->setDescription($value);
					$file->save();
	
					if(empty($value))
						return $this->renderText('<span style="cursor: pointer;" class="text">'.__("Add a description.").'</span>');
	
					return $this->renderText(nl2br($value));
				break;
	
				case 'author':
					$path = $file->getPathname();
	
					if(!$exif = ExifPeer::getTag("Author", $file->getId()))
					{
						$exif = new Exif();
						$exif->setTitle("Author");
						$exif->setValue($value);
						$exif->setFileId($file->getId());
						$exif->setCreatedAt(time());
					} else
						$exif->setValue($value);
	
					$exif->save();
	
					ExifPeer::writeExif(array("Artist" => $value, "Author" => $value), $path);
	
					if(!$iptc = IptcPeer::getTag("Writer/Editor", $file->getId()))
					{
						$iptc = new Iptc();
						$iptc->setTitle("Writer/Editor");
						$iptc->setValue($value);
						$iptc->setFileId($file->getId());
						$iptc->setCreatedAt(time());
					} else
						$iptc->setValue($value);
	
					$iptc->save();
	
					if(in_array($file->getExtention(), array("jpg", "jpeg")))
						IptcPeer::writeIptc(array('2#122' => $value), $path);
	
					if(empty($value))
						return $this->renderText('<span style="cursor: pointer;" class="text">'.__("To inform").'</span>');
	
					return $this->renderText($value);
				break;
	
				case 'shooting-date':
					$path = $file->getPathname();
	
					if($file->getType() == FilePeer::__TYPE_PHOTO)
					{
						if(!$exif = ExifPeer::getTag("DateTimeOriginal", $file->getId()))
						{
							$exif = new Exif();
							$exif->setTitle("DateTimeOriginal");
							$exif->setValue($value);
							$exif->setFileId($file->getId());
							$exif->setCreatedAt(time());
						} else
							$exif->setValue($value);
	
						$exif->save();
	
						ExifPeer::writeExif(array("DateTimeOriginal" => $value), $path);
	
						if(!$iptc = IptcPeer::getTag("Date Created", $file->getId()))
						{
							$iptc = new Iptc();
							$iptc->setTitle("Date Created");
							$iptc->setValue($value);
							$iptc->setFileId($file->getId());
							$iptc->setCreatedAt(time());
						} else
							$iptc->setValue($value);
	
						$iptc->save();
	
						IptcPeer::writeIptc(array('2#055' => $value), $path);
					}

					if(empty($value))
						return $this->renderText('<span style="cursor: pointer;" class="text">'.__("To inform").'</span>');
	
					return $this->renderText($value);
				break;
	
				case 'source':
					$file->setSource($value);
					$file->save();
	
					if(empty($value))
						return $this->renderText('<span style="cursor: pointer;" class="text">'.__("To inform").'</span>');
	
					return $this->renderText($value);
				break;
			}
		}
	
		$this->redirect404();
	}

	public function executeAccept()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
		
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		$this->forward404Unless(in_array($this->getRequestParameter("type"), array(FileWaitingPeer::__STATE_VALIDATE, FileWaitingPeer::__STATE_DELETE)));
	
		$file->setState($this->getRequestParameter("type"));
		$file->setUpdatedAt(time());
	
		$file->save();
	
		switch($this->getRequestParameter("type"))
		{
			case FileWaitingPeer::__STATE_VALIDATE:
			{
				$fileWaiting = FileWaitingPeer::retrieveByFileIdAndType($file->getId(), FileWaitingPeer::__STATE_WAITING_VALIDATE);
	
				$search = Array("**FILENAME**", "**CREATED_AT**", "**LINK**");
				$replace = Array($file->getName(), $file->getCreatedAt("d/m/Y H:i:s"), url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true));
	
				$email = new myMailer("accept_validate_file", "[wikiPixel] ".__("The file")." ".$file." ".__("has been validated"));
				$email->setTo(Array($file->getUser()->getEmail() => $file->getUser()->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
	
				$fileWaiting->delete();
			}
			break;
	
			case FileWaitingPeer::__STATE_DELETE:
			{
				$fileWaiting = FileWaitingPeer::retrieveByFileIdAndType($file->getId(), FileWaitingPeer::__STATE_WAITING_DELETE);
	
				$search = Array("**FILENAME**", "**CREATED_AT**");
				$replace = Array($file, $fileWaiting->getCreatedAt("d/m/Y H:i:s"));
	
				$email = new myMailer("accept_delete_file", "[wikiPixel] ".__("The file")." ".$file." ".__("has been deleted"));
				$email->setTo(Array($fileWaiting->getUser()->getEmail() => $fileWaiting->getUser()->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
	
				$fileWaiting->delete();
	
				$file->setState(FilePeer::__STATE_DELETE);
				$file->setUpdatedAt(time());
	
				$file->save();
	
				LogPeer::setLog($this->getUser()->getId(), $file->getId(), "file-delete", "3");
			}
			break;
		}
	
		$this->getResponse()->setContentType('application/json');
		return $this->renderText(json_encode(array("errorCode" => 0)));
	}
	
	public function executeDeny()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
		if ($roleGroup < RolePeer::__ADMIN) {
			;
		}
		elseif ($roleGroup == RolePeer::__ADMIN) {
			if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
				$this->forward404();
			}
		}
		elseif ($roleGroup == RolePeer::__CONTRIB) {
			if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
				$this->forward404();
			}
		}
		else {
			$this->forward404();
		}
	
		$this->forward404Unless(in_array($this->getRequestParameter("type"), array(FileWaitingPeer::__STATE_VALIDATE, FileWaitingPeer::__STATE_DELETE)));
	
		$file->setState($this->getRequestParameter("type"));
		$file->setUpdatedAt(time());
	
		$file->save();
	
		switch($this->getRequestParameter("type"))
		{
			case FileWaitingPeer::__STATE_DELETE:
				$fileWaiting = FileWaitingPeer::retrieveByFileIdAndType($file->getId(), FileWaitingPeer::__STATE_WAITING_VALIDATE);
	
				$search = Array("**FILENAME**", "**CREATED_AT**", "**REFUSAL**");
				$replace = Array($file, $file->getCreatedAt("d/m/Y H:i:s"), ($this->getRequestParameter("ground-deny-".$file->getId()) ? __("Ground for refusal")." : ".nl2br($this->getRequestParameter("ground-deny-".$file->getId())) : ""));
	
				$email = new myMailer("deny_validate_file", "[wikiPixel] ".__("The file")." ".$file." ".__("has been refused"));
				$email->setTo(Array($file->getUser()->getEmail() => $file->getUser()->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
	
				$fileWaiting->delete();
			break;
	
			case FileWaitingPeer::__STATE_VALIDATE:
				$fileWaiting = FileWaitingPeer::retrieveByFileIdAndType($file->getId(), FileWaitingPeer::__STATE_WAITING_DELETE);
	
				$search = Array("**FILENAME**", "**CREATED_AT**", "**REFUSAL**");
				$replace = Array($file, $fileWaiting->getCreatedAt("d/m/Y H:i:s"), ($this->getRequestParameter("ground-deny-".$file->getId()) ? __("Ground for refusal")." : ".nl2br($this->getRequestParameter("ground-deny-".$file->getId())) : ""));
	
				$email = new myMailer("deny_delete_file", "[wikiPixel] ".__("The request to delete the file")." ".$file." ".__("has been refused"));
				$email->setTo(Array($fileWaiting->getUser()->getEmail() => $fileWaiting->getUser()->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
	
				$fileWaiting->delete();
			break;
		}
	
		$this->getResponse()->setContentType('application/json');
		return $this->renderText(json_encode(array("errorCode" => 0)));
	}

	public function executeDeleteOnDemand()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	
		if($this->getRequestParameter("data")) {
			$data = $this->getRequestParameter("data");
			$id = array_key_exists("id", $data) ? $data["id"] : null;
		} else
			$id = $this->getRequestParameter("id");
	
		$this->forward404Unless($file = FilePeer::retrieveByPk($id));
		$this->getResponse()->setSlot('title', __("Request file deletion")." \"".(strlen($file) > 20 ? substr($file,0,20)."..." : $file)."\"");
	
	
		$this->form = new FileDeleteOnDemandeForm(
			array(
				"id" => $file->getId()
			)
		);
	
		if($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if ($this->form->isValid())
			{
				$fileWaiting = new FileWaiting();
				$fileWaiting->setFileId($file->getId());
				$fileWaiting->setUserId($this->getUser()->getId());
				$fileWaiting->setState(FileWaitingPeer::__STATE_WAITING_DELETE);
				$fileWaiting->setCause($this->form->getValue("reason"));
	
				$fileWaiting->save();
	
				$this->getUser()->setFlash("success", "Your request to delete the file was registered successfully.", true);
	
				$to = Array();
				$admins = UserPeer::retrieveByRoleIds(array(RolePeer::__ADMIN));
				foreach ($admins as $admin)
				{
					if ($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1) {
						$to[$admin->getEmail()] = $admin->getEmail();
					}
				}
	
				$validators = UserGroupPeer::getUsers($file->getGroupeId(), RolePeer::__ADMIN);
				foreach ($validators as $validator)
				{
					$user = $validator->getUser();
					if ($user && $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1) {
						if (!in_array($user->getEmail(), $to)) {
							$to[$user->getEmail()] = $user->getEmail();
						}
					}
				}
	
				$unitsValidators = UnitGroupPeer::getEffectiveByGroupIdAndRole($file->getGroupeId(), RolePeer::__ADMIN);
				foreach ($unitsValidators as $unitsValidator)
				{
					$user = $unitsValidator->getUser();
					if ($user && $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1) {
						if (!in_array($user->getEmail(), $to)) {
							$to[$user->getEmail()] = $user->getEmail();
						}
					}
				}

				sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
				$search = Array("**URL_FILE**", "**FILE_NAME**", "**USER**", "**URL**");
				$replace = Array(url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true), $file->getName(), $this->getUser()->getInstance()->getFullname(), $_SERVER["SERVER_NAME"], url_for("group/waiting?id=".$file->getGroupeId(), true));

				$email = new myMailer("request_delete_file", "[wikiPixel] ".__("Request for delete file")." \"".$file."\"");
				$email->setTo($to);
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
	
				$this->uri = url_for('file/show?id='.$file->getId()."&folder_id=".$file->getFolderId());
				$this->setTemplate('thankyou');
			}
		}
	
		return sfView::SUCCESS;
	}

	public function executeRotate()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			@copy($file->getPath().'/'.$file->getOriginal(), $file->getPath().'/DATET-'.time().$file->getOriginal());
	
			imageTools::rotateImage($this->getRequestParameter("angle"), $file);
	
			return $this->renderText("0");
		}
	
		$this->redirect404();
	}

	public function executeUpdateGps()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
	
			$file->setLat($this->getRequestParameter("lat"));
			$file->setLng($this->getRequestParameter("lng"));
			$file->save();
	
			GeolocationPeer::saveGeolocation($file, GeolocationPeer::__TYPE_FILE);
	
			return sfView::NONE;
		}
	
		$this->redirect404();
	}
	
	public function executeUpdateFolderCover()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
	
			if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
			{
				if($this->getRequestParameter("check") == "true" || $this->getRequestParameter("check") == "checked")
				{
					$file->setFolderCover(0);
	
					$folder = $file->getFolder();
	
					if (!$folder->getDiskId()) {
						$folder->setDiskId($this->getUser()->getDisk()->getId());
						$folder->save();
					}
	
					$path = $folder->getRealPath();
					$cover = ImageTools::setThumbnailForFolder($path, $file);
	
					$folder->setThumbnail($cover);
					$folder->save();
				}
				else
					$file->setFolderCover(0);
	
				$file->save();
			}
	
			return sfView::NONE;
		}
	
		$this->redirect404();
	}
	
	public function executeChoiceField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($content = FieldContentPeer::retrieveByPk($this->getRequestParameter("content_id")));
			$this->forward404Unless($field = FieldPeer::retrieveByPk($this->getRequestParameter("field_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$this->getResponse()->setContentType('application/json');
	
			switch($field->getFieldType())
			{
				case "Boolean field":
					$choices_array = array();
					$choices_array[0] = __("No");
					$choices_array[1] = __("Yes");
					$choices_array["selected"] = $content->getContent() ? 1 : 0;
				break;
	
				case "Multiple choice":
					$choices = FieldChoicePeer::retrieveByFieldId($field->getId());
	
					$choices_array = array();
	
					foreach($choices as $choice)
						$choices_array[$choice->getId()] = $this->getUser()->getCulture() == "fr" ? $choice->getNameFr() : $choice->getName();
	
					$choices_array["selected"] = $content->getContent();
				break;
			}
	
			return $this->renderText(json_encode($choices_array));
		}
	
		$this->redirect404();
	}
		
	public function executeEditJava()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless(FolderPeer::isAllowedToView($this->getRequestParameter('folder_id'), $this->getUser()->getId()));
		$this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter('folder_id'), "folder"));
	
		$this->getResponse()->setSlot('title', __("Upload files"));
	
		if(!($this->getRequestParameter("pass") == 1))
		{
			if($this->getRequest()->getMethod() == sfRequest::POST)
			{
				$temp = explode(";", $this->getRequestParameter("files"));
				$files_array = Array();
				foreach($temp as $file)
				{
					if(!empty($file))
					{
						if($file_a = FilePeer::retrieveByPk($file))
						{
							$this->saveFile($file_a);
							$files_array[] = $file_a->getId();
						}
					}
				}
	
				$this->getUser()->setFlash("success", __("The files informations has successfully saved."), false);
	
				LogPeer::setLog($this->getUser()->getId(), 0, "files-edit", "3", $files_array);
	
				$this->uri = 'folder/show?id='.$folder->getId();
				$this->setTemplate('thankyou');
			}
		}
	
		$this->folder = $folder;
		$this->files = $this->getRequestParameter("files");
		return sfView::SUCCESS;
	}
	
	public function executeCrop()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
	
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$width = $this->getRequestParameter("w");
			$height = $this->getRequestParameter("h");
			$x = $this->getRequestParameter("x");
			$y = $this->getRequestParameter("y");
	
			$web = getimagesize($file->getThumbWebPathname());
			$web_width = $web[0];
			$web_height = $web[1];
	
			$percent_width = round(($width / $web_width) * 100, 2);
			$percent_height = round(($height / $web_height) * 100, 2);
			$percent_x = round(($x / $web_width) * 100, 2);
			$percent_y = round(($y / $web_height) * 100, 2);
	
			$original = getimagesize($file->getPathname());
			$original_width = $original[0];
			$original_height = $original[1];
	
			$width = round(($percent_width * $original_width) / 100);
			$height = round(($percent_height * $original_height) / 100);
	
			$x = round(($percent_x * $original_width) / 100);
			$y = round(($percent_y * $original_height) / 100);
	
			@copy($file->getPath().'/'.$file->getOriginal(), $file->getPath().'/DATET-'.time().$file->getOriginal());
	
			imageTools::cropImage($file, $width, $height, $x, $y);
	
			return $this->renderText("0");
		}
	
		$this->redirect404();
	}
	
	public function executeAddReference()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file_to = FilePeer::retrieveByPk($this->getRequestParameter("to")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file_to->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file_to->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file_to->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file_to->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$this->getResponse()->setContentType('application/json');
	
			$from_ids = explode(',', $this->getRequestParameter("from"));
	
			foreach ($from_ids as $from_id)
			{
				$from_id = trim($from_id);
	
				if(!empty($from_id))
				{
					$this->forward404Unless($file_from = FilePeer::retrieveByPk($from_id));
					$this->forward404Unless(UserPeer::isAllowed($file_from->getId(), "file"));
	
					if(!FileRelatedPeer::retrieveByFileIdToAndFileIdFrom($file_to->getId(), $file_from->getId()))
					{
						$file_related = new FileRelated();
						$file_related->setFileIdTo($file_to->getId());
						$file_related->setFileIdFrom($file_from->getId());
						$file_related->setUserId($this->getUser()->getId());
	
						$file_related->save();
					}
	
					if(!FileRelatedPeer::retrieveByFileIdToAndFileIdFrom($file_from->getId(), $file_to->getId()))
					{
						$file_related = new FileRelated();
						$file_related->setFileIdTo($file_from->getId());
						$file_related->setFileIdFrom($file_to->getId());
						$file_related->setUserId($this->getUser()->getId());
	
						$file_related->save();
					}
				}
			}
	
			if(
				(UserGroupPeer::getRole($this->getUser()->getId(), $file_to->getGroupeId()) == RolePeer::__ADMIN) ||
				(FolderPeer::retrieveByPk($file_to->getFolderId())->getUserId() == $this->getUser()->getId()) ||
				($file_to->getUserId() == $this->getUser()->getId()) ||
				$file_to->getGroupe()->isAllowedTo(ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)
			)
				$role = true;
			else
				$role = false;
	
			$result = Array();
			$result["html"] = $this->getPartial("file/related", array("file" => $file_to, "role" => $role));
			$result["code"] = 0;
			return $this->renderText(json_encode($result));
		}
	
		$this->redirect404();
	}

	public function executeLoadThumb()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			if($this->getRequestParameter("folder_id"))
			{
				$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
	
				if($this->getRequestParameter("folder_id") != "all")
				{
					$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));
					$files = FilePeer::retrieveByFolderId($folder->getId());
				}
				else
					$files = FilePeer::retrieveByGroupId($group->getId());
	
				return $this->renderPartial("file/getFilesThumb", array("files" => $files));
			}
	
			$this->groups = GroupePeer::getGroupsInArray();
	
			if($this->getRequestParameter("group_id"))
			{
				$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
				$this->group_id = $group->getId();
			}
			else
			{
				$temp = array_keys($this->groups);
				$this->group_id = $temp[0];
			}
	
			$this->folders = FolderPeer::getFolderInArray(0, $this->group_id);
			$this->files = FilePeer::retrieveByGroupId($this->group_id);
	
			return sfView::SUCCESS;
		}
	
		$this->redirect404();
	}
	
	public function executeLoadLicence()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$this->getResponse()->setContentType('application/json');
	
			$licences = LicencePeer::getLicenceInArray();
	
			$array = Array();
			$array[0] = __("Choose");
	
			foreach($licences as $licence)
				$array[$licence->getId()] = $licence->getTitle();
	
			$array["selected"] = !$file->getLicenceId() ? LicencePeer::__NONE : $file->getLicenceId();
	
			return $this->renderText(json_encode($array));
		}
	
		$this->redirect404();
	}
	
	public function executeLoadDistribution()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$this->getResponse()->setContentType('application/json');
	
			$distributions = UsageDistributionPeer::getDistributions();
	
			$array = Array();
			$array[0] = __("Choose");
	
			foreach($distributions as $distribution)
				$array[$distribution->getId()] = $distribution->getTitle();
	
			$array["selected"] = $file->getUsageDistributionId();
	
			return $this->renderText(json_encode($array));
		}
	
		$this->redirect404();
	}
	
	public function executeLoadConstraint()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$this->getResponse()->setContentType('application/json');
	
			$constraints = UsageConstraintPeer::getConstraints();
	
			$array = Array();
			$array[0] = __("Choose");
	
			foreach($constraints as $constraint)
				$array[$constraint->getId()] = $constraint->getTitle();
	
			$array["selected"] = $file->getUsageConstraintId();
	
			return $this->renderText(json_encode($array));
		}
	
		$this->redirect404();
	}
	
	public function executeShowConstraints()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/constraints", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}
	
	public function executeShowLimitations()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/limitations", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}
	
	public function executeSaveLimitation()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($usageLimitation = UsageLimitationPeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			if($file_right = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $usageLimitation->getId()))
				$file_right->delete();
	
			$value = $this->getRequestParameter("value");
	
			if($usageLimitation->getId() == UsageLimitationPeer::__INTERNAL)
			{
				$file_rights = FileRightPeer::retrieveByType($file->getId(), 3);
	
				foreach($file_rights as $file_right)
					$file_right->delete();
			}
	
			if(!empty($value))
			{
				$file_right = new FileRight();
				$file_right->setObjectId($file->getId());
				$file_right->setType(3);
				$file_right->setUsageLimitationId($usageLimitation->getId());
				$file_right->setValue($value);
	
				$file_right->save();
			}
	
			return $this->renderText("");
		}
	
		$this->redirect404();
	}

	public function executeLoadUse()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$this->getResponse()->setContentType('application/json');
	
			$uses = UsageUsePeer::getUses();
	
			$array = Array();
			$array[0] = __("Choose");
	
			foreach($uses as $use)
				$array[$use->getId()] = $use->getTitle();
	
			$array["selected"] = $file->getUsageUseId();
	
			return $this->renderText(json_encode($array));
		}
	
		$this->redirect404();
	}
	
	public function executeShowCommercials()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
			return $this->renderPartial("file/commercials", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}
	
	public function executeLoadCommercial()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$this->getResponse()->setContentType('application/json');
	
			$commercials = UsageCommercialPeer::getCommercials();
	
			$array = Array();
			$array[0] = __("Choose");
	
			foreach($commercials as $commercial)
				$array[$commercial->getId()] = $commercial->getTitle();
	
			$array["selected"] = $file->getUsageCommercialId();
	
			return $this->renderText(json_encode($array));
		}
	
		$this->redirect404();
	}
	
	public function executeShowCopyrights()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/copyrights", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}

	public function executeShowCreativeCommons()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/creativeCommons", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}
	
	public function executeRotateSelected()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$file_ids = $this->getRequestParameter("file_ids") ? $this->getRequestParameter("file_ids") : array();
			if(!sizeof($file_ids))
			{
				$this->getUser()->setFlash("warning", __("Please select the files to move."), false);
				$this->redirect('folder/show?id='.$this->getRequestParameter("folder_id"));
			}
	
			foreach($file_ids as $file_id)
			{
				$file = FilePeer::retrieveByPk($file_id);
	
				@copy($file->getPath().'/'.$file->getOriginal(), $file->getPath().'/DATET-'.time().$file->getOriginal());
	
				imageTools::rotateImage($this->getRequestParameter("angle"), $file);
			}
	
			return $this->renderText("0");
		}
	
		$this->redirect404();
	}

	public function executeVideo()
	{
		$link = $this->getRequestParameter('link');
		$this->permalink = PermalinkPeer::getByLink($link);
	
		if(($this->permalink && $this->permalink->getState() == PermalinkPeer::__STATE_DISABLED) || (!$this->permalink))
			$this->redirect404();
	
		if($this->permalink->getObjectType() == PermalinkPeer::__OBJECT_FILE)
		{
			$this->forward404Unless($this->file = FilePeer::retrieveByPk($this->permalink->getObjectId()));
	
			if($this->file->getType() == FilePeer::__TYPE_VIDEO)
			{
				$this->format = $this->permalink->getType();
				return sfView::SUCCESS;
			}
		}
	
		$this->redirect404();
	}
	
	public function executeAttachFiles()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file_from = FilePeer::retrieveByPk($this->getRequestParameter("from")));
			$this->forward404Unless($file_to = FilePeer::retrieveByPk($this->getRequestParameter("to")));
	
			$ok = 0;
	
			if(!FileRelatedPeer::retrieveByFileIdToAndFileIdFrom($file_to->getId(), $file_from->getId()))
			{
				$this->forward404Unless($roleGroup = $this->getUser()->getRole($file_to->getGroupeId()));
	
				if ($roleGroup < RolePeer::__ADMIN) {
					;
				}
				elseif ($roleGroup == RolePeer::__ADMIN) {
					if (!$this->getUser()->hasCredential("admin") && $file_to->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file_to->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
						$this->forward404();
					}
				}
				elseif ($roleGroup == RolePeer::__CONTRIB) {
					if ($file_to->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file_to->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
						$this->forward404();
					}
				}
				else {
					$this->forward404();
				}
				
	
				$file_related = new FileRelated();
				$file_related->setFileIdTo($file_to->getId());
				$file_related->setFileIdFrom($file_from->getId());
				$file_related->setUserId($this->getUser()->getId());
	
				$file_related->save();
	
				$ok++;
			}
	
			if(!FileRelatedPeer::retrieveByFileIdToAndFileIdFrom($file_from->getId(), $file_to->getId()))
			{
				$this->forward404Unless($roleGroup = $this->getUser()->getRole($file_from->getGroupeId()));
				
				if ($roleGroup < RolePeer::__ADMIN) {
					;
				}
				elseif ($roleGroup == RolePeer::__ADMIN) {
					if (!$this->getUser()->hasCredential("admin") && $file_from->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file_from->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
						$this->forward404();
					}
				}
				elseif ($roleGroup == RolePeer::__CONTRIB) {
					if ($file_from->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file_from->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
						$this->forward404();
					}
				}
				else {
					$this->forward404();
				}
	
				$file_related = new FileRelated();
				$file_related->setFileIdTo($file_from->getId());
				$file_related->setFileIdFrom($file_to->getId());
				$file_related->setUserId($this->getUser()->getId());
	
				$file_related->save();
	
				$ok++;
			}
	
			return $this->renderText($ok);
		}
	
		$this->redirect404();
	}
	
	public function executeCustomDownload()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
	
			if($file->getType() == FilePeer::__TYPE_PHOTO)
			{
				if(!$file->getWidth() || !$file->getHeight() || $file->getExtention() == 'eps')
				{
					if(file_exists($file->getPath()."/".$file->getOriginal()))
					{
						$exec = shell_exec("identify -verbose ".escapeshellarg($file->getPath()."/".$file->getOriginal())." | grep \"Base\ geometry\" | awk '{print $3}'");
						$exec = trim($exec);
						$size = explode("x", $exec);
	
						if(empty($exec))
						{
							$exec = shell_exec("identify -verbose ".escapeshellarg($file->getPath()."/".$file->getOriginal())." | grep \"Geometry\" | awk '{print $2}'");
							$exec = trim($exec);
							$size = explode("x", substr($exec, 0, strpos($exec, "+")));
						}
	
						$file->setWidth($size[0]);
						$file->setHeight($size[1]);
	
						$file->save();
					}
				}
			}
	
			return $this->renderComponent("file", "customDownload", array("file" => $file));
		}
	
		$this->redirect404();
	}
	
	public function executeViewDocument()
	{
		$this->forward404Unless($this->file = FilePeer::retrieveByPk($this->getRequestParameter('file_id')));
		$this->forward404Unless($this->file->getType() == FilePeer::__TYPE_DOCUMENT);
	
		return sfView::SUCCESS;
	}
	
	public function executeReplace(sfWebRequest $request)
	{
		$fileId = $request->getParameter("id");

		$file = FilePeer::retrieveByPK($fileId);
		$this->forward404Unless($file);

		$folder = $file->getFolder();
		$album = $file->getGroupe();

		$roleAlbum = $this->getUser()->getRole($album->getId());
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__CONTRIB);

		$roleFolder = $this->getUser()->getRole($album->getId(), $folder->getId());
		$this->forward404Unless($roleFolder);

		$key = new UniqueKey();
		$key->setUserId($this->getUser()->getId());
		$key->setCreatedAt(time());
		$key->setExpiredAt(time());
		$key->setIp(@$_SERVER['REMOTE_ADDR']);
		$key->setUri(@$_SERVER['REQUEST_URI']);
		$key->setReferer(@$_SERVER['REFERER']);
		$key->save();

		$this->folder = $folder;
		$this->file = $file;
		$this->key = $key;

		return sfView::SUCCESS;
	}

	public function executeRegenerateThumbnails()
	{
		$this->forward404Unless($this->file = FilePeer::retrieveByPk($this->getRequestParameter('id')));
	
		if($this->getRequest()->isXmlHttpRequest())
		{
			if(file_exists($this->file->getPath().'/'.$this->file->getOriginal()))
				imageTools::regenerateThumbnail($this->file);
	
			return $this->renderText("");
		}
		else
		{
			$this->getResponse()->setSlot('title', __("Regenerate thumbnails of file"));
	
			return sfView::SUCCESS;
		}
	
		return $this->redirect404();
	}
	
	
	public function executeValueOfField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$temp = json_decode($this->getRequestParameter("id"));
			$value = $this->getRequestParameter("value");
			$file_id = $temp[0];
			$field_id = $temp[1];
	
			$this->forward404Unless($field = FieldPeer::retrieveByPk($field_id));
			$this->forward404Unless($file = FilePeer::retrieveByPk($file_id));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			if($field->getType() == FieldPeer::__TYPE_BOOLEAN)
			{
				switch($value)
				{
					case "true": $value = 1; break;
					case "false": $value = 0; break;
				}
			}
	
			if($content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $file->getId(), FieldContentPeer::__FILE))
				$content->setValue($value);
			else
			{
				$content = new FieldContent();
				$content->setFieldId($field->getId());
				$content->setObjectId($file->getId());
				$content->setObjectType(FieldContentPeer::__FILE);
				$content->setValue($value);
			}
	
			$content->save();
	
			return $this->renderText($value);
		}
	
		return $this->redirect404();
	}
	
	public function executeLoadFieldValue()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$temp = json_decode($this->getRequestParameter("id"));
			$file_id = $temp[0];
			$field_id = $temp[1];
	
			$this->forward404Unless($field = FieldPeer::retrieveByPk($field_id));
			$this->forward404Unless($file = FilePeer::retrieveByPk($file_id));
	
			$content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $file->getId(), FieldContentPeer::__FILE);
	
			$this->getResponse()->setContentType('application/json');
	
			$values = unserialize(base64_decode($field->getValues()));
	
			$array = Array();
	
			foreach($values as $value)
				$array[$value] = $value;
	
			$array["selected"] = $content ? $content->getValue() : "";
	
			return $this->renderText(json_encode($array));
		}
	
		$this->redirect404();
	}
	
	public function executeGetPerson()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("image-id")));
	
			$this->getResponse()->setContentType('application/json');
	
			$persons = FilePersonPeer::retrieveByFileId($file->getId());
	
			$result = Array();
			$result["Image"] = Array();
	
			$temp2 = Array(
				"id" => $file->getId(),
				"Tags" => Array()
			);
	
			foreach($persons as $person)
			{
				$temp = Array(
					"id" => $person->getId(),
					"text" => '<i class="icon-user"></i> '.$person->getPerson()->getName(),
					"left" => $person->getX(),
					"top" => $person->getY(),
					"width" => $person->getWidth(),
					"height" => $person->getHeight(),
					"isDeleteEnable" => true
				);
	
				array_push($temp2["Tags"], $temp);
			}
	
			array_push($result["Image"], $temp2);
	
			$result["options"] = Array(
				"tag" => Array("flashAfterCreation" => true)
			);
	
			return $this->renderText(json_encode($result));
		}
	
		$this->redirect404();
	}

	public function executeFetchPerson()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');
			$persons = PersonPeer::fetchByKeyword($this->getRequestParameter('term'));
	
			$results = array();
	
			foreach($persons as $person)
			{
				$temp = Array();
				$temp["id"] = $person->getId();
				$temp["value"] = $person->getName();
				$temp["label"] = $person->getName();
	
				array_push($results, $temp);
			}
	
			return $this->renderText(json_encode($results));
		}
	
		$this->redirect404();
	}
	
	public function executeAddPerson()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$name = $this->getRequestParameter("name");
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("image_id")));
			$this->forward404Unless(!empty($name)); 
	
			if(!$this->getRequestParameter("name_id"))
			{
				$person = new Person();
				$person->setCustomerId($this->getUser()->getCustomerId());
				$person->setName($this->getRequestParameter("name"));
	
				$person->save();
			}
			else
				$this->forward404Unless($person = PersonPeer::retrieveByPk($this->getRequestParameter("name_id")));
	
			$this->getResponse()->setContentType('application/json');
	
			$file_person = new FilePerson();
			$file_person->setFileId($file->getId());
			$file_person->setPersonId($person->getId());
			$file_person->setX($this->getRequestParameter("left"));
			$file_person->setY($this->getRequestParameter("top"));
			$file_person->setWidth($this->getRequestParameter("width"));
			$file_person->setHeight($this->getRequestParameter("height"));
	
			$file_person->save();
	
			$results = Array(
				"result" => true,
				"tag" => Array(
					"id" => $person->getId(),
					"text" => $person->getName(),
					"left" => $this->getRequestParameter("left"),
					"top" => $this->getRequestParameter("top"),
					"width" => $this->getRequestParameter("width"),
					"height" => $this->getRequestParameter("height"),
					"isDeleteEnable" => true
				)
			);
	
			return $this->renderText(json_encode($results));
		}
	
		$this->redirect404();
	}

	public function executeDeletePerson()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file_person = FilePersonPeer::retrieveByPk($this->getRequestParameter("tag-id")));
	
			$this->getResponse()->setContentType('application/json');
	
			$file_person->delete();
	
			return $this->renderText(json_encode(Array("result" => true, "message" => "success")));
		}
	
		$this->redirect404();
	}

	public function executeMoveDnd()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter('file_id')));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
	
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($folder->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($folder->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$path = $file->getPath().DIRECTORY_SEPARATOR;
			$path_to = $folder->getPathToSave().DIRECTORY_SEPARATOR;
	
			@mkdir($path_to, 0777, true);
	
			$file->setGroupeId($folder->getGroupeId());
			$file->setFolderId($folder->getId());
	
			$file->save();
	
			try
			{
				if(file_exists($path.$file->getFileName().".poster.jpeg"))
					copy($path.$file->getFileName().".poster.jpeg", $path_to.$file->getFileName().".poster.jpeg");
	
				if(file_exists($path.$file->getFileName().".flv"))
					copy($path.$file->getFileName().".flv", $path_to.$file->getFileName().".flv");
	
				copy($path.$file->getOriginal(), $path_to.$file->getOriginal());
				copy($path.$file->getWeb(), $path_to.$file->getWeb());
				copy($path.$file->getThumb200(), $path_to.$file->getThumb200());
				copy($path.$file->getThumb100(), $path_to.$file->getThumb100());
	
				// @unlink($path.$file->getThumb100());
				// @unlink($path.$file->getThumb200());
				// @unlink($path.$file->getWeb());
				// @unlink($path.$file->getOriginal());
				// @unlink($path.$file->getFileName().".poster.jpeg");
				// @unlink($path.$file->getFileName().".flv");
			}catch (Exception $e)
			{
	
			}
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode(Array("code" => 0, "msg" => "Success")));
		}
	}

	public function executeUnbindReference()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless(UserPeer::isAllowed($file->getId(), "file"));
	
			$this->forward404Unless($media = FilePeer::retrieveByPk($this->getRequestParameter("media_id")));
			$this->forward404Unless(UserPeer::isAllowed($media->getId(), "file"));
	
			$this->getResponse()->setContentType('application/json');
	
			$related = FileRelatedPeer::retrieveByFileIdToAndFileIdFrom($file->getId(), $media->getId());
	
			if($related)
				$related->delete();
	
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			$result = Array();
			$result["html"] = $this->getPartial("file/related", array("file" => $file, "role" => true));
			$result["code"] = 0;
			return $this->renderText(json_encode($result));
		}
	
		$this->redirect404();
	}

	public function executeUpdateGroupeCover()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
	
			if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
			{
				if($this->getRequestParameter("check") == "true" || $this->getRequestParameter("check") == "checked")
				{
					FilePeer::updateGroupeCover($file->getGroupeId());
					$file->setGroupeCover(1);
				}
				else
					$file->setGroupeCover(0);
	
				$file->save();
			}
	
			return sfView::NONE;
		}
	
		$this->redirect404();
	}

	public function executeLoadGroupeCover()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($album = GroupePeer::retrieveByPK($file->getGroupeId()));
	
			@mkdir($album->getPath().DIRECTORY_SEPARATOR, 0777, true);
			@copy($file->getPathname(), $album->getPath().DIRECTORY_SEPARATOR.$file->getOriginal());
			@chmod($album->getPath().DIRECTORY_SEPARATOR.$file->getOriginal(), 0666);
			$size = getimagesize($album->getPath().DIRECTORY_SEPARATOR.$file->getOriginal());

			$thumbnail = $file->getOriginal();

			if($size[0] > $size[1])
				$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);
			else
				$new = imageTools::initThumb($size[0], $size[1], 220, 100, true, false);

			$new_width = $new["width"];
			$new_height = $new["height"];

			return $this->renderPartial("file/groupeCover", Array("thumbnail" => $thumbnail, "new_width" => $new_width, "new_height" => $new_height));
		}
	
		$this->redirect404();
	}

	public function executeSaveGroupeCover()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
	
			if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
			{
				if($this->getRequestParameter("check") == "true" || $this->getRequestParameter("check") == "checked")
				{
					$groupe = $file->getGroupe();
	
					if(!$groupe->getDiskId())
					{
						$groupe->setDiskId($this->getUser()->getDisk()->getId());
						$groupe->save();
					}
	
					$path = $groupe->getPath();
					$cover = ImageTools::setThumbnailForFolder($path, $file);
	
					$groupe->setThumbnail($cover);
					$groupe->save();
				}
			}
	
			return sfView::NONE;
		}
	
		$this->redirect404();
	}

	public function executeShowCommercialsShow()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/commercialsShow", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}

	public function executeShowConstraintsShow()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/constraintsShow", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}

	public function executeShowLimitationsShow()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/limitationsShow", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}

	public function executeShowCreativeCommonsShow()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/creativeCommonsShow", array("file" => $file, "role" => $this->getRequestParameter("role")));
		}
	
		$this->redirect404();
	}

	public function executeLoadHomeFiles()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$page = $this->getRequestParameter("page");
			$perPage = $this->getRequestParameter("onPage");
			$sort = $this->getRequestParameter("sort");
	
			$temp = FilePeer::getHomeFiles($perPage, ($perPage * ($page - 1)), $sort);
	
			$this->getUser()->savePreferences("file/recent", $sort, $perPage);
	
			$return = Array();
			$return["files"] = "";
			$return["rightclick"] = "";
	
			foreach($temp["files"] as $file)
			{
				$return["files"] .= $this->getPartial("file/grid", Array("file" => $file));
			}
	
			// $return["index"] = $temp["count"] > ($index + 12) ? ($index + 12) : 0;
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode($return));
		}
	
		$this->redirect404();
	}

	public function executeLoadPresetSelected()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			$this->forward404Unless($preset = PresetPeer::retrieveByPk($this->getRequestParameter("id")));
	
			if($preset->getCustomerId() != $this->getUser()->getCustomerId())
				$this->redirect404();
	
			return $this->renderComponent("file", "copyrightSelected", Array("file" => $file, "preset" => $preset));
		}
	
		$this->redirect404();
	}

	public function executeShare()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter('id')));
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($file->getFolderId()));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
			
			if ($roleGroup < RolePeer::__ADMIN) {
				;
			}
			elseif ($roleGroup == RolePeer::__ADMIN) {
				if (!$this->getUser()->hasCredential("admin") && $file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE, RolePeer::__ADMIN)) {
					$this->forward404();
				}
			}
			elseif ($roleGroup == RolePeer::__CONTRIB) {
				if ($file->getFolder()->getUserId() != $this->getUser()->getId() && !$this->getUser()->getConstraint($file->getGroupeId(),ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB)) {
					$this->forward404();
				}
			}
			else {
				$this->forward404();
			}
	
			return $this->renderPartial("file/share", Array("file" => $file));
		}
	
		$this->redirect404();
	}

	public function executeFullscreen()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($this->file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
	
			$this->height = $this->getRequestParameter("height");
			$this->width = $this->getRequestParameter("width");
	
			return sfView::SUCCESS;
		}
	
		$this->redirect404();
	}

	public function executeRecent(sfWebRequest $request)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$breadCrumbs = array();

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
						"label"		=> __("Favorites")
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@file_recent_list"),
						"label"		=> __("Recents"),
						"selected"	=> true
			)
		);

		$this->getResponse()->setSlot('bread_crumbs', $breadCrumbs);
		$this->getResponse()->setSlot("selectedActions", $this->getPartial("public/selectedActions"));
		$preferences = $this->getUser()->getPreferences("file/recent", true, array("sort" => "uploaded_desc", "perPage" => 25));
		$this->getResponse()->setSlot("actions",$this->getPartial("file/breadcrumbRecentActions", array(
				"results" => array("selected" => $preferences["perPage"], "values" => array(25 =>25, 50 => 50, 100 => 100)),
				"sorts" => array("selected" => $preferences["sort"], "values" => array("name_asc" => __("Name ascending"), "name_desc" => __("Name descending"), "uploaded_asc" => __("Uploaded date ascending"), "uploaded_desc" => __("Uploaded date descending")))
		)));

		$temp = FilePeer::getHomeFiles($preferences["perPage"], 0, $preferences["sort"]);

		$this->files = $temp["files"];
		$this->count = $temp["count"];
		$this->itemsToShow = $preferences["perPage"];

		return sfView::SUCCESS;
	}

	public function executeLoadFiles()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("id")));
			$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
			$params = $this->getUser()->getAttribute("params.files.folder.show");
	
			$page = $this->getRequestParameter("page");
			$perPage = 25;
			$sort = $this->getRequestParameter("sort");
	
			$temp = FilePeer::getFilesInFolder(
				$folder->getId(),
				$params["tags"],
				null,
				$params["me"],
				$params["date"],
				$params["shooting"],
				$params["size"],
				$sort,
				$roleGroup <= RolePeer::__ADMIN ? array(FilePeer::__STATE_VALIDATE, FilePeer::__STATE_WAITING_VALIDATE) : array(FilePeer::__STATE_VALIDATE),
				$perPage,
				($perPage * ($page - 1))
			);
	
			$return = Array();
			$return["files"] = "";
			$return["rightclick"] = "";
	
			foreach($temp["files"] as $file)
			{
				$return["files"] .= $this->getPartial("file/grid", Array("file" => $file));
			}
	
			$return["index"] = $temp["count"] > ($page * $perPage) ? ($page * $perPage) : 0;
	
			$this->getResponse()->setContentType('application/json');
			return $this->renderText(json_encode($return));
		}
	
		$this->redirect404();
	}
}
