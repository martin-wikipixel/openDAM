<?php

/**
 * Subclass for performing query and update operations on the 'user_folder' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UserFolderPeer extends BaseUserFolderPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params, array $orderBy = array(), $limit = 0)
	{
		$userId = isset($params["userId"]) ? (int)$params["userId"] : 0;
		
		$criteria = new Criteria();
		
		if ($userId) {
			$criteria->add(self::USER_ID, $userId);
		}

		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi les dossiers en accès gérés que l'utilisateur n'a pas.
	 * 
	 * @return array<Folder>
	 */
	public static function findFoldersCanAddRight(array $params, array $orderBy = array(), $limit = 0)
	{
		$albumId = $params["albumId"];
		$userId = $params["userId"];
		
		Assert::ok($albumId > 0);
		Assert::ok($userId > 0);

		$criteria = new Criteria();

		$criteria->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$criteria->add(FolderPeer::FREE, 0);
		$criteria->add(FolderPeer::GROUPE_ID, $albumId);

		$folderCriteria = self::doCriteria(array("userId" => $userId));
		CriteriaUtils::setSelectColumn($folderCriteria, self::FOLDER_ID);
		
		$criteria->add(FolderPeer::ID, FolderPeer::ID." NOT IN(".
				CriteriaUtils::buidSqlFromCriteria($folderCriteria).")", Criteria::CUSTOM);
		
		CriteriaUtils::buildOrderBy($criteria, $orderBy);

		if ($limit) {
			$criteria->setLimit($limit);
		}
		
		return FolderPeer::doSelect($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByUserAndFolder($user_id, $folder_id)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
		$c->add(self::FOLDER_ID, $folder_id);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByFolderId($folder_id)
	{
		$c = new Criteria();
		$c->add(self::FOLDER_ID, $folder_id);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function deleteByFolderId($folder_id)
	{
		$c = new Criteria();
		
		$c->add(self::FOLDER_ID, $folder_id);
		$c->addJoin(self::FOLDER_ID, FolderPeer::ID);
		$c->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if (self::doCount($c) > 0) {
			$c = new Criteria();
			
			$c->add(self::FOLDER_ID, $folder_id);
			self::doDelete($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getRightFolderUser($user_id)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(self::FOLDER_ID, FolderPeer::ID);
		$c->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
	
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getFoldersFromUserInArray($user_id)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(self::FOLDER_ID, FolderPeer::ID);
		$c->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$folders = FolderPeer::doSelect($c);

		$folders_array = array();
		
		foreach ($folders as $folder) {
			$folders_array[$folder->getId()] = $folder->getId();
		}

		return $folders_array;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getRole($user_id, $folder_id)
	{
		$user = UserPeer::retrieveByPk($user_id);
		$folder = FolderPeer::retrieveByPk($folder_id);

		if ($folder->getFree())
			return true;

		if($user->getRoleId() == RolePeer::__ADMIN)
			return true;

		$c = new Criteria();
		$c->add(self::USER_ID, $user_id);
		$c->add(self::FOLDER_ID, $folder_id);
		$c->addJoin(self::FOLDER_ID, FolderPeer::ID);
		$c->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, $user->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$user_folder = self::doSelectOne($c);

		if($user_folder)
			return $user_folder->getId();
		else
		{
			if($folder->getUserId() == $user->getId())
				return true;

			if(UserGroupPeer::getRole($user_id, $folder->getGroupeId()) == RolePeer::__ADMIN)
				return true;
		}

		return "";
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getFoldersNoRight($user_id) 
	{
		$connection = Propel::getConnection();

		$query = "	SELECT folder.*
					FROM groupe, customer, folder
					WHERE groupe.customer_id = customer.id
					AND folder.groupe_id = groupe.id
					AND folder.state = ".FolderPeer::__STATE_ACTIVE."
					AND groupe.customer_id = ".sfContext::getInstance()->getUser()->getCustomerId()."
					AND customer.state = ".CustomerPeer::__STATE_ACTIVE."
					AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
					AND folder.free = 0
					AND folder.id  NOT IN (	SELECT user_folder.folder_id
											FROM user_folder
											WHERE user_folder.user_id = ".$user_id.")";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$folders_array = array();
		while ($rs = $statement->fetch())
		{
			$folder = new Folder();
			$folder->hydrate($rs);

			$folders_array[$folder->getId()] = str_replace('|', '/', GroupePeer::retrieveByPk($folder->getGroupeId())
					.'|'.FolderPeer::getBreadCrumbTxt($folder->getId()));
		}

		$statement->closeCursor();
		$statement = null;

		return $folders_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getNbRole($folder_id, $role, $userConnected = null)
	{
		$c = new Criteria();
		$c->add(UserFolderPeer::FOLDER_ID, $folder_id);
		$c->add(UserFolderPeer::USER_ID, "");
		$c->add(UserFolderPeer::ROLE, $role == "all" ? null : $role);

		return self::getCountAllUsers($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUsers($folder_id)
	{
		$folder = FolderPeer::retrieveByPk($folder_id);
		$users = Array();

		if($folder->getFree())
		{
			if($folder->getSubfolderId())
				return self::getUsers($folder->getSubfolderId());
			else
			{
				$group = GroupePeer::retrieveByPk($folder->getGroupeId());

				if($group->getFree())
					$users = CustomerPeer::getMyUsersNoPager($group->getCustomerId());
				else
				{
					$temp = UserGroupPeer::retrieveByGroupId($group->getId());

					foreach($temp as $user)
					{
						if($user->getUser())
							$users[] = $user->getUser();
					}
				}

				return $users;
			}
		}
		else
		{
			if($folder->getSubfolderId())
			{
				$c = new Criteria();
				$c->add(self::FOLDER_ID, $folder->getSubfolderId());

				$users_folders = self::doSelect($c);

				foreach($users_folders as $user_folder)
				{
					$user = UserPeer::retrieveByPk($user_folder->getUserId());
					$users[] = $user;
				}

				return $users;
			}
			else
			{
				$group = GroupePeer::retrieveByPk($folder->getGroupeId());

				if($group->getFree())
					$users = CustomerPeer::getMyUsersNoPager($group->getCustomerId());
				else
				{
					$temp = UserGroupPeer::retrieveByGroupId($group->getId());

					foreach($temp as $user)
					{
						if($user->getUser())
							$users[] = $user->getUser();
					}
				}

				return $users;
			}
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getAllUsers(Criteria $c)
	{
		$connection = Propel::getConnection();

		$max = $c->getLimit();
		$offset = $c->getOffset();
	
		$map = $c->getMap();
		$folder_id = $map[UserFolderPeer::FOLDER_ID]->getValue();
		$role = $map[UserFolderPeer::ROLE]->getValue();
		$search = $map[UserFolderPeer::USER_ID]->getValue();
		$folder = FolderPeer::retrieveByPK($folder_id);
		$group = $folder->getGroupe();
		$roleGroup = null;

		$query = "	SELECT user.*
					FROM user_folder, user, folder
					WHERE user.id = user_folder.user_id
					AND user_folder.folder_id = folder.id
					AND folder.state = ".FolderPeer::__STATE_ACTIVE."
					AND user.state = ".UserPeer::__STATE_ACTIVE."
					AND user_folder.folder_id = ".$folder->getId();

		if (!empty($search) && $search != __("search") && $search != __("Search")) {
			$query .= " AND (user.email LIKE \"%".$search."%\" OR user.lastname LIKE \"%".$search."%\" OR user.firstname LIKE \"%".$search."%\")";
		}

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
		$users = Array();

		while($rs = $statement->fetch())
		{
			$user = new User();
			$user->hydrate($rs);

			if ($user->getRoleId() < RolePeer::__ADMIN) {
				$roleGroup = RolePeer::__ADMIN;
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($group->getId(), $user->getId());

				if ($userGroup) {
					if ($group->getFree()) {
						$roleGroup = $userGroup->getRole() < $group->getFreeCredential() ? $userGroup->getRole() : $group->getFreeCredential();
					}
					else {
						$roleGroup = $userGroup->getRole();
					}
				}
				else {
					if ($group->getFree()) {
						$roleGroup = $group->getFreeCredential();
					}
				}
			}

			if (empty($role) || $role == $roleGroup) {
				$users[] = $user;
			}
		}

		$statement->closeCursor();
		$statement = null;

		$query = "	SELECT request.*
					FROM user, request
					WHERE user.id = request.user_id
					AND user.state = ".UserPeer::__STATE_ACTIVE."
					AND request.folder_id = ".$folder->getId();

		if (!empty($search) && $search != __("search") && $search != __("Search")) {
			$query .= " AND (user.email LIKE \"%".$search."%\" OR user.lastname LIKE \"%".$search."%\" OR user.firstname LIKE \"%".$search."%\")";
		}

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
		$requests = Array();

		while($rs = $statement->fetch())
		{
			$request = new Request();
			$request->hydrate($rs);

			$user = $request->getUser();

			if ($user->getRoleId() < RolePeer::__ADMIN) {
				$roleGroup = RolePeer::__ADMIN;
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($group->getId(), $user->getId());

				if ($userGroup) {
					if ($group->getFree()) {
						$roleGroup = $userGroup->getRole() < $group->getFreeCredential() ? $userGroup->getRole() : $group->getFreeCredential();
					}
					else {
						$roleGroup = $userGroup->getRole();
					}
				}
				else {
					if ($group->getFree()) {
						$roleGroup = $group->getFreeCredential();
					}
				}
			}
		
			if (empty($role) || $role == $roleGroup) {
				$requests[] = $request;
			}
		}

		return array_slice(array_merge($requests, $users), $offset, $max);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountAllUsers(Criteria $c)
	{
		$connection = Propel::getConnection();
	
		$map = $c->getMap();
		$folder_id = $map[UserFolderPeer::FOLDER_ID]->getValue();
		$role = $map[UserFolderPeer::ROLE]->getValue();
		$search = $map[UserFolderPeer::USER_ID]->getValue();
		$folder = FolderPeer::retrieveByPK($folder_id);
		$group = $folder->getGroupe();
		$roleGroup = null;
	
		$query = "	SELECT user.*
					FROM user_folder, user, folder
					WHERE user.id = user_folder.user_id
					AND user_folder.folder_id = folder.id
					AND folder.state = ".FolderPeer::__STATE_ACTIVE."
					AND user.state = ".UserPeer::__STATE_ACTIVE."
					AND user_folder.folder_id = ".$folder->getId();
	
		if (!empty($search) && $search != __("search") && $search != __("Search")) {
			$query .= " AND (user.email LIKE \"%".$search."%\" OR user.lastname LIKE \"%".$search."%\" OR user.firstname LIKE \"%".$search."%\")";
		}
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
		$users = Array();
	
		while($rs = $statement->fetch())
		{
			$user = new User();
			$user->hydrate($rs);
	
			if ($user->getRoleId() < RolePeer::__ADMIN) {
				$roleGroup = RolePeer::__ADMIN;
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($group->getId(), $user->getId());
					
				if ($userGroup) {
					if ($group->getFree()) {
						$roleGroup = $userGroup->getRole() < $group->getFreeCredential() ? $userGroup->getRole() : $group->getFreeCredential();
					}
					else {
						$roleGroup = $userGroup->getRole();
					}
				}
				else {
					if ($group->getFree()) {
						$roleGroup = $group->getFreeCredential();
					}
				}
			}
	
			if (empty($role) || $roleGroup && $role == $roleGroup) {
				$users[] = $user;
			}
		}
	
		$statement->closeCursor();
		$statement = null;
	
		$query = "	SELECT request.*
					FROM user, request
					WHERE user.id = request.user_id
					AND user.state = ".UserPeer::__STATE_ACTIVE."
					AND request.folder_id = ".$folder->getId();
	
		if (!empty($search) && $search != __("search") && $search != __("Search")) {
			$query .= " AND (user.email LIKE \"%".$search."%\" OR user.lastname LIKE \"%".$search."%\" OR user.firstname LIKE \"%".$search."%\")";
		}
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
		$requests = Array();
	
		while($rs = $statement->fetch())
		{
			$request = new Request();
			$request->hydrate($rs);
	
			$user = $request->getUser();
	
			if ($user->getRoleId() < RolePeer::__ADMIN) {
				$roleGroup = RolePeer::__ADMIN;
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($group->getId(), $user->getId());
					
				if ($userGroup) {
					if ($group->getFree()) {
						$roleGroup = $userGroup->getRole() < $group->getFreeCredential() ? $userGroup->getRole() : $group->getFreeCredential();
					}
					else {
						$roleGroup = $userGroup->getRole();
					}
				}
				else {
					if ($group->getFree()) {
						$roleGroup = $group->getFreeCredential();
					}
				}
			}
	
			if (empty($role) || $roleGroup && $role == $roleGroup) {
				$requests[] = $request;
			}
		}
	
		return count(array_merge($requests, $users));
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByFolderAndRole($folder_id, $role_id)
	{
		$criteria = new Criteria();
		$criteria->add(self::FOLDER_ID, $folder_id);
		$criteria->add(self::ROLE, $role_id);

		return self::doSelect($criteria);
	}
}
