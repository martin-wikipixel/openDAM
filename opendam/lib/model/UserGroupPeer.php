<?php

/**
 * Subclass for performing query and update operations on the 'user_group' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UserGroupPeer extends BaseUserGroupPeer
{
	const __STATE_ACTIVE = 1;
	const __STATE_PENDING = 2;

	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$state = isset($params["state"]) ? (int) $params["state"] : 0;
		$userId = isset($params["userId"]) ? (int) $params["userId"] : "";
		$albumId = isset($params["albumId"]) ? (int) $params["albumId"] : "";
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		$role = isset($params["role"]) ? (int) $params["role"] : "";
		$letter = isset($params["letter"]) ? $params["letter"] : "";
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";

		$criteria = new Criteria();

		
		$criteria->addJoin(self::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);

		if ($letter) {
			$criteria->add(UserPeer::EMAIL, $letter.'%', Criteria::LIKE);
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

		$criteria->addJoin(self::GROUPE_ID, GroupePeer::ID, Criteria::INNER_JOIN);

		if ($state) {
			$criteria->add(self::STATE, $state);
		}

		if ($role) {
			$criteria->add(self::ROLE, $role);
		}

		if ($customerId) {
			$criteria->add(UserPeer::CUSTOMER_ID, $customerId);
		}

		if ($albumId) {
			$criteria->add(self::GROUPE_ID, $albumId);
			$criteria->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		}

		if ($userId) {
			$criteria->add(self::USER_ID, $userId);
			$criteria->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		}

		CriteriaUtils::buildOrderBy($criteria, $orderBy);

		if ($limit) {
			$criteria->setLimit($limit);
		}

		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getPager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);
	
		$pager = new sfPropelPager("UserGroup", $itemPerPage);
		$pager->setCriteria(self::doCriteria($params, $orderBy));
	
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function findBy(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		return self::doSelect(self::doCriteria($params, $orderBy, $limit));
	}

	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public static function retrieveByUserAndGroup($userId, $groupId)
	{
		$criteria = new Criteria();
		
		$criteria->add(self::USER_ID, $userId);
		$criteria->add(self::GROUPE_ID, $groupId);
		
		return self::doSelectOne($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByGroupId($group_id)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUsers($group_id, $role)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::ROLE, $role);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUserGroup($group_id, $user_id, $force = false)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		if($force == false)
		{
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		}

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getRole($user_id, $group_id, $label = false)
	{
		$user = UserPeer::retrieveByPKNoCustomer($user_id);

		$c = new Criteria();
		$c->add(self::USER_ID, $user->getId());
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		
			$c->add(GroupePeer::CUSTOMER_ID, $user->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);


		$user_group = self::doSelectOne($c);
	
		if($user_group)
		{
			if($label)
			{
				$roles = array(
					RolePeer::__ADMIN => RolePeer::__LABEL_ADMIN,
					RolePeer::__CONTRIB => RolePeer::__LABEL_CONTRIB,
					RolePeer::__READER => RolePeer::__LABEL_READER
				);

				if(array_key_exists($user_group->getRole(), $roles))
					return $roles[$user_group->getRole()];
			}
			else
				return $user_group->getRole();
		}
		else
		{
			$group = GroupePeer::retrieveByPkNoCustomer($group_id);

			if($group)
			{
				if($group->getFree())
				{
					if($label)
					{
						$roles = array(
							RolePeer::__ADMIN => RolePeer::__LABEL_ADMIN,
							RolePeer::__CONTRIB => RolePeer::__LABEL_CONTRIB,
							RolePeer::__READER => RolePeer::__LABEL_READER
						);

						if(array_key_exists($group->getFreeCredential(), $roles))
							return $roles[$group->getFreeCredential()];
					}
					else
						return $group->getFreeCredential();
				}
			}
		}

		return "";
	}

	/*________________________________________________________________________________________________________________*/
	public static function getGroupIds($user_id, $role="", $force = false)
	{
		$c = new Criteria();
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		if($force == false)
		{
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		}

		if($role) $c->add(self::ROLE, $role);
		$user_groups = self::doSelect($c);

		$group_ids = array();

		foreach ($user_groups as $user_group)
			$group_ids[] = $user_group->getGroupeId();

		return $group_ids;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByGroupIdAndUserId($group_id, $user_id)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::USER_ID, $user_id);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveAdmin($group_id)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::ROLE, RolePeer::__ADMIN);

		return self::doSelect($c);
	}

	public static function updateToFreeAccess($group_id)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT user_group.*
					FROM user_group
					WHERE user_group.groupe_id = ".$group_id."
					AND user_group.role > ".RolePeer::__ADMIN;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);

		while ($rs = $statement->fetch()) {
			$userGroup = new UserGroup();
			$userGroup->hydrate($rs);

			$userGroup->delete();
		}

		$statement->closeCursor();
		$statement = null;

		$query = "	SELECT unit_group.*
					FROM unit_group
					WHERE unit_group.groupe_id = ".$group_id."
					AND unit_group.role > ".RolePeer::__ADMIN;
		
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);

		while ($rs = $statement->fetch()) {
			$unitGroup = new UnitGroup();
			$unitGroup->hydrate($rs);
		
			$unitGroup->delete();
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getCountAllUsersAndUnits(Criteria $c)
	{
		$connection = Propel::getConnection();
		$user_ids = Array();
	
		$map = $c->getMap();
		$group_id = $map[UserGroupPeer::GROUPE_ID]->getValue();
		$role = $map[UserGroupPeer::ROLE]->getValue();
		$search = $map[UserGroupPeer::USER_ID]->getValue();
	
		$select = "SELECT user_group.*";
		$from = "FROM user_group";
		$where = "WHERE user_group.groupe_id = ".$connection->quote($group_id);
	
		if(!empty($role) && $role != "pending")
			$where .= " AND user_group.role = ".$connection->quote($role);
	
		if($role == "pending")
			$where .= " AND user_group.state = ".self::__STATE_PENDING;
	
		if(!empty($search) && $search != __("search") && $search != __("Search"))
		{
			sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
			$keyword = htmlentities(replaceAccentedCharacters($search), ENT_QUOTES);
	
			if($role != "pending")
			{
				$from .= " LEFT JOIN user ON user_group.user_id = user.id";
				$where .= " AND ((user.lastname LIKE ".$connection->quote("%".$keyword."%").") OR (user.firstname LIKE ".$connection->quote("%".$keyword."%").") OR (user.email LIKE ".$connection->quote("%".$keyword."%")."))";
			}
		}
	
		$statement = $connection->query($select." ".$from." ".$where);
		$statement->setFetchMode(PDO::FETCH_NUM);
		$user_group = Array();
	
		while($rs = $statement->fetch())
		{
			$userGroup = new UserGroup();
			$userGroup->hydrate($rs);
	
			if(!in_array($rs[1], $user_ids) && !empty($rs[1]))
				$user_ids[] = $rs[1];
	
			if(!in_array($userGroup, $user_group))
				$user_group[] = $userGroup;
		}
	
		$statement->closeCursor();
		$statement = null;
	
		$unit_group = Array();
	
		if($role != "pending")
		{
			$select = "SELECT unit_group.*";
			$from = "FROM unit_group";
			$where = "WHERE unit_group.groupe_id = ".$connection->quote($group_id);
	
			if(!empty($role) && $role != "pending")
				$where .= " AND unit_group.role = ".$connection->quote($role);
	
			if(!empty($search) && $search != __("search") && $search != __("Search"))
			{
				sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
				$keyword = htmlentities(replaceAccentedCharacters($search), ENT_QUOTES);
	
				$from .= " LEFT JOIN unit ON unit_group.unit_id = unit.id";
				$where .= " AND unit.title LIKE \"%".$connection->quote($keyword)."%\"";
			}
	
			$statement = $connection->query($select." ".$from." ".$where);
			$statement->setFetchMode(PDO::FETCH_NUM);
	
			while($rs = $statement->fetch())
			{
				$unitGroup = new UnitGroup();
				$unitGroup->hydrate($rs);
	
				if(!in_array($unitGroup, $unit_group))
					$unit_group[] = $unitGroup;
			}
	
			$statement->closeCursor();
			$statement = null;
		}
	
		if(empty($role))
		{
			$c = new Criteria();
			$c->add(RequestPeer::GROUPE_ID, $group_id);
			$c->addJoin(GroupePeer::ID, RequestPeer::GROUPE_ID);
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
			if(!empty($search) && $search != __("search") && $search != __("Search"))
			{
				sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
				$keyword = htmlentities(replaceAccentedCharacters($search), ENT_QUOTES);
	
				$c->addJoin(RequestPeer::USER_ID, UserPeer::ID);
	
				$c1 = $c->getNewCriterion(UserPeer::LASTNAME, "%".$keyword."%", Criteria::LIKE);
				$c2 = $c->getNewCriterion(UserPeer::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
				$c3 = $c->getNewCriterion(UserPeer::EMAIL, "%".$keyword."%", Criteria::LIKE);
	
				$c1->addOr($c2);
				$c1->addOr($c3);
				$c->add($c1);
			}
	
			$user_request = RequestPeer::doSelect($c);
		}
		else
			$user_request = Array();
	
		return count(array_merge($user_request, $user_group, $unit_group));
	}
}
