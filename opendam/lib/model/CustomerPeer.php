<?php

/**
 * Subclass for performing query and update operations on the 'customer' table.
 *
 * 
 *
 * @package lib.model
 */ 
class CustomerPeer extends BaseCustomerPeer
{
	const __STATE_ACTIVE = 1;
	const __STATE_DELETE = 2;

	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$state = isset($params["state"]) ? (int) $params["state"] : self::__STATE_ACTIVE;
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$status = isset($params["status"]) ? (int) $params["status"] : 0;
		$zone = isset($params["zone"]) ? (int) $params["zone"] : 0;
		
		$criteria = new Criteria();
	
		$criteria->add(self::STATE, $state);

		if ($status) {
			$criteria->add(self::CUSTOMER_STATUS_ID, $status);
		}
		
		if ($zone) {
			CriteriaUtils::setZone($criteria, self::COUNTRY_ID, $zone);
		}
		
		if ($keyword) {
			$c1 = $criteria->getNewCriterion(self::NAME, "%".$keyword."%", Criteria::LIKE);
			$c2 = $criteria->getNewCriterion(self::FIRST_NAME, "%".$keyword."%", Criteria::LIKE);
			$c3 = $criteria->getNewCriterion(self::EMAIL, "%".$keyword."%", Criteria::LIKE);
			$c4 = $criteria->getNewCriterion(self::ADDRESS, "%".$keyword."%", Criteria::LIKE);
			$c5 = $criteria->getNewCriterion(self::CITY, "%".$keyword."%", Criteria::LIKE);
			$c6 = $criteria->getNewCriterion(self::COMPANY, "%".$keyword."%", Criteria::LIKE);

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

		$pager = new sfPropelPager("Customer", $itemPerPage);
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
	public static function countBy(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		return self::doCount(self::doCriteria($params, $orderBy, $limit));
	}
	
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public static function retrieveByEmail($email)
	{
		$c = new Criteria();
		$c->add(self::EMAIL, $email);
		$c->add(self::STATE, self::__STATE_ACTIVE);
	
		$res = self::doSelectOne($c);
	}

	

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * 
	 * @param unknown $customer_id
	 * @param unknown $page
	 * @param string $keyword
	 * @return sfPropelPager
	 */
	public static function getMyUsers($customer_id, $page, $keyword = null)
	{
		$c = new Criteria();
		$c->addJoin(self::ID, UserPeer::CUSTOMER_ID);
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(UserPeer::CUSTOMER_ID, $customer_id);
	
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
	
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
	
		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
			$c1 = $c->getNewCriterion(UserPeer::USERNAME, "%".$keyword."%", Criteria::LIKE);
			$c3 = $c->getNewCriterion(UserPeer::FIRSTNAME, "%".$keyword."%", Criteria::LIKE);
			$c4 = $c->getNewCriterion(UserPeer::EMAIL, "%".$keyword."%", Criteria::LIKE);
			$c5 = $c->getNewCriterion(UserPeer::POSITION, "%".$keyword."%", Criteria::LIKE);
			$c6 = $c->getNewCriterion(UserPeer::PHONE, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c3);
			$c1->addOr($c4);
			$c1->addOr($c5);
			$c1->addOr($c6);
			$c->add($c1);
		}
	
		$pager = new sfPropelPager('User', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getMyUsersNoPager($customer_id)
	{
		$c = new Criteria();
		
		$c->addJoin(self::ID, UserPeer::CUSTOMER_ID);
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->add(UserPeer::CUSTOMER_ID, $customer_id);
		$c->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
	
		return UserPeer::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getHeaderEmail($customer_id, $path = "web")
	{
		$serverName = $_SERVER["SERVER_NAME"];

		switch($path)
		{
			case "web": return "http://".$serverName."/".sfConfig::get('app_path_images_dir_name')."/".
				ConfigurationPeer::retrieveByType("default_header_email")->getValue(); break;
			case "absolute": return sfConfig::get('app_path_images_dir')."/".
				ConfigurationPeer::retrieveByType("default_header_email")->getValue(); break;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFromEmail($customer_id)
	{
		return ConfigurationPeer::retrieveByType("default_from_email")->getValue();
	}

	/*________________________________________________________________________________________________________________*/
	public static function getOwnFromEmail($customer_id)
	{
		return ConfigurationPeer::retrieveByType("default_from_email")->getValue();
	}

	/*________________________________________________________________________________________________________________*/
	public static function getInArray()
	{
		$customers_array = Array();
	
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::COMPANY);
		$c->addAscendingOrderByColumn(self::NAME);
		$c->addAscendingOrderByColumn(self::FIRST_NAME);
	
		$customers = self::doSelect($c);
	
		foreach($customers as $customer)
		{
			$label = "";
			if($customer->getCompany())
				$label .= ucfirst($customer->getCompany())." - ";
	
			if($customer->getName())
				$label .= strtoupper($customer->getName())." ";
	
			if($customer->getFirstName())
				$label .= ucfirst($customer->getFirstName());
	
			$customers_array[$customer->getId()] = $label;
		}
	
		return $customers_array;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getDisk($customer_id)
	{
		$customer = self::retrieveByPk($customer_id);
	
		if($customer)
		{
			if($disk = DiskPeer::retrieveByCustomer($customer->getId()))
				return $disk;
			else
			{
				if($disk = DiskPeer::getDefault())
					return $disk;
				else
					return false;
			}
		}
		else
			return false;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see UserPeer::retrieveFirstAdmin
	 */
	public static function retrieveFirstAdmin($customer_id)
	{
		$c = new Criteria();
		$c->addJoin(self::ID, UserPeer::CUSTOMER_ID);
		$c->add(UserPeer::ROLE_ID, RolePeer::__ADMIN);
		$c->add(UserPeer::CUSTOMER_ID, $customer_id);
		$c->add(self::STATE, self::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(UserPeer::CREATED_AT);
	
		$user = UserPeer::doSelectOne($c);
	
		if(!$user)
		{
			$c = new Criteria();
			$c->add(UserPeer::CUSTOMER_ID, $customer_id);
			$c->addJoin(self::ID, UserPeer::CUSTOMER_ID);
			$c->add(self::STATE, self::__STATE_ACTIVE);
			$c->addAscendingOrderByColumn(UserPeer::CREATED_AT);
	
			$user = UserPeer::doSelectOne($c);
		}
	
		return $user;
	}
}
