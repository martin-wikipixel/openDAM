<?php

/**
 * selection actions.
 *
 * @package    wikipixel
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class selectionActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeField()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			if($this->getRequestParameter("id"))
				$this->forward404Unless($basket = BasketPeer::retrieveByPk($this->getRequestParameter("id")));
			else
			{
				$this->forward404Unless($basket = $this->getUser()->getBasket());
	
				if(!BasketPeer::retrieveByPk($basket->getId()))
					$this->forward404();
			}
	
			switch($this->getRequestParameter("field"))
			{
				case "name":
					$basket->setTitle($this->getRequestParameter("value"));
					break;
	
				case "description":
					$basket->setDescription($this->getRequestParameter("value"));
					break;
	
				case "state":
					$basket->setState($this->getRequestParameter("value"));
	
					switch($this->getRequestParameter("value"))
					{
						case BasketPeer::__STATE_PUBLIC: $basket->setPassword(null); break;
						case BasketPeer::__STATE_PRIVATE: $basket->setPassword("00000"); break;
					}
					break;
	
				case "password":
					$basket->setPassword(md5($this->getRequestParameter("value")));
					break;
	
				case "download":
					$basket->setAllowDownloadHd($this->getRequestParameter("value") == "1" ? true : false);
					break;
	
				case "comment":
					$basket->setAllowComments($this->getRequestParameter("value") == "1" ? true : false);
					break;
			}
	
			LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-update", "13");
	
			$basket->save();
	
			return sfView::NONE;
		}
	
		return $this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAddTo()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');
	
			$basket = $this->getUser()->getBasket();
	
			$result = Array();
			$result["thumb"] = "";
			$result["html"] = "";
			$result["add"] = 0;
	
			if(!$basket || ($basket && !BasketPeer::retrieveByPk($basket->getId())))
			{
				$basket = new Basket();
				$basket->setUserId($this->getUser()->getId());
				$basket->setState(BasketPeer::__STATE_PUBLIC);
	
				if ($this->getUser()->isAdmin()) {
					$basket->setIsValid(true);
				}
	
				$basket->setTitle(date("Ymd-his"));
				$basket->save();
	
				LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-save", "13");
	
				$this->getUser()->setBasket($basket);
			}
	
			switch($this->getRequestParameter("type"))
			{
				case "files":
					$ids = $this->getRequestParameter("id");
	
					foreach($ids as $id)
					{
						$this->forward404Unless($file = FilePeer::retrieveByPk($id));
	
						if(!BasketHasContentPeer::searchIntoBasket($basket->getId(), $file->getId()))
						{
							if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
							{
								$content = new BasketHasContent();
								$content->setBasketId($basket->getId());
								$content->setFileId($file->getId());
								$content->save();
	
								LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-add", "13", array($file->getId()));
	
								$result["html"] .= $this->getPartial("selection/myBasketContent", Array("content" => $content));
								$result["thumb"] .= $this->getPartial("selection/myBasketThumbContent", Array("content" => $content));
								$result["add"] += 1;
							}
						}
					}
					break;
	
				case "file":
					{
						$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
	
						if(!BasketHasContentPeer::searchIntoBasket($basket->getId(), $file->getId()))
						{
							if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
							{
								$content = new BasketHasContent();
								$content->setBasketId($basket->getId());
								$content->setFileId($file->getId());
								$content->save();
	
								LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-add", "13", array($file->getId()));
	
								$result["html"] .= $this->getPartial("selection/myBasketContent", Array("content" => $content));
								$result["thumb"] .= $this->getPartial("selection/myBasketThumbContent", Array("content" => $content));
								$result["add"] += 1;
							}
						}
					}
					break;
	
				case "folder":
					{
						$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("id")));
	
						$files = $folder->getAllFiles();
	
						foreach($files as $file)
						{
							if(!BasketHasContentPeer::searchIntoBasket($basket->getId(), $file->getId()))
							{
								if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
								{
									$content = new BasketHasContent();
									$content->setBasketId($basket->getId());
									$content->setFileId($file->getId());
									$content->save();
	
									LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-add", "13", array($file->getId()));
	
									$result["html"] .= $this->getPartial("selection/myBasketContent", Array("content" => $content));
									$result["thumb"] .= $this->getPartial("selection/myBasketThumbContent", Array("content" => $content));
									$result["add"] += 1;
								}
							}
						}
					}
					break;
	
				case "group":
					{
						$this->forward404Unless($groupe = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
	
						$c = new Criteria();
						$c->add(FilePeer::GROUPE_ID, $groupe->getId());
						$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
						$files = FilePeer::doSelect($c);
	
						foreach($files as $file)
						{
							if(!BasketHasContentPeer::searchIntoBasket($basket->getId(), $file->getId()))
							{
								if($file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH)
								{
									$content = new BasketHasContent();
									$content->setBasketId($basket->getId());
									$content->setFileId($file->getId());
									$content->save();
	
									LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-add", "13", array($file->getId()));
	
									$result["html"] .= $this->getPartial("selection/myBasketContent", Array("content" => $content));
									$result["thumb"] .= $this->getPartial("selection/myBasketThumbContent", Array("content" => $content));
									$result["add"] += 1;
								}
							}
						}
					}
					break;
			}
	
			$result["code"] = 0;
	
			return $this->renderText(json_encode($result));
		}
	
		$this->redirect404();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeRemoveFrom()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');
			$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));
			$this->forward404Unless($basket = $this->getUser()->getBasket());
	
			if(!BasketPeer::retrieveByPk($basket->getId()))
				$this->forward404();
	
			$this->forward404Unless($content = BasketHasContentPeer::searchIntoBasket($basket->getId(), $file->getId()));
	
			LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-remove", "13", array($content->getFileId()));
	
			$content->delete();
	
			$result = Array();
			$result["code"] = 0;
	
			return $this->renderText(json_encode($result));
		}
	
		$this->redirect404();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeRemoveAllFrom()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');
			$this->forward404Unless($basket = $this->getUser()->getBasket());
	
			if(!BasketPeer::retrieveByPk($basket->getId()))
				$this->forward404();
	
			$c = new Criteria();
			$c->add(BasketHasContentPeer::BASKET_ID, $basket->getId());
	
			$contents = BasketHasContentPeer::doSelect($c);
	
			foreach($contents as $content)
			{
				LogPeer::setLog($this->getUser()->getId(), $basket->getId(), "cart-remove", "13", array($content->getFileId()));
				$content->delete();
			}
	
			$result = Array();
			$result["code"] = 0;
	
			return $this->renderText(json_encode($result));
		}
	
		$this->redirect404();
	}
	
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	
	
	/*________________________________________________________________________________________________________________*/
	public function executeShowCurrent(sfWebRequest $request)
	{
		$currentBasket = $this->getUser()->getBasket();
		$this->forward404Unless($currentBasket);
		
		$this->redirect("@selection_edit?id=".$currentBasket->getId());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeList(sfWebRequest $request)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array("created_at_desc"));
		$itemPerPage = 10;
		$breadCrumbs = array();

		array_push($breadCrumbs, array(
						"link"		=> path("@homepage"),
						"label"		=> __("Groups")." (".GroupePeer::getCountHomeGroups().")"
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@selection_list"),
						"label"		=> __("Selections"),
						"selected"	=> true
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@favorite_list"),
						"label"		=> __("Favorites")
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@file_recent_list"),
						"label"		=> __("Recents")
				)
		);

		$this->selections = BasketPeer::getPager($page, $itemPerPage,
				array(
						"keyword" 	=> $keyword,
						"userId"	=> $this->getUser()->getId()
				), $orderBy);

		$this->keyword = $keyword;
		$this->orderBy = $orderBy;
		$this->csrfToken = $this->getUser()->getCsrfToken();
		$this->breadCrumbs = $breadCrumbs;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSetCurrent(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($selection);
	
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}
		
		// csrf token
		SecurityUtils::checkCsrfToken();

		$this->getUser()->setBasket($selection);
		$this->getUser()->setFlash("success", __("This selection was defined as current selection."));
	
		return $this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeDelete(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($selection);
	
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}
		
		// csrf token
		SecurityUtils::checkCsrfToken();

		$selection->delete();
	
		LogPeer::setLog($this->getUser()->getId(), $selection->getId(), "cart-delete", "13");
		
		$this->getUser()->setFlash("success", __("This selection has been successfully deleted."));
	
		return $this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeEdit(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($selection);

		$allowPassword = (bool)$request->getParameter("allow_password", $selection->getPassword() != null);
		$allowComment = (bool)$request->getParameter("allow_comment");

		$password = $request->getParameter("password");
		$confirmPassword = $request->getParameter("confirmPassword");

		$isShared = $request->getParameter("isShared", $selection->getIsShared());
		
		$changePassword = false;
		
		// si oui est coché mais qu'on a pas entré de password, on garde l'ancien password.
		if ($allowPassword) {
			if ($selection->getPassword()) {// avant il y avait un mot de passe
				$changePassword = !empty($password);// et le champ password est vide -> on garde l'ancien password (changePassword = false)
			}
			else {
				$changePassword = true;
			}
		}

		$errors = array();

		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}

		$form = new Backend_Basket_EditForm(
			array(
				"name" => $selection->getName(),
				"description" => $selection->getDescription(),
			)
		);

		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
		
			// check password
			if ($allowPassword && $changePassword) {
				if (!$password) {
					$errors["password"] = __("Required.");
				}
				
				if (!$confirmPassword) {
					$errors["confirm_password"] = __("Required.");
				}
				
				if ($password && $confirmPassword) {
					if ($password != $confirmPassword) {
						$errors["password"] = __("Passwords do not match, please check and try again.");
					}
				}
			}

			if ($form->isValid() && !count($errors)) {
				$selection->setName($form->getValue("name"));
				$selection->setDescription($form->getValue("description"));
				
				$selection->setAllowComments($allowComment);
				$selection->setIsShared($isShared);
				
				if ($allowPassword) {
					if ($changePassword) {
						$selection->setPassword(md5($password));
						$selection->setState(BasketPeer::__STATE_PRIVATE);
					}
				}
				else {
					$selection->setPassword(null);
					$selection->setState(BasketPeer::__STATE_PUBLIC);
				}

				$selection->save();
				$this->getUser()->setFlash("success", __("Selection has been updated."));
			}
		}

		$this->isShared = $isShared;
		$this->allowPassword = $allowPassword;
		$this->allowComment = $allowComment;

		$this->password = $password;
		$this->confirmPassword = $confirmPassword;
		
		$this->errors = $errors;
		$this->form = $form;
		$this->selection = $selection;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFileList(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByPk($request->getParameter("selection"));
		$this->forward404Unless($selection);
		
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}
		
		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 15;
		$orderBy = array();
		
		$this->selectionFiles = BasketHasContentPeer::getPager($page, $itemPerPage,
				array(
						"basketId" 	=> $selection->getId(),
				), $orderBy);
		
		$this->csrfToken = $this->getUser()->getCsrfToken();
		$this->selection = $selection;
		//$this->form = $form;
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	private function processDownload(Basket $selection)
	{
		$contents = BasketHasContentPeer::getContents($selection->getId());
		$file_ids = Array();
		
		$archiveName = "collection-".$selection->getCode()."-".date("Ymd-His").".zip";
		$archivePathname = sys_get_temp_dir() ."/".$archiveName;

		$zip = new ZipArchive();
		
		$flag = $zip->open($archivePathname, ZIPARCHIVE::CREATE);
		Assert::ok($flag);
		
		foreach ($contents as $content) {
			$file = $content->getFile();
			$file_ids[] = $file->getId();

			if ($file->exists() && $file->getUsageDistributionId() != UsageDistributionPeer::__UNAUTH) {
				$zip->addFile($file->getPathname(), $file->getOriginal());
			}
		}
		
		$zip->close();
		
		LogPeer::setLog($this->getUser()->getId(), 0, "files-download", "3", $file_ids);
		LogPeer::setLog($this->getUser()->getId(), $selection->getId(), "cart-download", "13");
		
		$this->getResponse()->clearHttpHeaders();
		
		$download = new Httpdownload();
		
		$download->setInline(false);
		$download->setFilePath($archivePathname);
		$download->setFilename($archiveName);
		$download->executeDownload();
		
		@unlink($archivePathname);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Téléchargement de tous les fichiers de la sélection.
	 * 
	 * @param sfWebRequest $request
	 * @return string
	 */
	public function executeDownload(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByPk($request->getParameter("selection"));
		$this->forward404Unless($selection);
		
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}

		$this->processDownload($selection);
		
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFileDelete(sfWebRequest $request)
	{
		$fileId = $request->getParameter("file");

		$selection = BasketPeer::retrieveByPk($request->getParameter("selection"));
		$this->forward404Unless($selection);
		
		// check rights
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}
		
		$content = BasketHasContentPeer::searchIntoBasket($selection->getId(), $fileId);
		$this->forward404Unless($content);
	
		$content->delete();

		LogPeer::setLog($this->getUser()->getId(), $selection->getId(), "cart-remove", "13", array($content->getFileId()));
		$this->getUser()->setFlash("success", __("Selection has been deleted."));
	
		$count = BasketHasContentPeer::countFiles($selection->getId());
		
		if ($count == 0) {
			$selection->delete();
			LogPeer::setLog($this->getUser()->getId(), $selection->getId(), "cart-delete", "13");
			$this->getUser()->setFlash("success", __("This selection has been successfully deleted."));
			$this->redirect("@selection_list");
		}
		
		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeEmailSend(sfWebRequest $request)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$selection = BasketPeer::retrieveByPk($request->getParameter("selection"));
		$this->forward404Unless($selection);
		
		$user = $this->getUser()->getInstance();

		// check rights
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}

		$groups = UnitPeer::findBy(
				array(
						"customerId" => $user->getCustomerId()
				),
				array(
						UnitPeer::TITLE => "asc"
				)
		);

		$form = new Backend_Basket_EmailSendForm(
				array(
				),
				array(
						"groups" => $groups
				)
		);

		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));

			if ($form->isValid()) {
				$subject = $form->getValue("subject");
				$receivers = $form->getValue("receivers");
				$groupId = $form->getValue("groups");
				$emails_clear = array();

				if ($receivers) {
					$emails = explode(',', $receivers);

					foreach ($emails as $email) {
						$emails_clear[] = trim($email);

					}
				}
				else {
					if ($groupId) {
						$group = UnitPeer::retrieveByPK($groupId);

						if ($group) {
							$users = $group->getUsers();

							foreach ($users as $user) {
								$emails_clear[] = $user->getEmail();
							}
						}
					}
				}

				if ($emails_clear) {
					if (!$subject) {
						$subject =  $user->getEmail()." ".__("send to you file");
					}

					$description = "<br /><br />";
					$description .= $selection->getDescription() ? __("Description")." :<br /><br />".
						$selection->getDescription()."<br /><br />" : "";
	
					$description .= $form->getValue('message') ? __("Message")." :<br /><br />".
						nl2br($form->getValue('message')) : "";
	
					$search = array("**FIRSTNAME**", "**LASTNAME**", "**EMAIL**", "**CART_NAME**", "**URL**", 
							"**DESCRIPTION**");
	
					$replace = array(
							ucfirst(strtolower($user->getFirstname())), 
							ucfirst(strtolower($user->getLastname())), 
							$user->getEmail(), $selection, 
							url("permalink_cart_show", array("link" => $selection->getCode())), 
							$description
					);
	
					$email = new myMailer("send_cart", "[wikiPixel] ".$subject);
	
					$email->setTo($emails_clear);
					$email->setFrom(array("no-reply@wikipixel.com"));
					$email->compose($search, $replace);
					$email->send();
	
					LogPeer::setLog($user->getId(), $selection->getId(), "cart-send", "13");
				}

				$this->getUser()->setFlash("success", __("Email sent successfully."));
				$this->redirect("@selection_email_send?selection=".$selection->getId());
			}
		}

		$this->form = $form;
		$this->sender = $this->getUser()->getEmail();
		$this->selection = $selection;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCommentList(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByPk($request->getParameter("selection"));
		$this->forward404Unless($selection);
		
		// check rights
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}

		$this->comments = BasketHasCommentPeer::retrieveByBasketId($selection->getId());

		$this->selection = $selection;

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeCommentDelete(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$comment = BasketHasCommentPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($comment);
		
		$selection = $comment->getBasket();

		// check rights
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}
		
		$comment->delete();

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCommentUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$comment = BasketHasCommentPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($comment);

		$selection = $comment->getBasket();
		
		// check rights
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}
		
		$response = $this->getResponse();
		$response->setContentType("application/json");
		
		$formating = new Formating();
		
		$comment->setComment($formating->force_balance_tags($request->getParameter("content")));
		$comment->save();
		
		return $this->renderText("");
	}

	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/

	/*________________________________________________________________________________________________________________*/
	/**
	 * Test si on a le droit d'accéder à la selection.
	 * 
	 * @param Basket $selection
	 */
	private function publicCheckAuth(Basket $selection)
	{
		/*
		if ($selection->getState() == BasketPeer::__STATE_PUBLIC) {
			return true;
		}
		
		if ($selection->getState() == BasketPeer::__STATE_PRIVATE
			&& $this->getUser()->getAttribute("basket-authenticated") == $selection->getId()) {
			return true;
		}*/

		// pas partagé
		if (!$selection->getIsShared()) {
			return false;
		}
		
		// si partagé sans password
		if (!$selection->getPassword()) {
			return true;
		}
		
		// si partagé avec password et authentifié
		if ($selection->getPassword() 
				&& $this->getUser()->getAttribute("basket-authenticated") == $selection->getId()) {
			return true;
		}
		
		return false;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePublicShow(sfWebRequest $request)
	{
		$code = $request->getParameter("code", $request->getParameter("link"));
		
		$selection = BasketPeer::retrieveByCode($code);
		$this->forward404Unless($selection);
	
		if (!$selection->getIsShared()) {
			$this->forward404();
		}
		
		if (!$this->publicCheckAuth($selection)) {
			$this->forward("selection", "publicAuthentication");
		}
		
		$this->getResponse()->setTitle(__("Selection \"%name%\"", array("%name%" => $selection->getCode())));
		$this->getResponse()->setSlot("homepage_link", $request->getUri());
		
		$page = $request->getParameter("page", 1);
		$contents = BasketHasContentPeer::getContents($selection->getId());
	
		$begin = ($page - 1) * 20;
		$end = $begin + 20;
	
		$this->allContents = count($contents);
		$this->contents = array_slice($contents, $begin, $end);
		$this->maxPage = ceil(count($contents) / 20);
	
		if ($request->getParameter("page") >= $this->maxPage) {
			$this->forward404();
		}
		
		$connection = Propel::getConnection();
	
		$query = "	SELECT sum(file.size) as size
					FROM file, basket_has_content
					WHERE basket_has_content.file_id = file.id
					AND file.state = ".FilePeer::__STATE_VALIDATE."
					AND (file.usage_distribution_id != ".UsageDistributionPeer::__UNAUTH." OR file.usage_distribution_id IS NULL)
					AND basket_has_content.basket_id = ".$selection->getId();
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		$this->filesSize = $result[0]["size"];
	
		PermalinkLogPeer::addLog($_SERVER["REMOTE_ADDR"], $selection->getId(), PermalinkLogPeer::__BASKET);
	
		$this->basket = $selection;
		$this->page = $page;
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePublicAuthentication(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByCode($request->getParameter("code"));
		$this->forward404Unless($selection);
	
		$this->getResponse()->setTitle(__("Selection \"%name%\"", array("%name%" => $selection->getCode())));
	
		$form = new Backend_Basket_PublicAuthentificationForm(
				array(
						"id" => $selection->getId()
				)
		);
	
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
			$this->getResponse()->setSlot("form", $form);
	
			if ($form->isValid()) {
				$this->getUser()->setAttribute("basket-authenticated", $selection->getId());
				$this->redirect("@public_selection_show?code=".$selection->getCode());
			}
		}
	
		$this->form = $form;
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Téléchargement de tous les fichiers.
	 *
	 * @param sfWebRequest $request
	 */
	public function executePublicDownload(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByCode($request->getParameter("code"));
		$this->forward404Unless($selection);
		
		// check auth for selection
		if (!$this->publicCheckAuth($selection)) {
			$this->forward404();
		}
		
		if ($selection->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}

		$this->processDownload($selection);
		
		return sfView::NONE;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Téléchargement d'un fichier.
	 * 
	 * @param sfWebRequest $request
	 */
	public function executePublicFileDownload(sfWebRequest $request)
	{
		$selection = BasketPeer::retrieveByCode($request->getParameter("code"));
		$this->forward404Unless($selection);

		$file = FilePeer::retrieveByPK($request->getParameter("file"));
		$this->forward404Unless($file);
	
		// check auth for selection
		if (!$this->publicCheckAuth($selection)) {
			$this->forward404();
		}
		
		// check auth for file
		$inSelection = BasketHasContentPeer::searchIntoBasket($selection->getId(), $file->getId());
		
		if (!$inSelection) {
			$this->forward404();
		}
		
		/*if ($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH) {
			$this->forward404();
		}*/
		
		$download = new Httpdownload();
			
		$download->setInline(false);
		$download->setFilePath($file->getPathname());
		$download->setFilename($file->getName());
			
		$download->executeDownload();
	
		LogPeer::setLog(null, $file->getId(), "file-download", "3", array(), $file->getCustomerId());
		
		die();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePublicSlideshow(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$selection = BasketPeer::retrieveByCode($request->getParameter("code"));
		$this->forward404Unless($selection);
	
		// check auth for selection
		if (!$this->publicCheckAuth($selection)) {
			$this->forward404();
		}
		
		$c = new Criteria();
	
		$c->addJoin(BasketHasContentPeer::FILE_ID, FilePeer::ID);
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
		$c->add(BasketHasContentPeer::BASKET_ID, $selection->getId());
		$c->addDescendingOrderByColumn(FilePeer::NAME);
		$c->addDescendingOrderByColumn(FilePeer::ORIGINAL);
	
		$this->files = FilePeer::doSelect($c);
	
		$this->start = $request->getParameter("start", 0);
		$this->countFile = Array();
		$this->height = $request->getParameter("height");
		$this->width = $request->getParameter("width");
	
		$this->basket = $selection;
	
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePublicCommentList(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$selection = BasketPeer::retrieveByCode($request->getParameter("code"));
		$this->forward404Unless($selection);
	
		// check auth for selection
		if (!$this->publicCheckAuth($selection)) {
			$this->forward404();
		}

		return $this->renderComponent("selection", "publicComment", array("basket" => $selection));
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePublicCommentAdd(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$selection = BasketPeer::retrieveByCode($request->getParameter("code"));
		$this->forward404Unless($selection);
	
		// check auth for selection
		if (!$this->publicCheckAuth($selection)) {
			$this->forward404();
		}

		$this->getResponse()->setContentType("application/json");
	
		if (!filter_var($request->getParameter("email"), FILTER_VALIDATE_EMAIL)) {
			return $this->renderText(json_encode(array("code" => 1)));
		}
		else {
			$comment = new BasketHasComment();
	
			$comment->setBasketId($selection->getId());
			$comment->setEmail($request->getParameter("email"));
			$comment->setComment($request->getParameter("comment"));
	
			$comment->save();
	
			// send mail
			$user = UserPeer::retrieveByPK($selection->getUserId());
				
			$search = array("**BASKET_NAME**", "**COMMENT_EMAIL**", "**COMMENT**");
			$replace = array($selection->getTitle(), $request->getParameter("email"), nl2br($request->getParameter("comment")));
	
			$email = new myMailer("basket_comment", "[wikiPixel] ".
					__("New comment about %1% basket", array("%1%" => $selection->getTitle())));
				
			$email->setTo(array($user->getEmail()));
			$email->setFrom(array("no-reply@wikipixel.com" => "no-reply@wikipixel.com"));
			$email->compose($search, $replace);
				
			$email->send();
	
			$html = $this->getComponent("selection", "publicComment", array("basket" => $selection));
	
			return $this->renderText(json_encode(array("code" => 0, "html" => $html)));
		}
	}
}
