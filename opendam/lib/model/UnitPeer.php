<?php

/**
 * Subclass for performing query and update operations on the 'unit' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UnitPeer extends BaseUnitPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$letter = isset($params["letter"]) ? $params["letter"] : "";
		$role = isset($params["role"]) ? (int)$params["role"] : 0;
		$albumId = isset($params["albumId"]) ? (int)$params["albumId"] : 0;
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		$userId = isset($params["userId"]) ? (int)$params["userId"] : 0;
		$roleState = isset($params["roleState"]) ? $params["roleState"] : 0;

		$criteria = new Criteria();

		if ($customerId) {
			$criteria->add(self::CUSTOMER_ID, $customerId);
		}

		if ($letter) {
			$criteria->add(self::TITLE, $letter.'%', Criteria::LIKE);
		}

		if ($userId) {
			$criteria->addJoin(UserUnitPeer::UNIT_ID, self::ID, Criteria::INNER_JOIN);
			$criteria->add(UserUnitPeer::USER_ID, $userId);
		}

		if (($role || $roleState) && $albumId) {
			$criteria->setDistinct();
			$criteria->addJoin(UnitGroupPeer::UNIT_ID, self::ID);
			$criteria->add(UnitGroupPeer::GROUPE_ID, $albumId);

			if ($role) {
				$criteria->add(UnitGroupPeer::ROLE, $role);
			}
		}

		if ($keyword) {
			$c1 = $criteria->getNewCriterion(self::TITLE, "%".$keyword."%", Criteria::LIKE);
			$criteria->add($c1);
		}
	
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
	
		if ($limit) {
			$criteria->setLimit($limit);
		}
	
		return $criteria;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function findBy(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		return self::doSelect(self::doCriteria($params, $orderBy, $limit));
	}

	/*________________________________________________________________________________________________________________*/
	public static function getPager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);
	
		$pager = new sfPropelPager("Unit", $itemPerPage);
		$pager->setCriteria(self::doCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getLettersOfPager(array $params = array())
	{
		$criteria = self::doCriteria($params);
	
		$criteria->clearSelectColumns();
		$criteria->addSelectColumn("DISTINCT UPPER(substr(".self::TITLE.", 1, 1 )) AS letter");
		$criteria->addAscendingOrderByColumn("letter");
	
		$letters = self::doSelectStmt($criteria);
	
		return $letters->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi les groupes que l'utilisateur ne possède pas.
	 */
	public static function findGroupOfUserToAdd(array $params, array $orderBy = array(), $limit = 0)
	{
		Assert::ok($params["customerId"] > 0);
		Assert::ok($params["userId"] > 0);
		
		$customerId = (int)$params["customerId"];
		$userId = (int)$params["userId"];
		
		$criteria = self::doCriteria(array("customerId" => $customerId), $orderBy, $limit);
		
		$criteriaToRemove = self::doCriteria(array("userId" => $userId));
		CriteriaUtils::setSelectColumn($criteriaToRemove, self::ID);
		
		$criteria->add(self::ID, self::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($criteriaToRemove).")", 
				Criteria::CUSTOM);

		return self::doSelect($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getUnitPager($keyword="", $sort="title_asc", $l="all", $page=1)
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
	
		if ($keyword && $keyword != __("search") && $keyword != __("Search")) {
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
			$c->add(self::TITLE, "%".$keyword."%", Criteria::LIKE);
		}

		switch ($sort) {
			default:;
			case "name_asc": $c->addAscendingOrderByColumn(self::TITLE); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::TITLE); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			case "effective_asc": $c->addAscendingOrderByColumn('count('.UserUnitPeer::UNIT_ID.')'); break;
			case "effective_desc": $c->addDescendingOrderByColumn('count('.UserUnitPeer::UNIT_ID.')'); break;
		}
	
		if ($l && $l != "all") {
			$c->add(UnitPeer::TITLE, $l.'%', Criteria::LIKE);
		}
	
		$c->setDistinct();
	
		$pager = new sfPropelPager('Unit', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getLetters()
	{
		$connection = Propel::getConnection();
	
		$query = "SELECT DISTINCT UPPER(substr( title, 1, 1 )) AS letter
					FROM `unit`, customer
					WHERE unit.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND unit.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					ORDER BY `letter` ASC";
	
		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		$l = array();
		
		for ($i = 0; $i < count($rs); $i++) {
			$l[] = $rs[$i]['letter'];
		}

		return $l;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getEffective($unitId)
	{
		$connection = Propel::getConnection();
	
		$query = "SELECT count(user_unit.id) AS effectif
					FROM `user_unit`, `unit`, customer
					WHERE user_unit.unit_id = unit.id
					AND unit.customer_id = customer.id
					AND customer.state = ".CustomerPeer::__STATE_ACTIVE."
					AND user_unit.unit_id = \"".$unitId."\"";

		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if (count($rs) > 0)
			return $rs[0]['effectif'];
		else
			return 0;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getEffectiveInArray($unit_id)
	{
		$c = new Criteria();
		
		//$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		//$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->addJoin(UserUnitPeer::UNIT_ID, self::ID);
		$c->addJoin(UserUnitPeer::USER_ID, UserPeer::ID);
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		$c->add(self::ID, $unit_id);
		$c->addAscendingOrderByColumn(self::TITLE);
		
		$users = UserPeer::doSelect($c);
	
		$users_array = array();
	
		foreach ($users as $user){
			$users_array[$user->getId()] = $user;
		}
		
		return $users_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getEffectiveForm($unit_id)
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->addJoin(UserUnitPeer::UNIT_ID, self::ID);
		$c->addJoin(UserUnitPeer::USER_ID, UserPeer::ID);
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		$c->add(self::ID, $unit_id);
		$c->addAscendingOrderByColumn(self::TITLE);
		$users = UserPeer::doSelect($c);
	
		$users_array = array();
	
		foreach ($users as $user){
			$users_array[$user->getId()] = ucfirst(strtolower($user->getFirstname()))." / ".
				ucfirst(strtolower($user->getLastname()))." / ".$user->getEmail();
		}
		
		return $users_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getEffectiveIdInArray($unit_id)
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->addJoin(UserUnitPeer::UNIT_ID, self::ID);
		$c->addJoin(UserUnitPeer::USER_ID, UserPeer::ID);
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		$c->add(self::ID, $unit_id);
		$c->addAscendingOrderByColumn(self::TITLE);
		$users = UserPeer::doSelect($c);
	
		$users_array = array();
		
		foreach ($users as $user){
			$users_array[] = $user->getId();
		}
		
		return $users_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByCustomerId($customer_id)
	{
		$c = new Criteria();
	
		$c->add(self::CUSTOMER_ID, $customer_id);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::TITLE);
	
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getUnitsInArray($user_id = 0)
	{
		$c = new Criteria();

		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::ID, UserUnitPeer::getUnitsFromUserInArray($user_id), Criteria::NOT_IN);
		$c->setDistinct();
		$units = self::doSelect($c);
	
		$units_array = array();
		
		foreach ($units as $unit){
			$units_array[$unit->getId()] = $unit;
		}

		return $units_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function fetchUnit($key)
	{
		$c = new Criteria();

		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		$c1 = $c->getNewCriterion(self::TITLE, "%".$key."%", Criteria::LIKE);
	
		$c->add($c1);
	
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByName($unit_name, $customer_id = null)
	{
		$c = new Criteria();

		if ($customer_id) {
			$c->add(self::CUSTOMER_ID, $customer_id);
		}
		//else
			//$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::TITLE, $unit_name);
	
		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	/*public static function retrieveByPK($pk, PropelPDO $con = null)
	{

		if (null !== ($obj = UnitPeer::getInstanceFromPool((string) $pk))) {
			return $obj;
		}

		if ($con === null) {
			$con = Propel::getConnection(UnitPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$criteria = new Criteria(UnitPeer::DATABASE_NAME);
		$criteria->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$criteria->add(UnitPeer::ID, $pk);

		$v = UnitPeer::doSelect($criteria, $con);

		return !empty($v) > 0 ? $v[0] : null;
	}*/

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByPKNocustomer($pk, PropelPDO $con = null)
	{
		return parent::retrieveByPK($pk, $con);
	}

	/*________________________________________________________________________________________________________________*/
	/*public static function retrieveByPKs($pks, PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(UnitPeer::DATABASE_NAME, Propel::CONNECTION_READ);
		}

		$objs = null;
		if (empty($pks)) {
			$objs = array();
		} else {
			$criteria = new Criteria(UnitPeer::DATABASE_NAME);
			$criteria->add(UnitPeer::ID, $pks, Criteria::IN);
			$criteria->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$objs = UnitPeer::doSelect($criteria, $con);
		}
		return $objs;
	}*/

	/*________________________________________________________________________________________________________________*/
	public static function deleteAll($unit_id)
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::ID, $unit_id);

		if (self::doCount($c) > 0) {
			$c = new Criteria();
			$c->add(UserUnitPeer::UNIT_ID, $unit_id);
			UserUnitPeer::doDelete($c);
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function fetchUnitForAlbum($group_id, $term, $output = "array")
	{
		$connection = Propel::getConnection();
		$group = GroupePeer::retrieveByPK($group_id);
	
		$query = "	SELECT unit.*
					FROM unit
					WHERE unit.customer_id = ".sfContext::getInstance()->getUser()->getCustomerId()."
					AND unit.id NOT IN (SELECT unit_group.unit_id
										FROM unit_group
										WHERE unit_group.groupe_id = ".$group->getId().")
					AND unit.title LIKE \"%".$term."%\"";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		$units = array();
	
		while ($rs = $statement->fetch()) {
			$unit = new Unit();
			$unit->hydrate($rs);
	
			switch($output) {
				case "json":
					$units[] = array(
						"id" => "unit-".$unit->getId(),
						"value" => $unit->getTitle(),
						"label" => "<i class='icon-group'></i> ".$unit->getTitle()
					);
					break;
	
				default: $units[$unit->getId()] = $unit; break;
			}
		}
	
		$statement->closeCursor();
		$statement = null;
	
		return $units;
	}
}
