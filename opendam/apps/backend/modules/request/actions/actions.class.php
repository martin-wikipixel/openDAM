<?php
/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class requestActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
	}
	/*________________________________________________________________________________________________________________*/
	public function executeToFolder()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter('folder_id')));
		$this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter('folder_id'), "folder"));
	
		// checks user rights
		$this->forward404Unless(
			(UserGroupPeer::getRole($this->getUser()->getId(), $folder->getGroupeId()) == RolePeer::__ADMIN) ||
			$this->getUser()->isAdmin()
		);
	
		$this->getResponse()->setSlot('title', __("Manage folder")." \"".$folder."\"");
	
		$this->requests = RequestPeer::retrieveByFolderId($folder->getId());
		$this->folder = $folder;
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSendRequest()
	{
		if($this->getRequestParameter("data")) {
			$data = $this->getRequestParameter("data");
			$group_id = array_key_exists("group_id", $data) ? $data["group_id"] : null;
		} else
			$group_id = $this->getRequestParameter("group_id");
	
		$this->forward404Unless($group = GroupePeer::retrieveByPK($group_id));
		$this->getResponse()->setSlot('title', __("Send a request for access to the group").' "'.$group.'"');
	
		$this->form = new RequestSendRequestForm(
			array(
				"group_id" => $group_id
			)
		);
	
		if ($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if($this->form->isValid())
			{
				if(!RequestPeer::getRequest($group->getId(), $this->getUser()->getId()))
				{
					$request = new Request();
					$request->setGroupeId($group->getId());
					$request->setUserId($this->getUser()->getId());
					$request->setIsRequest(1);
					$request->setMessage($this->form->getValue("message"));
					$request->save();
	
					$to = Array();
					$admins = UserPeer::retrieveByRoleIds(array(RolePeer::__ADMIN));
					foreach ($admins as $admin)
					{
						if($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true 
							|| $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
							$to[$admin->getEmail()] = $admin->getEmail();
					}
	
					$validators = UserGroupPeer::getUsers($group->getId(), RolePeer::__ADMIN);
					foreach ($validators as $validator)
					{
						$user = $validator->getUser();
						if ($user && $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true 
							|| $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1) {
							if (!in_array($user->getEmail(), $to)) {
								$to[$user->getEmail()] = $user->getEmail();
							}
						}
					}
	
					$unitsValidators = UnitGroupPeer::getEffectiveByGroupIdAndRole($group->getId(), RolePeer::__ADMIN);
					foreach ($unitsValidators as $unitsValidator)
					{
						$user = $unitsValidator->getUser();
						if ($user && $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true 
							|| $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1) {
							if (!in_array($user->getEmail(), $to)) {
								$to[$user->getEmail()] = $user->getEmail();
							}
						}
					}
	
					;
					$user = $this->getUser()->getInstance();
					$email = new myMailer("request_send_group", "[wikiPixel] ".__("Request for access to the group")." \"".$group."\"");
					$search = Array("**ALBUM_NAME**", "**LASTNAME**", "**FIRSTNAME**", "**EMAIL**", "**REQUEST_MESSAGE**","**URL**");
					$replace = Array($group->getName(), ucfirst(strtolower($user->getLastname())), ucfirst(strtolower($user->getFirstname())),
							$user->getEmail(), nl2br($this->form->getValue("message")),
							url_for("@homepage?pop=1&url=".url_for("@group_right_user_list?album=".$group->getId(), true), true));
					$email->setTo($to);
					$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
					$email->compose($search, $replace);
					$email->send();
	
					if($request->getIsRequest()){
						$group = GroupePeer::retrieveByPK($request->getGroupeId());
						$subject = __("Your request for access to the group %1% successfully sent.", array("%1%" => '"'.$group.'"'));
						$content = __("Your request for access to the group %1% successfully sent.", array("%1%" => '"'.$group.'"'));
					}else{
						$subject = __("Your contact message successfully sent.");
						$content = __("Your contact message successfully sent.");
					}
	
					$search = Array("**CONTENT**", "**MESSAGE**");
					$replace = Array($content, nl2br($this->form->getValue("message")));
	
					$email = new myMailer("request_send_to_me", "[wikiPixel] ".$subject);
					$email->setTo(Array($this->getUser()->getInstance()->getEmail() => $this->getUser()->getInstance()->getEmail()));
					$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
					$email->compose($search, $replace);
					$email->send();
	
					// set log
					LogPeer::setLog($this->getUser()->getId(), $this->form->getValue("group_id"), "request-group", "8");
	
					$this->getUser()->setFlash("success", __("Your request for access to this group successfully sent."), false);
				} else
					$this->getUser()->setFlash("warning", __("Your request for access to this group is already sent."), false);
	
				sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
				$this->uri = url_for("@homepage");
				$this->setTemplate("thankyou");
			}
		}
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSendRequestFolder()
	{
		if($this->getRequestParameter("data")) {
			$data = $this->getRequestParameter("data");
			$folder_id = array_key_exists("folder_id", $data) ? $data["folder_id"] : null;
		} else
			$folder_id = $this->getRequestParameter("folder_id");
	
		$this->forward404Unless($folder = FolderPeer::retrieveByPK($folder_id));
	
		$this->getResponse()->setSlot('title', __("Send a request for access to the folder").' "'.$folder.'"');
	
		$this->form = new RequestSendFolderForm(
			array(
				"folder_id" => $folder_id
			)
		);
	
		if ($this->getRequest()->getMethod() == sfRequest::POST)
		{
			$this->form->bind($this->getRequestParameter('data'));
			$this->getResponse()->setSlot("form", $this->form);
	
			if($this->form->isValid())
			{
				if(!RequestPeer::getRequest($this->getRequestParameter("folder_id"), $this->getUser()->getId()))
				{
					$request = new Request();
					$request->setFolderId($folder->getId());
					$request->setUserId($this->getUser()->getId());
					$request->setIsRequest(1);
					$request->setMessage($this->form->getValue("message"));
					$request->save();
	
					$this->getRequest()->setParameter("request_id", $request->getId());
	
					$to = Array();
					$admins = UserPeer::retrieveByRoleIds(array(RolePeer::__ADMIN));
					foreach ($admins as $admin)
					{
						if($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true 
							|| $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
							$to[$admin->getEmail()] = $admin->getEmail();
					}
	
					$validators = UserGroupPeer::getUsers($folder->getGroupeId(), RolePeer::__ADMIN);
					foreach ($validators as $validator)
					{
						$user = $validator->getUser();
						if (($user && $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true)
							|| ($user && $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)) {
							if (!in_array($user->getEmail(), $to)) {
								$to[$user->getEmail()] = $user->getEmail();
							}
						}
					}
	
					$unitsValidators = UnitGroupPeer::getEffectiveByGroupIdAndRole($folder->getGroupeId(), RolePeer::__ADMIN);
					foreach ($unitsValidators as $unitsValidator)
					{
						$user = $unitsValidator->getUser();
						if ($user && $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true 
							|| $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1) {
							if (!in_array($user->getEmail(), $to)) {
								$to[$user->getEmail()] = $user->getEmail();
							}
						}
					}
					
					
	
					$search = Array("**FOLDER_NAME**", "**LASTNAME**", "**FIRSTNAME**", "**EMAIL**", "**REQUEST_MESSAGE**", "**URL**");
					$replace = Array($folder->getName(), ucfirst(strtolower($this->getUser()->getLastname())), 
							ucfirst(strtolower($this->getUser()->getFirstname())), $this->getUser()->getEmail(), $request->getMessage(), 
							url_for("@homepage?pop=1&url=".url_for("@folder_right_user_list?folder=".$folder->getId(), true), true));
	
					$email = new myMailer("request_send_folder", "[wikiPixel] ".__("Request for access to the folder")." \"".$folder->getName()."\"");
					$email->setTo($to);
					$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
					$email->compose($search, $replace);
					$email->send();
	
					if($request->getIsRequest()){
						$subject = __("Your request for access to the folder")." \"".$folder->getName()."\" ".__("successfully sent.");
						$content = __("Your request for access to the folder")." \"".$folder->getName()."\" ".__("successfully sent.");
					}else{
						$subject = __("Your contact message successfully sent.");
						$content = __("Your contact message successfully sent.");
					}
	
					$search = Array("**CONTENT**", "**MESSAGE**");
					$replace = Array($content, $request->getMessage());
	
					$email = new myMailer("request_send_to_me_folder", "[wikiPixel] ".$subject);
					$email->setTo(Array($this->getUser()->getInstance()->getEmail() => $this->getUser()->getInstance()->getEmail()));
					$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
					$email->compose($search, $replace);
					$email->send();
	
					LogPeer::setLog($this->getUser()->getId(), $folder->getId(), "request-folder", "8");
					
					$this->getUser()->setFlash("success", __("Your request for access to this folder successfully sent."), false);
				} else
					$this->getUser()->setFlash("warning", __("Your request for access to this folder is already sent."), false);
			}
	
			sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
			$this->uri = url_for("group/show?id=".$folder->getGroupeId());
			$this->setTemplate("thankyou");
		}

		$this->folderPath = explode("|", FolderPeer::getBreadCrumbTxt($folder->getId()));
		$this->folderPath = array_reverse($this->folderPath);
		$this->folderPath = substr(implode(" / ", $this->folderPath), 3, strlen(implode(" / ", $this->folderPath)));

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	# USERS CANCEL THEIR SENT REQUESTS
	public function executeCancel()
	{
		// TODO: have to check user role ?
		try {
			$this->forward404Unless(UserPeer::isAllowed($this->getRequestParameter('group_id'), "group"));
			RequestPeer::deleteRequest($this->getRequestParameter("group_id"), $this->getUser()->getId());
	
			$this->getUser()->setFlash("success", __("Your access request has successfully cenceled."), true);
	
			// set log
			LogPeer::setLog($this->getUser()->getId(), $this->getRequestParameter("group_id"), "request-cancel-group", "8");
		}
		catch (Exception $e){}
	
		$this->redirect("@homepage");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCancelFolder()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPK($this->getRequestParameter("folder_id")));
		RequestPeer::deleteRequestFolder($folder->getId(), $this->getUser()->getId());
		$this->getUser()->setFlash("success", __("Your access request has successfully cenceled."), true);
		LogPeer::setLog($this->getUser()->getId(), $this->getRequestParameter("folder_id"), "request-cancel-folder", "8");
	
		$this->redirect("group/show?id=".$folder->getGroupeId());
	}

	/*________________________________________________________________________________________________________________*/
	# ADMIN, SUPER ADMIN AND GROUP ADMINISTRATOR CAN ACCEPT
	public function executeAccept()
	{
		if($request = RequestPeer::retrieveByPK($this->getRequestParameter('id'))){
			if($group = GroupePeer::retrieveByPK($request->getGroupeId()))
			{
				$this->forward404Unless($roleGroup = $this->getUser()->getRole($group->getId()));
				$this->forward404If($roleGroup > RolePeer::__ADMIN);
		
				if(!$user_group = UserGroupPeer::getUserGroup($request->getGroupeId(), $request->getUserId())){
					$user_group = New UserGroup();
				}
				
				$user_group->setGroupeId($request->getGroupeId());
				$user_group->setUserId($request->getUserId());
				$user_group->setRole($this->getRequestParameter("role"));
				$user_group->setState(UserGroupPeer::__STATE_ACTIVE);
				$user_group->save();
			
				// TODO: have to set log
				LogPeer::setLog($this->getUser()->getId(), $request->getGroupeId(), "request-accept", "8");
			
				$roles = array(
					RolePeer::__ADMIN => __("Manager"),
					RolePeer::__CONTRIB => __("Contributor"),
					RolePeer::__READER => __("Reader")
				);
		
				$search = Array("**URL**", "**ALBUM_NAME**", "**ROLE**");
				$replace = Array(url_for("@group_show?id=".$group->getId()), $group->getName(), $roles[$user_group->getRole()]);
		
				$email = new myMailer("request_accept", "[wikiPixel] ".__("Your request accepted."));
				$email->setTo(Array(UserPeer::retrieveByPk($request->getUserId())->getEmail() => UserPeer::retrieveByPk($request->getUserId())->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
			}
		
			$request->delete();
		
			if($this->getRequest()->getParameter("new") == 1) {
				$this->getResponse()->setContentType('application/json');
				return $this->renderText(json_encode(array(
						"errorCode" => 0,
						"html" => $this->getComponent("group", "lineDetailsUserGroup", array("user_right" => $user_group, "group" => $group)))
				));
			}
		}
	
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAcceptFolder()
	{
		if($request = RequestPeer::retrieveByPK($this->getRequestParameter('id'))) {
			if($folder = FolderPeer::retrieveByPK($request->getFolderId()))
			{
				$this->forward404Unless($roleGroup = $this->getUser()->getRole($folder->getGroupeId()));
				$this->forward404If($roleGroup > RolePeer::__CONTRIB);
		
				$userFolder = new UserFolder();
				$userFolder->setUserId($request->getUserId());
				$userFolder->setFolderId($folder->getId());
				$userFolder->setRole(RolePeer::__READER);
				$userFolder->save();
		
				LogPeer::setLog($this->getUser()->getId(), $request->getFolderId(), "request-accept-folder", "8");
		
				// send email about the accepted request
				$search = Array("**URL**", "**FOLDER_NAME**");
				$replace = Array(url_for("folder/show?id=".$folder->getId(), true), $folder->getName());
		
				$email = new myMailer("request_accept_folder", "[wikiPixel] ".__("Your request accepted."));
				$email->setTo(Array(UserPeer::retrieveByPk($request->getUserId())->getEmail() => UserPeer::retrieveByPk($request->getUserId())->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
			}
			
			$request->delete();
		
			if($this->getRequest()->getParameter("new") == 1) {
				$this->getResponse()->setContentType('application/json');
				return $this->renderText(json_encode(array(
					"errorCode" => 0,
					"html" => $this->getComponent("folder", "lineDetailsUserFolder", array("user" => $userFolder->getUser(), "folder" => $folder)))
			));
			}
		}
	
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	# ADMIN, SUPER ADMIN AND GROUP ADMINISTRATOR ? CAN DENY
	public function executeDeny()
	{
		if($request = RequestPeer::retrieveByPK($this->getRequestParameter('id'))) {
			// TODO: have to set log
			LogPeer::setLog($this->getUser()->getId(), $request->getGroupeId(), "request-deny-group", "8");
	
			// send email the refusal note or the reply message to request sender
			if($request->getGroupeId() == 0 || in_array($request->getType(), Array(2,3))){
				$search = Array("**NOTE**");
				$replace = Array($this->getRequestParameter("note"));
		
				$email = new myMailer("request_reply", "[wikiPixel] ".__("Reply for your contact message."));
				$email->setTo(Array(UserPeer::retrieveByPk($request->getUserId())->getEmail() => UserPeer::retrieveByPk($request->getUserId())->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
			}
			else{
				$album = GroupePeer::retrieveByPk($request->getGroupeId());

				$search = Array("**ALBUM_NAME**", "**NOTE**");
				$replace = Array($album->getName(), $this->getRequestParameter("note"));
		
				$email = new myMailer("request_deny", "[wikiPixel] ".__("Your request denied."));
				$email->setTo(Array(UserPeer::retrieveByPk($request->getUserId())->getEmail() => UserPeer::retrieveByPk($request->getUserId())->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
			}
	
			$request->delete();
		
			if($this->getRequest()->getParameter("new") == 1) {
				$this->getResponse()->setContentType('application/json');
				return $this->renderText(json_encode(array("errorCode" => 0)));
			}
		}
	
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDenyFolder()
	{
		if($request = RequestPeer::retrieveByPK($this->getRequestParameter('id'))){
			// TODO: have to set log
			LogPeer::setLog($this->getUser()->getId(), $request->getFolderId(), "request-deny-folder", "8");
	
			// send email the refusal note or the reply message to request sender
			if($request->getFolderId() == 0){
				;
			}else{
				$folder = FolderPeer::retrieveByPk($request->getFolderId());

				$search = Array("**FOLDER_NAME**", "**NOTE**");
				$replace = Array($folder->getName(), $this->getRequestParameter("note"));
		
				$email = new myMailer("request_deny_folder", "[wikiPixel] ".__("Your request denied."));
				$email->setTo(Array(UserPeer::retrieveByPk($request->getUserId())->getEmail() => UserPeer::retrieveByPk($request->getUserId())->getEmail()));
				$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($this->getUser()->getCustomerId())));
				$email->compose($search, $replace);
				$email->send();
			}
	
			$request->delete();
	
			if($this->getRequest()->getParameter("new") == 1) {
				$this->getResponse()->setContentType('application/json');
				return $this->renderText(json_encode(array("errorCode" => 0)));
			}
		}

		return sfView::NONE;
	}
}
