<?php

/**
 * Subclass for performing query and update operations on the 'user' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UserPeer extends BaseUserPeer
{
	const __STATE_ACTIVE = 1;
	const __STATE_DELETE = 2;
	const __STATE_SUSPEND = 3;

	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$letter = isset($params["letter"]) ? $params["letter"] : "";
		$role = isset($params["role"]) ? (int)$params["role"] : 0;
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		$states = isset($params["states"]) ? (array)$params["states"] : array(self::__STATE_ACTIVE, 
				self::__STATE_SUSPEND);
		
		$zone = isset($params["zone"]) ? (int) $params["zone"] : 0;
		
		$criteria = new Criteria();
	
		if ($role == RolePeer::__ADMIN) {
			$criteria->add(self::ROLE_ID, RolePeer::__ADMIN);
		}
		elseif ($role == RolePeer::__READER) {
			$criteria->add(self::ROLE_ID, RolePeer::__READER);
		}

		if ($customerId) {
			$criteria->add(self::CUSTOMER_ID, $customerId);
		}
		
		if ($letter) {
			$criteria->add(self::LASTNAME, $letter.'%', Criteria::LIKE);
		}
	
		if (count($states)) {
			$criteria->add(self::STATE, $states, Criteria::IN);
		}

		if ($zone) {
			$criteria->addJoin(self::CUSTOMER_ID, CustomerPeer::ID, Criteria::INNER_JOIN);
			CriteriaUtils::setZone($criteria, CustomerPeer::COUNTRY_ID, $zone);
		}

		if ($keyword) {
			$c1 = $criteria->getNewCriterion(self::USERNAME, "%".$keyword."%", Criteria::LIKE);
	
			$c2 = $criteria->getNewCriterion(self::LASTNAME, "%".$keyword."%", Criteria::LIKE);
			$c3 = $criteria->getNewCriterion(self::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
			$c4 = $criteria->getNewCriterion(self::EMAIL, "%".$keyword."%", Criteria::LIKE);
			$c5 = $criteria->getNewCriterion(self::POSITION, "%".$keyword."%", Criteria::LIKE);
			$c6 = $criteria->getNewCriterion(self::PHONE, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c2);
			$c1->addOr($c3);
			$c1->addOr($c4);
			$c1->addOr($c5);
			$c1->addOr($c6);
			$criteria->add($c1);
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
	
		$pager = new sfPropelPager("User", $itemPerPage);
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
	/**
	 * Renvoie le dernier utilisateur qui c'est connecté au compte d'un client.
	 * 
	 * @param integer $customerId
	 */
	public static function getLastConnectionOfCustomer($customerId)
	{
		Assert::ok($customerId > 0);
	
		$criteria = new Criteria();
	
		$criteria->clearSelectColumns();
		UserPeer::addSelectColumns($criteria);
		$criteria->addSelectColumn("max(last_login_at) as max_last_login");
		
		$criteria->add(self::CUSTOMER_ID, $customerId);
		$criteria->add(self::LAST_LOGIN_AT, "0000-00-00 00:00:00", Criteria::NOT_EQUAL);
		
		return self::doSelectOne($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le premier admin actif ou non actif d'un customer.
	 * 
	 * @param int $customerId
	 */
	public static function retrieveFirstAdmin($customerId)
	{
		$criteria = new Criteria();
		
		$criteria->add(self::CUSTOMER_ID, $customerId);
		$criteria->add(self::ROLE_ID, RolePeer::__ADMIN);
		$criteria->add(self::STATE, array(self::__STATE_ACTIVE, self::__STATE_SUSPEND), Criteria::IN);
		$criteria->addAscendingOrderByColumn(self::CREATED_AT);
		
		return UserPeer::doSelectOne($criteria);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le premier admin actif d'un customer.
	 *
	 * @param int $customerId
	 */
	public static function retrieveFirstActiveAdmin($customerId)
	{
		$criteria = new Criteria();
	
		$criteria->add(self::CUSTOMER_ID, $customerId);
		$criteria->add(self::ROLE_ID, RolePeer::__ADMIN);
		$criteria->add(self::STATE, self::__STATE_ACTIVE);
		
		$criteria->addAscendingOrderByColumn(self::CREATED_AT);
	
		return UserPeer::doSelectOne($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/

	/*___________________________________________________________________________________________________________*/
	public static function retrieveByCustomerId($customer_id, $active = true, $login = false)
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, $customer_id);
		//$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		//$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		if ($active) {
			$c->add(self::STATE, self::__STATE_ACTIVE);
		}
		
		if ($login) {
			$c->add(self::LAST_LOGIN_AT, "0000-00-00 00:00:00", Criteria::NOT_EQUAL);
		}
		
		return self::doSelect($c);
	}

	/*___________________________________________________________________________________________________________*/
	public static function countByCustomerId($customer_id, $active = true, $login = false)
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, $customer_id);
		//$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		//$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		if ($active) {
			$c->add(self::STATE, self::__STATE_ACTIVE);
		}
		
		if ($login) {
			$c->add(self::LAST_LOGIN_AT, "0000-00-00 00:00:00", Criteria::NOT_EQUAL);
		}
		
		return self::doCount($c);
	}

	/*___________________________________________________________________________________________________________*/
	public static function retrieveByUsername($username)
	{
		$c = new Criteria();
		$c->add(self::USERNAME, $username);
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		return self::doSelectOne($c);
	}

	/*___________________________________________________________________________________________________________*/
	public static function retrieveByLogin($username)
	{
		$c = new Criteria();
		$c->add(self::USERNAME, $username);
		$c->add(self::STATE, self::__STATE_ACTIVE);

		return self::doSelectOne($c);
	}

	/*___________________________________________________________________________________________________________*/
	public static function retrieveByFirstname($firstname)
	{
		$c = new Criteria();
		$c->add(self::FIRSTNAME, $firstname);
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		return self::doSelectOne($c);
	}

	/*___________________________________________________________________________________________________________*/
	public static function retrieveByNames($names)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT user.id
					FROM user, customer
					WHERE user.customer_id = customer.id
					AND CONCAT(CONCAT(UCASE(SUBSTRING(user.lastname, 1, 1)), LCASE(SUBSTRING(user.lastname, 2))), ' ', CONCAT(UCASE(SUBSTRING(user.firstname, 1, 1)), LCASE(SUBSTRING(user.firstname, 2)))) = \"".$names."\"
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND user.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND user.state = ".$connection->quote(self::__STATE_ACTIVE);

		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(count($rs) > 0)
			return $rs[0]['id'];

		return 0;
	}

	/*___________________________________________________________________________________________________________*/
	public static function getUsers($keyword="", $sort="name_asc")
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));

		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);

			$c1 = $c->getNewCriterion(self::USERNAME, "%".$keyword."%", Criteria::LIKE);

			$c3 = $c->getNewCriterion(self::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
			$c4 = $c->getNewCriterion(self::EMAIL, "%".$keyword."%", Criteria::LIKE);
			$c5 = $c->getNewCriterion(self::POSITION, "%".$keyword."%", Criteria::LIKE);
			$c6 = $c->getNewCriterion(self::PHONE, "%".$keyword."%", Criteria::LIKE);

			$c1->addOr($c3);
			$c1->addOr($c4);
			$c1->addOr($c5);
			$c1->addOr($c6);
			$c->add($c1);
		}

		switch ($sort)
		{
			default:;
			case "name_asc": $c->addAscendingOrderByColumn(self::LASTNAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::LASTNAME); break;
			case "role_asc": $c->addAscendingOrderByColumn(self::ROLE_ID); break;
			case "role_desc": $c->addDescendingOrderByColumn(self::ROLE_ID); break;
			case "username_asc": $c->addAscendingOrderByColumn(self::USERNAME); break;
			case "username_desc": $c->addDescendingOrderByColumn(self::USERNAME); break;
			case "email_asc": $c->addAscendingOrderByColumn(self::EMAIL); break;
			case "email_desc": $c->addDescendingOrderByColumn(self::EMAIL); break;
			case "position_asc": $c->addAscendingOrderByColumn(self::POSITION); break;
			case "position_desc": $c->addDescendingOrderByColumn(self::POSITION); break;
			case "phone_asc": $c->addAscendingOrderByColumn(self::PHONE); break;
			case "phone_desc": $c->addDescendingOrderByColumn(self::PHONE); break;
			case "logged_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "logged_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::LAST_LOGIN_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::LAST_LOGIN_AT); break;
			case "hash_asc": $c->addAscendingOrderByColumn(self::HASH); break;
			case "hash_desc": $c->addDescendingOrderByColumn(self::HASH); break;
		}

		$c->setDistinct();
		return self::doSelect($c);
	}

	/*___________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getUserPager($keyword="", $sort="name_asc", $role="all", $l="all", $page=1)
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));

		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);

			$c1 = $c->getNewCriterion(self::USERNAME, "%".$keyword."%", Criteria::LIKE);

			$c2 = $c->getNewCriterion(self::LASTNAME, "%".$keyword."%", Criteria::LIKE);
			$c3 = $c->getNewCriterion(self::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
			$c4 = $c->getNewCriterion(self::EMAIL, "%".$keyword."%", Criteria::LIKE);
			$c5 = $c->getNewCriterion(self::POSITION, "%".$keyword."%", Criteria::LIKE);
			$c6 = $c->getNewCriterion(self::PHONE, "%".$keyword."%", Criteria::LIKE);

			$c1->addOr($c2);
			$c1->addOr($c3);
			$c1->addOr($c4);
			$c1->addOr($c5);
			$c1->addOr($c6);
			$c->add($c1);
		}

		switch ($sort)
		{
			default:;
			case "name_asc": $c->addAscendingOrderByColumn(self::LASTNAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::LASTNAME); break;
			case "fname_asc": $c->addAscendingOrderByColumn(self::FIRSTNAME); break;
			case "fname_desc": $c->addDescendingOrderByColumn(self::FIRSTNAME); break;
			case "role_asc": $c->addAscendingOrderByColumn(self::ROLE_ID); break;
			case "role_desc": $c->addDescendingOrderByColumn(self::ROLE_ID); break;
			case "username_asc": $c->addAscendingOrderByColumn(self::USERNAME); break;
			case "username_desc": $c->addDescendingOrderByColumn(self::USERNAME); break;
			case "email_asc": $c->addAscendingOrderByColumn(self::EMAIL); break;
			case "email_desc": $c->addDescendingOrderByColumn(self::EMAIL); break;
			case "position_asc": $c->addAscendingOrderByColumn(self::POSITION); break;
			case "position_desc": $c->addDescendingOrderByColumn(self::POSITION); break;
			case "phone_asc": $c->addAscendingOrderByColumn(self::PHONE); break;
			case "phone_desc": $c->addDescendingOrderByColumn(self::PHONE); break;
			case "logged_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "logged_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::LAST_LOGIN_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::LAST_LOGIN_AT); break;
			case "hash_asc": $c->addAscendingOrderByColumn(self::HASH); break;
			case "hash_desc": $c->addDescendingOrderByColumn(self::HASH); break;
		}

		if($role == RolePeer::__LABEL_ADMIN)
			$c->add(self::ROLE_ID, RolePeer::__ADMIN);
		elseif($role == RolePeer::__LABEL_READER)
			$c->add(self::ROLE_ID, RolePeer::__READER);
		elseif($role == "external")
			$c->add(self::ROLE_ID, 6);

		if($l && $l != "all")
			$c->add(self::LASTNAME, $l.'%', Criteria::LIKE);

		$c->setDistinct();

		$pager = new sfPropelPager('User', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();

		return $pager;
	}

	/*___________________________________________________________________________________________________________*/
	public static function fetchUsers($name)
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		$c1 = $c->getNewCriterion(self::LASTNAME, "%".$name."%", Criteria::LIKE);
		$c2 = $c->getNewCriterion(self::FIRSTNAME, "%".$name."%", Criteria::LIKE);

		$c1->addOr($c2);
		$c->add($c1);
		$c->setDistinct();

		return self::doSelect($c);
	}

	/*___________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see UserUnitPeer.findUsersToAdd
	 */
	public static function fetchUsersUnit($name, $unit_id)
	{
		$users = UnitPeer::getEffectiveIdInArray($unit_id);

		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		$c1 = $c->getNewCriterion(self::LASTNAME, "%".$name."%", Criteria::LIKE);
		$c2 = $c->getNewCriterion(self::FIRSTNAME, "%".$name."%", Criteria::LIKE);
		$c3 = $c->getNewCriterion(self::EMAIL, "%".$name."%", Criteria::LIKE);

		$c1->addOr($c2);
		$c1->addOr($c3);
		$c->add($c1);
		$c->add(self::ID, $users, Criteria::NOT_IN);

		return self::doSelect($c);
	}

	/*___________________________________________________________________________________________________________*/
	public static function fetchUser($key)
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		$c1 = $c->getNewCriterion(self::USERNAME, "%".$key."%", Criteria::LIKE);
		$c2 = $c->getNewCriterion(self::FIRSTNAME, "%".$key."%", Criteria::LIKE);
		$c3 = $c->getNewCriterion(self::EMAIL, "%".$key."%", Criteria::LIKE);

		$c1->addOr($c2);
		$c1->addOr($c3);
		$c->add($c1);

		return self::doSelect($c);
	}

	/*___________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function fetchUserGroup($key, $group_id)
	{
		$group = GroupePeer::retrieveByPk($group_id);

		if($group->getFree())
			$users = CustomerPeer::getMyUsersNoPager($group->getCustomerId());
		else
		{
			$users = Array();
			$temp = UserGroupPeer::retrieveByGroupId($group->getId());

			foreach($temp as $user)
			{
				if($user->getUser())
					$users[] = $user->getUser();
			}
		}

		$return_users = Array();

		foreach($users as $user)
		{
			if($user->getState() == self::__STATE_ACTIVE)
			{
				if(strstr($user->getUsername(), $key) || strstr($user->getFirstname(), $key) || strstr($user->getEmail(), $key))
					$return_users[] = $user;
			}
		}

		return $return_users;
	}

	/*___________________________________________________________________________________________________________*/
	public static function fetchUserFolder($key, $folder_id)
	{
		$folder = FolderPeer::retrieveByPk($folder_id);

		if($folder->getSubfolderId())
		{
			$users = UserFolderPeer::getUsers($folder->getSubfolderId());

			$return_users = Array();

			foreach($users as $user)
			{
				if($user->getState() == self::__STATE_ACTIVE)
				{
					if(strstr($user->getUsername(), $key) || strstr($user->getFirstname(), $key) || strstr($user->getEmail(), $key))
						$return_users[] = $user;
				}
			}

			return $return_users;
		}
		else
			return self::fetchUserGroup($key, $folder->getGroupeId());
	}

	/*___________________________________________________________________________________________________________*/
	public static function retrieveByRoleIds($role_ids=array())
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::ROLE_ID, $role_ids, Criteria::IN);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * 
	 * @param number $year
	 * @param number $month
	 * @return number
	 */
	public static function retrieveTotalNB($year=0, $month=0)
	{
		$connection = Propel::getConnection();

		$endDay = date("t", time(0,0,0,$month,1,$year));
		$date_s = $year.'-'.$month.'-'.'01 00:00:00';
		$date_f = $year.'-'.$month.'-'.$endDay.' 23:59:59';

		if(!empty($year) && !empty($month))
		{
			$query = "SELECT count(*) as total FROM `user`";
			$query .= ", customer";

			$query .= " WHERE user.created_at < \"".$connection->quote($date_f)."\" and user.state = ".
			$connection->quote(self::__STATE_ACTIVE);
		}
		else
		{
			$query = "SELECT count(*) as total FROM `user`";
			$query .= ", customer";

			$query .= " WHERE user.state = ".$connection->quote(self::__STATE_ACTIVE);
		}

		$query .= " AND customer.id = user.customer_id AND customer.state = ".
		$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND user.customer_id = ".
		$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(count($rs) > 0)
		  return $rs[0]["total"];
		else
			return 0;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * 
	 * @return multitype:NULL
	 */
	public static function getLetters()
	{
		$connection = Propel::getConnection();

		$query = "SELECT DISTINCT UPPER(substr( lastname, 1, 1 )) AS letter
					FROM `user`";
		$query .= ", customer";

		$query .= " WHERE user.state = ".$connection->quote(self::__STATE_ACTIVE);

		$query .= " AND user.customer_id = customer.id AND customer.state = ".
		$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND user.customer_id = ".
		$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

		$query .= " ORDER BY `letter` ASC";

		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$l = array();
		for($i = 0; $i < count($rs); $i++) {
		  $l[] = $rs[$i]['letter'];
		}

		return $l;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des premières lettres de nom des utilisateurs actifs.
	 * 
	 * @param string $customerId
	 * @return multitype:NULL
	 */
	public static function getFirstLettersOfName($customerId = null)
	{
		$connection = Propel::getConnection();
		
		$query = "SELECT DISTINCT UPPER(substr( lastname, 1, 1 )) AS letter
					FROM `user`";
		
		$query .= " WHERE user.state = ".$connection->quote(self::__STATE_ACTIVE);
		
		if ($customerId) {
			$query .= " AND user.customer_id = ".(int)$customerId;
		}
		
		$query .= " ORDER BY `letter` ASC";
		
		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
		
		$l = array();
		
		for($i = 0; $i < count($rs); $i++) {
			$l[] = $rs[$i]['letter'];
		}
		
		return $l;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function selectGroupIds($user_id)
	{
		$connection = Propel::getConnection();

		$query = "SELECT groupe_id as id FROM `user_group`, `groupe`, customer 
				WHERE user_group.group_id = groupe.id AND groupe.customer_id = customer.id 
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND user_id = ".
		$connection->quote($user_id)." AND groupe.customer_id = ".
		$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$group_ids = array();
		for($i = 0; $i < count($rs); $i++) {
			$group_ids[] = $rs[$i]["id"];
		}

		return $group_ids;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveActiveUserNB($year=0, $month=0)
	{
		$endDay = date("t", time(0,0,0,$month,1,$year));
		$date_s = $year.'-'.$month.'-'.'01 00:00:00';
		$date_f = $year.'-'.$month.'-'.$endDay.' 23:59:59';

		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(ConsumerLogCriteriaPeer::CREATED_AT, $year.'-'.$month.'-01 00:00:01', Criteria::LESS_THAN);
		$c->addDescendingOrderByColumn(ConsumerLogCriteriaPeer::CREATED_AT);
		$consumer_log_criteria = ConsumerLogCriteriaPeer::doSelectOne($c);
		if($consumer_log_criteria){
			
			$union = '';
			if($consumer_log_criteria->getFileDownload()):
				if($union != '') $union .= ',';
				$union .= "'file-download'";  
			endif;
			
			if($consumer_log_criteria->getCreateFolder()):
				if($union != '') $union .= ',';
				$union .= "'group-create','folder-create'";  
			endif;
			
			if($consumer_log_criteria->getFileUpload()):
				if($union != '') $union .= ',';
				$union .= "'file-download'";  
			endif;
			
			if($consumer_log_criteria->getFileRetouch()):
				if($union != '') $union .= ',';
				$union .= "'file-retouch'";  
			endif;
			if($consumer_log_criteria->getFilePrint()):
				if($union != '') $union .= ',';
				$union .= "'file-print'";  
			endif;
			
			if($consumer_log_criteria->getSendFile()):
				if($union != '') $union .= ',';
				$union .= "'file-send'";  
			endif;
			
			if($consumer_log_criteria->getCreatePermalink()):
				if($union != '') $union .= ',';
				$union .= "'permalink-create'";  
			endif;
			
		}else{
			$union = "'group-create','folder-create','file-retouch', 'file-download', 'file-print', 'file-send', 'permalink-create'";
		}

		$connection = Propel::getConnection();

		if(!empty($year) && !empty($month))
		{
			$query = "SELECT count(*) as total FROM `user`";
			$query .= ", customer";

			$query .= " WHERE user.state = ".$connection->quote(self::__STATE_ACTIVE)." AND user.id IN(select user_id from log where created_at > \"".$connection->quote($date_s)."\" and created_at < \"".$connection->quote($date_f)."\" and log_type in($union))";
		}
		else
		{
			$query = "SELECT count(*) as total FROM `user`";
			$query .=", customer";

			$query .= " WHERE user.state = ".$connection->quote(self::__STATE_ACTIVE)." AND id IN(select user_id from log where log_type in($union))";
		}

		$query .= " AND user.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND user.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(count($rs) > 0)
			return $rs[0]["total"];
		else
			return 0;
	}

	/*________________________________________________________________________________________________________________*/
	public static function isAllowed($id, $type)
	{
		$connection = Propel::getConnection();

		switch($type) {
			case 'group':
				
					$query = "SELECT count(user.id) as total
							FROM user, groupe, customer
							WHERE user.customer_id = groupe.customer_id
							AND user.customer_id = customer.id
							AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
							AND user.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
							AND user.state = ".$connection->quote(self::__STATE_ACTIVE)."
							AND groupe.id = ".$connection->quote($id);
				
			break;

			case 'folder':
				
					$query = "SELECT count(user.id) as total
							FROM user, groupe, folder, customer
							WHERE user.customer_id = groupe.customer_id
							AND folder.groupe_id = groupe.id
							AND user.customer_id = customer.id
							AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
							AND user.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
							AND user.state = ".$connection->quote(self::__STATE_ACTIVE)."
							AND folder.id = ".$connection->quote($id);
				
			break;

			case 'file':
				$file = FilePeer::retrieveByPk($id);
				
					$query = "SELECT count(user.id) as total
								FROM user, groupe, file, customer
								WHERE user.customer_id = groupe.customer_id
								AND file.groupe_id = groupe.id
								AND user.customer_id = customer.id
								AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
								AND user.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
								AND user.state = ".$connection->quote(self::__STATE_ACTIVE)."
								AND file.id = ".$connection->quote($id);
				
			break;

			case 'user':
				$query = "SELECT count(user.id) as total
							FROM user, customer
							WHERE user.customer_id = customer.id
							AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
							AND user.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
							AND user.state = ".$connection->quote(self::__STATE_ACTIVE)."
							AND user.id = ".$connection->quote($id);
			break;
		}

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return !empty($rs[0]["total"]) > 0 ? true : false;
	}

	/*public static function retrieveByPK($pk, PropelPDO $con = null)
	{

		if (null !== ($obj = UserPeer::getInstanceFromPool((string) $pk))) {
			return $obj;
		}

		if ($con === null) {
			$con = Propel::getConnection(UserPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria = new Criteria(UserPeer::DATABASE_NAME);
		$criteria->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$criteria->add(UserPeer::ID, $pk);

		$v = UserPeer::doSelect($criteria, $con);

		return !empty($v) > 0 ? $v[0] : null;
	}*/

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * 
	 * @param unknown $pk
	 * @param PropelPDO $con
	 * @return Ambigous <User, NULL, multitype:>|Ambigous <NULL, unknown, User, multitype:>
	 */
	public static function retrieveByPKNoCustomer($pk, PropelPDO $con = null)
	{
		return parent::retrieveByPK($pk, $con);
	}

	/*________________________________________________________________________________________________________________*/
	/*public static function retrieveByPKs($pks, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(UserPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$objs = null;
		if (empty($pks)) {
			$objs = array();
		} else {
			$criteria = new Criteria(UserPeer::DATABASE_NAME);
			$criteria->add(UserPeer::ID, $pks, Criteria::IN);
			$criteria->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$objs = UserPeer::doSelect($criteria, $con);
		}
		return $objs;
	}*/

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByEmail($email, $customer_id = 0, $active_only = true)
	{
		$c = new Criteria();
		$c->add(self::EMAIL, $email);

		if($active_only)
			$c->add(self::STATE, self::__STATE_ACTIVE);

		if(!empty($customer_id))
		{
			$c->add(self::CUSTOMER_ID, $customer_id);
			$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		}

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getStats($user_id)
	{
		$results = array();

		$connection1 = Propel::getConnection();

		$query = "SELECT sum(size) as download
				FROM file, log
				WHERE file.id = log.object_id
				AND log.type = '3'
				AND log_type IN ('file-download', 'files-download')
				AND file.user_id = ".$connection1->quote($user_id);

		$statement = $connection1->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs1 = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(count($rs1) > 0)
			$results["download"] = $rs1[0]["download"];
		else
			$results["download"] = 0;

		$connection2 = Propel::getConnection();

		$query = "SELECT count(file.id) as total_file, sum(file.size) as total_size
				FROM file
				WHERE file.user_id = ".$connection2->quote($user_id)."
				AND file.state = ".$connection2->quote(FilePeer::__STATE_VALIDATE);

		$statement = $connection2->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs2 = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(count($rs2) > 0)
		{
			$results["nb"] = $rs2[0]["total_file"];
			$results["size"] = $rs2[0]["total_size"];
		}
		else
		{
			$results["nb"] = 0;
			$results["size"] = 0;
		}

		$connection3 = Propel::getConnection();

		$query = "SELECT sum(file.size) as upload
				FROM file
				WHERE file.user_id = ".$connection3->quote($user_id);

		$statement = $connection3->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs3 = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(count($rs3) > 0)
			$results["upload"] = $rs3[0]["upload"];
		else
			$results["upload"] = 0;

		$connection4 = Propel::getConnection();

		$query = "SELECT file.groupe_id as group_id, count(file.id) as nb_file
				FROM file
				WHERE file.user_id = ".$connection4->quote($user_id)."
				AND file.state = ".$connection4->quote(FilePeer::__STATE_VALIDATE)."
				GROUP BY file.groupe_id
				ORDER BY count(file.id) DESC
				LIMIT 0,1";

		$statement = $connection4->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs4 = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(count($rs4) > 0)
			$results["group"] = "<a href=\"/group/show/".$rs4[0]["group_id"]."/session/start\" target=\"_blank\">".GroupePeer::retrieveByPk($rs4[0]["group_id"])." (".$rs4[0]["nb_file"].")</a>";
		else
			$results["group"] = null;

		return $results;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByHash($hash)
	{
		$c = new Criteria();
		$c->add(self::HASH, $hash);
		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByRoleIdsAndCustomerId($role_ids=array(), $customer_id = null)
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(self::ROLE_ID, $role_ids, Criteria::IN);
		$c->add(self::CUSTOMER_ID, $customer_id);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function fetchUserForAlbum($group_id, $term)
	{
		$connection = Propel::getConnection();
		$group = GroupePeer::retrieveByPK($group_id);

		$query = "	SELECT user.*
					FROM user
					WHERE user.customer_id = ".sfContext::getInstance()->getUser()->getCustomerId()."
					AND user.state = ".self::__STATE_ACTIVE."
					AND user.id NOT IN (SELECT user_group.user_id
										FROM user_group
										WHERE user_group.groupe_id = ".$group->getId()."
										AND user_group.user_id IS NOT NULL)
					AND (user.email LIKE \"%".$term."%\" OR user.firstname LIKE \"%".$term."%\" OR user.lastname LIKE \"%".$term."%\")";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);

		$users = array();

		while ($rs = $statement->fetch()) {
			$user = new User();
			$user->hydrate($rs);
			$users[$user->getId()] = $user;
		}

		$statement->closeCursor();
		$statement = null;

		return $users;
	}
}
