<?php

/**
 * Subclass for performing query and update operations on the 'groupe' table.
 *
 * 
 *
 * @package lib.model
 */ 
class GroupePeer extends BaseGroupePeer
{
	const __STATE_ACTIVE = 1;
	const __STATE_DELETE = 2;

	const __TYPE_PROD = 0;
	const __TYPE_DEMO = 1;

	/*________________________________________________________________________________________________________________*/
	/**
	 * 
	 * @param array $params
	 * @param array $orderBy
	 * @param number $limit
	 * 
	 * @return Criteria
	 */
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		$state = isset($params["state"]) ? (int) $params["state"] : self::__STATE_ACTIVE;
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$userId = isset($params["userId"]) ? (int) $params["userId"] : "";
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		
		$criteria = new Criteria();
		
		$criteria->add(self::STATE, $state);
	
		if ($userId) {
			$criteria->add(self::USER_ID, $userId);
		}
	
		if ($customerId) {
			$criteria->add(self::CUSTOMER_ID, $customerId);
		}
		
		if ($keyword) {
			$c1 = $criteria->getNewCriterion(self::NAME, "%".$keyword."%", Criteria::LIKE);
			$c2 = $criteria->getNewCriterion(self::DESCRIPTION, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c2);
			$criteria->add($c1);
		}
	
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
	
		if ($limit) {
			$criteria->setLimit($limit);
		}
	
		//echo $criteria->toString()."<br><br>";
	
