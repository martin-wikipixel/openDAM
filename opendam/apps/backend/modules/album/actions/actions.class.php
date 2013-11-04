<?php

/**
 * album actions.
 *
 * @package    wikipixel
 * @subpackage album
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class albumActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGenerateThumbnail(sfWebRequest $request)
	{
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);
		
		$response = $this->getResponse();
		$response->clearHttpHeaders();

		$file = FilePeer::getFirstFileOfAlbum($album->getId());

		// pas de preview et pas d'erreur
		if (!$file) {
			$contentType = "image/png";
			$pathname = sfConfig::get("sf_web_dir")."/images/no-access-file-200x200.png";
		}
		else {
			$contentType = $file->getContentType();
			$pathname = $file->getPathname();
		}

		$response->setHttpHeader("Content-Type", $contentType);
		readfile($pathname);

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getListParams()
	{
		$sf_user = sfContext::getInstance()->getUser();
		
		return array(
				"userId" => $sf_user->getId(),
				"customerId" => $sf_user->getCustomerId()
		);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Liste mes albums que j'ai accès pour mon customer.
	 * voir executeSharedList pour voir les albums d'un autre customer
	 * 
	 * TODO a déplacer dans admin
	 * Si admin, liste tous les albums
	 * 
	 * @param sfWebRequest $request
	 * @return string
	 */
	public function executeList(sfWebRequest $request)
	{
		$orderBy = $request->getParameter("orderBy", array("name_asc"));
		
		$page = $request->getParameter("page", 1);
		$perPage = $request->getParameter("perPage", 8);

		$albums = GroupePeer::getAlbumsHaveAccessForUserPager($page, $perPage, self::getListParams(), $orderBy);
		
		$this->albums = $albums;
		$this->orderBy = $orderBy;
		$this->perPage = $perPage;
		
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Liste mes albums pour mon customer.
	 */
	public function executeListThumbnail(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$this->getResponse()->setContentType("application/json");

		$orderBy = $request->getParameter("orderBy", array("name_asc"));
		$perPage = $request->getParameter("perPage", 8);
		$page = $request->getParameter("page");

		$albums = GroupePeer::getAlbumsHaveAccessForUserPager($page, $perPage, self::getListParams(), $orderBy);
		
		$response = array(
			"count" => $albums->count(),
			"lastPage" => $albums->getLastPage(),
			"html" => $this->getPartial("album/listThumbnail", array("albums" => $albums)),
		);
		
		return $this->renderText(json_encode($response));
	}

	/*________________________________________________________________________________________________________________*/
	/*_________________________________________GESTION DES PERMISSIONS________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public function executeRightUserList(sfWebRequest $request)
	{
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);
		
		// check rights
		$this->forward404Unless($role = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$role || $role > RolePeer::__ADMIN);
	
		$keyword = $request->getParameter("keyword", "");
		$letter = $request->getParameter("letter", "");
		$page = (int)$request->getParameter("page", 1);
		$role = $request->getParameter("role", "");
		$state = $request->getParameter("state", "");
		$itemPerPage = 10;
		
		$isExternalAlbum = $album->getCustomerId() != $this->getUser()->getCustomerId() ? true : false;
		
		$users = GroupePeer::getUsersPager($page, $itemPerPage,
				array(
						"albumId"		=> $album->getId(),
						"customerId"	=> $album->getCustomerId(),
						"keyword"		=> $keyword,
						"userStates"	=> array(UserPeer::__STATE_ACTIVE),
						"role"			=> $role,
						"roleState"		=> $state ? $state : ($isExternalAlbum ? "active" : ""),
						"letter"		=> $letter
				), array(UserPeer::EMAIL => "asc"));
	
		$letters = GroupePeer::getLettersOfUsersPager(
				array(
						"albumId"		=> $album->getId(),
						"customerId"	=> $album->getCustomerId(),
						"keyword"		=> $keyword,
						"userStates"	=> array(UserPeer::__STATE_ACTIVE),
						"role"			=> $role,
						"roleState"		=> $state ? $state : ($isExternalAlbum ? "active" : "")));
	
		$this->keyword = $keyword;
		$this->currentLetter = $letter;
		$this->currentRole = $role;
		$this->currentState = $state;
		$this->roles = RolePeer::getRoles();
		$this->album = $album;
		$this->page = $page;
		$this->isExternalAlbum = $isExternalAlbum;
		
		$this->users = $users;
		$this->letters = $letters;
	
		$this->csrfToken = $this->getUser()->getCsrfToken();
	
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeRightUserDelete(sfWebRequest $request)
	{
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);
		
		$user = UserPeer::retrieveByPK($request->getParameter("user"));
		$this->forward404Unless($user);
		
		// check rights
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);
	
		$isExternalAlbum = $album->getCustomerId() != $this->getUser()->getCustomerId() ? true : false;
	
		//check csrf token
		SecurityUtils::checkCsrfToken();
	
		$userAlbum = UserGroupPeer::retrieveByUserAndGroup($user->getId(), $album->getId());
	
		//TODO a revoir
		if ($userAlbum) {
			if (!$isExternalAlbum) {
				// check rights
				if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
					$this->forward404();
				}
	
				if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
					$this->forward404();
				}
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
	public function executeRightUserUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);
		
		$this->forward404Unless($user = UserPeer::retrieveByPK($request->getParameter("user")));
		
		//check rights
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);
	
		$isExternalAlbum = $album->getCustomerId() != $this->getUser()->getCustomerId() ? true : false;
	
		//TODO a revoir
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
			if (!$isExternalAlbum) {
				if ($user->getCustomerId() != $this->getUser()->getCustomerId()) {
					$this->forward404();
				}
	
				if ($album->getCustomerId() != $this->getUser()->getCustomerId()) {
					$this->forward404();
				}
			}
	
			// update
			$userAlbum->setRole($role->getId());
			$userAlbum->save();
		}
	
		$request = RequestPeer::getRequest($album->getId(), $user->getId());
	
		if ($request) {
			$request->delete();
		}
	
		return sfView::NONE;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * TODO a revoir
	 * 
	 * @param sfWebRequest $request
	 * @return string
	 */
	public function executeNotify(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);
		
		$user = UserPeer::retrieveByPk($this->getRequestParameter("user"));
		$this->forward404Unless($user);
		
		// check rights
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
	/*______________________________________________GESTION PAR GROUPE _______________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public function executeRightGroupList(sfWebRequest $request)
	{
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);
		
		// check rights
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);
	
		$page = (int)$request->getParameter("page", 1);
		$itemPerPage = 10;
		$orderBy = $request->getParameter("orderBy", array("title_asc"));
		$letter = $request->getParameter("letter", "");
		$keyword = $request->getParameter("keyword", "");
		$role = $request->getParameter("role");
		
		$isExternalAlbum = $album->getCustomerId() != $this->getUser()->getCustomerId() ? true : false;
	
		$groups = UnitPeer::getPager($page, $itemPerPage,
				array(
						"albumId"		=> $album->getId(),
						"keyword"		=> $keyword,
						"customerId"	=> $album->getCustomerId(),
						"role"			=> $role,
						"letter"		=> $letter,
						"roleState"		=> $isExternalAlbum ? "active" : ""
				), $orderBy);
	
		$letters = UnitPeer::getLettersOfPager(
				array(
						"albumId"		=> $album->getId(),
						"keyword"		=> $keyword,
						"customerId"	=> $album->getCustomerId(),
						"role"			=> $role,
						"roleState"		=> $isExternalAlbum ? "active" : ""
				));
	
		$this->album = $album;
		$this->groups = $groups;
		$this->letters = $letters;
		$this->orderBy = $orderBy;
		$this->keyword = $keyword;
		$this->currentLetter = $letter;
		$this->currentRole = $role;
		$this->roles = RolePeer::getRoles();
		
		$this->isExternalAlbum = $isExternalAlbum;
	
		$this->csrfToken = $this->getUser()->getCsrfToken();
	
		return sfView::SUCCESS;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeRightGroupDelete(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$group = UnitPeer::retrieveByPK($request->getParameter("group"));
	
		$this->forward404Unless($album);
		$this->forward404Unless($group);
		
		// check rights
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);
	
		$isExternalAlbum = $album->getCustomerId() != $this->getUser()->getCustomerId() ? true : false;
	
		// check rights
		if (!$isExternalAlbum) {
			if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}
	
			if (!RightUtils::canUpdateAlbum($album)) {
				$this->forward404();
			}
		}
	
		$albumGroup = UnitGroupPeer::retrieveByUnitIdAndGroupeId($group->getId(), $album->getId());
	
		if ($albumGroup) {
			$albumGroup->delete();
		}
	
		$this->redirect($request->getReferer());
	}
	
	/*________________________________________________________________________________________________________________*/
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
		
		// check rights
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);
	
		$isExternalAlbum = $album->getCustomerId() != $this->getUser()->getCustomerId() ? true : false;
	
		// check rights
		if (!$isExternalAlbum) {
			// on vérifie que le groupe appartient au même client
			if ($group->getCustomerId() != $this->getUser()->getCustomerId()) {
				$this->forward404();
			}
				
			if (!RightUtils::canUpdateAlbum($album)) {
				$this->forward404();
			}
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
	public function executeRightEverybodyUpdate(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$album = GroupePeer::retrieveByPK($request->getParameter("album"));
		$this->forward404Unless($album);
	
		// check rights
		$this->forward404Unless($roleAlbum = $this->getUser()->getRole($album->getId()));// ?
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__ADMIN);
	
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

}
