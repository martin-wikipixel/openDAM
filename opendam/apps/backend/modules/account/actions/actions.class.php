<?php

/**
 * account actions.
 *
 * @package    wikipixel
 * @subpackage account
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class accountActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeIndex(sfWebRequest $request)
	{
		$user = $this->getUser()->getInstance();
		
		$countries = CountryPeer::findAll();
				
		$form = new Backend_Account_EditForm(
				array(
						"firstname" => $user->getFirstname(),
						"lastname" => $user->getLastname(),
						"email" => $user->getEmail(),
						"language" => $user->getCulture(),
						"phone" => $user->getPhone(),
						"country" => $user->getCountryId(),
				),
				array("user" => $user, "countries" => $countries)
		);
		
		$selectedCountryId = $user->getCountryId();
		
		if ($request->getMethod() == sfRequest::POST ) {
			$form->bind($request->getParameter("data"));
		
			$data = $request->getParameter("data");
			$selectedCountryId = $data["country"];
			
			if ($form->isValid()) {
				$user->setFirstname($form->getValue("firstname"));
				$user->setLastname($form->getValue("lastname"));
				
				// protection pour être sur qu'un non admin puisse pas modifier le mail
				if ($this->getUser()->isAdmin()) {
					$user->setEmail($form->getValue("email"));
				}
				
				$user->setCulture($form->getValue("language"));

				$user->setPhone($form->getValue("phone"));
				$user->setCountryId($form->getValue("country"));
				$user->save();

				// update session
				$this->getUser()->setAccountAttributes($user);
				
				LogPeer::setLog($this->getUser()->getId(), 0, "profile-update", "4");
				$this->getUser()->setFlash("success", __("Personal information has been updated."));
				
				$this->redirect("@homepage");
			}
		}
		
		$this->form = $form;
		$this->user = $user;
		$this->countries = $countries;
		$this->selectedCountryId = $selectedCountryId;
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePassword(sfWebRequest $request)
	{
		$user = $this->getUser()->getInstance();
		
		if (!$this->getUser()->haveAccessModule(ModulePeer::__MOD_REINIT_PASSWORD)) {
			$this->forward404();
		}

		$form = new Internal_Account_PasswordForm();
		
		$encoder = Factory::getPasswordEncoder();
		
		if ($request->getMethod() == sfRequest::POST ) {
			$form->bind($request->getParameter("data"));
		
			if ($form->isValid()) {
				$encoded = $encoder->encodePassword($form->getValue("new_password"), "");
				
				$user->setPassword($encoded);
				$user->save();
		
				$this->getUser()->setFlash("success", __("Password has been changed."));
				$this->redirect("@homepage");
			}
		}

		$this->form = $form;
		$this->user = $user;

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePermalinkList(sfWebRequest $request)
	{
		$this->redirect("@account_permalink_album_list");
	}

	/*________________________________________________________________________________________________________________*/
	public function executePermalinkAlbumList(sfWebRequest $request)
	{
		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 15;
		$orderBy = array(PermalinkPeer::CREATED_AT => "desc");
		
		$this->permalinks = PermalinkPeer::getByUserPager($page, $itemPerPage, array(
			"userId" 		=> $this->getUser()->getId(),
			"objectType" 	=> PermalinkPeer::__OBJECT_GROUP
		), $orderBy);
	
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executePermalinkFolderList(sfWebRequest $request)
	{	
		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 15;
		$orderBy = array(PermalinkPeer::CREATED_AT => "desc");
		
		$this->permalinks = PermalinkPeer::getByUserPager($page, $itemPerPage, array(
			"userId" 		=> $this->getUser()->getId(),
			"objectType" 	=> PermalinkPeer::__OBJECT_FOLDER
		), $orderBy);
		
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executePermalinkFileList(sfWebRequest $request)
	{
		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 15;
		$orderBy = array(PermalinkPeer::CREATED_AT => "desc");
		
		$this->permalinks = PermalinkPeer::getByUserPager($page, $itemPerPage, array(
			"userId" 		=> $this->getUser()->getId(),
			"objectType" 	=> PermalinkPeer::__OBJECT_FILE
		), $orderBy);
		
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executePermalinkDelete(sfWebRequest $request)
	{
		$permalink = PermalinkPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($permalink);
	
		// csrf token
		SecurityUtils::checkCsrfToken();

		// check rights
		if ($permalink->getUserId() != $this->getUser()->getId()) {
			$this->forward404();
		}
		
		$permalink->delete();
		LogPeer::setLog($this->getUser()->getId(), $this->getRequestParameter('id'), "permalink-delete", "10");
	
		$this->getUser()->setFlash("success", __("The permalink has been deleted."));
		
		return $this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeModuleList(sfWebRequest $request)
	{
		$user = $this->getUser()->getInstance();
		
		// les modules spécifiques de l'utilisateur (sans affiché les modules hérités)
		$this->userModules = UserHasModulePeer::findBy(array(
				"userId" => $user->getId())
		);
		
		// les modules que l'utilisateur peut ajouter
		$this->newModules = UserHasModulePeer::getModulesCanAdd($user);
		
		$this->user = $user;
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeModuleUpdateState(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$userModule = UserHasModulePeer::retrieveByUserAndModule($this->getUser()->getId(),
				$request->getParameter("_module"));
	
		$this->forward404Unless($userModule);
	
		$userModule->setActive(($request->getParameter("state") ? true : false));
		$userModule->save();
	
		return sfView::NONE;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeModuleDelete(sfWebRequest $request)
	{
		$userModule = UserHasModulePeer::retrieveByUserAndModule($this->getUser()->getId(),
				$request->getParameter("_module"));
	
		$this->forward404Unless($userModule);
	
		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$userModule->delete();
	
		$this->getUser()->setFlash("success", __("User value has successfully deleted."));
	
		$this->redirect("@account_module_list");
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeModuleAdd(sfWebRequest $request)
	{
		$user = $this->getUser()->getInstance();
		$module = ModulePeer::retrieveByPK($request->getParameter("_module"));
		$this->forward404Unless($module);
	
		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$userModule = new UserHasModule();
		$userModule->setUserId($user->getId());
		$userModule->setModuleId($module->getId());
		$userModule->setModuleValueId(null);
		$userModule->setActive(true);
		$userModule->save();
	
		$this->getUser()->setFlash("success", __("The module has been added to user."));
	
		$this->redirect("@account_module_list");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLog(sfWebRequest $request)
	{
		$type = $request->getParameter("type", 0);
		$keyword = $request->getParameter("keyword");
		$page = (int)$request->getParameter("page", 1);
		$sort = $request->getParameter("sort");

		$customerId = $this->getUser()->getCustomerId();
		$userId = $this->getUser()->getId();

		$itemPerPage = 30;

		$logs = LogPeer::getLogPager(
				array(
						"type"			=> $type,
						"keyword"		=> $keyword,
						"customerId"	=> $customerId,
						"userId"		=> $userId
				),
				$sort,
				$page,
				$itemPerPage
		);

		$this->keyword = $keyword;
		$this->logs = $logs;
		$this->currentType = $type;
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeStatistic(sfWebRequest $request)
	{
		$user = $this->getUser()->getInstance();
		$stats = UserPeer::getStats($user->getId());
		
		$usedSpace = $stats["size"];
		$available =  $this->getUser()->getDiskSpace("b");
		$progressWidth = 0;

		if ($available) {
			$progressWidth = round($usedSpace / $available, 2);

			if ($progressWidth > 1) {
				$progressWidth = 1;
			}
		}
		
		$this->stats = $stats;
		$this->usedSpace = $usedSpace;
		$this->available = $available;
		$this->progressWidth = $progressWidth;
		
		return sfView::SUCCESS;
	}
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________PUBLIC__________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public function executeLogout(sfWebRequest $request)
	{
		$this->getUser()->signOut();
	
		if ($request->isXmlHttpRequest()) {
			return $this->renderText("");
		}

		$this->redirect("@homepage");
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeForgotPassword(sfWebRequest $request)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	
		if ($this->getUser()->isAuthenticated()) {
			$this->redirect('@homepage');
		}
	
		$form = new Backend_Account_ForgotPasswordForm(
				array(
					"referer" => $request->getParameter("referer", "@homepage")
				)
		);
	
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
			$this->getResponse()->setSlot("form", $form);
	
			if ($form->isValid()) {
				$user = UserPeer::retrieveByEmail($form->getValue("email"));
	
				$request = ResetPasswordRequest::newInstance($user);
	
				$url = url_for("@account_reset_password?token=".$request->getToken(), true);
	
				$search = array("**URL**");
				$replace = array($url);
	
				$email = new myMailer("forgot-password-step1", "[wikiPixel] ".__("Request password reset"));
	
				$email->setTo(array($user->getEmail() => $user->getEmail()));
				$email->setFrom(array("no-reply@wikipixel.com"));
				$email->compose($search, $replace);
				$email->send();
	
				$this->getUser()->setFlash("success", __("An email with a link to reset your password you have been sending."));
	
				$this->redirect($form->getValue("referer"));
			}
		}
	
		$this->form = $form;
	
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeResetPassword(sfWebRequest $request)
	{
		$encoder = Factory::getPasswordEncoder();
	
		$resetRequest = ResetPasswordRequestPeer::retrieveByPK($request->getParameter("token"));
		$this->forward404Unless($resetRequest);
		
		// check max valid token (max: 30 minutes)
		$duration = time() - $resetRequest->getCreatedAt("U");
		
		if ($duration > 30 * 60) {
			$resetRequest->delete();
			$this->forward404();
		}
		
		$form = new Backend_Account_ResetPasswordForm(
			array()
		);

		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
			$this->getResponse()->setSlot("form", $form);

			if ($form->isValid()) {
				$user = $resetRequest->getUser();
				$user->setPassword($encoder->encodePassword($form->getValue("password"), ""));
				$user->save();

				$resetRequest->delete();
				
				$this->getUser()->signin($user);
				
				$this->getUser()->setFlash("success", __("Your password was successfully changed."));
				$this->redirect("@homepage");
			}
		}
	
		$this->form = $form;

		return sfView::SUCCESS;
	}
}