		return $criteria;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getPager($page, $itemPerPage, array $params = array(), array $orderBy = array(), $peerMethod = "doSelect")
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);
	
		$pager = new sfPropelPager("Groupe", $itemPerPage);
		
		$pager->setCriteria(self::doCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod($peerMethod);
		$pager->init();
	
		return $pager;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function findBy(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		return self::doSelect(self::doCriteria($params, $orderBy, $limit));
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * TODO A déplacer dans UserPeer
	 * 
	 * @param array $params
	 * @param array $orderBy
	 * @param number $limit
	 * @return Criteria
	 */
	public static function doCriteriaUsers(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$letter = isset($params["letter"]) ? $params["letter"] : "";
		$role = isset($params["role"]) ? (int)$params["role"] : 0;
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		$userStates = isset($params["userStates"]) ? (array)$params["userStates"] : array(UserPeer::__STATE_ACTIVE,
				UserPeer::__STATE_SUSPEND);
		$albumId = isset($params["albumId"]) ? (int)$params["albumId"] : 0;
		$roleState = isset($params["roleState"]) ? $params["roleState"] : "";

		$criteria = new Criteria();

		if ($letter) {
			$criteria->add(UserPeer::EMAIL, $letter.'%', Criteria::LIKE);
		}

		if (count($userStates)) {
			$criteria->add(UserPeer::STATE, $userStates, Criteria::IN);
		}

		if ($keyword) {
			$c1 = $criteria->getNewCriterion(UserPeer::USERNAME, "%".$keyword."%", Criteria::LIKE);
		
			$c2 = $criteria->getNewCriterion(UserPeer::LASTNAME, "%".$keyword."%", Criteria::LIKE);
			$c3 = $criteria->getNewCriterion(UserPeer::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
			$c4 = $criteria->getNewCriterion(UserPeer::EMAIL, "%".$keyword."%", Criteria::LIKE);
			$c5 = $criteria->getNewCriterion(UserPeer::POSITION, "%".$keyword."%", Criteria::LIKE);
			$c6 = $criteria->getNewCriterion(UserPeer::PHONE, "%".$keyword."%", Criteria::LIKE);

			$c1->addOr($c2);
			$c1->addOr($c3);
			$c1->addOr($c4);
			$c1->addOr($c5);
			$c1->addOr($c6);
			$criteria->add($c1);
		}

		if ($customerId && !$role) {
			$criteria->add(UserPeer::CUSTOMER_ID, $customerId);
		}

		if ($albumId && $customerId) {
			if ($role || $roleState == "active") {
				// Exception user
				$criteriaUserGroup = new Criteria();
				$criteriaUserGroup->addJoin(UserGroupPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
				$criteriaUserGroup->add(UserGroupPeer::GROUPE_ID, $albumId);
				$criteriaUserGroup->add(UserPeer::CUSTOMER_ID, $customerId);

				if ($role) {
					$criteriaUserGroup->add(UserGroupPeer::ROLE, $role);
				}
				else {
					$criteriaUserGroup->add(UserGroupPeer::ROLE, "", Criteria::NOT_EQUAL);
				}

				if (count($userStates)) {
					$criteriaUserGroup->add(UserPeer::STATE, $userStates, Criteria::IN);
				}

				CriteriaUtils::setSelectColumn($criteriaUserGroup, UserPeer::ID);

				// Exception group
				$criteriaUnitGroup = new Criteria();
				$criteriaUnitGroup->addJoin(UnitGroupPeer::UNIT_ID, UserUnitPeer::UNIT_ID, Criteria::INNER_JOIN);
				$criteriaUnitGroup->addJoin(UserUnitPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
				$criteriaUnitGroup->add(UnitGroupPeer::GROUPE_ID, $albumId);
				$criteriaUnitGroup->add(UserPeer::CUSTOMER_ID, $customerId);

				if (count($userStates)) {
					$criteriaUnitGroup->add(UserPeer::STATE, $userStates, Criteria::IN);
				}

				// Exclusion des exceptions user
				$criteriaExcludeUnitGroup = new Criteria();
				$criteriaExcludeUnitGroup->addJoin(UserGroupPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
				$criteriaExcludeUnitGroup->add(UserGroupPeer::GROUPE_ID, $albumId);
				$criteriaExcludeUnitGroup->add(UserPeer::CUSTOMER_ID, $customerId);

				if (count($userStates)) {
					$criteriaExcludeUnitGroup->add(UserPeer::STATE, $userStates, Criteria::IN);
				}

				CriteriaUtils::setSelectColumn($criteriaExcludeUnitGroup, UserPeer::ID);

				$subQuery = UserPeer::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($criteriaExcludeUnitGroup).")";

				$criteriaUnitGroup->add(UserPeer::ID, $subQuery, Criteria::CUSTOM);

				if ($role) {
					$criteriaUnitGroup->add(UnitGroupPeer::ROLE, $role);
				}

				CriteriaUtils::setSelectColumn($criteriaUnitGroup, UserPeer::ID);

				$subQuery = UserPeer::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteriaUserGroup).")";
				$subQuery .= " OR ".UserPeer::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteriaUnitGroup).")";

				$criteria->add(UserPeer::ID, $subQuery, Criteria::CUSTOM);
			}

			if ($roleState == "pending") {
				$criteriaRequest = new Criteria();
				$criteriaRequest->addJoin(UserPeer::ID, RequestPeer::USER_ID);
				$criteriaRequest->add(RequestPeer::GROUPE_ID, $albumId);
				$criteriaRequest->add(RequestPeer::IS_REQUEST, true);

				if (count($userStates)) {
					$criteriaRequest->add(UserPeer::STATE, $userStates, Criteria::IN);
				}

				CriteriaUtils::setSelectColumn($criteriaRequest, RequestPeer::USER_ID);

				$subQuery = UserPeer::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteriaRequest).")";

				$criteria->add(UserPeer::ID, $subQuery, Criteria::CUSTOM);
			}
		}

		CriteriaUtils::buildOrderBy($criteria, $orderBy);

		if ($limit) {
			$criteria->setLimit($limit);
		}

		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * TODO A déplacer dans UserPeer
	 * 
	 * @param unknown $page
	 * @param unknown $itemPerPage
	 * @param array $params
	 * @param array $orderBy
	 * @return sfPropelPager
	 */
	public static function getUsersPager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);

		$pager = new sfPropelPager("User", $itemPerPage);
		$pager->setCriteria(self::doCriteriaUsers($params, $orderBy));

		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();

		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Obtenir la premiere lettre de l'email d'un utilisateur.
	 * 
	 * @param array $params
	 * @return array
	 */
	public static function getLettersOfUsersPager(array $params = array())
	{
		$criteria = self::doCriteriaUsers($params);

		$criteria->clearSelectColumns();
		$criteria->addSelectColumn("DISTINCT UPPER(substr(".UserPeer::EMAIL.", 1, 1 )) AS letter");
		$criteria->addAscendingOrderByColumn("letter");

		$letters = UserPeer::doSelectStmt($criteria);

		return $letters->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie tous les albums que l'utilisateur a accès.
	 *
	 * L'utilisateur a accès à un album si :
	 * 1 - l'utilisateur appartient à l'album (voir table UserGroup)
	 * 2 - ou l'utilisateur appartient à un groupe qui appartient à l'album (voir table UserUnit et UnitGroup)
	 * 3 - ou il existe des albums avec accès "everybody" sur son customer avec permission non "NONE"
	 *
	 * Par défaut, liste les albums des autres customers auxquel on a ajouté des droits, ajouter le champ "customerId"
	 * pour restraintre a un customer.
	 *
	 * Par défaut ne liste pas les albums systèmes.
	 *
	 * @param int $page
	 * @param int $itemPerPage
	 * @param array $params
	 * @param array $orderBy
	 *
	 * @return Album
	 */
	public static function getAlbumsHaveAccessForUserCriteria( array $params = array(), array $orderBy = array(), 
			$limit = 0)
	{
		$userId = isset($params["userId"]) ? (int)$params["userId"] : 0;
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		
		$customerId_notEquals = isset($params["customerId_notEquals"]) ? (boolean)$params["customerId_notEquals"] : false;
		
		// permet de filtrer par méthode d'héritage de droits
		$inherit = isset($params["inherit"]) ? (int)$params["inherit"] : 0;

		Assert::ok($userId > 0);
		
		$criteria = new Criteria();
	
		
		// query 1
		$criteria_q1 = UserGroupPeer::doCriteria($params);
		CriteriaUtils::setSelectColumn($criteria_q1, UserGroupPeer::GROUPE_ID);
		
		// query 2
		$criteria_q2 = new Criteria();
		
		$criteria_q2->addJoin(UserUnitPeer::UNIT_ID, UnitGroupPeer::UNIT_ID);
		$criteria_q2->addJoin(UnitGroupPeer::GROUPE_ID, GroupePeer::ID);
		
		$criteria_q2->add(UserUnitPeer::USER_ID, $userId);
		$criteria_q2->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		if ($customerId) {
			$criteria_q2->add(GroupePeer::CUSTOMER_ID, $customerId, ($customerId_notEquals ? Criteria::NOT_EQUAL : Criteria::EQUAL));
		}
		
		$criteria_q2->setDistinct();
		CriteriaUtils::setSelectColumn($criteria_q2, UnitGroupPeer::GROUPE_ID);
		
		// query 3
		$criteria_q3 = null;
		
		if ($customerId && !$customerId_notEquals) {
			$criteria_q3 = new Criteria();
		
			$criteria_q3->add(GroupePeer::CUSTOMER_ID, $customerId);
			$criteria_q3->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			
			// si role est null
			$freeCredentialCriterion1 = $criteria_q3->getNewCriterion(self::FREE_CREDENTIAL, null, Criteria::ISNOTNULL);
			
			// si role est à "NONE"
			$freeCredentialCriterion1->addAnd($criteria_q3->getNewCriterion(self::FREE_CREDENTIAL, RolePeer::__NONE, Criteria::NOT_EQUAL));
			
			$criteria_q3->add($freeCredentialCriterion1);
			
			CriteriaUtils::setSelectColumn($criteria_q3, GroupePeer::ID);
		}
		
		/*
		 echo CriteriaUtils::buidSqlFromCriteria($criteria_q1);
		echo "<br><br>";
		*/
		
		//echo CriteriaUtils::buidSqlFromCriteria($criteria_q2);
		//echo "<br><br>";
		
		/*
		 echo CriteriaUtils::buidSqlFromCriteria($criteria_q3);
		echo "<br><br>";
		*/
		
		$subQql = "";
		
		switch ($inherit) {
			case 1:// uniquement les droits rattachés à l'utilisateur
				$subQql .= self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q1).")";
				break;
		
			case 2:// uniquement les droits hérités par des groupes
				$subQql .= self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q2).")";
				$subQql .= " AND ".self::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q1).")";
				break;
		
			case 3:// uniquement les droits hérités par "everybody" (album)
				if ($criteria_q3) {
					$subQql .= self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q3).") AND ";
				}
		
				$subQql .= self::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q2).") AND ";
				$subQql .= self::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q1).")";
				break;
		
			default:
				$subQql .= self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q1).")";
				$subQql .= " OR ".self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q2).")";
		
				if ($criteria_q3) {
					$subQql .= " OR ".self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteria_q3).")";
				}
		}
		
		$criteria->add(self::ID, $subQql, Criteria::CUSTOM);
		
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
		
		//echo $criteria->toString();
		
		return $criteria;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getAlbumsHaveAccessForUserPager($page, $itemPerPage, array $params = array(), 
			array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);
		
		$pager = new sfPropelPager("Groupe", $itemPerPage);
		$pager->setCriteria(self::getAlbumsHaveAccessForUserCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function countAlbumsHaveAccessForUser(array $params = array())
	{
		return self::doCount(self::getAlbumsHaveAccessForUserCriteria($params));
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie les albums qui n'ont pas été ajouté DIRECTEMENT à utilisateur (voir table UserGroup).
	 * Attention, liste les albums qui ont des droits hérités par groupe ou par everybody.
	 * 
	 * TODO a renommer en ??
	 * 
	 * @param array $params
	 * @param array $orderBy
	 * @param number $limit
	 *
	 * @return array<Groupe>
	 */
	public static function getAlbumsCanAddToUser(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		$userId = isset($params["userId"]) ? (int) $params["userId"] : "";
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
	
		Assert::ok($userId > 0);
		Assert::ok($customerId > 0);
	
		$criteria = new Criteria();
	
		$criteria->add(self::CUSTOMER_ID, $customerId);
		$criteria->add(self::STATE, GroupePeer::__STATE_ACTIVE);
	
		$notInCriteria  = new Criteria();
		
		$notInCriteria->add(UserGroupPeer::USER_ID, $userId);
		CriteriaUtils::setSelectColumn($notInCriteria, UserGroupPeer::GROUPE_ID);
		
		$criteria->add(self::ID, self::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($notInCriteria).")", 
				Criteria::NOT_IN);
	
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
	
		if ($limit) {
			$criteria->setLimit($limit);
		}
	
		return self::doSelect($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi les albums qui ont au moins un dossier vérouiller (accès gérés).
	 *
	 * @param array $params
	 * @param array $orderBy
	 */
	public static function findAlbumsWithLockedFolders(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$customerId = isset($params["customerId"]) ? (int) $params["customerId"] : 0;
		$exludeAlbumOfUserId = isset($params["exludeAlbumOfUserId"]) ? (int) $params["exludeAlbumOfUserId"] : 0;
	
		$criteria = new Criteria();
	
		$criteria->add(self::STATE, self::__STATE_ACTIVE);
	
		if ($customerId) {
			$criteria->add(self::CUSTOMER_ID, $customerId);
		}

		$params["isFree"] = false;
	
		// select dossier vérrouller
		$folderCriteria = FolderPeer::doCriteria($params);
		CriteriaUtils::setSelectColumn($folderCriteria, GroupePeer::ID);
	
		$folderCriteria->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID, Criteria::INNER_JOIN);
	
		// moins les dossiers que l'utilisateur possède déja 
		if ($exludeAlbumOfUserId) {
			$folderUserCriteria = UserFolderPeer::doCriteria(array("userId" => $exludeAlbumOfUserId));
	
			CriteriaUtils::setSelectColumn($folderUserCriteria, UserFolderPeer::FOLDER_ID);
				
			$folderCriteria->add(FolderPeer::ID, FolderPeer::ID." NOT IN(".
					CriteriaUtils::buidSqlFromCriteria($folderUserCriteria).")", Criteria::CUSTOM);
		}
	
		$criteria->add(self::ID, self::ID . " IN(".CriteriaUtils::buidSqlFromCriteria($folderCriteria).")",
				Criteria::CUSTOM);
	
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
	
		if ($limit) {
			$criteria->setLimit($limit);
		}
	
		return self::doSelect($criteria);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le premier album d'un customer.
	 */
	public static function getFirstAlbumOfCustomer($customerId)
	{
		$albums = self::findBy(array("customerId" => $customerId), array(), 1);
		
		return count($albums) ? $albums[0] : null;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie les albums partagés avec un utilisateur mais qui n'appartient pas au customer passé en paramètre.
	 *
	 * @return Array<Groupe>
	 */
	/*public static function getExternalSharedAlbumsPager(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$criteria = new Criteria();
		
		$criteria = self::doCriteria(array());
	}*/

	/*________________________________________________________________________________________________________________*/
	/**
	 */
	public static function deleteAlbum(Groupe $album)
	{
		// TODO a optimiser
		// select 1-n danger !!
		$folders = FolderPeer::retrieveByGroupId($album->getId());

		$sf_user = sfContext::getInstance()->getUser();
		
		foreach ($folders as $folder) {
			$folder->setState(FolderPeer::__STATE_DELETE);
			$folder->save();
		
			LogPeer::setLog($sf_user->getId(), $folder->getId(), "folder-delete", "2");
		
			$c = new Criteria();
			
			$c->add(FilePeer::FOLDER_ID, $folder->getId());
			$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
		
			$files = FilePeer::doSelect($c);
		
			foreach ($files as $file) {
				$file->setState(FilePeer::__STATE_DELETE);
				$file->setUpdatedAt(time());
		
				$file->save();
		
				LogPeer::setLog($sf_user->getId(), $file->getId(), "file-delete", "3");
			}
		
			FolderPeer::deleteArbo($folder->getId());
		}
		
		$album->setState(GroupePeer::__STATE_DELETE);
		
		$album->save();
		
		LogPeer::setLog($sf_user->getId(), $album->getId(), "group-delete", "1");
	}
	
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	
	public static function retrieveByPKNoCustomer($pk, PropelPDO $con = null)
	{
		return parent::retrieveByPK($pk, $con);
	}

	/*________________________________________________________________________________________________________________*/
	// lib/groupUniqueValidator
	public static function retrieveByName($name)
	{
		$c = new Criteria();

		$c->add(self::NAME, $name);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	// 
	/**
	 * request/sendSuccess
	 */
	public static function getNoAccessGroupsInArray($user_id)
	{
		$group_ids1 = UserGroupPeer::getGroupIds($user_id);
		$group_ids2 = RequestPeer::getGroupIds($user_id);
		
		$group_ids = array_merge($group_ids1, $group_ids2);
		
		$c = new Criteria();
		$c->add(self::ID, $group_ids, Criteria::NOT_IN);
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::NAME);
		$groups = self::doSelect($c);
		
		$groups_array = array();
		foreach ($groups as $group){
				if(!$group->getFree())
					$groups_array[$group->getId()] = $group;
		}
		return $groups_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * folder/editSuccess, group/manageSuccess, search/_information, upload/selectFolder
	 */
	public static function getGroupsInArray($user_id=0, $group_id=0)
	{
		if ($user_id) {
			$user = UserPeer::retrieveByPKNoCustomer($user_id);
		}
		else {
			$user = sfContext::getInstance()->getUser()->getInstance();
		}

		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, $user->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		if (!in_array($user->getRoleId(), array(RolePeer::__ADMIN))) {
			$c->add(UserGroupPeer::USER_ID, $user->getId());
			$c->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
		}

		if ($group_id) {
			$c->add(self::ID, $group_id, Criteria::NOT_EQUAL);
		}

		$c->addAscendingOrderByColumn(self::NAME);
		$groups = self::doSelect($c);

		$groups_array = array();

		foreach ($groups as $group) {
			$groups_array[$group->getId()] = $group->getName();
		}

		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::FREE, true);
		$c->addAscendingOrderByColumn(self::NAME);

		$groups = self::doSelect($c);

		foreach ($groups as $group) {
			if (!in_array($user->getRoleId(), array(RolePeer::__ADMIN))) {
				if($group->getFreeCredential() == RolePeer::__CONTRIB) {
					$groups_array[$group->getId()] =  $group->getName();
				}
				else {
					$role = UserGroupPeer::getRole($user_id, $group->getId());
		
					if($role <= RolePeer::__CONTRIB) {
						$groups_array[$group->getId()] = $group->getName();
					}
				}
			}
			else {
				$groups_array[$group->getId()] =  $group->getName();
			}
		}

		return $groups_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUploadGroups($user_id)
	{
		$connection = Propel::getConnection();

		if ($user_id) {
			$user = UserPeer::retrieveByPKNoCustomer($user_id);
		}
		else {
			$user = sfContext::getInstance()->getUser()->getInstance();
		}

		$userId = $user->getId();
		$customerId = $user->getCustomerId();
		$role = $user->getRoleId();
		$groups = array();

		$query = "	SELECT groupe.*
					FROM groupe
					WHERE groupe.customer_id = ".$customerId."
					AND groupe.state = ".self::__STATE_ACTIVE;

		if ($role > RolePeer::__ADMIN) {
			$query .= "	AND (groupe.free = 1 OR (groupe.free = 0 AND (groupe.id IN (SELECT user_group.groupe_id
																					FROM user_group
																					WHERE user_group.user_id = ".$userId."
																					AND user_group.role <= ".RolePeer::__ADMIN.")
						OR groupe.id IN (	SELECT unit_group.groupe_id
											FROM unit_group, unit, user_unit
											WHERE unit_group.unit_id = unit.id
											AND unit.id = user_unit.unit_id
											AND unit.customer_id = ".$customerId."
											AND user_unit.user_id = ".$userId."))
						))";
		}

		$query .= "	ORDER BY groupe.name";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);

		while ($rs = $statement->fetch()) {
			$group = new Groupe();
			$group->hydrate($rs);
			$groups[$group->getId()] = $group;
		}

		return $groups;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * folder/editSuccess, group/manageSuccess, search/_information, upload/selectFolder
	 * @deprecated
	 */
	public static function getGroupsNoRight($user_id)
	{
		$connection = Propel::getConnection();
	
		$query = "	SELECT DISTINCT groupe.*
					FROM groupe, customer
					WHERE groupe.customer_id = customer.id
					AND groupe.free = 0
					AND groupe.id NOT IN (	SELECT groupe_id
											FROM user_group
											WHERE user_id =".$user_id.")
					AND customer.state = ".CustomerPeer::__STATE_ACTIVE."
					AND groupe.customer_id = ".sfContext::getInstance()->getUser()->getCustomerId()."
					AND groupe.state = ".self::__STATE_ACTIVE."
					ORDER BY groupe.name";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		$groups = array();
	
		while ($rs = $statement->fetch()) {
			$group = new Groupe();
			$group->hydrate($rs);
			$groups[$group->getId()] = $group;
		}

		$query = "	SELECT unit_group.groupe_id as id
					FROM unit, unit_group, user_unit
					WHERE user_unit.unit_id = unit.id
					AND unit.id = unit_group.unit_id
					AND user_unit.user_id = ".$user_id;
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);

		while($rs = $statement->fetch()) {
		if (array_key_exists($rs["id"], $groups)) {
			unset($groups[$rs["id"]]);
			}
		}

		return $groups;
	}

	/*________________________________________________________________________________________________________________*/
	# group/list, group/_home
	/**
	 * @deprecated
	 * configuration/access, gruop/list, group/_home
	 */
	public static function getGroupPager($user_id=0, $sort="name_asc", $page=1, $per_page=100, $author_id=0)
	{
		if (!sfContext::getInstance()->getUser()->hasCredential("admin") 
			&& !sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_SHOW_UNAUTH))
		{
			$c = new Criteria();
			$c->add(GroupePeer::USER_ID, $user_id);
	
			switch ($sort) {
				case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
				case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
				case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
				case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
				default: $c->addAscendingOrderByColumn(self::NAME); break;
			}
	
			$pager = new sfPropelPager('Groupe', $per_page);
			$pager->setCriteria($c);
			$pager->setPage($page);
			$pager->setPeerMethod('getGroupPagerHomeAccess');
			$pager->setPeerCountMethod("getCountGroupPagerHomeAccess");
			$pager->init();
		}
		else
		{
			$c = new Criteria();
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(self::STATE, self::__STATE_ACTIVE);
	
			if($author_id){
			  $c->add(self::USER_ID, $author_id);
			}elseif(!sfContext::getInstance()->getUser()->hasCredential("admin") && $user_id){
			  $c->add(UserGroupPeer::USER_ID, $user_id);
			  $c->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
			}
	
			switch ($sort) {
				case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
				case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
				case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
				case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
				default: $c->addAscendingOrderByColumn(self::NAME); break;
			}
			
			$pager = new sfPropelPager('Groupe', $per_page);
			$pager->setCriteria($c);
			$pager->setPage($page);
			$pager->setPeerMethod('doSelect');
			$pager->init();
		}
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getGroupPagerHome(Criteria $c, $count = null, $onlyAccess = null)
	{
		if(!$count)
		{
			$max = $c->getLimit();
			$offset = $c->getOffset();
		}

		$sort = $c->getOrderByColumns();
	
		$map = $c->getMap();
		$user_id = $map[GroupePeer::USER_ID]->getValue();
	
		$connection = Propel::getConnection();

		if(!sfContext::getInstance()->getUser()->hasCredential("admin") && $onlyAccess)
		{
			$query = "	SELECT distinct groupe.*
						FROM groupe, customer
						WHERE groupe.customer_id = customer.id
						AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
						AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
						AND groupe.state = ".$connection->quote(self::__STATE_ACTIVE)."
						AND (groupe.free = 1 OR (groupe.free = 0 AND groupe.id IN (SELECT user_group.groupe_id
																FROM user_group
																WHERE user_group.user_id = ".
												$connection->quote(sfContext::getInstance()->getUser()->getId()).")))
						ORDER BY ".$sort[0];
	
			if(!$count)
				$query .= " LIMIT ".$offset.",".$max;
	
			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 
	
			$groups = array();
	
			while ($rs = $statement->fetch())
			{
				$group = new Groupe();
				$group->hydrate($rs);
				$groups[] = serialize($group);
			}
	
			$statement->closeCursor();
			$statement = null;
	
			if(!$count)
				return array_map("unserialize", $groups);
			else
				return count($groups);
		}
		else
		{
			if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
			{
				$query = "SELECT distinct groupe.*
							FROM groupe, user_group, customer
							WHERE groupe.id = user_group.groupe_id
							AND user_group.user_id = ".$connection->quote($user_id);
			}
			else
			{
				$query = "SELECT distinct groupe.*
						FROM groupe, customer
						WHERE 1= 1";
			}
	
			$query .= " AND groupe.customer_id = customer.id
						AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
						AND groupe.state = ".$connection->quote(self::__STATE_ACTIVE)."
						AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());
	
			$query .= " ORDER BY ".$sort[0];
	
			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 
	
			$groups_1 = array();
			while ($rs = $statement->fetch())
			{
				$group = new Groupe();
				$group->hydrate($rs);
				$groups_1[] = serialize($group);
			}
	
			$statement->closeCursor();
			$statement = null;
	
			$groups_2 = array();
	
			if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
			{
				$query = "SELECT distinct groupe.*
							FROM groupe, customer
							WHERE groupe.id NOT IN (SELECT user_group.groupe_id
													FROM user_group
													WHERE user_group.user_id =".$connection->quote($user_id).")
							AND groupe.customer_id = customer.id
							AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
							AND groupe.state = ".$connection->quote(self::__STATE_ACTIVE)."
							AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());
	
				$query .= " ORDER BY ".$sort[0];
	
				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_NUM); 
	
				while ($rs2 = $statement->fetch())
				{
					$group = new Groupe();
					$group->hydrate($rs2);
					$groups_2[] = serialize($group);
				}
	
				$statement->closeCursor();
				$statement = null;
			}
	
			$groups = array_merge($groups_1, $groups_2);
			$results = array_unique($groups);
	
			if(!$count)
				return array_slice(array_map("unserialize", $results), $offset, $max);
			else
				return count($results);
		}
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function preGetUserGroupPager2($user_id)
	{
		$connection = Propel::getConnection();
		$user = UserPeer::retrieveByPk($user_id);
	
		if ($user->getRoleId() <= RolePeer::__ADMIN) {
			$query = "	SELECT groupe.id
						FROM groupe
						WHERE groupe.state = ".self::__STATE_ACTIVE."
						AND ((groupe.customer_id = ".$user->getCustomerId().") OR (groupe.id IN (SELECT user_group.groupe_id
																								FROM user_group
																								WHERE user_group.user_id = ".$user->getId()."
																								AND user_group.state = ".UserGroupPeer::__STATE_ACTIVE.")))";
		}
		else {
			$query = "	SELECT groupe.id
						FROM groupe
						WHERE groupe.state = ".self::__STATE_ACTIVE."
						AND ((groupe.free = 1 AND groupe.customer_id = ".$user->getCustomerId().") OR (groupe.id IN (	SELECT user_group.groupe_id
																																			FROM user_group
																																			WHERE user_group.user_id = ".$user->getId()."
																																			AND user_group.state = ".UserGroupPeer::__STATE_ACTIVE.")))";
		}
		
		$ids = array();
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		
		while($rs = $statement->fetch()) {
			$ids[] = $rs["id"];
		}
		
		$query = "	SELECT unit_group.groupe_id as id
					FROM unit, unit_group, user_unit
					WHERE user_unit.unit_id = unit.id
					AND unit.id = unit_group.unit_id
					AND user_unit.user_id = ".$user->getId();
		
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		
		while($rs = $statement->fetch()) {
			if(!in_array($rs["id"], $ids)) {
				$ids[] = $rs["id"];
			}
		}
	
		return $ids;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see GroupePeer::getAlbumsHaveAccessForUserPager
	 * 
	 * @param unknown $user_id
	 * @param number $page
	 * @param number $per_page
	 * @return sfPropelPager
	 */
	public static function getUserGroupPager2($user_id, $page=1, $per_page=10)
	{
		$c = new Criteria();
		$c->add(self::ID, self::preGetUserGroupPager2($user_id), Criteria::IN);
		$c->addAscendingOrderByColumn(self::NAME);

		$pager = new sfPropelPager('Groupe', $per_page);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
		
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountGroupPagerHome(Criteria $c)
	{
		return self::getGroupPagerHome($c, 1);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getGroupPagerHomeAccess(Criteria $c, $count = null, $onlyAccess = null)
	{
		return self::getGroupPagerHome($c, null, 1);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountGroupPagerHomeAccess(Criteria $c)
	{
		return self::getGroupPagerHome($c, 1, 1);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUserGroupPager($user_id=0, $sort="name_asc", $page=1, $per_page=100, $shared=0)
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
	
		if($shared == 0) 
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		elseif($shared == 1)
		{
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId(), Criteria::NOT_EQUAL);
	
			$c1 = $c->getNewCriterion(UserGroupPeer::EXPIRATION, null);
			$c2 = $c->getNewCriterion(UserGroupPeer::EXPIRATION, date("Y-m-d h:i:s"), Criteria::GREATER_THAN);
	
			$c1->addOr($c2);
			$c->add($c1);
		}
	
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(UserGroupPeer::USER_ID, $user_id);
		$c->addJoin(UserGroupPeer::GROUPE_ID, self::ID);

		switch ($sort) {
			case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			default: $c->addAscendingOrderByColumn(self::NAME); break;
		}

		$pager = new sfPropelPager('Groupe', $per_page);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();

		return $pager;
	}

  /*________________________________________________________________________________________________________________*/
  # search/search
  public static function search($engine, $keyword="", $user_id=0, $limit=1000, $tag_ids=array(), $author_id=0, 
  	$years=0, $sort="date_desc")
  { 
    $f1 = false;
    $f2 = false;

    $c = new Criteria();
	$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(self::STATE, self::__STATE_ACTIVE);

    if(sizeof($tag_ids)){
      $query = " (
        SELECT groupe.id
        FROM customer, groupe
        INNER JOIN file_tag ON ( file_tag.FILE_ID = groupe.ID AND file_tag.TYPE = '1')
        WHERE file_tag.TAG_ID IN (".join(",", $tag_ids).")
		AND groupe.customer_id = customer.id
		AND customer.state = ".CustomerPeer::__STATE_ACTIVE."
		AND groupe.CUSTOMER_ID = ".sfContext::getInstance()->getUser()->getCustomerId()." GROUP BY groupe.ID
        HAVING COUNT( groupe.id )=".sizeof($tag_ids)."
      )";
      $c->addAlias('f1', $query);
      $c->addJoin(self::ID, "f1.id", Criteria::LEFT_JOIN);
      $f1 = true;
    }
    
    sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
    
    if($keyword && $keyword != __("search") && $keyword != __("Search"))
    {
	  $engine->setMode(SPH_MATCH_EXTENDED);
	  $engine->setIndex("groups");
	  $ids = $engine->search(utf8_decode($keyword));

	  if(count($ids) > 0 && is_array($ids))
	  {
		$query = "(SELECT groupe.ID
					FROM groupe
					WHERE groupe.ID IN (".implode(",", $ids)."))";
	  }
	  else
	  {
		$query = "(SELECT groupe.ID
					FROM groupe
					WHERE groupe.ID IN (null))";
	  }
	  
	  $c->addAlias('f2', $query);
	  $c->addJoin(self::ID, "f2.id", Criteria::LEFT_JOIN);
	  $f2 = true;
    }
    
    if($f1 && $f2){
      $c->add(self::ID, "(f1.id && f2.id)=1", Criteria::CUSTOM);
    }elseif($f1){
      $c->add(self::ID, "f1.id", Criteria::CUSTOM);
    }elseif($f2){
      $c->add(self::ID, "f2.id", Criteria::CUSTOM);
    }
    
    if(!sfContext::getInstance()->getUser()->hasCredential("admin")){
      if($author_id){
        $c->add(self::USER_ID, sfContext::getInstance()->getUser()->getId());
      }elseif($user_id){
        $c->add(UserGroupPeer::USER_ID, $user_id);
        $c->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
      }
    }
    
    sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
    if(sizeof($years) && $years[0] && $years[1]){
      $c1 = $c->getNewCriterion(self::CREATED_AT, $years[0]."-01-01", Criteria::GREATER_EQUAL);
      $c2 = $c->getNewCriterion(self::CREATED_AT, $years[1]."-12-31", Criteria::LESS_EQUAL);
      
      $c1->addAnd($c2);
      $c->add($c1);
    }
    
    switch ($sort) {
      default: ;
    	case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
    	case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
    	case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
    	case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
    }
    
    $c->setDistinct();
    $c->setLimit($limit);
    
    return self::doSelect($c);
  }

	/*________________________________________________________________________________________________________________*/
	public static function getGroupsInTree()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("Url"));
		$group_array = Array();

		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			$connection = Propel::getConnection();

			$query = "	SELECT groupe.*
						FROM groupe, customer
						WHERE customer.id = groupe.customer_id
						AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
						AND groupe.state = ".$connection->quote(self::__STATE_ACTIVE)."
						AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while($rs = $statement->fetch())
			{
				$group = new Groupe();
				$group->hydrate($rs);

				$role = null;

				if($group->getFree())
				{
					$role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $group->getId());

					if(!$role)
						$role = $group->getFreeCredential();
				}
				elseif(!$group->getFree())
					$role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $group->getId());

				if($role)
				{
					$temp = array();
					$temp["key"] = $group->getId();
					$temp["title"] = $group->getName();
					$temp["tooltip"] = $group->getDescription();
					$temp["isFolder"] = false;
					$temp["isLazy"] = true;
					$temp["addClass"] = "node-group";
					$temp["expand"] = true;
					$temp["children"] = FolderPeer::getFoldersInTree($group->getId());
					$temp["href"] = urldecode(url_for("@group_show?session=start&id=".$group->getId()));
					$temp["right"] = $role;
					$temp["icon"] = "../../images/dynatree/group.gif";

					array_push($group_array, $temp);
				}
			}

			$statement->closeCursor();
			$statement = null;
		}
		else
		{
			$c = new Criteria();
			$c->add(self::STATE, self::__STATE_ACTIVE);
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$groups = self::doSelect($c);

			foreach($groups as $group)
			{
				$temp = array();
				$temp["key"] = $group->getId();
				$temp["title"] = $group->getName();
				$temp["tooltip"] = $group->getDescription();
				$temp["isFolder"] = false;
				$temp["isLazy"] = true;
				$temp["addClass"] = "node-group";
				$temp["expand"] = true;
				$temp["children"] = FolderPeer::getFoldersInTree($group->getId());
				$temp["href"] = urldecode(url_for("@group_show?session=start&id=".$group->getId()));
				$temp["right"] = 0;
				$temp["icon"] = "../../images/dynatree/group.gif";

				array_push($group_array, $temp);
			}
		}

		return $group_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @return number
	 */
	public static function getFirstGroup()
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			$c->add(UserGroupPeer::USER_ID, sfContext::getInstance()->getUser()->getId());
			$c->add(UserGroupPeer::ROLE, array(RolePeer::__ADMIN, RolePeer::__CONTRIB), Criteria::IN);
			$c->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
		}

		$c->addAscendingOrderByColumn(self::NAME);
		$groups = self::doSelect($c);

		if(!sfContext::getInstance()->getUser()->hasCredential("admin") && empty($groups))
		{
			$c = new Criteria();
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(self::STATE, self::__STATE_ACTIVE);
			$c->add(self::FREE, 1);
			$c->add(self::FREE_CREDENTIAL, RolePeer::__CONTRIB);

			$groups = self::doSelect($c);
		}

		foreach($groups as $group)
		{
			$c = new Criteria();
			$c->add(FolderPeer::GROUPE_ID, $group->getId());
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
			$c->addJoin(self::ID, FolderPeer::GROUPE_ID);
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(self::STATE, self::__STATE_ACTIVE);

			$folders = FolderPeer::doSelect($c);

			foreach ($folders as $folder)
			{
				$passe = false;

				if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
				{
					if($role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $group->getId()))
					{
						if($role <= RolePeer::__CONTRIB)
							return $folder->getId();
					}
				}
				else
					return $folder->getId();
			}
		}

		return -1;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCount()
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::TYPE, self::__TYPE_PROD);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		return self::doCount($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByCustomerId($customer_id)
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, $customer_id);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getGroupsInArray2($user_id=0, $group_id=0)
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		if(!sfContext::getInstance()->getUser()->hasCredential("admin") && $user_id)
		{
			$c->add(UserGroupPeer::USER_ID, $user_id);
			$c->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
		}

		if($group_id)
			$c->add(self::ID, $group_id, Criteria::NOT_EQUAL);

		$c->addAscendingOrderByColumn(self::NAME);
		$groups = self::doSelect($c);

		$groups_array = array();

		foreach ($groups as $group)
			$groups_array[$group->getId()] = $group;

		if(!sfContext::getInstance()->getUser()->hasCredential("admin") && $user_id)
		{
			$c = new Criteria();
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(self::STATE, self::__STATE_ACTIVE);
			$c->add(self::FREE, 1);
			$c->add(self::FREE_CREDENTIAL, RolePeer::__READER, Criteria::GREATER_EQUAL);

			$groups = self::doSelect($c);

			foreach ($groups as $group)
				$groups_array[$group->getId()] = $group;
		}

		return $groups_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountMax()
	{
		$connection = Propel::getConnection();

		$query = "	SELECT count(groupe.id)
					FROM groupe";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $rs[0][0];
	}

	/*________________________________________________________________________________________________________________*/
	public static function searchEngine($keyword = null, $userId = null, $limit = 1000, $tagIds = Array(), 
			$authorId = null, $years = Array(), $sort = "date_desc")
	{
		function processGroupeIndex($operator, $array)
		{
			$return = Array();

			switch($operator)
			{
				case '':
				case "OR":
				{
					foreach($array as $values)
						$return = array_merge($return, $values);
				}
				break;

				case "AND":
				{
					$oldValue = Array();
					foreach($array as $values)
					{
						if(!empty($oldValue))
							$return = array_merge($return, array_intersect($oldValue, $values));

						$oldValue = $values;
					}
				}
				break;
			}

			return array_unique($return);
		}

		$connection = Propel::getConnection();

		$currentUserId = sfContext::getInstance()->getUser()->getId();
		$customerId = sfContext::getInstance()->getUser()->getCustomerId();

		$ids = Array();
		$groups = Array();

		if(!empty($tagIds))
		{
			$query = "	SELECT distinct groupe.id
						FROM file_tag, tag, groupe
						WHERE groupe.id = file_tag.file_id
						AND file_tag.tag_id = tag.id
						AND file_tag.type = ".FileTagPeer::__TYPE_GROUP."
						AND groupe.customer_id = ".$customerId."
						AND groupe.state = ".self::__STATE_ACTIVE."
						AND tag.customer_id = ".$customerId."
						AND tag.id IN (".implode($tagIds, ",").")";

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC);

			while($rs = $statement->fetch())
			{
				if(!in_array($rs["id"], $ids))
					$ids[] = $rs["id"];
			}

			$statement->closeCursor();
			$statement = null;

			if(empty($ids))
				$ids[] = -1;
		}

		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$searchExpression = Array();
			$searchOperator = "";

			if(substr($keyword, 0, 1) == '"' && substr($keyword, -1) == '"')
				$searchExpression[] = $keyword;
			else
			{
				$temp = explode(" OR ", $keyword);
				if(count($temp) > 1)
				{
					$searchOperator = "OR";

					foreach($temp as $t)
					{
						if(!empty($t))
						{
							if(substr($t, 0, 1) == '*')
								$t = "%".$t;
							elseif(substr($t, -1) == '*')
								$t = $t."%";
							else
								$t = "%".$t."%";

							$searchExpression[] = $t;
						}
					}
				}

				if(empty($searchExpression))
				{
					$temp = explode(" AND ", $keyword);
					if(count($temp) > 1)
					{
						$searchOperator = "AND";

						foreach($temp as $t)
						{
							if(!empty($t))
							{
								if(substr($t, 0, 1) == '*')
									$t = "%".$t;
								elseif(substr($t, -1) == '*')
									$t = $t."%";
								else
									$t = "%".$t."%";

								$searchExpression[] = $t;
							}
						}
					}
				}

				if(empty($searchExpression))
				{
					$temp = explode(" ", $keyword);
					if(count($temp) > 1)
					{
						$searchOperator = "AND";

						foreach($temp as $t)
						{
							if(!empty($t))
							{
								if(substr($t, 0, 1) == '*')
									$t = "%".$t;
								elseif(substr($t, -1) == '*')
									$t = $t."%";
								else
									$t = "%".$t."%";

								$searchExpression[] = $t;
							}
						}
					}
				}

				if(empty($searchExpression))
				{
					$searchOperator = "";

					if(substr($keyword, 0, 1) == '*')
						$keyword = "%".$keyword;
					elseif(substr($keyword, -1) == '*')
						$keyword = $keyword."%";
					else
						$keyword = "%".$keyword."%";

					$searchExpression[] = $keyword;
				}
			}

			/**
				Search tags.

				Search on TAG.TITLE
			**/
			$temp = Array();
			foreach($searchExpression as $searchTerm)
			{
				$temp[$searchTerm] = Array();

				$searchTag = str_replace("%", "", $searchTerm);

				$query = "	SELECT distinct groupe.id
							FROM file_tag, tag, groupe
							WHERE groupe.id = file_tag.file_id
							AND file_tag.tag_id = tag.id
							AND file_tag.type = ".FileTagPeer::__TYPE_GROUP."
							AND groupe.customer_id = ".$customerId."
							AND groupe.state = ".self::__STATE_ACTIVE."
							AND tag.customer_id = ".$customerId."
							AND tag.title = '".$searchTag."'";

				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_ASSOC);

				while($rs = $statement->fetch())
				{
					if(!in_array($rs["id"], $temp[$searchTerm]))
						$temp[$searchTerm][] = $rs["id"];
				}

				$statement->closeCursor();
				$statement = null;
			}

			$idsTags = processGroupeIndex($searchOperator, $temp);

			/**
				Search folder.

				Search on GROUPE.NAME
				Search on GROUPE.DESCRIPTION
				Search on USER.FIRSTNAME
				Search on USER.LASTNAME
				Search on USER.EMAIL
			**/
			$temp = Array();
			foreach($searchExpression as $searchTerm)
			{
				$temp[$searchTerm] = Array();

				$query = "	SELECT distinct groupe.id
							FROM groupe, user
							WHERE groupe.user_id = user.id
							AND groupe.customer_id = ".$customerId."
							AND groupe.state = ".self::__STATE_ACTIVE."
							AND
								(
									(groupe.name LIKE '".$searchTerm."')
									OR
									(groupe.description LIKE '".$searchTerm."')
									OR
									(user.firstname LIKE '".$searchTerm."')
									OR
									(user.lastname LIKE '".$searchTerm."')
									OR
									(user.email LIKE '".$searchTerm."')
								)
							";

				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_ASSOC);

				while($rs = $statement->fetch())
				{
					if(!in_array($rs["id"], $temp[$searchTerm]))
						$temp[$searchTerm][] = $rs["id"];
				}

				$statement->closeCursor();
				$statement = null;
			}

			$idsGroups = processGroupeIndex($searchOperator, $temp);

			$ids = array_merge($ids, $idsTags, $idsGroups);

			if(empty($ids))
				$ids[] = -1;
		}

		$query = "	SELECT distinct groupe.*
					FROM groupe
					WHERE groupe.state = ".self::__STATE_ACTIVE."
					AND groupe.customer_id = ".$customerId;

		if (!empty($ids)) {
			$tempIds = array();

			$groups_ = UserGroupPeer::getGroupIds($currentUserId, "", true);
			$groups2 = GroupePeer::getGroupsInArray2($currentUserId);

			foreach($groups_ as $group) {
				if (in_array($group, $ids)) {
					$tempIds[] = $group;
				}
			}

			foreach($groups2 as $group) {
				if (in_array($group->getId(), $ids)) {
					$tempIds[] = $group->getId();
				}
			}

			if (empty($tempIds)) {
				$tempIds[] = -1;
			}

			$query .= " AND groupe.id IN (".implode($tempIds, ",").")";
		}

		if(!empty($userId))
			$query .= " AND groupe.user_id = ".$connection->quote($userId);
		elseif(!empty($authorId))
			$query .= " AND groupe.user_id = ".$connection->quote($currentUserId);

		if(!empty($years) && $years[0] && $years[1])
			$query .= " AND groupe.created_at >= '".$years[0]."-01-01' AND groupe.created_at <= '".$years[1]."-12-31'";

		switch ($sort)
		{
			default: ;
			case "name_asc":
				$query .= " ORDER BY groupe.name ASC";
			break;

			case "name_desc":
				$query .= " ORDER BY groupe.name DESC";
			break;

			case "date_asc":
				$query .= " ORDER BY groupe.created_at ASC";
			break;

			case "date_desc": 
				$query .= " ORDER BY groupe.created_at DESC";
			break;
		}

		if(!empty($limit))
			$query .= " LIMIT 0,".$limit;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);

		while($rs = $statement->fetch())
		{
			$groupe = new Groupe();
			$groupe->hydrate($rs);
			$groups[] = $groupe;
		}

		return $groups;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByUrl($url)
	{
		$c = new Criteria();
		$c->add(self::URL, $url);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getHomeGroups($limit, $offset, $sort = null)
	{
		$connection = Propel::getConnection();

		switch($sort) {
			case "name_asc": $sort = "groupe.name ASC"; break;
			case "name_desc": $sort = "groupe.name DESC"; break;
			case "creation_asc": $sort = "groupe.created_at ASC"; break;
			case "creation_desc": $sort = "groupe.created_at DESC"; break;
			case "activity_asc": $sort = "lastActivity ASC, groupe.name ASC"; break;
			case "activity_desc": $sort = "lastActivity DESC, groupe.name ASC"; break;
		}

		if(!sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_SHOW_UNAUTH))
		{
			if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
			{
				
					$query = "	SELECT distinct groupe.*, max(file.created_at) as lastActivity
								FROM customer, groupe LEFT JOIN file ON groupe.id = file.groupe_id
								WHERE groupe.customer_id = customer.id
								AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
								AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
								AND (groupe.free = 1 OR (groupe.free = 0 AND groupe.id IN (SELECT user_group.groupe_id
																		FROM user_group
																		WHERE user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")))
								GROUP BY groupe.id";

					if(!empty($sort)) {
						$query .= " ORDER BY ".$sort;
					}
	
					$count = "	SELECT count(distinct groupe.id) as count
								FROM groupe, customer
								WHERE groupe.customer_id = customer.id
								AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
								AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
								AND (groupe.free = 1 OR (groupe.free = 0 AND groupe.id IN (SELECT user_group.groupe_id
																		FROM user_group
																		WHERE user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")))";
			}
			else
			{
				
					$query = "	SELECT distinct groupe.*, max(file.created_at) as lastActivity
								FROM customer, groupe LEFT JOIN file ON groupe.id = file.groupe_id
								WHERE groupe.customer_id = customer.id
								AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
								AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
								GROUP BY groupe.id";

					if(!empty($sort)) {
						$query .= " ORDER BY ".$sort;
					}

					$count = "	SELECT count(distinct groupe.id) as count
								FROM groupe, customer
								WHERE groupe.customer_id = customer.id
								AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
								AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);
			}
		}
		else
		{
			
				$query = "	SELECT distinct groupe.*, max(file.created_at) as lastActivity
							FROM customer, groupe LEFT JOIN file ON groupe.id = file.groupe_id
							WHERE groupe.customer_id = customer.id
							AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
							AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
							AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
							GROUP BY groupe.id";

				if(!empty($sort)) {
					$query .= " ORDER BY ".$sort;
				}

				$count = "	SELECT count(distinct groupe.id) as count
							FROM groupe, customer
							WHERE groupe.customer_id = customer.id
							AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
							AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
							AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

		}

		if($limit != "all") {
			$query .= " LIMIT ".$offset.", ".$limit;
		}

		$statement = $connection->query($count);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$groups = Array();

		while($rs = $statement->fetch())
		{
			$group = new Groupe();
			$group->hydrate($rs);
			$groups[] = serialize($group);
		}

		$statement->closeCursor();
		$statement = null;

		return Array("groups" => array_map("unserialize", $groups), "count" => $result[0]["count"]);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getHomeGroupsShared($limit, $offset, $sort = null)
	{
		switch($sort) {
			case "name_asc": $sort = "groupe.name ASC"; break;
			case "name_desc": $sort = "groupe.name DESC"; break;
			case "creation_asc": $sort = "groupe.created_at ASC"; break;
			case "creation_desc": $sort = "groupe.created_at DESC"; break;
			case "activity_asc": $sort = "lastActivity ASC, groupe.name ASC"; break;
			case "activity_desc": $sort = "lastActivity DESC, groupe.name ASC"; break;
		}

		$connection = Propel::getConnection();

		$query = "	SELECT distinct groupe.*, max(file.created_at) as lastActivity
					FROM customer, user_group, groupe LEFT JOIN file ON groupe.id = file.groupe_id
					WHERE groupe.customer_id = customer.id
					AND groupe.id = user_group.groupe_id
					AND user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
					AND (user_group.expiration IS NULL OR user_group.expiration > ".$connection->quote(date("Y-m-d h:i:s")).")
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND customer.id != ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					GROUP BY groupe.id";

		if(!empty($sort)) {
			$query .= " ORDER BY ".$sort;
		}

		$count = "	SELECT count(distinct groupe.id) as count
					FROM groupe, customer, user_group
					WHERE groupe.customer_id = customer.id
					AND groupe.id = user_group.groupe_id
					AND user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
					AND (user_group.expiration IS NULL OR user_group.expiration > ".$connection->quote(date("Y-m-d h:i:s")).")
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND customer.id != ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					ORDER BY groupe.name ASC";

		if($limit != "all") {
			$query .= " LIMIT ".$offset.", ".$limit;
		}

		$statement = $connection->query($count);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$groups = Array();

		while($rs = $statement->fetch())
		{
			$group = new Groupe();
			$group->hydrate($rs);
			$groups[] = serialize($group);
		}

		$statement->closeCursor();
		$statement = null;

		return Array("groups" => array_map("unserialize", $groups), "count" => $result[0]["count"]);
	}


	/*________________________________________________________________________________________________________________*/
	public static function getGroupsInArray3($user_id=0, $group_id=0)
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		if(!sfContext::getInstance()->getUser()->hasCredential("admin") && $user_id)
		{
			$c->add(UserGroupPeer::USER_ID, $user_id);
			$c->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
		}

		if($group_id)
			$c->add(self::ID, $group_id, Criteria::NOT_EQUAL);

		$c->addAscendingOrderByColumn(self::NAME);
		$groups = self::doSelect($c);

		$groups_array = array();

		foreach ($groups as $group)
			$groups_array[$group->getId()] = $group;

		if(!sfContext::getInstance()->getUser()->hasCredential("admin") && $user_id)
		{
			$c = new Criteria();
			$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(self::STATE, self::__STATE_ACTIVE);
			$c->add(self::FREE, 1);
			$c->add(self::FREE_CREDENTIAL, RolePeer::__CONTRIB);

			$groups = self::doSelect($c);

			foreach ($groups as $group)
				$groups_array[$group->getId()] = $group;
		}

		return $groups_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountPrivateGroupPagerHome(Criteria $c)
	{
		return self::getGroupPrivatePagerHome($c, 1);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getGroupPrivatePagerHome(Criteria $c, $count = null)
	{
		if(!$count)
		{
			$max = $c->getLimit();
			$offset = $c->getOffset();
		}

		$sort = $c->getOrderByColumns();

		$map = $c->getMap();
		$user_id = $map[GroupePeer::USER_ID]->getValue();

		$connection = Propel::getConnection();

		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			$query = "	SELECT distinct groupe.*
						FROM groupe, customer
						WHERE groupe.customer_id = customer.id
						AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
						AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
						AND groupe.state = ".$connection->quote(self::__STATE_ACTIVE)."
						AND groupe.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
						ORDER BY ".$sort[0];
		}
		else
		{
			$query = "	SELECT distinct groupe.*
						FROM groupe, customer
						WHERE groupe.customer_id = customer.id
						AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
						AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
						AND groupe.state = ".$connection->quote(self::__STATE_ACTIVE)."
						ORDER BY ".$sort[0];
		}

		if(!$count)
			$query .= " LIMIT ".$offset.",".$max;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$groups = array();

		while ($rs = $statement->fetch())
		{
			$group = new Groupe();
			$group->hydrate($rs);
			$groups[] = serialize($group);
		}

		$statement->closeCursor();
		$statement = null;

		if(!$count)
			return array_map("unserialize", $groups);
		else
			return count($groups);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getHomeGroupsPrivate($limit, $offset, $sort = null)
	{
		$connection = Propel::getConnection();

		switch($sort) {
			case "name_asc": $sort = "groupe.name ASC"; break;
			case "name_desc": $sort = "groupe.name DESC"; break;
			case "creation_asc": $sort = "groupe.created_at ASC"; break;
			case "creation_desc": $sort = "groupe.created_at DESC"; break;
			case "activity_asc": $sort = "lastActivity ASC, groupe.name ASC"; break;
			case "activity_desc": $sort = "lastActivity DESC, groupe.name ASC"; break;
		}

		$query = "	SELECT distinct groupe.*, max(file.created_at) as lastActivity
					FROM customer, groupe LEFT JOIN file ON groupe.id = file.groupe_id
					WHERE groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					AND groupe.user_id != ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
					GROUP BY groupe.id";

		if(!empty($sort)) {
			$query .= " ORDER BY ".$sort;
		}

		$count = "	SELECT count(distinct groupe.id) as count
					FROM groupe, customer
					WHERE groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					AND groupe.user_id != ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
					ORDER BY groupe.name ASC";

		if($limit != "all") {
			$query .= " LIMIT ".$offset.", ".$limit;
		}

		$statement = $connection->query($count);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$groups = Array();

		while($rs = $statement->fetch())
		{
			$group = new Groupe();
			$group->hydrate($rs);
			$groups[] = serialize($group);
		}

		$statement->closeCursor();
		$statement = null;

		return Array("groups" => array_map("unserialize", $groups), "count" => $result[0]["count"]);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getCountHomeGroupsShared()
	{
		$connection = Propel::getConnection();
		$count = "	SELECT count(distinct groupe.id) as count
					FROM groupe, customer, user_group
					WHERE groupe.customer_id = customer.id
					AND groupe.id = user_group.groupe_id
					AND user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
					AND (user_group.expiration IS NULL OR user_group.expiration > ".$connection->quote(date("Y-m-d h:i:s")).")
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND customer.id != ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					ORDER BY groupe.name ASC";
		$statement = $connection->query($count);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $result[0]["count"];
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getCountHomeGroupsPrivate()
	{
		$connection = Propel::getConnection();
	
		$count = "	SELECT count(distinct groupe.id) as count
					FROM groupe, customer
					WHERE groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					AND groupe.user_id != ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
					ORDER BY groupe.name ASC";

		$statement = $connection->query($count);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		return $result[0]["count"];
	}
	
	/*________________________________________________________________________________________________________________*/

	/**
	 * @deprecated
	 */
	public static function getCountHomeGroups()
	{
		$connection = Propel::getConnection();

		if(!sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_SHOW_UNAUTH))
		{
			if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
			{
				
					$count = "	SELECT count(distinct groupe.id) as count
								FROM groupe, customer
								WHERE groupe.customer_id = customer.id
								AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
								AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
								AND (groupe.free = 1 OR (groupe.free = 0 AND groupe.id IN (SELECT user_group.groupe_id
																		FROM user_group
																		WHERE user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")))";

			}
			else
			{
				
					$count = "	SELECT count(distinct groupe.id) as count
								FROM groupe, customer
								WHERE groupe.customer_id = customer.id
								AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
								AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

			}
		}
		else
		{
			
				$count = "	SELECT count(distinct groupe.id) as count
							FROM groupe, customer
							WHERE groupe.customer_id = customer.id
							AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
							AND customer.id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
							AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

		}

		$statement = $connection->query($count);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		return $result[0]["count"];
	}

	/*________________________________________________________________________________________________________________*/
	public static function getGroupsNoRightPager($user_id, $page = 1, $per_page=10)
	{
		$c = new Criteria();
		$c->add(self::ID, array_keys(self::getGroupsNoRight($user_id)), Criteria::IN);
		$c->addAscendingOrderByColumn(self::NAME);

		$pager = new sfPropelPager('Groupe', $per_page);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();

		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getGroupsWithRole($user_id, $role, $limit = 100)
	{
		$user = UserPeer::retrieveByPKNoCustomer($user_id);

		$groupsCriteria = null;
		$albumsCriteria = new Criteria();

		if ($user->getRoleId() > RolePeer::__ADMIN) {
			switch ($role) {
				case RolePeer::__ADMIN:
					$albumsCriteria->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
					$albumsCriteria->add(UserGroupPeer::USER_ID, $user->getId());
					$albumsCriteria->add(UserGroupPeer::ROLE, $role);
					$albumsCriteria->add(self::CUSTOMER_ID, $user->getCustomerId());
					$albumsCriteria->add(self::STATE, self::__STATE_ACTIVE);
				break;

				case RolePeer::__CONTRIB:
				case RolePeer::__READER:
					$albumsCriteria->add(self::CUSTOMER_ID, $user->getCustomerId());
					$albumsCriteria->add(self::STATE, self::__STATE_ACTIVE);

					$criteriaInclude = new Criteria();
					$criteriaInclude->add(UserGroupPeer::USER_ID, $user->getId());
					$criteriaInclude->add(UserGroupPeer::ROLE, $role);

					CriteriaUtils::setSelectColumn($criteriaInclude, UserGroupPeer::GROUPE_ID);

					$subQuery = "(".self::FREE." = ".((int)true)." AND ".self::FREE_CREDENTIAL." = ".$role.")";
					$subQuery .= " OR (".self::FREE." = ".((int)false);
					$subQuery .= " AND ".self::ID." IN (".CriteriaUtils::buidSqlFromCriteria($criteriaInclude)."))";

					$albumsCriteria->add(self::ID, $subQuery, Criteria::CUSTOM);
				break;
			}

			$groupsCriteria = new Criteria();
			$groupsCriteria->addJoin(UserUnitPeer::UNIT_ID, UnitPeer::ID);
			$groupsCriteria->addJoin(UnitPeer::ID, UnitGroupPeer::UNIT_ID);
			$groupsCriteria->addJoin(UnitGroupPeer::GROUPE_ID, self::ID);
			$groupsCriteria->add(UnitPeer::CUSTOMER_ID, $user->getCustomerId());
			$groupsCriteria->add(UserUnitPeer::USER_ID, $user->getId());
			$groupsCriteria->add(UnitGroupPeer::ROLE, $role);
			$groupsCriteria->add(self::CUSTOMER_ID, $user->getCustomerId());
			$groupsCriteria->add(self::STATE, self::__STATE_ACTIVE);

			CriteriaUtils::setSelectColumn($groupsCriteria, self::ID);
		}
		else {

			$albumsCriteria->add(self::CUSTOMER_ID, $user->getCustomerId());
			$albumsCriteria->add(self::STATE, self::__STATE_ACTIVE);
		}

		CriteriaUtils::setSelectColumn($albumsCriteria, self::ID);

		$sharedCriteria = new Criteria();
		$sharedCriteria->addJoin(UserGroupPeer::GROUPE_ID, self::ID);
		$sharedCriteria->add(UserGroupPeer::ROLE, $role);
		$sharedCriteria->add(UserGroupPeer::USER_ID, $user_id);
		$sharedCriteria->add(self::CUSTOMER_ID, $user->getCustomerId(), Criteria::NOT_EQUAL);
		$sharedCriteria->add(self::STATE, self::__STATE_ACTIVE);

		CriteriaUtils::setSelectColumn($sharedCriteria, self::ID);

		$criteria = new Criteria();
		$criteria->setDistinct();

		$subQuery = self::ID." IN (".CriteriaUtils::buidSqlFromCriteria($albumsCriteria).")";
		$subQuery .= " OR ".self::ID." IN (".CriteriaUtils::buidSqlFromCriteria($sharedCriteria).")";

		if ($groupsCriteria) {
			$subQuery .= " OR ".self::ID." IN (".CriteriaUtils::buidSqlFromCriteria($groupsCriteria).")";
		}

		$criteria->add(self::ID, $subQuery, Criteria::CUSTOM);
		$criteria->addAscendingOrderByColumn(self::NAME);

		if ($limit) {
			$criteria->setLimit($limit);
		}

		return self::doSelect($criteria);
	}
}
