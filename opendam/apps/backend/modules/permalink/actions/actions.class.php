<?php

/**
 * permalink actions.
 *
 * @package    media management
 * @subpackage permalink
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 3335 2007-01-23 16:19:56Z fabien $
 */
class permalinkActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function executeIndex()
	{
		return $this->forward('permalink', 'list');
	}

	/*________________________________________________________________________________________________________________*/
	public function executeShow()
	{
		$this->forward404Unless($this->permalink = PermalinkPeer::retrieveByPk($this->getRequestParameter('id')));

		switch($this->permalink->getObjectType())
		{
			case PermalinkPeer::__OBJECT_FILE: $this->forward404Unless(UserPeer::isAllowed($this->permalink->getObjectId(), "file")); break;
			case PermalinkPeer::__OBJECT_FOLDER: $this->forward404Unless(UserPeer::isAllowed($this->permalink->getObjectId(), "folder")); break;
			case PermalinkPeer::__OBJECT_GROUP: $this->forward404Unless(UserPeer::isAllowed($this->permalink->getObjectId(), "group")); break;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDownload()
	{
		$link = $this->getRequestParameter('link');
		$permalink = PermalinkPeer::getByLink($link);
		$user = UserPeer::retrieveByPkNoCustomer($permalink->getUserId());
	
		if(ModulePeer::haveAccessModule(ModulePeer::__MOD_PERMALINK, $user->getCustomerId(), $user->getId()))
		{
			if($permalink->getObjectType() == PermalinkPeer::__OBJECT_FILE)
			{
				$file = FilePeer::retrieveByPK($permalink->getObjectId());
				$this->forward404Unless(GroupeConstraintPeer::isAllowedTo($file->getGroupeId(), ConstraintPeer::__PERMALINK_FILE));
	
				if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH)
					$this->forward404();
	
				$file_name = '';
	
				if($permalink->getType() == PermalinkPeer::__TYPE_CUSTOM)
					$this->redirect("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId());
	
				switch($file->getType())
				{
					case FilePeer::__TYPE_PHOTO:
					{
						myTools::addTags($file);
	
						if($permalink->getType() == PermalinkPeer::__TYPE_WEB)
						{
							$path = $file->getPath().DIRECTORY_SEPARATOR;
							if(!file_exists($path."download-".$file->getWeb()))
							{
								$temp = substr($file->getOriginal(), 0, strpos($file->getOriginal(), ".".$file->getExtention()));
								$file_name = imageTools::createThumbnail("download-".$temp, "jpeg", $path.$file->getOriginal(), $path, "jpeg", "thumb");
							}
							else
								$file_name = "download-".$file->getWeb();
						}
						elseif($permalink->getType() == PermalinkPeer::__TYPE_ORIGINAL)
							$file_name = $file->getOriginal();
	
						$file_path = $file->getPath().DIRECTORY_SEPARATOR.$file_name;
						$info = getimagesize($file_path);
						$mime = image_type_to_mime_type($info[2]);
					}
					break;
	
					case FilePeer::__TYPE_AUDIO:
					case FilePeer::__TYPE_VIDEO:
					{
						if (!$this->getUser()->getAttribute("token_file_".$file->getId())) {
							$this->redirect("@permalink_preview?link=".$link);
						}
						else {
							$routing = sfContext::getInstance()->getRouting();
							$referer = $routing->generate("permalink_preview", array("link" => $link), true);
								
							if (array_key_exists("HTTP_REFERER", $_SERVER) && $_SERVER["HTTP_REFERER"] == $referer) {
								$this->getUser()->setAttribute("token_file_".$file->getId(), null);
						
								$file_name = $file->getOriginal();
								$file_path = $file->getPath().DIRECTORY_SEPARATOR.$file_name;
						
								$mime = mime_content_type($file_path);
							}
							else {
								$this->getUser()->setAttribute("token_file_".$file->getId(), null);
								$this->redirect("@permalink_preview?link=".$link);
							}
						}
					}
					break;
	
					default:
					{
						if(!$this->getUser()->getAttribute("token_file_".$file->getId()))
							$this->redirect("@permalink_preview?link=".$link);
						else
						{
							$this->getUser()->setAttribute("token_file_".$file->getId(), null);
	
							$file_name = $file->getOriginal();
							$file_path = $file->getPath().DIRECTORY_SEPARATOR.$file_name;
	
							$mime = mime_content_type($file_path);
						}
					}
					break;
				}
	
				if(empty($file_path))
					$this->redirect("permalink/error");
	
				LogPeer::setLog(null, $file->getId(), "file-download", "3", array(), $file->getCustomerId());
				PermalinkLogPeer::addLog($_SERVER["REMOTE_ADDR"], $file->getId(), PermalinkLogPeer::__FILE);
	
				$this->setLayout(false);
	
				$download = new Httpdownload();
				$download->setInline(false);
				$download->setFilePath($file_path);
				$download->setFilename($file_name);
				$download->executeDownload();
			}
		}
		else
			$this->redirect404();
	
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDeleteExpiredPermalinks()
	{
		try {
			PermalinkPeer::deleteExpiredPermalinks();
		}
		catch (Exception $e) {}

		$this->redirect("@homepage");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeField()
	{
	if($this->getRequest()->isXmlHttpRequest())
	{
		$this->forward404Unless($permalink = PermalinkPeer::retrieveByPk($this->getRequestParameter("id")));

		switch($permalink->getObjectType())
		{
			case PermalinkPeer::__OBJECT_FILE:
				$this->forward404Unless($file = FilePeer::retrieveByPk($permalink->getObjectId()));

				$this->forward404Unless(
					(UserGroupPeer::getRole($this->getUser()->getId(), $file->getGroupeId()) == RolePeer::__ADMIN) ||
					(FolderPeer::retrieveByPk($file->getFolderId())->getUserId() == $this->getUser()->getId()) ||
					($file->getUserId() == $this->getUser()->getId()) ||
					$this->getUser()->isAdmin()
				);
			break;

			case PermalinkPeer::__OBJECT_FOLDER:
				$this->forward404Unless($folder = FolderPeer::retrieveByPk($permalink->getObjectId()));
				$this->forward404Unless(FolderPeer::isAllowedToView($folder->getId(), $this->getUser()->getId()));
			break;

			case PermalinkPeer::__OBJECT_GROUP:
				$this->forward404Unless($group = GroupePeer::retrieveByPk($permalink->getObjectId()));
				$this->forward404Unless(UserPeer::isAllowed($group->getId(), "group"));
			break;
		}

		$value = $this->getRequestParameter('value');
		$field = $this->getRequestParameter('field');

		switch($field)
		{
			case "notify_comment":
				if(!($notification = PermalinkNotificationPeer::retrieveByPermalinkIdAndUserId($permalink->getId(), $this->getUser()->getId())))
				{
					$notification = new PermalinkNotification();
					$notification->setUserId($this->getUser()->getId());
					$notification->setPermalinkId($permalink->getId());

					$notification->save();
				}

				$notification->setAddComment($value == "1" ? true : false);
				$notification->save();
			break;

			case "comment":
				$permalink->setAllowComments($value == "1" ? true : false);
			break;

			case "format":
				$permalink->setFormatHd($value == "1" ? true : false);
			break;

			case "type":
			case "state":
				$permalink->setState($value);

				switch($this->getRequestParameter("value"))
				{
					case PermalinkPeer::__STATE_DISABLED:
					case PermalinkPeer::__STATE_PUBLIC: $permalink->setPassword(null); break;
					case PermalinkPeer::__STATE_PRIVATE: $permalink->setPassword("00000"); break;
				}
			break;

			case "password":
				$permalink->setState(PermalinkPeer::__STATE_PRIVATE);
				$permalink->setPassword(md5($value));
			break;
		}

		$permalink->save();

		return sfView::NONE;
	}
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDesactivate()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		if($this->getRequestParameter("file_id"))
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
			PermalinkPeer::deletByUserIdAndObjectId($this->getUser()->getId(), $file->getId(), 
				PermalinkPeer::__OBJECT_FILE);

			return $this->renderPartial("permalink/show", array("permalink_original" => null, "permalink_web" => null, 
					"file" => $file));
		}
		elseif($this->getRequestParameter("folder_id"))
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));
			PermalinkPeer::deletByUserIdAndObjectId($this->getUser()->getId(), $folder->getId(), 
				PermalinkPeer::__OBJECT_FOLDER);

			return $this->renderPartial("permalink/showFolder", array("folder" => $folder));
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRegenerate()
	{
		if($this->getRequestParameter("file_id"))
		{
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
	
			PermalinkPeer::deletByUserIdAndObjectId($this->getUser()->getId(), $file->getId(), 
				PermalinkPeer::__OBJECT_FILE);
	
			$url = PermalinkPeer::getUrl();
			$qrcode = PermalinkPeer::buildQrCode($file->getId(), $url, PermalinkPeer::__OBJECT_FILE);
	
			$permalink_original = new Permalink();
			$permalink_original->setType(PermalinkPeer::__TYPE_ORIGINAL);
			$permalink_original->setObjectId($file->getId());
			$permalink_original->setObjectType(PermalinkPeer::__OBJECT_FILE);
			$permalink_original->setUserId($this->getUser()->getId());
			$permalink_original->setLink($url);
			$permalink_original->setQrcode($qrcode);
			$permalink_original->setAllowComments(false);
			$permalink_original->setState(PermalinkPeer::__STATE_PUBLIC);
	
			$permalink_original->save();
	
			LogPeer::setLog($this->getUser()->getId(), $permalink_original->getId(), "permalink-create", "10");
	
			$url = PermalinkPeer::getUrl();
	
			$permalink_internal = new Permalink();
			$permalink_internal->setType(PermalinkPeer::__TYPE_CUSTOM);
			$permalink_internal->setObjectId($file->getId());
			$permalink_internal->setObjectType(PermalinkPeer::__OBJECT_FILE);
			$permalink_internal->setUserId($this->getUser()->getId());
			$permalink_internal->setLink($url);
			$permalink_internal->setQrcode("");
			$permalink_internal->setAllowComments(false);
			$permalink_internal->setState(PermalinkPeer::__STATE_PUBLIC);
	
			$permalink_internal->save();
	
			switch($file->getType())
			{
				case FilePeer::__TYPE_VIDEO:
				case FilePeer::__TYPE_PHOTO:
					$url = PermalinkPeer::getUrl();
					$qrcode = PermalinkPeer::buildQrCode($file->getId(), $url, PermalinkPeer::__OBJECT_FILE);
	
					$permalink_web = new Permalink();
					$permalink_web->setType(PermalinkPeer::__TYPE_WEB);
					$permalink_web->setObjectId($file->getId());
					$permalink_web->setObjectType(PermalinkPeer::__OBJECT_FILE);
					$permalink_web->setUserId($this->getUser()->getId());
					$permalink_web->setLink($url);
					$permalink_web->setQrcode($qrcode);
					$permalink_web->setAllowComments(false);
					$permalink_web->setState(PermalinkPeer::__STATE_PUBLIC);
	
					$permalink_web->save();
	
					LogPeer::setLog($this->getUser()->getId(), $permalink_web->getId(), "permalink-create", "10");
				break;
	
				default: $permalink_web = null; break;
			}
	
			if($this->getRequestParameter("template") == "showFile")
				return $this->renderComponent("permalink", "showFile", array("file" => $file));
	
			return $this->renderPartial("permalink/show", array("permalink_internal" => $permalink_internal, 
					"permalink_original" => $permalink_original, "permalink_web" => $permalink_web, "file" => $file));
		}
		elseif($this->getRequestParameter("folder_id"))
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));
	
			PermalinkPeer::deletByUserIdAndObjectId($this->getUser()->getId(), $folder->getId(), 
				PermalinkPeer::__OBJECT_FOLDER);
	
			$url = PermalinkPeer::getUrl();
			$qrcode = PermalinkPeer::buildQrCode($folder->getId(), "f/".$url, PermalinkPeer::__OBJECT_FOLDER);
	
			$permalink = new Permalink();
			$permalink->setType(PermalinkPeer::__TYPE_CUSTOM);
			$permalink->setObjectId($folder->getId());
			$permalink->setObjectType(PermalinkPeer::__OBJECT_FOLDER);
			$permalink->setUserId($this->getUser()->getId());
			$permalink->setLink($url);
			$permalink->setQrcode($qrcode);
			$permalink->setAllowComments(false);
			$permalink->setState(PermalinkPeer::__STATE_PUBLIC);
	
			$permalink->save();
	
			LogPeer::setLog($this->getUser()->getId(), $permalink->getId(), "permalink-create", "10");
	
			return $this->renderPartial("permalink/showFolder", array("folder" => $folder, "showLabel" => 
					$this->getRequestParameter("showLabel")));
		}
		elseif($this->getRequestParameter("group_id"))
		{
			$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("group_id")));
	
			PermalinkPeer::deletByUserIdAndObjectId($this->getUser()->getId(), $group->getId(), 
				PermalinkPeer::__OBJECT_GROUP);
	
			$url = PermalinkPeer::getUrl();
			$qrcode = PermalinkPeer::buildQrCode($group->getId(), "mf/".$url, PermalinkPeer::__OBJECT_GROUP);
	
			$permalink = new Permalink();
			$permalink->setType(PermalinkPeer::__TYPE_CUSTOM);
			$permalink->setObjectId($group->getId());
			$permalink->setObjectType(PermalinkPeer::__OBJECT_GROUP);
			$permalink->setUserId($this->getUser()->getId());
			$permalink->setLink($url);
			$permalink->setQrcode($qrcode);
			$permalink->setAllowComments(false);
			$permalink->setState(PermalinkPeer::__STATE_PUBLIC);
	
			$permalink->save();
	
			LogPeer::setLog($this->getUser()->getId(), $permalink->getId(), "permalink-create", "10");
	
			return $this->renderPartial("permalink/showGroup", array("group" => $group, 
					"showLabel" => $this->getRequestParameter("showLabel")));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCreate()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}

		switch($this->getRequestParameter("type"))
		{
			case "group":
				$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
				$type = PermalinkPeer::__OBJECT_GROUP;

				$url = PermalinkPeer::getUrl();
				$qrcode = PermalinkPeer::buildQrCode($group->getId(), "mf/".$url, $type);

				$permalink = new Permalink();

				$permalink->setType(PermalinkPeer::__TYPE_CUSTOM);
				$permalink->setObjectId($group->getId());
				$permalink->setObjectType($type);
				$permalink->setUserId($this->getUser()->getId());
				$permalink->setLink($url);
				$permalink->setQrcode($qrcode);
				$permalink->setAllowComments(false);
				$permalink->setState(PermalinkPeer::__STATE_PUBLIC);

				$permalink->save();

				LogPeer::setLog($this->getUser()->getId(), $permalink->getId(), "permalink-create", "10");

				return $this->renderPartial("permalink/showGroup", array("group" => $group, 
						"showLabel" => $this->getRequestParameter("showLabel")));
			break;

			case "folder":
				$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("id")));
				$type = PermalinkPeer::__OBJECT_FOLDER;

				$url = PermalinkPeer::getUrl();
				$qrcode = PermalinkPeer::buildQrCode($folder->getId(), "f/".$url, $type);

				$permalink = new Permalink();
				$permalink->setType(PermalinkPeer::__TYPE_CUSTOM);
				$permalink->setObjectId($folder->getId());
				$permalink->setObjectType($type);
				$permalink->setUserId($this->getUser()->getId());
				$permalink->setLink($url);
				$permalink->setQrcode($qrcode);
				$permalink->setAllowComments(false);
				$permalink->setState(PermalinkPeer::__STATE_PUBLIC);

				$permalink->save();

				LogPeer::setLog($this->getUser()->getId(), $permalink->getId(), "permalink-create", "10");

				return $this->renderPartial("permalink/showFolder", array("folder" => $folder, 
						"showLabel" => $this->getRequestParameter("showLabel")));
			break;

			case "file":
				$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
				$type = PermalinkPeer::__OBJECT_FILE;

				$url = PermalinkPeer::getUrl();
				$qrcode = PermalinkPeer::buildQrCode($file->getId(), $url, $type);

				$permalink_original = new Permalink();
				$permalink_original->setType(PermalinkPeer::__TYPE_ORIGINAL);
				$permalink_original->setObjectId($file->getId());
				$permalink_original->setObjectType($type);
				$permalink_original->setUserId($this->getUser()->getId());
				$permalink_original->setLink($url);
				$permalink_original->setQrcode($qrcode);
				$permalink_original->setAllowComments(false);
				$permalink_original->setState(PermalinkPeer::__STATE_PUBLIC);
			
				$permalink_original->save();

				LogPeer::setLog($this->getUser()->getId(), $permalink_original->getId(), "permalink-create", "10");

				switch($file->getType())
				{
					case FilePeer::__TYPE_VIDEO:
					case FilePeer::__TYPE_PHOTO:
						$url = PermalinkPeer::getUrl();
						$qrcode = PermalinkPeer::buildQrCode($file->getId(), $url, $type);

						$permalink_web = new Permalink();
						$permalink_web->setType(PermalinkPeer::__TYPE_WEB);
						$permalink_web->setObjectId($file->getId());
						$permalink_web->setObjectType($type);
						$permalink_web->setUserId($this->getUser()->getId());
						$permalink_web->setLink($url);
						$permalink_web->setQrcode($qrcode);
						$permalink_web->setAllowComments(false);
						$permalink_web->setState(PermalinkPeer::__STATE_PUBLIC);
					
						$permalink_web->save();

						LogPeer::setLog($this->getUser()->getId(), $permalink_web->getId(), "permalink-create", "10");
					break;

					default: $permalink_web = null; break;
				}

				if($this->getRequestParameter("template") == "showFile")
					return $this->renderComponent("permalink", "showFile", array("file" => $file));

				return $this->renderPartial("permalink/show", array("file" => $file, 
						"permalink_original" => $permalink_original, "permalink_web" => $permalink_web));
			break;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function executePreview()
	{
		$this->link = $this->getRequestParameter('link');
	
		$this->forward404Unless($permalink = PermalinkPeer::getByLink($this->link));
		$this->forward404Unless($permalink->getObjectType() == PermalinkPeer::__OBJECT_FILE);
		$this->forward404Unless($this->file = FilePeer::retrieveByPK($permalink->getObjectId()));
		$this->forward404Unless(in_array($this->file->getType(), 
				array(FilePeer::__TYPE_AUDIO, FilePeer::__TYPE_VIDEO, FilePeer::__TYPE_DOCUMENT)));
	
		$this->getUser()->setAttribute("token_file_".$this->file->getId(), true);
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeError()
	{
	}

	/*________________________________________________________________________________________________________________*/
	public function executeEdit()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
		$this->forward404Unless($permalink = PermalinkPeer::retrieveByPk($this->getRequestParameter("id")));

		switch($permalink->getObjectType())
		{
			case PermalinkPeer::__OBJECT_FILE:
			{
				$this->forward404Unless($file = FilePeer::retrieveByPk($permalink->getObjectId()));
				$this->forward404Unless(UserPeer::isAllowed($permalink->getObjectId(), "file"));
	
				return $this->redirect(url_for("/p/".$permalink->getLink()));
			}
			break;
	
			case PermalinkPeer::__OBJECT_FOLDER:
			{
				$this->forward404Unless($folder = FolderPeer::retrieveByPk($permalink->getObjectId()));
				$this->forward404Unless(UserPeer::isAllowed($permalink->getObjectId(), "folder"));
	
				return $this->redirect(url_for("/f/".$permalink->getLink()));
			}
			break;

			case PermalinkPeer::__OBJECT_GROUP:
			{
				$this->forward404Unless($group = GroupePeer::retrieveByPk($permalink->getObjectId()));
				$this->forward404Unless(UserPeer::isAllowed($permalink->getObjectId(), "group"));
	
				return $this->redirect(url_for("/g/".$permalink->getLink()));
			}
			break;
		}
	}
}