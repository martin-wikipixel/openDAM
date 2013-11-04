<?php

/**
 * Subclass for performing query and update operations on the 'folder' table.
 *
 * 
 *
 * @package lib.model
 */ 
class FolderPeer extends BaseFolderPeer
{
	const __STATE_ACTIVE = 1;
	const __STATE_DELETE = 2;

	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		$state = isset($params["state"]) ? (int) $params["state"] : self::__STATE_ACTIVE;
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		$albumId = isset($params["albumId"]) ? (int)$params["albumId"] : 0;
		$userId = isset($params["userId"]) ? (int)$params["userId"] : 0;
		$isFree = isset($params["isFree"]) ? (bool)$params["isFree"] : null;

		$criteria = new Criteria();
		
		if ($state) {
			$criteria->add(self::STATE, $state);
		}
		
		if ($userId) {
			$criteria->add(self::USER_ID, $userId);
		}
		
		if ($albumId) {
			$criteria->add(self::GROUPE_ID, $albumId);
		}
		
		if ($isFree !== null) {
			$criteria->add(self::FREE, $isFree);
		}

		// pour lister les dossier d'un client
		if ($customerId) {
			$criteria->addJoin(self::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
			$criteria->add(UserPeer::CUSTOMER_ID, $customerId);
		}

		CriteriaUtils::buildOrderBy($criteria, $orderBy);
		
		if ($limit) {
			$criteria->setLimit($limit);
		}
		
		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	public static function findBy(array $params = array(), array $orderBy = array())
	{
		return self::doSelect(self::doCriteria($params, $orderBy));
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
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		$userStates = isset($params["userStates"]) ? (array)$params["userStates"] : array(UserPeer::__STATE_ACTIVE,
				UserPeer::__STATE_SUSPEND);
		$albumId = isset($params["albumId"]) ? (int)$params["albumId"] : 0;
		$folderId = isset($params["folderId"]) ? (int)$params["folderId"] : 0;
		$state = isset($params["state"]) ? $params["state"] : "";
		$roleState = isset($params["roleState"]) ? $params["roleState"] : "";

		$currentFolder = self::retrieveByPK($folderId);
		$currentAlbum = GroupePeer::retrieveByPK($albumId);
		$subQueryState = null;

		if (!$currentFolder && !$currentAlbum) {
			return false;
		}

		$criteria = new Criteria();

		if ($letter) {
			$criteria->add(UserPeer::EMAIL, $letter.'%', Criteria::LIKE);
		}

		if (count($userStates)) {
			$criteria->add(UserPeer::STATE, $userStates, Criteria::IN);
		}

		if ($keyword) {
			$c1 = $criteria->getNewCriterion(UserPeer::USERNAME, "%".$keyword."%", Criteria::LIKE);
			$c2 = $criteria->getNewCriterion(UserPeer::EMAIL, "%".$keyword."%", Criteria::LIKE);

			$c1->addOr($c2);
			$criteria->add($c1);
		}

		if ($customerId) {
			$criteria->add(UserPeer::CUSTOMER_ID, $customerId);
		}

		if (!$currentFolder->getSubfolderId()) {
			if ($roleState == "active" || !$roleState) {
				if ($state) {
					if (($currentFolder->getFree() && $state == "noAccess") ||
					(!$currentFolder->getFree() && $state == "access")) {
						$operator = "IN";
						$join = "OR";
					}
					else {
						$operator = "NOT IN";
						$join = "AND";
					}

					// Exception user
					$criteriaUserFolder = new Criteria();
					$criteriaUserFolder->addJoin(UserFolderPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
					$criteriaUserFolder->add(UserFolderPeer::FOLDER_ID, $currentFolder->getId());

					// Exception group
					$criteriaUnitFolder = new Criteria();
					$criteriaUnitFolder->addJoin(UnitFolderPeer::UNIT_ID,
							UserUnitPeer::UNIT_ID, Criteria::INNER_JOIN);
					$criteriaUnitFolder->addJoin(UserUnitPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
					$criteriaUnitFolder->add(UnitFolderPeer::FOLDER_ID, $currentFolder->getId());
					$criteriaUnitFolder->add(UserPeer::CUSTOMER_ID, $customerId);

					if ($userStates) {
						$criteriaUserFolder->add(UserPeer::STATE, $userStates, Criteria::IN);
						$criteriaUnitFolder->add(UserPeer::STATE, $userStates, Criteria::IN);
					}

					CriteriaUtils::setSelectColumn($criteriaUserFolder, UserPeer::ID);

					CriteriaUtils::setSelectColumn($criteriaUnitFolder, UserPeer::ID);

					$subQuery = UserPeer::ID." ".$operator."(";
					$subQuery .= CriteriaUtils::buidSqlFromCriteria($criteriaUserFolder).")";
					$subQuery .= " ".$join." ".UserPeer::ID." ".$operator."(";
					$subQuery .= CriteriaUtils::buidSqlFromCriteria($criteriaUnitFolder).")";

					/*if ($currentAlbum->getFree()) {
						$criteria->add(UserPeer::ID, $subQuery, Criteria::CUSTOM);
					}
					else {*/
						$subQueryState = $subQuery;
					// }
				}

				if (!$currentAlbum->getFree()) {
					// Exception user
					$criteriaUserGroup = new Criteria();
					$criteriaUserGroup->addJoin(UserGroupPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
					$criteriaUserGroup->add(UserGroupPeer::GROUPE_ID, $currentAlbum->getId());
					$criteriaUserGroup->add(UserPeer::CUSTOMER_ID, $customerId);

					// Exception group
					$criteriaUnitGroup = new Criteria();
					$criteriaUnitGroup->addJoin(UnitGroupPeer::UNIT_ID, UserUnitPeer::UNIT_ID, Criteria::INNER_JOIN);
					$criteriaUnitGroup->addJoin(UserUnitPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
					$criteriaUnitGroup->add(UnitGroupPeer::GROUPE_ID, $currentAlbum->getId());
					$criteriaUnitGroup->add(UserPeer::CUSTOMER_ID, $customerId);

					if ($userStates) {
						$criteriaUserGroup->add(UserPeer::STATE, $userStates, Criteria::IN);
						$criteriaUnitGroup->add(UserPeer::STATE, $userStates, Criteria::IN);
					}

					if ($subQueryState) {
						$criteriaUserGroup->add(UserPeer::ID, $subQueryState, Criteria::CUSTOM);
						$criteriaUnitGroup->add(UserPeer::ID, $subQueryState, Criteria::CUSTOM);
					}

					CriteriaUtils::setSelectColumn($criteriaUserGroup, UserPeer::ID);

					CriteriaUtils::setSelectColumn($criteriaUnitGroup, UserPeer::ID);

					$subQuery = UserPeer::ID." IN (";
					$subQuery .= CriteriaUtils::buidSqlFromCriteria($criteriaUserGroup).")";
					$subQuery .= " OR ".UserPeer::ID." IN (";
					$subQuery .= CriteriaUtils::buidSqlFromCriteria($criteriaUnitGroup).")";

					$subQueryState = $subQuery;
				}
			}

			$criteriaRequest = new Criteria();
			$criteriaRequest->addJoin(UserPeer::ID, RequestPeer::USER_ID);
			$criteriaRequest->add(RequestPeer::FOLDER_ID, $currentFolder->getId());
			$criteriaRequest->add(RequestPeer::IS_REQUEST, true);

			if (count($userStates)) {
				$criteriaRequest->add(UserPeer::STATE, $userStates, Criteria::IN);
			}

			CriteriaUtils::setSelectColumn($criteriaRequest, RequestPeer::USER_ID);

			if ($roleState == "active") {
				$operator = "NOT IN";
				$join = "AND";
			}
			else {
				$operator = "IN";
				$join = "OR";
			}

			if ($subQueryState) {
				$subQuery = $subQueryState." ".$join." ";
			}
			else {
				if ($roleState != "pending") {
					$subQuery = "1 = 1 ".$join." ";
				}
				else {
					$subQuery = "";
				}
			}

			$subQuery .= UserPeer::ID." ".$operator."(".CriteriaUtils::buidSqlFromCriteria($criteriaRequest).")";

			$criteria->add(UserPeer::ID, "(".$subQuery.")", Criteria::CUSTOM);
		}
		else {
			$recursiveFolders = self::prepareRecursiveQuery($currentFolder);

			$subQuery = implode(" AND ", $recursiveFolders);

			if ($roleState == "active" || !$roleState) {
				if ($state) {
					if (($currentFolder->getFree() && $state == "noAccess") ||
					(!$currentFolder->getFree() && $state == "access")) {
						$operator = "IN";
						$join = "OR";
					}
					else {
						$operator = "NOT IN";
						$join = "AND";
					}

					// Exception user
					$criteriaUserFolder = new Criteria();
					$criteriaUserFolder->addJoin(UserFolderPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
					$criteriaUserFolder->add(UserFolderPeer::FOLDER_ID, $currentFolder->getId());

					// Exception group
					$criteriaUnitFolder = new Criteria();
					$criteriaUnitFolder->addJoin(UnitFolderPeer::UNIT_ID,
							UserUnitPeer::UNIT_ID, Criteria::INNER_JOIN);
					$criteriaUnitFolder->addJoin(UserUnitPeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
					$criteriaUnitFolder->add(UnitFolderPeer::FOLDER_ID, $currentFolder->getId());
					$criteriaUnitFolder->add(UserPeer::CUSTOMER_ID, $customerId);

					if ($userStates) {
						$criteriaUserFolder->add(UserPeer::STATE, $userStates, Criteria::IN);
						$criteriaUnitFolder->add(UserPeer::STATE, $userStates, Criteria::IN);
					}

					CriteriaUtils::setSelectColumn($criteriaUserFolder, UserPeer::ID);

					CriteriaUtils::setSelectColumn($criteriaUnitFolder, UserPeer::ID);

					$subQuery .= " AND ".UserPeer::ID." ".$operator."(";
					$subQuery .= CriteriaUtils::buidSqlFromCriteria($criteriaUserFolder).")";
					$subQuery .= " ".$join." ".UserPeer::ID." ".$operator."(";
					$subQuery .= CriteriaUtils::buidSqlFromCriteria($criteriaUnitFolder).")";
				}
			}

			$criteriaRequest = new Criteria();
			$criteriaRequest->addJoin(UserPeer::ID, RequestPeer::USER_ID);
			$criteriaRequest->add(RequestPeer::FOLDER_ID, $currentFolder->getId());
			$criteriaRequest->add(RequestPeer::IS_REQUEST, true);

			if (count($userStates)) {
				$criteriaRequest->add(UserPeer::STATE, $userStates, Criteria::IN);
			}

			CriteriaUtils::setSelectColumn($criteriaRequest, RequestPeer::USER_ID);

			if ($roleState == "active" || $state) {
				$operator = "NOT IN";
				$join = "AND";
			}
			else if ($roleState == "pending") {
				$operator = "IN";
				$join = "AND";
			}
			else {
				$operator = "IN";
				$join = "OR";
			}

			$subQuery .= " ".$join." ";
			$subQuery .= UserPeer::ID." ".$operator."(".CriteriaUtils::buidSqlFromCriteria($criteriaRequest).")";

			$criteria->add(UserPeer::ID, "(".$subQuery.")", Criteria::CUSTOM);
		}

		CriteriaUtils::buildOrderBy($criteria, $orderBy);

		if ($limit) {
			$criteria->setLimit($limit);
		}
	
		return $criteria;
	}

	public static function prepareRecursiveQuery(Folder $folder, array &$sql = array())
	{
		if ($folder->getSubfolderId()) {
			$currentFolder = self::retrieveByPK($folder->getSubfolderId());

			$criteriaUserFolder = new Criteria();
			$criteriaUserFolder->add(UserFolderPeer::FOLDER_ID, $currentFolder->getId());

			CriteriaUtils::setSelectColumn($criteriaUserFolder, UserFolderPeer::USER_ID);

			if ($currentFolder->getFree()) {
				$operator = "NOT IN";
			}
			else {
				$operator = "IN";
			}

			$sqlLine = UserPeer::ID." ".$operator." (".CriteriaUtils::buidSqlFromCriteria($criteriaUserFolder).")";

			$criteriaUnitFolder = new Criteria();
			$criteriaUnitFolder->addJoin(UserUnitPeer::UNIT_ID, UnitFolderPeer::UNIT_ID);
			$criteriaUnitFolder->add(UnitFolderPeer::FOLDER_ID, $currentFolder->getId());
			$criteriaUnitFolder->add(UnitFolderPeer::ROLE, RolePeer::__READER);

			CriteriaUtils::setSelectColumn($criteriaUnitFolder, UserUnitPeer::USER_ID);

			$sqlLine .= " OR ".UserPeer::ID." IN (".CriteriaUtils::buidSqlFromCriteria($criteriaUnitFolder).")";

			$sql[] = "(".$sqlLine.")";

			self::prepareRecursiveQuery($currentFolder, $sql);
		}

		return $sql;
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
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public static function retrieveByName($folder_name)
	{
		$c = new Criteria();
		
		$c->add(self::NAME, $folder_name);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		return self::doSelectOne($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function retrieveByGroupId($group_id, $folder_id=0)
	{
		$c = new Criteria();
		
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if($folder_id) $c->add(self::ID, $folder_id, Criteria::NOT_EQUAL);
			return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function retrieveByGroupIdInSelect($group_id, $folder_id=0)
	{
		$folders = self::retrieveByGroupId($group_id, $folder_id);
		$folders_array = array();
		
		foreach ($folders as $folder){
			$folders_array[$folder->getId()] = $folder;
		}
	
		return $folders_array;
}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getUploadFolders($group_id, $user_id)
	{
		$c = new Criteria();

		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if(!sfContext::getInstance()->getUser()->hasCredential("admin")){
			$c->add(self::USER_ID, $user_id);
		}
		
		$folders = self::doSelect($c);
		
		$folders_array = array();
		
		foreach ($folders as $folder){
			$folders_array[$folder->getId()] = $folder;
		}
		
		return $folders_array;
}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getUploadFoldersPath($group_id = null, $user_id = null)
	{
		$c = new Criteria();
		
		if ($group_id)
			$c->add(self::GROUPE_ID, $group_id);

		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		$folders = self::doSelect($c);

		$folders_array = array();
	
		foreach ($folders as $folder) {
			$passe = false;
	
			if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
			{
				if($role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $group_id))
				{
					if($role <= RolePeer::__CONTRIB)
						$passe = true;
					else
						$passe = false;
				}
				else
					$passe = false;
			}
			else
				$passe = true;
	
			if($passe == true)
			{
				$bread = explode('|',self::getBreadCrumbTxt($folder->getId()));
				array_splice($bread, count($bread) - 1);
				krsort($bread);
				$txt = '';
				foreach($bread as $case)
					$txt .= $case.'/';
				$folders_array[$folder->getId()] = substr($txt, 0, -1);
			}
		}
	
		return $folders_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function retrieveByGroupIdInArray($group_id)
	{
		$folders = self::retrieveByGroupId($group_id);
		$folders_array = array();
		
		foreach ($folders as $folder){
			$folders_array[] = $folder->getId();
		}

		return $folders_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getFolders($group_id, $sort="date_desc", $tag_ids=array())
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		switch ($sort) {
			default:
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
		}
	
		if (sizeof($tag_ids)) {
			$tag_ids = array_unique($tag_ids);
			$c->add(FileTagPeer::TAG_ID, $tag_ids, Criteria::IN);
			$c->add(FileTagPeer::TYPE, 2);
			$c->addJoin(FileTagPeer::FILE_ID, self::ID);
			$c->addAsColumn('CNT', 'COUNT('.self::ID.')');
			$c->addGroupByColumn(self::ID);
			$c->addHaving($c->getNewCriterion(self::ID, 'CNT='.sizeof($tag_ids), Criteria::CUSTOM));
		}
	
	
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getBounds($folder_ids)
	{
		$connection = Propel::getConnection();

		//preparing query
		$query = "SELECT
				MAX(folder.lat) as max_lat, 
				MAX(folder.lng) as max_long, 
				MIN(folder.lat) as min_lat, 
				MIN(folder.lng) as min_long
			FROM folder, groupe, customer
			WHERE folder.groupe_id = groupe.id
				AND customer.id = groupe.customer_id
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND folder.state = ".$connection->quote(FolderPeer::__STATE_ACTIVE)."
				AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
				AND folder.lat <> '' AND folder.lng <> '' ".(sizeof($folder_ids) ? "AND folder.id in (".
						$connection->quote(join(', ', $folder_ids)).")" : "");
	
		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if (count($rs) > 0) {
			if ($rs[0]["max_lat"] && $rs[0]["max_long"] && $rs[0]["min_lat"] && $rs[0]["min_long"]) {
				return array(
					"max" => array("lat" => $rs[0]["max_lat"], "long" => $rs[0]["max_long"]),
					"min" => array("lat" => $rs[0]["min_lat"], "long" => $rs[0]["min_long"])
				);
			}
			else
				return false;
		} 
		else {
			return false;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getMapFolders($user_id=0, $folder_ids, $s_lat, $n_lat, $s_lng, $n_lng)
	{
		$c = new Criteria();

		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		if ($user_id) {
			$c->add(UserGroupPeer::USER_ID, $user_id);
			$c->addJoin(UserGroupPeer::GROUPE_ID, self::GROUPE_ID);
		}

		$c->add(self::ID, $folder_ids, Criteria::IN);

		// latitude, longitude
		if ($s_lat && $n_lat && $s_lng && $n_lng) {
			$c->add(self::LAT, $s_lat, Criteria::GREATER_EQUAL);
			$c->add(self::LAT, $n_lat, Criteria::LESS_EQUAL);
			$c->add(self::LNG, $s_lng, Criteria::GREATER_EQUAL);
			$c->add(self::LNG, $n_lng, Criteria::LESS_EQUAL);

			$c->add(self::LAT, "", Criteria::NOT_EQUAL);
			$c->add(self::LNG, "", Criteria::NOT_EQUAL);
		}

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getMyFolders($user_id=0, $group_ids=array(), $sort="name_asc", $keyword="")
	{
		$c = new Criteria();
		
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(self::GROUPE_ID, $group_ids, Criteria::IN);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if ($user_id) {
			$c->add(UserGroupPeer::USER_ID, $user_id);
			$c->addJoin(UserGroupPeer::GROUPE_ID, self::GROUPE_ID);
		}

		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));

		if ($keyword && $keyword != __("search") && $keyword != __("Search")) {
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);

			$c1 = $c->getNewCriterion(self::NAME, $keyword."%", Criteria::LIKE);
			$c2 = $c->getNewCriterion(self::DESCRIPTION, "%".$keyword."%", Criteria::LIKE);      
			$c1->addOr($c2);
			$c->add($c1);
		}

		switch ($sort) {
			default: ;
			case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
		}

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	# user/edit
	/**
	 * @deprecated
	 */
	public static function getUserFolders($author_id=0, $sort="name_asc", $keyword="", $force = false)
	{
		$c = new Criteria();
		
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(self::USER_ID, $author_id);
		$c->addJoin(UserPeer::ID, self::USER_ID);
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		
		if ($force == false) {
			$c->add(UserPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(UserPeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		}
	
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
	
		if ($keyword && $keyword != __("search") && $keyword != __("Search")) {
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
			$c1 = $c->getNewCriterion(self::NAME, $keyword."%", Criteria::LIKE);
			$c2 = $c->getNewCriterion(self::DESCRIPTION, "%".$keyword."%", Criteria::LIKE);
			$c1->addOr($c2);
			$c->add($c1);
		}
	
		switch ($sort) {
			default: ;
			case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
		}
		
		return self::doSelect($c);
	}

  /*________________________________________________________________________________________________________________*/
  # search/filter
  public static function search($engine, $keyword="", $user_id=0, $limit=1000, $tag_ids=array(), $author_id=0, 
  	$group_id=0, $locations=array(), $usage_right=0, $years=array(), $sort="name_asc")
  {
    $f1 = false;
    $f2 = false;
    
    $c = new Criteria();
	$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
    
    if(sizeof($tag_ids)){
      $query = " (
        SELECT folder.id
        FROM folder
        INNER JOIN file_tag ON ( file_tag.FILE_ID = folder.ID AND file_tag.TYPE = '2')
        WHERE file_tag.TAG_ID IN (".join(",", $tag_ids).") GROUP BY folder.ID
        HAVING COUNT( folder.id )=".sizeof($tag_ids)."
      )";
      $c->addAlias('f1', $query);
      $c->addJoin(self::ID, "f1.id", Criteria::LEFT_JOIN);
      $f1 = true;
    }
      
    sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));

    if($keyword && $keyword != __("search") && $keyword != __("Search"))
    {
	  $engine->setMode(SPH_MATCH_EXTENDED);
	  $engine->setIndex("folders");
	  $ids = $engine->search(utf8_decode($keyword));

	  if(count($ids) > 0 && is_array($ids))
	  {
		$query = "(SELECT folder.ID
					FROM folder
					WHERE folder.state = \"".FolderPeer::__STATE_ACTIVE."\" AND folder.ID IN (".implode(",", $ids)."))";
	  }
	  else
	  {
		$query = "(SELECT folder.ID
					FROM folder
					WHERE folder.ID IN (null))";
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
        $c->addJoin(UserGroupPeer::GROUPE_ID, self::GROUPE_ID);
      }
    }
    
    if($group_id) $c->add(self::GROUPE_ID, $group_id);
    
    if(sizeof($locations) && $locations[0] && $locations[1]){
      $c1 = $c->getNewCriterion(self::LAT, $locations[0]);
      $c2 = $c->getNewCriterion(self::LAT, $locations[1]);      
      $c1->addAnd($c2);
      $c->add($c1);
    }
    
    if($usage_right) $c->add(self::USAGE_RIGHT, $usage_right);
    
    sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
    if(sizeof($years) && $years[0] && $years[1]) {
      $c1 = $c->getNewCriterion(self::CREATED_AT, $years[0]."-01-01", Criteria::GREATER_EQUAL);
      $c2 = $c->getNewCriterion(self::CREATED_AT, $years[1]."-12-31", Criteria::LESS_EQUAL);
      
      $c1->addAnd($c2);
      $c->add($c1);
    }    
    
    switch ($sort){
      default: ;
    	case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
    	case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
    	case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
    	case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
    }
    
    $c->setLimit($limit);
    
    return self::doSelect($c);
    
  }


	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getBreadCrumb($folderId, $txt = '', $zindex = '11')
	{
		$c = new Criteria();
		
		$c->add(self::ID, $folderId);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		
		$folders = self::doSelect($c);
	
		foreach ($folders as $folder) {
			if($folder->getSubfolderId() > 0)
				$txt .= self::getBreadCrumb($folder->getSubfolderId(), 
						"<a style='z-index:".$zindex.";' class='subfolder' href='/folder/show?id=".$folder->getId()."'>".$folder."</a>|", ($zindex+1));
			else
				$txt .= self::getBreadCrumb($folder->getSubfolderId(), 
						"<a style='z-index:".$zindex.";' class='folder' href='/folder/show?id=".$folder->getId()."'>".$folder."</a>|", ($zindex+1));
		}
		
		return $txt;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUserAllowedFromFolder($userConnected, $folder, $page = 1, $role = null, $keyword = null)
	{
		if($role == "all")
			$role = null;

		$array_users = array();
		$users_folder = UserFolderPeer::retrieveByFolderId($folder->getId());
		$group = GroupePeer::retrieveByPk($folder->getGroupeId());
	
		foreach($users_folder as $user_folder)
		{
			if($group->getFree())
			{
				if((!empty($role) && $role == $group->getFreeCredential()) || empty($role))
					$array_users[] = $user_folder->getUserId();
			}
			elseif(!$group->getFree())
			{
				if((!empty($role) && $role == UserGroupPeer::getRole($user_folder->getUserId(), $group->getId())) || empty($role))
					$array_users[] = $user_folder->getUserId();
			}
		}

		if($group->getFree())
		{
			$users = UserGroupPeer::getUsers($group->getId(), RolePeer::__ADMIN);
	
			foreach($users as $user)
			{
				if(!in_array($user->getUserId(), $array_users))
					$array_users[] = $user->getUserId();
			}
	
			if($group->getFreeCredential() == RolePeer::__CONTRIB)
			{
				if(!in_array($userConnected->getRoleId(), Array(RolePeer::__ADMIN)))
				{
					if(!in_array($userConnected->getId(), $array_users))
					{
						$array_users[] = $userConnected->getId();
					}
				}
			}
		}

		$c = new Criteria();
		$c->add(UserPeer::ID, $array_users, Criteria::IN);
	
		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
			$c1 = $c->getNewCriterion(UserPeer::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
			$c2 = $c->getNewCriterion(UserPeer::LASTNAME, "%".$keyword."%", Criteria::LIKE);
			$c3 = $c->getNewCriterion(UserPeer::EMAIL, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c2);
			$c1->addOr($c3);
			$c->add($c1);
		}
	
		$pager = new sfPropelPager('User', 10);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUserNotAllowedFromFolder($folder)
	{
		$group = GroupePeer::retrieveByPk($folder->getGroupeId());
	
		if ($group->getFree())
			$users = CustomerPeer::getMyUsers($group->getCustomerId());
		else {
			$users_group = UserGroupPeer::retrieveByGroupId($group->getId());
			$users = array();
	
			foreach($users_group as $user_group)
			{
				if($user_group->getUserId())
					$users[] = UserPeer::retrieveByPk($user_group->getUserId());
			}
		}
	
		$array_users = array();
	
		foreach( $users as $user) {
			if (!UserFolderPeer::getRole($user->getId(), $folder->getId()))
				$array_users[] = $user;
		}
	
		return $array_users;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function isAllowedToView($folderId, $userId)
	{
		$folder = self::retrieveByPk($folderId);
	
		if($folder->getFree())
			return true;
		else
		{
			if(sfContext::getInstance()->getUser()->hasCredential("admin"))
			{
				if($folder->getCustomerId() == sfContext::getInstance()->getUser()->getCustomerId())
					return true;
			}
	
			return UserFolderPeer::getRole($userId, $folderId);
		}
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getBreadCrumbTxt($folderId, $txt = '')
	{
		$c = new Criteria();
		
		$c->add(self::ID, $folderId);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		$folders = self::doSelect($c);

		foreach($folders as $folder)
			$txt .= self::getBreadCrumbTxt($folder->getSubfolderId(), $folder."|");

		return $txt;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getBreadCrumbTxtPublic($folderId, $max, $txt = '')
	{
		$c = new Criteria();
		
		$c->add(self::ID, $folderId);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$folders = self::doSelect($c);
	
		foreach ($folders as $folder) {
			if ($folder->getId() == $max) {
				$txt .= serialize($folder)."|";
	
				return $txt;
			}
	
			$txt .= self::getBreadCrumbTxtPublic($folder->getSubfolderId(), $max, serialize($folder)."|");
		}
	
		return $txt;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getFolderInArray($user_id = 0, $group_id = 0, $free = "all")
	{
		$c = new Criteria();
		
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
	
		if($free == "free")
			$c->add(self::FREE, true);
		elseif($free == "not_free")
			$c->add(self::FREE, false);
	
		$c->add(self::ID, UserFolderPeer::getFoldersFromUserInArray($user_id), Criteria::NOT_IN);
		$c->add(self::GROUPE_ID, $group_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->setDistinct();
		$c->addAscendingOrderByColumn(self::SUBFOLDER_ID);
		$folders = self::doSelect($c);
	
		$folders_array = array();
		
		foreach ($folders as $folder){
			$bread = explode('|',self::getBreadCrumbTxt($folder->getId()));
			array_splice($bread, count($bread) - 1);
			krsort($bread);
			$txt = '';
			
			foreach($bread as $case)
				$txt .= $case.'/';
			
			$folders_array[$folder->getId()] = substr($txt, 0, -1);
		}
	
		return $folders_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getFoldersRightsInArray($user_id = 0, $group_id = 0, $free = "all")
	{
		$c = new Criteria();
	
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
	
		if($free == "free")
			$c->add(self::FREE, true);
		elseif($free == "not_free")
			$c->add(self::FREE, false);
	
		$c->add(self::ID, UserFolderPeer::getFoldersFromUserInArray($user_id), Criteria::NOT_IN);
		$c->add(self::GROUPE_ID, $group_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->setDistinct();
		$c->addAscendingOrderByColumn(self::SUBFOLDER_ID);
		$folders = self::doSelect($c);
	
		$folders_array = array();
		
		foreach ($folders as $folder)
		{
			$passe = true;
	
			if($folder->getSubfolderId())
			{
				if(!UserFolderPeer::getRole($user_id, $folder->getSubfolderId()))
					$passe = false;
			}
	
			if($passe == true)
			{
			  $bread = explode('|',self::getBreadCrumbTxt($folder->getId()));
			  array_splice($bread, count($bread) - 1);
			  krsort($bread);
			  $txt = '';
			  foreach($bread as $case)
				$txt .= $case.'/';
			  $folders_array[$folder->getId()] = substr($txt, 0, -1);
			}
		}
	
		return $folders_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getAllPathFolder($group_id) 
	{
		$group = GroupePeer::retrieveByPkNoCustomer($group_id);
	
		$c = new Criteria();
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(self::GROUPE_ID, $group->getId());
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->setDistinct();
		$c->addAscendingOrderByColumn(self::SUBFOLDER_ID);
		$folders = self::doSelect($c);
	
		$folders_array = array();
		
		foreach ($folders as $folder){
			$bread = explode('|',self::getBreadCrumbTxt($folder->getId()));
			array_splice($bread, count($bread) - 1);
			krsort($bread);
			$txt = '';
			
			foreach($bread as $case)
				$txt .= $case.'/';
			
			$folders_array[$folder->getId()] = substr($txt, 0, -1);
		}
	
		return $folders_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function moveSubfolder($folder_id, $group_id)
	{
		$folder = self::retrieveByPk($folder_id);
		$folder->setGroupeId($group_id);
		$folder->save();
	
		$files = FilePeer::retrieveByFolderId($folder->getId());
		
		foreach ($files as $file){
			$file->setGroupeId($group_id);
			$file->save();
		}
	
		$c = new Criteria();
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(self::SUBFOLDER_ID, $folder->getId());
		$folders = self::doSelect($c);
	
		foreach($folders as $folder_child)
			self::moveSubfolder($folder_child->getId(), $group_id);
	
		return true;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFullTree($user_id = 0, $group_id = 0, $folder_id = null, $separator = "", &$folders_array = array())
	{
		$c = new Criteria();

		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(self::ID, UserFolderPeer::getFoldersFromUserInArray($user_id), Criteria::NOT_IN);
		$c->add(self::GROUPE_ID, $group_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::SUBFOLDER_ID, $folder_id);
		$c->setDistinct();
		$c->addAscendingOrderByColumn(self::SUBFOLDER_ID);
		$folders = self::doSelect($c);
	
		foreach ($folders as $folder){
			$folders_array[$folder->getId()] = $separator.$folder;
			$temp = self::getFullTree($user_id, $group_id, $folder->getId(), $separator."*", $folders_array);
		}
	
		return $folders_array;
	}

  /*________________________________________________________________________________________________________________*/
  public static function getParent($folderId, $txt = '')
  {
	$c = new Criteria();
	$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
	$c->add(self::ID, $folderId);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	$folders = self::doSelect($c);

	foreach($folders as $folder)
		$txt .= self::getParent($folder->getSubfolderId(), 'f_'.$folder->getId()."|");

	return $txt;
  }

  /*________________________________________________________________________________________________________________*/
  public static function createArbo($folder_id, $folders)
  {
	$temp = explode("/", $folders);
	$folder_name = $temp[0];

	if(!empty($folder_name))
	{
		$parent = self::retrieveByPk($folder_id);

		$c = new Criteria();
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(self::NAME, $folder_name);
		$c->add(self::SUBFOLDER_ID, $folder_id);

		$folder = self::doSelectOne($c);

		if(!$folder)
		{
			$folder = new Folder();
			$folder->setState(FolderPeer::__STATE_ACTIVE);
			$folder->setName($folder_name);
			$folder->setDescription($folder_name);
			$folder->setLat("");
			$folder->setLng("");
			$folder->setUserId(sfContext::getInstance()->getUser()->getId());
			$folder->setGroupeId($parent->getGroupeId());
			$folder->setSubfolderId($parent->getId());

			$folder->save();
		}

		return self::createArbo($folder->getId(), implode("/", array_slice($temp, 1)));
	}

	return $folder_id;
  }

  /*________________________________________________________________________________________________________________*/
  public static function deleteArbo($folder_id)
  {
	$c = new Criteria();
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(self::SUBFOLDER_ID, $folder_id);

	$folders = self::doSelect($c);

	foreach($folders as $folder)
	{
		$folder->setState(self::__STATE_DELETE);
		$folder->save();

		LogPeer::setLog(sfContext::getInstance()->getUser()->getId(), $folder->getId(), "folder-delete", "2");

		$c = new Criteria();
		$c->add(FilePeer::FOLDER_ID, $folder->getId());
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);

		$files = FilePeer::doSelect($c);

		foreach($files as $file)
		{
			$file->setState(FilePeer::__STATE_DELETE);
			$file->setUpdatedAt(time());

			$file->save();

			LogPeer::setLog(sfContext::getInstance()->getUser()->getId(), $file->getId(), "file-delete", "3");
		}

		self::deleteArbo($folder->getId());
	}
	}

	/*________________________________________________________________________________________________________________*/
	public static function isUnderFolder($folder_id, $folder_id2)
	{
		$c = new Criteria();
	
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(FolderPeer::SUBFOLDER_ID, $folder_id2);
	
		$folders = FolderPeer::doSelect($c);
	
		foreach ($folders as $folder) {
			if ($folder->getId() == $folder_id)
				return true;
			else
				return self::isUnderFolder($folder_id, $folder->getId());
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFoldersInTree($group_id = null, $folder_id = null)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("Url"));
		$folder_array = Array();

		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			$connection = Propel::getConnection();

			$query = "SELECT folder.*
					FROM folder, groupe, customer
					WHERE folder.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND folder.state = ".$connection->quote(self::__STATE_ACTIVE)."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					AND groupe.free = 1
					AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

			if($group_id)
				$query .= " AND folder.groupe_id = ".$connection->quote($group_id)."
							AND folder.subfolder_id IS NULL";

			if($folder_id)
				$query .= " AND folder.subfolder_id = ".$connection->quote($folder_id);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while($rs = $statement->fetch())
			{
				$folder = new Folder();
				$folder->hydrate($rs);

				if(sfContext::getInstance()->getUser()->hasCredential("admin"))
					$role = RolePeer::__ADMIN;
				else
				{
					$group = GroupePeer::retrieveByPk($folder->getGroupeId());

					$role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $group->getId());

					if(!$role)
						$role = $group->getFreeCredential();
				}

				if($folder->getFree() || (!$folder->getFree() && UserFolderPeer::getRole(sfContext::getInstance()->getUser()->getId(), $folder->getId())))
				{
					$temp = array();
					$temp["key"] = $folder->getId();
					$temp["title"] = $folder->getName();
					$temp["tooltip"] = $folder->getDescription();
					$temp["isFolder"] = false;
					$temp["isLazy"] = true;
					$temp["addClass"] = "node-folder";
					$temp["href"] = urldecode(url_for("folder/show?id=".$folder->getId()));
					$temp["right"] = $role;

					array_push($folder_array, $temp);
				}
			}

			$statement->closeCursor();
			$statement = null;

			$query = "SELECT folder.*
					FROM folder, groupe, user_group, customer
					WHERE folder.groupe_id = groupe.id
					AND groupe.id = user_group.groupe_id
					AND groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND folder.state = ".$connection->quote(self::__STATE_ACTIVE)."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					AND groupe.free = 0
					AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId())."
					AND user_group.role <= ".$connection->quote(RolePeer::__READER);

			if($group_id)
				$query .= " AND folder.groupe_id = ".$connection->quote($group_id)."
							AND folder.subfolder_id IS NULL";

			if($folder_id)
				$query .= " AND folder.subfolder_id = ".$connection->quote($folder_id);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while($rs = $statement->fetch())
			{
				$folder = new Folder();
				$folder->hydrate($rs);

				if(sfContext::getInstance()->getUser()->hasCredential("admin"))
					$role = RolePeer::__ADMIN;
				else
				{
					$role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $folder->getGroupeId());

					if(!empty($role))
						$role = 10;
				}

				if($folder->getFree() || (!$folder->getFree() && UserFolderPeer::getRole(sfContext::getInstance()->getUser()->getId(), $folder->getId())))
				{
					$temp = array();
					$temp["key"] = $folder->getId();
					$temp["title"] = $folder->getName();
					$temp["tooltip"] = $folder->getDescription();
					$temp["isFolder"] = false;
					$temp["isLazy"] = true;
					$temp["addClass"] = "node-folder";
					$temp["href"] = urldecode(url_for("folder/show?id=".$folder->getId()));
					$temp["right"] = $role;

					array_push($folder_array, $temp);
				}
			}

			$statement->closeCursor();
			$statement = null;
		}
		else
		{
			$c = new Criteria();
			$c->add(self::STATE, self::__STATE_ACTIVE);
			$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

			if($group_id)
			{
				$c->add(self::GROUPE_ID, $group_id);
				$c->add(self::SUBFOLDER_ID, null);
			}

			if($folder_id)
				$c->add(self::SUBFOLDER_ID, $folder_id);

			$folders = self::doSelect($c);

			foreach($folders as $folder)
			{
				$temp = array();
				$temp["key"] = $folder->getId();
				$temp["title"] = $folder->getName();
				$temp["tooltip"] = $folder->getDescription();
				$temp["isFolder"] = false;
				$temp["isLazy"] = true;
				$temp["addClass"] = "node-folder";
				$temp["href"] = urldecode(url_for("folder/show?id=".$folder->getId()));
				$temp["right"] = RolePeer::__ADMIN;

				array_push($folder_array, $temp);
			}
		}

		return $folder_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveNode($folder_id, $txt = "")
	{
		$c = new Criteria();
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(self::ID, $folder_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$folders = self::doSelect($c);

		foreach($folders as $folder)
			$txt .= self::retrieveNode($folder->getSubfolderId(), $folder->getId()."/");

		return $txt;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveAllSubfolder($folder_id, &$folders_array = array())
	{
		$c = new Criteria();
		$c->add(self::SUBFOLDER_ID, $folder_id);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);

		$folders = self::doSelect($c);
		foreach($folders as $folder)
		{
			$folders_array[$folder->getId()] = $folder->getId();
			$temp = self::retrieveAllSubfolder($folder->getId(), $folders_array);
		}

		return $folders_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retriveByCustomerId($customer_id)
	{
		$c = new Criteria();
		$c->addJoin(self::GROUPE_ID, GroupePeer::ID);
		$c->add(GroupePeer::CUSTOMER_ID, $customer_id);
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveBySubfolderId($subfolder_id)
	{
		$c = new Criteria();
		$c->add(self::SUBFOLDER_ID, $subfolder_id);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByGroupIdAndCustomerId($group_id, $customer_id)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, $customer_id);
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		return self::doSelect($c);
	}

	

	/*________________________________________________________________________________________________________________*/
	public static function getCountMax()
	{
		$connection = Propel::getConnection();

		$query = "	SELECT count(folder.id)
					FROM folder";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $rs[0][0];
	}

	/*________________________________________________________________________________________________________________*/
	public static function countFolders($folderId, &$count = 0)
	{
		$folder = FolderPeer::retrieveByPk($folderId);

		if($folder->getState() == FolderPeer::__STATE_ACTIVE)
		{
			$c = new Criteria();
			$c->add(FolderPeer::SUBFOLDER_ID, $folder->getId());
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

			$folders = FolderPeer::doSelect($c);

			foreach($folders as $folder)
			{
				$count ++;
				self::countFolders($folder->getId(), $count);
			}
		}

		return $count;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByCustomerId($customer_id)
	{
		$c = new Criteria();
		
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::ID, $customer_id);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function countByCustomerId($customer_id)
	{
		$c = new Criteria();
		
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::ID, $customer_id);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_ACTIVE);
	
		return self::doCount($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function isAllowedToManageAndAddFolder($u, $id)
	{
		$folder = FolderPeer::retrieveByPk($id);

		if($folder->getState() == FolderPeer::__STATE_ACTIVE)
		{
			/* Test authorization */
			if(!in_array($u->getRoleId(), Array(RolePeer::__ADMIN)))
			{
				if($folder->getFree())
					;
				elseif(!$folder->getFree())
				{
					if(!UserFolderPeer::getRole($u->getId(), $folder->getId()))
						return false;
				}

				$mainFolder = GroupePeer::retrieveByPKNoCustomer($folder->getGroupeId());

				if($mainFolder->getState() == GroupePeer::__STATE_ACTIVE)
				{
					if($mainFolder->getFree() && $mainFolder->getFreeCredential() == RolePeer::__CONTRIB)
					{
						$role = UserGroupPeer::getRole($u->getId(), $mainFolder->getId());

						if($folder->getUserId() == $u->getId())
							;
						elseif($role == RolePeer::__ADMIN)
							;
						else
							return false;
					}
					elseif($mainFolder->getFree() && $mainFolder->getFreeCredential() == RolePeer::__READER)
					{
						$role = UserGroupPeer::getRole($u->getId(), $mainFolder->getId());

						if($role == RolePeer::__ADMIN)
							;
						else
							return false;
					}
					elseif(!$mainFolder->getFree())
					{
						$role = UserGroupPeer::getRole($u->getId(), $mainFolder->getId());

						if($role == RolePeer::__ADMIN || ($role == RolePeer::__CONTRIB && $folder->getUserId() == $u->getId()))
							;
						else
							return false;
					}
				}
				else
					return false;
			}

			return true;
		}
		else
			return false;
	}

	/*________________________________________________________________________________________________________________*/
	public static function searchEngine($keyword = null, $userId = null, $limit = 50, $tagIds = Array(), 
			$authorId = null, $groupId = null, $locations = Array(), $years = Array(), $sort = "name_asc")
	{
		function processFolderIndex($operator, $array)
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
		$folders = Array();

		if(!empty($tagIds))
		{
			$query = "	SELECT distinct folder.id
						FROM folder, file_tag, tag, groupe
						WHERE groupe.id = folder.groupe_id
						AND folder.id = file_tag.file_id
						AND file_tag.tag_id = tag.id
						AND file_tag.type = ".FileTagPeer::__TYPE_FOLDER."
						AND folder.state = ".self::__STATE_ACTIVE."
						AND groupe.customer_id = ".$customerId."
						AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
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

				$query = "	SELECT distinct folder.id
							FROM folder, file_tag, tag, groupe
							WHERE groupe.id = folder.groupe_id
							AND folder.id = file_tag.file_id
							AND file_tag.tag_id = tag.id
							AND file_tag.type = ".FileTagPeer::__TYPE_FOLDER."
							AND folder.state = ".self::__STATE_ACTIVE."
							AND groupe.customer_id = ".$customerId."
							AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
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

			$idsTags = processFolderIndex($searchOperator, $temp);

			/**
				Search folder.

				Search on FOLDER.NAME
				Search on FOLDER.DESCRIPTION
				Search on USER.FIRSTNAME
				Search on USER.LASTNAME
				Search on USER.EMAIL
			**/
			$temp = Array();
			foreach($searchExpression as $searchTerm)
			{
				$temp[$searchTerm] = Array();

				$query = "	SELECT distinct folder.id
							FROM folder, groupe, user
							WHERE groupe.id = folder.groupe_id
							AND folder.user_id = user.id
							AND folder.state = ".self::__STATE_ACTIVE."
							AND groupe.customer_id = ".$customerId."
							AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
							AND
								(
									(folder.name LIKE '".$searchTerm."')
									OR
									(folder.description LIKE '".$searchTerm."')
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

			$idsFolders = processFolderIndex($searchOperator, $temp);

			/**
				Search field content.

				Search on FIELD_CONTENT.VALUE
			**/
			$temp = Array();
			foreach($searchExpression as $searchTerm)
			{
				$temp[$searchTerm] = Array();

				$query = "	SELECT distinct folder.id
							FROM folder, groupe, field_content
							WHERE groupe.id = folder.groupe_id
							AND folder.id = field_content.object_id
							AND field_content.object_type = ".FieldContentPeer::__FOLDER."
							AND folder.state = ".self::__STATE_ACTIVE."
							AND groupe.customer_id = ".$customerId."
							AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
							AND field_content.value LIKE '".$searchTerm."'";

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

			$idsFields = processFolderIndex($searchOperator, $temp);

			$ids = array_merge($ids, $idsTags, $idsFolders, $idsFields);

			if(empty($ids))
				$ids[] = -1;
		}

		if (empty($groupId)) {
			$groups_ = UserGroupPeer::getGroupIds($currentUserId, "", true);
			$groups2 = GroupePeer::getGroupsInArray2($currentUserId);

			foreach($groups_ as $group)
				$groups[] = $group;

			foreach($groups2 as $group)
				$groups[] = $group->getId();
		}
		else
			$groups[] = $groupId;

		$query = "	SELECT distinct folder.*
					FROM folder, groupe
					WHERE folder.groupe_id = groupe.id
					AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
					AND folder.state = ".self::__STATE_ACTIVE."
					AND groupe.customer_id = ".$customerId."
					AND groupe.id IN (".implode($groups, ",").")";

		if(!empty($ids))
			$query .= " AND folder.id IN (".implode($ids, ",").")";

		if(!empty($userId))
			$query .= " AND folder.user_id = ".$connection->quote($userId);
		elseif(!empty($authorId))
			$query .= " AND folder.user_id = ".$connection->quote($currentUserId);

		if(!empty($locations) && $locations[0] && $locations[1])
			$query .= " AND folder.lat = ".$connection->quote($locations[0])." 
				AND folder.lng = ".$connection->quote($locations[1]);

		if(!empty($years) && $years[0] && $years[1])
			$query .= " AND folder.created_at >= '".$years[0]."-01-01' AND folder.created_at <= '".$years[1]."-12-31'";

		switch ($sort)
		{
			default: ;
			case "name_asc":
				$query .= " ORDER BY folder.name ASC";
			break;

			case "name_desc":
				$query .= " ORDER BY folder.name DESC";
			break;

			case "date_asc":
				$query .= " ORDER BY folder.created_at ASC";
			break;

			case "date_desc": 
				$query .= " ORDER BY folder.created_at DESC";
			break;
		}

		if(!empty($limit))
			$query .= " LIMIT 0,".$limit;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);

		while($rs = $statement->fetch())
		{
			$folder = new Folder();
			$folder->hydrate($rs);
			$folders[] = $folder;
		}

		return $folders;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFoldersInGroup($group_id, $tag_ids=array(), $user_id=0, $author_id=0, $dates=array(), 
			$sort="date_desc", $crit="N", $sub = null, $limit = null, $offset = null)
	{
		$connection = Propel::getConnection();
		$folders = Array();

		if($author_id == "false")
			$author_id = false;

		if($author_id == "true")
			$author_id = true;

		$count = "	SELECT count(distinct folder.id) as count, max(file.created_at) as lastActivity";
		$select = "	SELECT distinct folder.*, max(file.created_at) as lastActivity";
		$from = "";
		$where = "	WHERE 1 = 1";

		
			$from = "	FROM customer";
			$where .= "	AND groupe.customer_id = customer.id
						AND customer.state = ".CustomerPeer::__STATE_ACTIVE."
						AND customer.id = ".sfContext::getInstance()->getUser()->getCustomerId();


		if(empty($from)) {
			$from .= "	FROM file RIGHT JOIN folder ON file.folder_id = folder.id INNER JOIN groupe ON 
				folder.groupe_id = groupe.id";
			
		}
		else {
			$from .= "	, file RIGHT JOIN folder ON file.folder_id = folder.id INNER JOIN groupe ON 
				folder.groupe_id = groupe.id";
		}

		$where .= "	AND folder.state = ".FolderPeer::__STATE_ACTIVE."
					AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
					AND groupe.id = ".$group_id;

		if($sub)
			$where .= " AND folder.subfolder_id = ".$sub;
		else
			$where .= " AND folder.subfolder_id IS NULL";

		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			if($author_id)
				$where .= " AND folder.user_id = ".sfContext::getInstance()->getUser()->getId();
			elseif($user_id)
			{
				$group = GroupePeer::retrieveByPkNoCustomer($group_id);

				if(!$group->getFree())
					$from .= "	RIGHT JOIN user_group ON groupe.id = user_group.groupe_id";
			}
		}

		if(sizeof($dates) && $dates["min"] && $dates["max"])
		{
			$min = explode("/", $dates["min"]);
			$max = explode("/", $dates["max"]);

			$where .= "	AND folder.created_at >= \"".$min[2]."-".$min[1]."-".$min[0]." 00:00:00\"
						AND folder.created_at <= \"".$max[2]."-".$max[1]."-".$max[0]." 23:59:59\"";
		}

		if(sizeof($tag_ids))
		{
			$folders_tags = Array();

			$query = "	SELECT file.id, file.folder_id
						FROM file
						INNER JOIN file_tag ON ( file_tag.FILE_ID = file.ID AND file_tag.TYPE = '3')
						WHERE file.STATE = \"".FilePeer::__STATE_VALIDATE."\"
						AND file_tag.TAG_ID IN (".join(",", $tag_ids).") 
						AND file.GROUPE_ID = \"".$group_id."\"";

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 

			while($rs = $statement->fetch())
			{
				$temp = explode("|", substr(self::getParent($rs["folder_id"]), 0, -1));

				foreach($temp as $t)
				{
					$folder_id = substr($t, 2);

					if(!in_array($folder_id, $folders_tags))
						$folders_tags[] = $folder_id;
				}
			}

			$statement->closeCursor();
			$statement = null;

			if(!empty($folders_tags))
				$where .= "	AND folder.id IN (".implode($folders_tags, ",").")";
			else
				$where .= "	AND folder.id = -1";
		}

		$statement = $connection->query($count.$from.$where);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$where .= " GROUP BY folder.id";

		switch ($sort)
		{
			default: ;
			case "creation_desc": $where .= "	ORDER BY folder.created_at DESC"; break;
			case "creation_asc": $where .= "	ORDER BY folder.created_at ASC"; break;
			case "name_desc": $where .= "	ORDER BY folder.name DESC"; break;
			case "name_asc": $where .= "	ORDER BY folder.name ASC"; break;
			case "activity_asc": $sort = "	ORDER BY lastActivity ASC, folder.name ASC"; break;
			case "activity_desc": $sort = "	ORDER BY lastActivity DESC, folder.name ASC"; break;
		}

		if (!empty($offset) || !empty($limit)) {
			if ($limit != "all") {
				$where .= "	LIMIT ".$offset.", ".$limit;
			}
		}

		$statement = $connection->query($select.$from.$where);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		while($rs = $statement->fetch())
		{
			$folder = new Folder();
			$folder->hydrate($rs);
			$folders[] = serialize($folder);
		}

		$statement->closeCursor();
		$statement = null;

		return Array("folders" => array_map("unserialize", $folders), "count" => $result[0]["count"]);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getDateRange($group_id, $subfolder_id = null)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT UNIX_TIMESTAMP(min(folder.created_at)) as min, UNIX_TIMESTAMP(max(folder.created_at)) as max
					FROM folder
					WHERE folder.groupe_id = ".$group_id."
					AND folder.state = ".self::__STATE_ACTIVE;

		if(!empty($subfolder_id))
			$query .= "	AND folder.subfolder_id = ".$subfolder_id;
		else
			$query .= "	AND folder.subfolder_id IS NULL ";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $result[0];
	}

	/*________________________________________________________________________________________________________________*/
	public static function getRecursiveFoldersId($folderId, &$recursives = Array())
	{
		$folder = FolderPeer::retrieveByPk($folderId);

		if($folder->getState() == FolderPeer::__STATE_ACTIVE)
		{
			$c = new Criteria();
			$c->add(FolderPeer::SUBFOLDER_ID, $folder->getId());

			$folders = FolderPeer::doSelect($c);

			foreach($folders as $folder)
			{
				$recursives[] = $folder->getId();
				self::getRecursiveFoldersId($folder->getId(), $recursives);
			}
		}

		return $recursives;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getBreadCrumbNew($folderId, &$breadCrumb = Array())
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");

		$c = new Criteria();
		$c->add(self::ID, $folderId);
		$c->add(self::STATE, FolderPeer::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		$folders = self::doSelect($c);

		foreach($folders as $folder)
		{
			$breadCrumb[] = Array("link" => url_for("folder/show?id=".$folder->getId()), "label" => $folder->getName());
			self::getBreadCrumbNew($folder->getSubfolderId(), $breadCrumb);
		}

		return $breadCrumb;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getNoAccessFolders($group_id, $user_id, $subfolder_id = null, $page = 1, $perPage = 10, 
		$output = "json", $label = "", &$access = array())
	{
		$connection = Propel::getConnection();
		$user = UserPeer::retrieveByPK($user_id);

		if ($user->getRoleId() > RolePeer::__ADMIN) {
			$query = "	SELECT folder.*
						FROM folder
						WHERE folder.groupe_id = ".$group_id."
						AND folder.state = ".self::__STATE_ACTIVE;
	
			if (empty($subfolder_id)) {
				$query .= " AND folder.subfolder_id IS NULL";
			}
			else {
				$query .= " AND folder.subfolder_id = ".$subfolder_id;
			}
	
			$query .= " ORDER BY folder.subfolder_id ASC, folder.name ASC";
	
			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM);
	
			while($rs = $statement->fetch())
			{
				$folder = new Folder();
				$folder->hydrate($rs);
	
				if (!$folder->getFree() && !UserFolderPeer::retrieveByUserAndFolder($user_id, $folder->getId())) {
					switch($output) {
						case "json":
							$access[] = array("id" => $folder->getId(), "label" => $label.$folder->getName());
						break;

						case "pager":
							$access[] = $folder->getId();
						break;
		
						default:
							$access[] = array("data" => $folder, "label" => $label.$folder->getName());
						break;
					}
				}
	
				self::getNoAccessFolders($group_id, $user_id, $folder->getId(), $page, $perPage, $output,
					$label.$folder->getName()."/", $access);
			}
	
			$statement->closeCursor();
			$statement = null;
		}

		if ($output == "pager") {
			$c = new Criteria();
			$c->add(self::ID, $access, Criteria::IN);
			$c->addAscendingOrderByColumn(self::SUBFOLDER_ID);
			$c->addAscendingOrderByColumn(self::NAME);

			$pager = new sfPropelPager("Folder", $perPage);
			$pager->setCriteria($c);
			$pager->setPage($page);
			$pager->setPeerMethod('doSelect');
			$pager->init();

			return $pager;
		}
		else {
			return $access;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getAccessFolders($group_id, $user_id, $subfolder_id = null, $page = 1, $perPage = 10,
		$output = "json", $label = "", &$access = array())
	{
		$connection = Propel::getConnection();
		$user = UserPeer::retrieveByPK($user_id);
	
		$query = "	SELECT folder.*
					FROM folder
					WHERE folder.groupe_id = ".$group_id."
					AND folder.state = ".self::__STATE_ACTIVE;
	
		if (empty($subfolder_id)) {
			$query .= " AND folder.subfolder_id IS NULL";
		}
		else {
			$query .= " AND folder.subfolder_id = ".$subfolder_id;
		}
	
		$query .= "	ORDER BY folder.subfolder_id ASC, folder.name ASC";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		while($rs = $statement->fetch())
		{
			$folder = new Folder();
			$folder->hydrate($rs);
	
			if (
				$user->getRoleId() <= RolePeer::__ADMIN ||
				$folder->getFree() ||
				!$folder->getFree() &&
				UserFolderPeer::retrieveByUserAndFolder($user->getId(), $folder->getId())
			) {
				switch($output) {
					case "json":
						$access[] = array("id" => $folder->getId(), "label" => $label.$folder->getName());
						break;
		
					case "pager":
						$access[] = $folder->getId();
					break;
	
					default:
						$access[] = array("data" => $folder, "label" => $label.$folder->getName());
					break;
				}
			}

			self::getAccessFolders($group_id, $user_id, $folder->getId(), $page, $perPage, $output,
				$label.$folder->getName()."/", $access);
		}
	
		$statement->closeCursor();
		$statement = null;

		if ($output == "pager") {
			$c = new Criteria();
			$c->add(self::ID, $access, Criteria::IN);
			$c->addAscendingOrderByColumn(self::SUBFOLDER_ID);
			$c->addAscendingOrderByColumn(self::NAME);

			$pager = new sfPropelPager("Folder", $perPage);
			$pager->setCriteria($c);
			$pager->setPage($page);
			$pager->setPeerMethod('doSelect');
			$pager->init();

			return $pager;
		}
		else {
			return $access;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getPathName($folder_id, &$name = array())
	{
		if(!empty($folder_id)) {
			$c = new Criteria();
			$c->add(self::ID, $folder_id);
	
			$folder = self::doSelectOne($c);
	
			$name[] = $folder->getName();

			self::getPathName($folder->getSubfolderId(), $name);
		}

		return $name;
	}
}
