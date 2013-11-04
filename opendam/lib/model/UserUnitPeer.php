<?php

/**
 * Subclass for performing query and update operations on the 'user_unit' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UserUnitPeer extends BaseUserUnitPeer
{
	/*___________________________________________________________________________________________________________*/
	/**
	 * Renvoi les utilisateurs a ajouter dans un groupe.
	 * (Liste tous les utilisateurs d'un customer moins ceux déjà ajoutés.
	 * 
	 * @param array $params
	 * @param array $orderBy
	 * @param number $limit
	 * 
	 * @return Array<User>
	 */
	public static function findUsersToAdd(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$groupId = isset($params["groupId"]) ? (int)$params["groupId"] : 0;
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		
		Assert::ok($groupId > 0);
		Assert::ok($customerId > 0);
		
		$criteria = new Criteria();

		$criteria->addJoin(UserPeer::CUSTOMER_ID, CustomerPeer::ID, Criteria::INNER_JOIN);
		$criteria->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		
		$criteria->add(UserPeer::CUSTOMER_ID, $customerId);

		// liste les users dans le groups
		$notInCriteria = new Criteria();
		$notInCriteria->add(self::UNIT_ID, $groupId);
		CriteriaUtils::setSelectColumn($notInCriteria, self::USER_ID);
		
		$criteria->add(UserPeer::ID, UserPeer::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($notInCriteria).")", 
				Criteria::CUSTOM);

		if ($keyword) {
			$c1 = $criteria->getNewCriterion(UserPeer::LASTNAME, "%".$keyword."%", Criteria::LIKE);
			$c2 = $criteria->getNewCriterion(UserPeer::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
			$c3 = $criteria->getNewCriterion(UserPeer::EMAIL, "%".$keyword."%", Criteria::LIKE);
		
			$c1->addOr($c2);
			$c1->addOr($c3);
			$criteria->add($c1);
		}
		
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
		
		if ($limit) {
			$criteria->setLimit($limit);
		}
		
		return UserPeer::doSelect($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByUserAndUnit($user_id, $unit_id)
	{
		$c = new Criteria();

		$c->add(self::USER_ID, $user_id);
		$c->add(self::UNIT_ID, $unit_id);
		$c->addJoin(UnitPeer::ID, self::UNIT_ID);

		$c->addJoin(UnitPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByUser($user_id)
	{
		$criteria = new Criteria();
	
		$criteria->add(self::USER_ID, $user_id);
		$criteria->addJoin(self::UNIT_ID, UnitPeer::ID);
		$criteria->addJoin(UnitPeer::CUSTOMER_ID, CustomerPeer::ID);
		$criteria->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		return UnitPeer::doSelect($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getUnitsFromUserInArray($user_id)
	{
		$units = self::retrieveByUser($user_id);
		$units_array = array();

		foreach ($units as $unit ){
			$units_array[$unit->getId()] = $unit->getId();
		}

		return $units_array;
	}
}
