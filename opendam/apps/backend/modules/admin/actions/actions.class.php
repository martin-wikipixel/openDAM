<?php

/**
 * admin actions.
 *
 * @package    wikipixel
 * @subpackage admin
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class adminActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeIndex()
	{
		$this->redirect("@homepage");
	}
	
	/******************************************************************************************************************/
	/**
	 * 							Gestion des users
	 */
	/******************************************************************************************************************/
	/*________________________________________________________________________________________________________________*/
	/**
	 * Connexion au compte d'un utilisateur.
	 */
	public function executeUserLoginInto(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($user);
	
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$this->getUser()->signIn($user);
	
		$this->redirect("@homepage");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserList(sfWebRequest $request)
	{
		$customer = $this->getUser()->getCustomerInstance();

		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$role = (int)$request->getParameter("role", 0);
		$letter = $request->getParameter("letter", "");
		$orderBy = $request->getParameter("orderBy", array("lastname_asc"));
		
		$itemPerPage = 15;
		
		$this->canAddUser = $customer->canAddUser();
		
		$this->users = UserPeer::getPager($page, $itemPerPage,
				array(
						"keyword" 	=> $keyword,
						"role" 		=> $role,
						"letter" 	=> $letter,
						"customerId" => $customer->getId(),
				), $orderBy);
		
		$this->customer = $customer;
		$this->currentLetter = $letter;
		$this->currentRole = $role;
		$this->letters =  UserPeer::getFirstLettersOfName($customer->getId());
		$this->orderBy = $orderBy;
		$this->keyword = $keyword;
		
		$this->csrfToken = $this->getUser()->getCsrfToken();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserDelete(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($user);
	
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$user->delete();
	
		$this->getUser()->setFlash("success", __("User account deleted successfully."));
	
		return $this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserActivate(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($user);
		
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$encoder = Factory::getPasswordEncoder();
	
		// csrf token
		SecurityUtils::checkCsrfToken();
	
		if ($user->getState() != UserPeer::__STATE_SUSPEND) {
			throw new Exception("User is not in state suspend");
		}
	
		$user->setState(UserPeer::__STATE_ACTIVE);
	
		if (!$user->getPassword()) {
			$encoded = $encoder->encodePassword(myTools::makeRandomPassword(), "");
			$user->setPassword($encoded);
		}
	
		$user->save();
	
		$this->getUser()->setFlash("success", __("The user has been successfully activated."));
	
		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserNew(sfWebRequest $request)
	{
		$encoder = Factory::getPasswordEncoder();
		$customer = $this->getUser()->getCustomerInstance();

		if (!$customer->canAddUser()) {
			$this->getUser()->setFlash("error", __("You have reached the maximum number of allowed users.
					Please contact your administrator."));
		}
		
		$countries = CountryPeer::findAll();
		
		$currentCountry = "";

		$form = new Backend_Admin_User_NewForm(
				array(
						"customer" => $customer->getId(),
						"country" => CountryPeer::ID_FRANCE,
						"culture" => 1
				),
				array("countries" => $countries)
		);
		
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
		
			$data = $request->getParameter("data");
			$selectedCountryId = $data["country"];
			
			if ($form->isValid()) {
				$user = new User();
				$user->setState(UserPeer::__STATE_ACTIVE);
		
				$user->setCustomerId($customer->getId());
				$user->setUsername($form->getValue("email"));
		
				$encoded = $encoder->encodePassword($form->getValue("password"), "");
				$user->setPassword($encoded);
		
				$user->setFirstname($form->getValue("firstname"));
				$user->setLastname($form->getValue("lastname"));
				$user->setEmail($form->getValue("email"));
				$user->setPosition($form->getValue("position"));
				$user->setPhone($form->getValue("phone"));
				$user->setRoleId($form->getValue("role_id"));
				$user->setHash(md5($form->getValue("email")));
				$user->setCountryId($form->getValue("country"));
				$user->setCulture($form->getValue("culture"));
				$user->setComment($form->getValue("comment") ? $form->getValue("comment") : null);

				$user->save();
		
				if ($form->getValue("send_username") == true) {
					$customer = $user->getCustomer();
		
					$search = Array("**EMAIL**", "**PASSWORD**", "**URL**");
					$replace = Array($user->getEmail(), $form->getValue("password"), $_SERVER["SERVER_NAME"]);
		
					
					$email = new myMailer("send_user_access_no_trial", "[wikiPixel] ".__("Sign in Wikipixel"));
		
					$email->setTo(Array($user->getEmail() => $user->getEmail()));
					$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail($user->getCustomerId())));
					$email->compose($search, $replace);
					$email->send();
				}
		
				$this->getUser()->setFlash("success", __("The user was added successfully. "));
		
				return $this->redirect("@admin_user_edit?id=".$user->getId());
			}
		}
		
		$this->customer = $customer;
		$this->form = $form;
		$this->countries = $countries;
		$this->currentCountry = $currentCountry;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserEdit(sfWebRequest $request)
	{
		$id = $request->getParameter("id");
		$user = UserPeer::retrieveByPk($id);
		$this->forward404Unless($user);
		
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$countries = CountryPeer::findAll();
		
		$form = new Backend_Admin_User_EditForm(
				array(
						"id" => $id,
						"firstname" => $user->getFirstname(),
						"lastname" => $user->getLastname(),
						"email" => $user->getEmail(),
						"position" => $user->getPosition(),
						"phone" => $user->getPhone(),
						"role_id" => $user->getRoleId(),
						"customer" => $user->getCustomerId(),
						"culture" => $user->getCulture(),
						"country" => $user->getCountryId(),
						"phone_code" => "+".CountryPeer::retrieveByPk($user->getCountryId())->getPhoneCode(),
						"comment" => $user->getComment()
				),
				array("user" => $user, "countries" => $countries)
		);
		
		$selectedCountryId = $user->getCountryId();
		
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
		
			$data = $request->getParameter("data");
			$selectedCountryId = $data["country"];

			if ($form->isValid()) {
				// save user info
				$user->setFirstname($form->getValue("firstname"));
				$user->setLastname($form->getValue("lastname"));
				$user->setEmail($form->getValue('email'));
				$user->setPosition($form->getValue('position'));
				$user->setPhone($form->getValue('phone'));
				$user->setRoleId($form->getValue('role_id'));
		
				$user->setUsername($form->getValue('email'));
				$user->setCountryId($form->getValue('country'));
				$user->setCulture($form->getValue('culture'));
				$user->setComment($form->getValue("comment") ? $form->getValue("comment") : null);
		
				$user->save();
		
				$this->getUser()->setFlash("success", __("User informations saved successfully."));
		
				$this->redirect("@admin_user_edit?id=".$user->getId());
			}
		}
		
		$this->form = $form;
		$this->user = $user;
		$this->countries = $countries;
		$this->selectedCountryId = $selectedCountryId;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserPassword(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($user);
	
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$encoder = Factory::getPasswordEncoder();
	
		$form = new Backend_Admin_User_PasswordForm(
				array(
						"id" => $user->getId()
				)
		);
	
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
	
			if ($form->isValid()) {
				$encoded = $encoder->encodePassword($form->getValue("password"), "");
	
				$user->setPassword($encoded);
				$user->save();
					
				$this->getUser()->setFlash("success", __("User informations saved successfully."));
			}
		}
	
		$this->user = $user;
		$this->form = $form;
	
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserGroupList(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPk($request->getParameter("user"));
		$this->forward404Unless($user);

		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$this->newGroups = UnitPeer::findGroupOfUserToAdd(array(
				"userId" 		=> $user->getId(),
				"customerId" 	=> $user->getCustomerId()
		));
	
		$this->groups = UserUnitPeer::retrieveByUser($user->getId());
		$this->user = $user;
		$this->csrfToken = $this->getUser()->getCsrfToken();
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserGroupDelete(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPk($request->getParameter("user"));
		$this->forward404Unless($user);
		
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$userGroup = UserUnitPeer::retrieveByUserAndUnit($request->getParameter("user"),
				$request->getParameter("group"));
	
		$this->forward404Unless($userGroup);
	
		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$userGroup->delete();
	
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserGroupAdd(sfWebRequest $request)
	{
		$userId = (int) $request->getParameter("user");
		$groupId = (int) $request->getParameter("group");
	
		$user = UserPeer::retrieveByPk($request->getParameter("user"));
		$this->forward404Unless($user);
		
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$userUnit = UserUnitPeer::retrieveByUserAndUnit($userId, $groupId);
	
		if (!$userUnit) {
			$userUnit = new UserUnit();
	
			$userUnit->setUserId($userId);
			$userUnit->setUnitId($groupId);
	
			$userUnit->save();
		}
	
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserModuleList(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPK($request->getParameter("user"));
		$this->forward404Unless($user);
	
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

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
	public function executeUserModuleUpdateState(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$user = UserPeer::retrieveByPk($request->getParameter("user"));
		$this->forward404Unless($user);
		
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$userModule = UserHasModulePeer::retrieveByUserAndModule($request->getParameter("user"),
				$request->getParameter("_module"));
	
		$this->forward404Unless($userModule);
	
		$userModule->setActive(($request->getParameter("state") ? true : false));
		$userModule->save();
	
		return sfView::NONE;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserModuleDelete(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPk($request->getParameter("user"));
		$this->forward404Unless($user);
		
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$userModule = UserHasModulePeer::retrieveByUserAndModule($request->getParameter("user"),
				$request->getParameter("_module"));
	
		$this->forward404Unless($userModule);
	
		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$userModule->delete();
	
		$this->getUser()->setFlash("success", __("User value has successfully deleted."));
	
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserModuleAdd(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPK($request->getParameter("user"));
		$module = ModulePeer::retrieveByPK($request->getParameter("_module"));
	
		$this->forward404Unless($user);
		$this->forward404Unless($module);
	
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$userModule = new UserHasModule();
		$userModule->setUserId($user->getId());
		$userModule->setModuleId($module->getId());
		$userModule->setModuleValueId(null);
		$userModule->setActive(true);
		$userModule->save();
	
		$this->getUser()->setFlash("success", __("Module has successfully added to user."));
	
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUserAlbumRightList(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPK($request->getParameter("user"));
		$this->forward404Unless($user);

		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 10;
		$orderBy = $request->getParameter("orderBy", array(GroupePeer::NAME => "asc"));
		$inherit = (int)$request->getParameter("inherit", 0);

		$this->roles = RolePeer::getRoles();

		$this->albumsCanAdd = GroupePeer::getAlbumsCanAddToUser(array(
			"customerId" => $this->getUser()->getCustomerId(),
			"userId"	=> $user->getId()
		));
		
		$this->albums = GroupePeer::getAlbumsHaveAccessForUserPager($page, $itemPerPage,
				array(
						"customerId" 	=> $user->getCustomerId(),
						"userId" 		=> $user->getId(),
						"inherit"		=> $inherit
				), $orderBy);

		$this->user = $user;
		$this->keyword = $keyword;
		$this->currentInherit = $inherit;
		
		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserAlbumRightDelete(sfWebRequest $request)
	{
		$userAlbum = UserGroupPeer::retrieveByUserAndGroup($request->getParameter("user"), 
				$request->getParameter("album"));
		
		$this->forward404Unless($userAlbum);
		
		$user = $userAlbum->getUser();
		$album = $userAlbum->getAlbum();
	
		//check csrf token
		SecurityUtils::checkCsrfToken();

		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// delete
		$userAlbum = UserGroupPeer::retrieveByUserAndGroup($user->getId(), $album->getId());

		if ($userAlbum) {
			$userAlbum->delete();
		}

		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserAlbumRightUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$role = $request->getParameter("role");
		
		$userAlbum = UserGroupPeer::retrieveByUserAndGroup($request->getParameter("user"), 
				$request->getParameter("album"));
		
		$this->forward404Unless($userAlbum);
		
		$user = $userAlbum->getUser();
		$album = $userAlbum->getAlbum();
		
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		// update
		$userAlbum->setRole($role);
		$userAlbum->save();

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserAlbumRightAdd(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPK($request->getParameter("user"));
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$role = RolePeer::retrieveByPK($request->getParameter("role"));

		$this->forward404Unless($user);
		$this->forward404Unless($album);
		$this->forward404Unless($role);

		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		SecurityUtils::checkCsrfToken();
		
		// add
		$userAlbum = UserGroupPeer::retrieveByUserAndGroup($user->getId(), $album->getId());

		if (!$userAlbum) {
			$userAlbum = new UserGroup();
	
			$userAlbum->setUser($user);
			$userAlbum->setGroupe($album);
			$userAlbum->setRole($role->getId());
	
			$userAlbum->save();
			$this->getUser()->setFlash("success", __("The right has been successfully added."));
		}

		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUserFolderRightList(sfWebRequest $request)
	{
		$user = UserPeer::retrieveByPK($request->getParameter("user"));
		$this->forward404Unless($user);
	
		// check rights
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		$this->user = $user;

		return sfView::SUCCESS;
	}

	/******************************************************************************************************************/
	/**
	 * 								Fin gestion des users
	 */
	/******************************************************************************************************************/

	/******************************************************************************************************************/
	/**
	 * 								Fichiers
	 */
	/******************************************************************************************************************/

	/*________________________________________________________________________________________________________________*/
	public function executeFileDuplicateList(sfWebRequest $request)
	{
		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array("name_asc"));
		$itemPerPage = 15;
		
		//TODO a refaire
		$this->files = FilePeer::getDuplicatePager($page, $itemPerPage,
				array(
						"customerId" => $this->getUser()->getCustomerId(),
						"keyword" 	=> $keyword,
				), $orderBy);

		$this->orderBy = $orderBy;
		$this->keyword = $keyword;
		$this->orderBy = $orderBy;
		
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeFileDownload(sfWebRequest $request)
	{
		$file = FilePeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($file);
		
		if ($file->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$response = $this->getResponse();
		$response->clearHttpHeaders();
		
		$download = new HttpDownload();
		
		$download->setInline(true);
		$download->setFilename($file->getName());
		$download->setFilePath($file->getPathname());
		
		$download->executeDownload();
		
		die();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeFileDelete(sfWebRequest $request)
	{
		$file = FilePeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($file);
	
		if ($file->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$file->setState(FilePeer::__STATE_DELETE);
		$file->save();
	
		@unlink($file->getPath().'/'.$file->getThumb100());
		@unlink($file->getPath().'/'.$file->getThumb200());
		@unlink($file->getPath().'/'.$file->getWeb());
		@unlink($file->getPath().'/'.$file->getOriginal());
		@unlink($file->getPath().'/'.$file->getFileName().".poster.jpeg");
		@unlink($file->getPath().'/'.$file->getFileName().".flv");
	
		$this->getUser()->setFlash("success", "The file removed successfully");
	
		return $this->redirect($request->getReferer());
	}
	
	/******************************************************************************************************************/
	/**
	 * 								Fin gestion des fichiers
	 */
	/******************************************************************************************************************/
	/******************************************************************************************************************/
	/**
	 * 								Modules
	 */
	/******************************************************************************************************************/
	
	/*________________________________________________________________________________________________________________*/
	public function executeModuleList(sfWebRequest $request)
	{
		$customer = $this->getUser()->getCustomerInstance();

		$this->customerModules = CustomerHasModulePeer::findBy(
				array(
						"customerId" => $customer->getId(), 
						"visibilityId" => ModuleVisibilityPeer::__ADMIN
				),
				array(ModuleI18nPeer::TITLE => "asc")
			);

		$this->customer = $customer;
		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeModuleAdd(sfWebRequest $request)
	{
		$customer = $this->getUser()->getCustomerInstance();
		$module = ModulePeer::retrieveByPk($request->getParameter("mod"));
	
		$this->forward404Unless($module);
	
		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$customerModule = new CustomerHasModule();
	
		$customerModule->setCustomer($customer);
		$customerModule->setModule($module);
		$customerModule->setCreatedAt(time());
	
		$customerModule->save();
	
		$this->getUser()->setFlash("success", __("Module has successfully added to customer."));
	
		$this->redirect("@admin_module_list");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeModuleValueUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$customerId = $this->getUser()->getCustomerId();
		
		$customerModule = CustomerHasModulePeer::retrieveByModuleAndCustomer($request->getParameter("moduleId"), 
				$customerId);
		
		$this->forward404Unless($customerModule);
	
		$customerModule->setModuleValueId($request->getParameter("value"));
		$customerModule->save();
	
		return sfView::NONE;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeModuleValueActivate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$customerId = $this->getUser()->getCustomerId();
		
		$customerModule = CustomerHasModulePeer::retrieveByModuleAndCustomer($request->getParameter("moduleId"),
				$customerId);
	
		$this->forward404Unless($customerModule);
	
		$active = (bool) $request->getParameter("active");
		
		$customerModule->setActive($active);
		$customerModule->save();

		return sfView::NONE;
	}
	/******************************************************************************************************************/
	/**
	 * 								Fin modules
	 */
	/******************************************************************************************************************/
	/******************************************************************************************************************/
	/**
	 * 								Groupes et permissions
	 */
	/******************************************************************************************************************/
	/*________________________________________________________________________________________________________________*/
	public function executeGroupList(sfWebRequest $request)
	{
		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array("title_asc"));
		
		$itemPerPage = 15;

		$this->groups = UnitPeer::getPager($page, $itemPerPage,
				array(
						"customerId"	=> $this->getUser()->getCustomerId(),
						"keyword" 	=> $keyword,
				), $orderBy);
		
		$this->letters =  UnitPeer::getLetters();
		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeGroupNew(sfWebRequest $request)
	{
		$form = new Backend_Admin_Group_NewForm(
				array(
				),
				array("customerId"	=> $this->getUser()->getCustomerId())
		);
	
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
	
			if ($form->isValid()) {
				$group = new Unit();
				
				$group->setCustomerId($this->getUser()->getCustomerId());
				$group->setName($form->getValue("name"));
				$group->setDescription($form->getValue("description"));
				$group->save();
	
				$this->getUser()->setFlash("success", __("User's group added successfully."));
				$this->redirect("@admin_group_list");
			}
		}
	
		$this->form = $form;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGroupDelete(sfWebRequest $request)
	{
		$group = UnitPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($group);
		
		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// csrf token
		SecurityUtils::checkCsrfToken();
		
		try {
			$group->delete();
			LogPeer::setLog($this->getUser()->getId(), $this->getUser()->getId(), "unit-delete", "12");
		}
		catch (Exception $e){}
		
		$this->getUser()->setFlash("success", __("User group deleted successfully."));
		
		return $this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGroupEdit(sfWebRequest $request)
	{
		$group = UnitPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($group);

		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$form = new Backend_Admin_Group_EditForm(
				array(
						"name" => $group->getName(),
						"description" => $group->getDescription()
				),
				array("originalName" => $group->getName(), "customerId"	=> $this->getUser()->getCustomerId())
		);

		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));

			if ($form->isValid()) {
				$group->setName($form->getValue("name"));
				$group->setDescription($form->getValue("description"));
				$group->save();
				
				$this->getUser()->setFlash("success", __("User's group updated successfully."));
				$this->redirect("@admin_group_edit?id=".$group->getId());
			}
		}

		$this->form = $form;
		$this->group = $group;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGroupUserList(sfWebRequest $request)
	{
		$group = UnitPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($group);
		
		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$this->users = $group->getUsers();
		$this->group = $group;
		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGroupUserAdd(sfWebRequest $request)
	{
		$group = UnitPeer::retrieveByPK($request->getParameter("group"));
		$user = UserPeer::retrieveByPK($request->getParameter("user"));

		$this->forward404Unless($group);
		$this->forward404Unless($user);
		
		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// on peut ajouter uniquement des utilisteurs de son customer
		if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$userUnit = new UserUnit();
		
		$userUnit->setUserId($user->getId());
		$userUnit->setUnitId($group->getId());
		
		$userUnit->save();

		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGroupUserAutocomplete(sfWebRequest $request)
	{
		$limit = (int) $request->getParameter("limit", 8);
		$keyword = $request->getParameter("keyword");
		$groupId = $request->getParameter("group");
		
		$response = $this->getResponse();
		$response->setContentType("application/json");
		
		$users = UserUnitPeer::findUsersToAdd(
				array("keyword" => $keyword, "customerId" => $this->getUser()->getCustomerId(), "groupId" => $groupId),
				array("lastname" => "asc")
		);

		$rows = array();

		foreach ($users as $user) {
			$row = array(
					"id" 		=> $user->getId(),
					"lastname"	=> $user->getLastname(),
					"firstname" => $user->getFirstname(),
					"email" 	=> $user->getEmail(),
			);
				
			$rows[] = $row;
		}
		
		echo json_encode($rows);
		
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGroupUserDelete(sfWebRequest $request)
	{
		$userGroup = UserUnitPeer::retrieveByUserAndUnit($request->getParameter("user"), 
				$request->getParameter("group"));

		$this->forward404Unless($userGroup);
		
		$group = $userGroup->getUnit();
		
		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// pas de test sur un user car on peut inviter des users externes

		// csrf token
		SecurityUtils::checkCsrfToken();

		$userGroup->delete();

		$this->getUser()->setFlash("success", __("The user has been successfully deleted."));
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeGroupPermissionList(sfWebRequest $request)
	{
		$group = UnitPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($group);
		
		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$rights = UnitGroupPeer::retrieveByUnitId($group->getId());

		$this->roles = RolePeer::getRoles();
		
		// bug, ne doit pas lister les albums supprimé
		$this->albums = UnitGroupPeer::getFreeGroups($group->getId());//TODO a refaire
		
		$this->rights = $rights;
		$this->group = $group;
		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeGroupPermissionAdd(sfWebRequest $request)
	{
		$group = UnitPeer::retrieveByPK($request->getParameter("group"));
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$role = RolePeer::retrieveByPK($request->getParameter("role"));

		$this->forward404Unless($group);
		$this->forward404Unless($album);
		$this->forward404Unless($role);

		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		SecurityUtils::checkCsrfToken();

		// add
		$albumGroup = UnitGroupPeer::retrieveByUnitIdAndGroupeId($group->getId(), $album->getId());

		if (!$albumGroup) {
			$albumGroup = new UnitGroup();
	
			$albumGroup->setUnit($group);
			$albumGroup->setGroupe($album);
			$albumGroup->setRole($role->getId());
	
			$albumGroup->save();
	
			$this->getUser()->setFlash("success", __("The right has been successfully added."));
		}
		
		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGroupPermissionDelete(sfWebRequest $request)
	{
		$albumGroup = UnitGroupPeer::retrieveByUnitIdAndGroupeId($request->getParameter("group"), 
				$request->getParameter("album"));

		$this->forward404Unless($albumGroup);

		$group = $albumGroup->getUnit();
		$album = $albumGroup->getGroupe();

		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		// csrf token
		SecurityUtils::checkCsrfToken();

		$albumGroup->delete();

		$this->getUser()->setFlash("success", __("The right has been successfully deleted."));
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeGroupPermissionUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$albumGroup = UnitGroupPeer::retrieveByUnitIdAndGroupeId($request->getParameter("group"), 
				$request->getParameter("album"));

		$this->forward404Unless($albumGroup);
		
		$role = $request->getParameter("role");//int
		
		$group = $albumGroup->getUnit();
		$album = $albumGroup->getGroupe();

		// check rights
		if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$this->getResponse()->setContentType("application/json");

		$albumGroup->setRole($role);
		$albumGroup->save();

		return $this->renderText(json_encode(array("code" => 0)));
	}

	/******************************************************************************************************************/
	/**
	 * 								Fin groupes et permissions
	 */
	/******************************************************************************************************************/
	/******************************************************************************************************************/
	/**
	 * 								Preset
	 */
	/******************************************************************************************************************/
	/*________________________________________________________________________________________________________________*/
	public function executePresetList(sfWebRequest $request)
	{
		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array("created_at_desc"));
		$itemPerPage = 15;
		
		$this->presets = PresetPeer::getPager($page, $itemPerPage,
				array(
						"customerId" => $this->getUser()->getCustomerId(),
						"keyword" 	=> $keyword,
				), $orderBy);
		
		$this->orderBy = $orderBy;
		$this->keyword = $keyword;
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	private function normalizeValue(UsageLimitation $limitation, array $values, array $selectedCountriesId, array $selectedSupportsId)
	{
		// normalize value
		switch ($limitation->getUsageTypeId()) {
			case UsageTypePeer::__TYPE_GEO: // champ séparer par ";" ex: 1;2;3
				$value = "";
		
				foreach ($selectedCountriesId as $countryId) {
					$value .= $countryId.";";
				}
					
				break;
		
			case UsageTypePeer::__TYPE_SUPPORT:
				$value = "";
					
				foreach ($selectedSupportsId as $supportId) {
					$value .= $supportId.";";
				}
		
				break;
					
			case UsageTypePeer::__TYPE_BOOLEAN:
				$value = "1"; // "1" car champ text en base
				break;
		
			default:
				$value = isset($values[$limitation->getId()]) ?
				$values[$limitation->getId()] : "";
		}
		
		return $value;
	}
	
	/*________________________________________________________________________________________________________________*/
	private function denormalizeValue(FileRight $right, array& $limitationValues, array& $selectedCountriesId, array& $selectedSupportsId)
	{
		$value = $right->getValue();

		switch ($right->getUsageLimitationId()) {
			case UsageLimitationPeer::__GEO_LIMIT: // champ séparer par ";" ex: 1;2;3
				$selectedCountriesId = explode(";", $value);
				$value = "";

				break;
				
			case UsageLimitationPeer::__SUPPORT:
				$selectedSupportsId = explode(";", $value);
				$value = "";
				
				break;
		}
				
		if ($value) {
			$limitationValues[$right->getUsageLimitationId()] = $value;
		}
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Regroupement de new et edit
	 * 
	 * @param sfWebRequest $request
	 * @return string
	 */
	public function executePresetNew(sfWebRequest $request)
	{
		$form = new Backend_Admin_Preset_NewEditForm(
			array(),
			array(
				"customerId" => $this->getUser()->getCustomerId()
			)
		);

		$limitations = UsageLimitationPeer::getLimitations();
		
		$licence = null;
		$creativeCommons = null;
		$usageDistribution = null;
		
		$limitationChecks = array();
		$limitationValues = array();

		$selectedCountriesId = array();
		$selectedSupportsId = array();

		if ($this->getRequest()->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));

			$selectedCountriesId = $request->getParameter("countries", array());
			$selectedSupportsId = $request->getParameter("supports", array());
			
			$limitationChecks = $request->getParameter("limitationChecks", array());
			$limitationValues = $request->getParameter("limitationValues", array());

			$data = $request->getParameter("data");
			$licence = $data["licence"];
			$creativeCommons = CreativeCommonsPeer::retrieveByPK($request->getParameter("creative-commons"));
			$usageDistribution = $data["distribution"];

			// save
			if ($form->isValid()) {
				$name = $form->getValue("name");
				$distribution = $form->getValue("distribution");
				$use = $form->getValue("use");
				
				$preset = new Preset();
				$preset->setCustomerId($this->getUser()->getCustomerId());

				$preset->setName($form->getValue("name"));
				$preset->setUsageDistributionId($distribution ? $distribution : null);
				$preset->setUsageUseId($use ? $use : null);
				$preset->setLicenceId($licence ? $licence : null);

				if ($licence == LicencePeer::__CREATIVE_COMMONS) {
					Assert::ok($creativeCommons !== null);
					$preset->setCreativeCommons($creativeCommons);
				}

				$preset->save();

				if ($distribution == UsageDistributionPeer::__AUTH) {
					// ajout des limitations
					foreach ($limitationChecks as $limitationId) {
						$limitation = UsageLimitationPeer::retrieveByPK($limitationId);
						Assert::ok($limitation !== null);
						
						// normalize value
						$value = $this->normalizeValue($limitation, $limitationValues, $selectedCountriesId, 
								$selectedSupportsId);
						
						if (!empty($value)) {
							$fileRight = new FileRight();
							
							$fileRight->setObjectId($preset->getId());
							$fileRight->setType(FileRightPeer::__TYPE_PRESET);
							$fileRight->setUsageLimitationId($limitationId);
							$fileRight->setValue($value);

							$fileRight->save();
						}
					}
				}
				
				$this->getUser()->setFlash("success", __("The preset has been added."));
				$this->redirect("@admin_preset_list");
			}
		}

		$this->creativeCommonsList = CreativeCommonsPeer::getCreativeCommons();
		$this->limitations = $limitations;
		$this->currentLicence = $licence;
		$this->currentCreativeCommons = $creativeCommons;
		$this->currentUsageDistribution = $usageDistribution;
		
		$this->selectedCountriesId = $selectedCountriesId;
		$this->selectedSupportsId = $selectedSupportsId;
		
		$this->limitationChecks = $limitationChecks;
		$this->limitationValues = $limitationValues;
		
		$this->form = $form;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Regroupement de new et edit
	 *
	 * @param sfWebRequest $request
	 * @return string
	 */
	public function executePresetEdit(sfWebRequest $request)
	{
		$preset = PresetPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($preset);
		
		// check rights
		if ($preset->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$form = new Backend_Admin_Preset_NewEditForm(
				array(
						"name"			=> $preset->getName(),
						"licence"		=> $preset->getLicenceId(),
						"use"			=> $preset->getUsageUseId(),
						"distribution"	=> $preset->getUsageDistributionId()
				),
				array(
						"customerId" 	=> $this->getUser()->getCustomerId(),
						"id" 			=> $preset->getId(),
				)
		);
		
		$limitations = UsageLimitationPeer::getLimitations();
		$fileRights = FileRightPeer::retrieveByType($preset->getId(), FileRightPeer::__TYPE_PRESET);
		
		if ($request->getMethod() == sfRequest::GET) {
			$licence = $preset->getLicenceId();
			$creativeCommons = $preset->getCreativeCommons();
			$usageDistribution = $preset->getUsageDistributionId();
			
			// initialiation des limitation
			$limitationChecks = array();
			$limitationValues = array();
				
			$selectedCountriesId = array();
			$selectedSupportsId = array();

			foreach ($fileRights as $right) {
				$limitationId = $right->getUsageLimitationId();
				$limitationChecks[] = $limitationId;
				
				$this->denormalizeValue($right, $limitationValues, $selectedCountriesId, $selectedSupportsId);
			}
		}
		elseif ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
		
			$selectedCountriesId = $request->getParameter("countries", array());
			$selectedSupportsId = $request->getParameter("supports", array());
				
			$limitationChecks = $request->getParameter("limitationChecks", array());
			$limitationValues = $request->getParameter("limitationValues", array());
		
			$data = $request->getParameter("data");
			$licence = $data["licence"];
			$creativeCommons = CreativeCommonsPeer::retrieveByPK($request->getParameter("creative-commons"));
			$usageDistribution = $data["distribution"];
		
			// save 
			if ($form->isValid()) {
				$name = $form->getValue("name");
				$distribution = $form->getValue("distribution");
				$use = $form->getValue("use");
		
				$preset->setName($form->getValue("name"));
				$preset->setUsageDistributionId($distribution ? $distribution : null);
				$preset->setUsageUseId($use ? $use : null);
				$preset->setLicenceId($licence ? $licence : null);
		
				if ($licence == LicencePeer::__CREATIVE_COMMONS) {
					Assert::ok($creativeCommons !== null);
					$preset->setCreativeCommons($creativeCommons);
				}
		
				$preset->save();
		
				if ($distribution != UsageDistributionPeer::__AUTH) {// delete all
					FileRightPeer::deleteByType($preset->getId(), FileRightPeer::__TYPE_PRESET);
				}
				else {
					foreach ($limitations as $limitation) {
						$fileRight = FileRightPeer::retrieveByTypeAndLimitation($preset->getId(), FileRightPeer::__TYPE_PRESET,
								$limitation->getId());
						
						if (!in_array($limitation->getId(), $limitationChecks)) {// delete
							if ($fileRight) {
								$fileRight->delete();
							}
						}
						else {// add or update
							if (!$fileRight) {
								$fileRight = new FileRight();
								$fileRight->setObjectId($preset->getId());
								$fileRight->setType(FileRightPeer::__TYPE_PRESET);
								$fileRight->setUsageLimitationId($limitation->getId());
							}
							
							// normalize value
							$value = $this->normalizeValue($limitation, $limitationValues, $selectedCountriesId,
									$selectedSupportsId);
							
							$fileRight->setValue($value);
							
							$fileRight->save();
						}
					}
				}

				$this->getUser()->setFlash("success", __("The preset has been updated."));
				$this->redirect("@admin_preset_list");
			}
		}
		
		$this->creativeCommonsList = CreativeCommonsPeer::getCreativeCommons();
		$this->limitations = $limitations;
		$this->currentLicence = $licence;
		$this->currentCreativeCommons = $creativeCommons;
		$this->currentUsageDistribution = $usageDistribution;
		
		$this->selectedCountriesId = $selectedCountriesId;
		$this->selectedSupportsId = $selectedSupportsId;
		
		$this->limitationChecks = $limitationChecks;
		$this->limitationValues = $limitationValues;
		
		$this->form = $form;
		$this->preset = $preset;

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executePresetDelete(sfWebRequest $request)
	{
		$preset = PresetPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($preset);
		
		// check rights
		if ($preset->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		// csrf token
		SecurityUtils::checkCsrfToken();

		$preset->delete();
		
		$this->getUser()->setFlash("success", __("Preset has been successfully removed."));
		
		return $this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeGetCreativeCommons(sfWebRequest $request)
	{
		$creative_commons = CreativeCommonsPeer::retrieveByPk($request->getParameter("value"));
		$this->getResponse()->setContentType("application/json");
	
		return $this->renderText(json_encode(array("img" => $creative_commons->getImagePath(),
				"description" => $creative_commons->getDescription())));
	}

	/******************************************************************************************************************/
	/**
	 * 								Fin Preset
	 */
	/******************************************************************************************************************/
	/******************************************************************************************************************/
	/**
	 * 								Tag
	 */
	/******************************************************************************************************************/
	/*________________________________________________________________________________________________________________*/
	public function executeTagList(sfWebRequest $request)
	{
		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$letter = $request->getParameter("letter", "");
		$orderBy = $request->getParameter("orderBy", array("title_asc"));
		$itemPerPage = 10;
	
		$customerId = $this->getUser()->getCustomerId();
		
		$this->tags = TagPeer::getPager($page, $itemPerPage,
				array(
						"customerId" => $customerId,
						"keyword" 	 => $keyword,
						"letter" 	 => $letter,
				), $orderBy);
		
		$this->letters = TagPeer::getFirstLettersOfName($customerId);
		$this->currentLetter = $letter;
		$this->orderBy = $orderBy;
		$this->keyword = $keyword;
		$this->csrfToken = $this->getUser()->getCsrfToken();

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeReplaceBy(sfWebRequest $request)
	{
		$newTag = TagPeer::retrieveByPK($request->getParameter("tag"));

		// check rights
		if ($newTag->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		// csrf token
		SecurityUtils::checkCsrfToken();
		
		$tagIds = (array)$request->getParameter("oldTag");// tags to delete
		$tags = TagPeer::retrieveByPKs($tagIds);
		
		foreach ($tags as $oldTag) {
			// check rights
			Assert::ok($oldTag->getCustomerId() == $this->getUser()->getCustomerId());

			if ($oldTag->getId() != $newTag->getId()) {// remplace pas par lui-même
				// cherche tous les fichiers associés à l'ancien tag
				$file_tags = FileTagPeer::retrieveByTagId($oldTag->getId());
					
				// ajout du nouveau tag si n'existe pas
				foreach ($file_tags as $fileTag) {
					$newFileTag = FileTagPeer::getFileTag($fileTag->getType(), $fileTag->getFileId(), 
							$newTag->getId());
					
					if (!$newFileTag) {
						$newFileTag = new FileTag();
						
						$newFileTag->setFileId($fileTag->getFileId());
						$newFileTag->setTagId($newTag->getId());
						$newFileTag->setType($fileTag->getType());
						
						$newFileTag->save();
					}
				}
				
				// suppression de l'ancien tag et des fichiers associés
				$oldTag->delete();
			}
		}
		
		$this->getUser()->setFlash("success", __("Tags has successfully replaced by %1% tag.", 
				array("%1%" => '"'.$newTag->getName().'"')));
		
		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTagDelete(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPk($request->getParameter("id"));
		$this->forward404Unless($tag);
	
		// check rights
		if ($tag->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		// csrf token
		SecurityUtils::checkCsrfToken();
	
		$tag->delete();
		LogPeer::setLog($this->getUser()->getId(), $tag->getId(), "tag-delete", "9");
	
		$this->getUser()->setFlash("success", __("The tag has been deleted."));
		
		return $this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTagAllDelete(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$tag_ids = $request->getParameter("ids", array());

		foreach ($tag_ids as $tag_id) {
			$tag = TagPeer::retrieveByPk($tag_id);
			$this->forward404Unless($tag);

			// check rights
			Assert::ok($tag->getCustomerId() == $this->getUser()->getCustomerId());

			$tag->delete();
			LogPeer::setLog($this->getUser()->getId(), $tag->getId(), "tag-delete", "9");
		}

		$this->getUser()->setFlash("success", __("Tags were successfully deleted."));
		//$this->redirect("@admin_tag_list");
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTagNew(sfWebRequest $request)
	{
		$customerId = $this->getUser()->getCustomerId();
		
		$form = new Backend_Admin_Tag_NewForm(
				array(),
				array("customerId" => $customerId)
		);
		
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));

			if ($form->isValid()) {
				$tag = new Tag();
				
				$tag->setCustomerId($customerId);
				$tag->setName($form->getValue("name"));
				$tag->save();
		
				LogPeer::setLog($this->getUser()->getId(), $tag->getId(), "tag-create", "9");
				$this->getUser()->setFlash("success", __("The tag has been added."));
		
				return $this->redirect("@admin_tag_list");
			}
		}
		
		$this->form = $form;
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeTagEdit(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($tag);
		
		// check rights
		if ($tag->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
		
		$form = new Backend_Admin_Tag_EditForm(
				array("name" => $tag->getName()),
				array("id" => $tag->getId())
		);
		
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));

			if ($form->isValid()) {
				$oldName = $tag->getName();
				$newName = $form->getValue("name");
				
				if ($oldName != $newName) {
					$tag->setName($form->getValue("name"));
					$tag->save();
					
					LogPeer::setLog($this->getUser()->getId(), $tag->getId(), "tag-update", "9");
					$this->getUser()->setFlash("success", __("The tag has been updated."));
				}

				return $this->redirect("@admin_tag_edit?id=".$tag->getId());
			}
		}
		
		$this->tag = $tag;
		$this->form = $form;
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTagAlbumList(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPk($request->getParameter("tag"));
		$this->forward404Unless($tag);

		// check rights
		if ($tag->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array(GroupePeer::NAME => "asc"));
		
		$itemPerPage = 15;
		
		$this->albums = FileTagPeer::getAlbumsOfTagPager($page, $itemPerPage,
				array(
						"tagId" 	=> $tag->getId(),
				), $orderBy);
		
		$this->tag = $tag;
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeTagAlbumDelete(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPk($request->getParameter("tag"));
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
	
		$this->forward404Unless($tag);
		$this->forward404Unless($album);
	
		// check rights
		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		SecurityUtils::checkCsrfToken();
	
		$albumTag = FileTagPeer::retrieveByTagAndAlbum($tag->getId(), $album->getId());
	
		if ($albumTag) {
			$albumTag->delete();
			$this->getUser()->setFlash("success", __("The tag has been deleted."));
		}
	
		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTagFolderList(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPk($request->getParameter("tag"));
		$this->forward404Unless($tag);

		// check rights
		if ($tag->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array(FolderPeer::NAME => "asc"));
		
		$itemPerPage = 15;
		
		$this->folders = FileTagPeer::getFoldersOfTagPager($page, $itemPerPage,
				array(
						"tagId" 	=> $tag->getId(),
				), $orderBy);
		
		$this->tag = $tag;
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTagFolderDelete(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPk($request->getParameter("tag"));
		$folder = FolderPeer::retrieveByPK($request->getParameter("folder"));
	
		$this->forward404Unless($tag);
		$this->forward404Unless($folder);
	
		// check rights
		if ($folder->getUser()->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		SecurityUtils::checkCsrfToken();
	
		$folderTag = FileTagPeer::retrieveByTagAndFolder($tag->getId(), $folder->getId());
	
		if ($folderTag) {
			$folderTag->delete();
			$this->getUser()->setFlash("success", __("The tag has been deleted."));
		}
	
		$this->redirect($request->getReferer());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTagFileList(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPk($request->getParameter("tag"));
		$this->forward404Unless($tag);

		// check rights
		if ($tag->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array(FilePeer::NAME => "asc"));
		
		$itemPerPage = 15;
		
		$this->files = FileTagPeer::getFilesOfTagPager($page, $itemPerPage,
				array(
						"tagId" 	=> $tag->getId(),
				), $orderBy);
		
		$this->tag = $tag;
		$this->csrfToken = $this->getUser()->getCsrfToken();
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeTagFileDelete(sfWebRequest $request)
	{
		$tag = TagPeer::retrieveByPk($request->getParameter("tag"));
		$file = FilePeer::retrieveByPK($request->getParameter("file"));
	
		$this->forward404Unless($tag);
		$this->forward404Unless($file);
	
		// check rights
		if ($file->getUser()->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}
	
		SecurityUtils::checkCsrfToken();
	
		$fileTag = FileTagPeer::retrieveByTagAndFile($tag->getId(), $file->getId());

		if ($fileTag) {
			$fileTag->delete();
			$this->getUser()->setFlash("success", __("The tag has been deleted."));
		}
		
		$this->redirect($request->getReferer());
	}

	/******************************************************************************************************************/
	/**
	 * 							Gestion des logs
	 */
	/******************************************************************************************************************/
	/*________________________________________________________________________________________________________________*/
	public function executeLogList(sfWebRequest $request)
	{
		$type = $request->getParameter("type", 0);
		$keyword = $request->getParameter("keyword");
		$page = (int)$request->getParameter("page", 1);
		$sort = $request->getParameter("sort");
		$customerId = $this->getUser()->getCustomerId();

		$itemPerPage = 30;

		$logs = LogPeer::getLogPager(
				array(
						"type"			=> $type,
						"keyword"		=> $keyword,
						"customerId"	=> $customerId
				),
				$sort,
				$page,
				$itemPerPage
		);

		$this->keyword = $keyword;
		$this->logs = $logs;
		$this->currentType = $type;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUsageTracking(sfWebRequest $request)
	{
		if($this->getRequestParameter('year_month'))
			list($this->year, $this->month) = explode('-', $this->getRequestParameter('year_month'));
		else
		{
			$this->year = date('Y');
			$this->month = date('m');
		}
		
		$earliest_photo = LogPeer::retrieveEarliest(0);
		
		if($earliest_photo)
		{
			$this->start_year = date('Y', $earliest_photo->getCreatedAt("U"));
			$this->start_month = date('m', $earliest_photo->getCreatedAt("U"));
		}
		else
		{
			$this->start_year = date('Y');
			$this->start_month = date('m');
		}
		
		$c = new Criteria();
		$c->add(ConsumerLogCriteriaPeer::CREATED_AT, $this->year.'-'.
				($this->month == 'all' ? '01' : $this->month).'-01 00:00:01', Criteria::LESS_THAN);
		$c->addDescendingOrderByColumn(ConsumerLogCriteriaPeer::CREATED_AT);
		$this->consumer_log_criteria = ConsumerLogCriteriaPeer::doSelectOne($c);
		
		if(($this->year >= 2012 && $this->month >= 3) || $this->year > 2012)
		{
			$this->upload_traffic = Array();
			$this->download_traffic = Array();
			$temp = LogGroupePeer::getForMonthForCustomer($this->getUser()->getCustomerId(), $this->month, $this->year);
		
			$this->view_global = $temp["views"];
			$this->view_unique = $temp["unique_views"];
			$this->upload_traffic["total"] = $temp["upload_traffic"];
			$this->upload_traffic["nb"] = $temp["upload_traffic_files"];
			$this->download_traffic["total"] = $temp["download_traffic"];
			$this->download_traffic["nb"] = $temp["download_traffic_files"];
		}
		else
		{
			$this->view_global = FilePeer::getGlobalView($this->year, $this->month, 0);
			$this->view_unique = FilePeer::getUniqueView($this->year, $this->month, 0);
			$this->upload_traffic = LogPeer::getUploadTraffic($this->year, $this->month, 0);
			$this->download_traffic = LogPeer::getDownloadTraffic($this->year, $this->month, 0);
		}
		
		$this->total_size = FilePeer::retrieveTotalSize(0);
		
		$c = new Criteria();
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
		$c->addJoin(FilePeer::GROUPE_ID, GroupePeer::ID);
		$c->add(GroupePeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		$this->total_picture = FilePeer::doCount($c);
		
		$c = new Criteria();
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID);
		$c->add(GroupePeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		$this->total_folders = FolderPeer::doCount($c);
		
		$c = new Criteria();
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
		
		$this->total_main_folders = GroupePeer::doCount($c);
		$this->total_active_users = UserPeer::retrieveActiveUserNB($this->year, $this->month, 0);
		$this->total_users = UserPeer::retrieveTotalNB(0, 0, 0);
		

		$this->max_user = __("unlimited");
		$this->max_files = __("unlimited");
		$this->max_disk = __("unlimited");
		$this->max_main_folder = __("unlimited");

		$c = new Criteria();
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
		
		$this->groups = GroupePeer::doSelect($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUsageTrackingExport(sfWebRequest $request)
	{
		$tmpDir = sys_get_temp_dir();

		$response = $this->getResponse();
		$filename = 'log-'.$request->getParameter("month").'-'.$request->getParameter("year").'.csv';
		$disk = time().".csv";
	
		$c = new Criteria();
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
	
		$txt = $this->getPartial("admin/logGroup", array("groups" => GroupePeer::doSelect($c), 
				"year" => $request->getParameter("year"), 
				"month" => $request->getParameter("month"), "type" => "csv"));
	
		file_put_contents($tmpDir."/".$disk, $txt);
	
		$download = new Httpdownload();
		$download->setInline(false);
		$download->setFilePath($tmpDir."/".$disk);
		$download->setFilename($filename);
		$download->executeDownload();
	
		@unlink($tmpDir."/".$disk);
	
		die();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeUsageTrackingExportUser(sfWebRequest $request)
	{
		set_time_limit(0);
	
		$tmpDir = sys_get_temp_dir();
		$filename = 'log-'.$request->getParameter("month").'-'.$request->getParameter("year").'.csv';
		$disk = time().".csv";
	
		$c = new Criteria();
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		$c->add(UserPeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
	
		$txt = $this->getComponent("admin", "logUsersCsv", 
				array("customer_id" => $this->getUser()->getCustomerId(), "users" => UserPeer::doSelect($c), 
						"year" => $request->getParameter("year"), 
						"month" => $request->getParameter("month")));
	
		file_put_contents($tmpDir."/".$disk, $txt);
	
		$download = new Httpdownload();
		$download->setInline(false);
		$download->setFilePath($tmpDir."/".$disk);
		$download->setFilename($filename);
		$download->executeDownload();
	
		@unlink($tmpDir."/".$disk);
	
		die();
	}
	
	/******************************************************************************************************************/
	/**
	 * 							Gestion du thesaurus
	 */
	/******************************************************************************************************************/
	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusList(sfWebRequest $request)
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));

		$this->culture_ = $request->getParameter("culture", $this->getUser()->getCulture());
		$this->cultures = CulturePeer::get();
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusAdd(sfWebRequest $request)
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));
	
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$this->getResponse()->setContentType("application/json");
		
		$culture = CulturePeer::retrieveByCode($request->getParameter("culture"));
		$this->forward404Unless($culture);
	
		$title = $request->getParameter("title");
		
		//TODO check title
		// throw 400 bad request
		$thesaurus = new Thesaurus();

		$thesaurus->setType(ThesaurusPeer::__TYPE_TAG);
		$thesaurus->setCustomerId($this->getUser()->getCustomerId());
		$thesaurus->setCultureId($culture->getId());
		$thesaurus->setTitle($title);
		$thesaurus->setParentId(null);

		$thesaurus->save();

		return $this->renderText(json_encode(array("code" => 0, "id" => $thesaurus->getId())));
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusRandomTags(sfWebRequest $request)
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));
	
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		return $this->renderComponent("admin", "thesaurusRandomTags");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusDelete(sfWebRequest $request)
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));
	
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$this->getResponse()->setContentType("application/json");
		
		$thesaurus = ThesaurusPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($thesaurus);
			
		// check rights
		if ($thesaurus->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		ThesaurusPeer::recursiveDelete($thesaurus->getId());
		$thesaurus->delete();
	
		return $this->renderText(json_encode(array("code" => 0, "label" => "Success")));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusTree(sfWebRequest $request)
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));
		
		$culture = CulturePeer::retrieveByCode($request->getParameter("culture"));
		$this->forward404Unless($culture);
		
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}

		$root = $request->getParameter("id");
	
		if (empty($root)) {
			$root = null;
		}
		else {
			$this->forward404Unless(ThesaurusPeer::retrieveByPk($root));
		}

		$this->getResponse()->setContentType("application/json");
	
		//TODO placer dans le model
		$c = new Criteria();
			
		$c->add(ThesaurusPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(ThesaurusPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(ThesaurusPeer::PARENT_ID, $root);
		$c->add(ThesaurusPeer::CULTURE_ID, $culture->getId());
		$c->addAscendingOrderByColumn(ThesaurusPeer::TITLE);
	
		$thesaurus_ = ThesaurusPeer::doSelect($c);
	
		// normalize for jstree
		$thesaurus_array = array();
	
		foreach ($thesaurus_ as $thesaurus) {
			$temp = array();
			
			$temp["data"] = $thesaurus->getTitle();
			$temp["state"] = $thesaurus->getType() == ThesaurusPeer::__TYPE_CLASS ? "closed" : "";
			$temp["attr"] = array("id" => "node_".$thesaurus->getId(), "rel" => $thesaurus->getType());
	
			array_push($thesaurus_array, $temp);
		}
	
		if (!count($thesaurus_array)) {
			$c = new Criteria();
			
			$c->add(ThesaurusPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(ThesaurusPeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(ThesaurusPeer::ID, $root);
			$c->add(ThesaurusPeer::CULTURE_ID, $culture->getId());

			$thesaurus = ThesaurusPeer::doSelectOne($c);
	
			if ($thesaurus) {
				$thesaurus->setType(ThesaurusPeer::__TYPE_TAG);
				$thesaurus->save();
			}
		}
	
		return $this->renderText(json_encode($thesaurus_array));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusUpdate(sfWebRequest $request)
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));

		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}

		$thesaurus = ThesaurusPeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($thesaurus);

		$response = $this->getResponse();
		$response->setContentType("application/json");
		
		$value = $request->getParameter("value");
		$errors = array();
		
		switch ($request->getParameter("field")) {
			case "title": 
				if (empty($value)) {
					$errors[] = array("code" => 1, "message" => __("The title is required."));
				}
				else {
					$thesaurus->setTitle($value); 
				}
				
				break;
		}

		if (count($errors)) {
			$response->setStatusCode(400);
			$this->renderText(json_encode(array("error" => $errors[0])));
		}
		else {
			$thesaurus->save();
		}
		
		return $this->renderText("");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusMove(sfWebRequest $request)
	{
		$this->forward404Unless($this->getUser()->haveAccessModule(ModulePeer::__MOD_THESAURUS));

		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}

		$from = ThesaurusPeer::retrieveByPk($request->getParameter("from"));
		$to = ThesaurusPeer::retrieveByPk($request->getParameter("to"));
		
		$this->forward404Unless($from);
		$this->forward404Unless($to);
		
		// check rights
		$this->forward404Unless($from->getCustomerId() == $this->getUser()->getCustomerId());
		$this->forward404Unless($to->getCustomerId() == $this->getUser()->getCustomerId());

		$this->getResponse()->setContentType("application/json");

		if ($from->getId() != $to->getId()) {
			$from->setType(ThesaurusPeer::__TYPE_CLASS);
			$from->setParentId($to->getId());
			$from->save();

			$to->setType(ThesaurusPeer::__TYPE_CLASS);
			$to->save();
		}

		if (!$from->hasChildren()) {
			$from->setType(ThesaurusPeer::__TYPE_TAG);
			$from->save();
		}

		if (!$to->hasChildren()) {
			$to->setType(ThesaurusPeer::__TYPE_TAG);
			$to->save();
		}
	
		return $this->renderText(json_encode(Array("code" => 0, "label" => "Success")));
	}

	/******************************************************************************************************************/
	/**
	 * 							Gestion des albums
	 */
	/******************************************************************************************************************/
	public function executeAlbumList(sfWebRequest $request)
	{
		$keyword = $request->getParameter("keyword", "");
		$page = (int)$request->getParameter("page", 1);
		$orderBy = $request->getParameter("orderBy", array("name_asc"));

		$itemPerPage = 15;
		
		$this->albums = GroupePeer::getPager($page, $itemPerPage,
				array(
						"customerId" => $this->getUser()->getCustomerId(),
						"keyword" 	 => $keyword,
				), $orderBy);
		
		$this->orderBy = $orderBy;
		$this->keyword = $keyword;
		
		$this->csrfToken = $this->getUser()->getCsrfToken();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeAlbumDelete(sfWebRequest $request)
	{
		$album = GroupePeer::retrieveByPK($request->getParameter("id"));
		$this->forward404Unless($album);
		
		// check rights
		if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
			$this->forward404();
		}

		// csrf token
		SecurityUtils::checkCsrfToken();
		
		$album->delete();
		
		$this->getUser()->setFlash("success", __("The album has been deleted."));
		
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeAlbumNew(sfWebRequest $request)
	{
		$customerId = $this->getUser()->getCustomerId();
		
		$form = new Backend_Admin_Album_NewForm(
				array(), 
				array("customerId" => $customerId)
		);
	
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
		
			if ($form->isValid()) {
				$album = new Groupe();
				
				$album->setCustomerId($customerId);
				$album->setUserId($this->getUser()->getId());
				$album->setName($form->getValue("name"));
				$album->setDescription($form->getValue("description"));
				
				$album->save();
		
				$this->getUser()->setFlash("success", __("The album has been added."));
				$this->redirect("admin_album_list");
			}
		}
		
		$this->form = $form;
	}
}
