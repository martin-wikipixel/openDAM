<?php

/**
 * Subclass for performing query and update operations on the 'customer_has_module' table.
 *
 * 
 *
 * @package lib.model
 */ 
class CustomerHasModulePeer extends BaseCustomerHasModulePeer
{
	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		$visibilityId = isset($params["visibilityId"]) ? (int)$params["visibilityId"] : 0;

		$criteria = new Criteria();

		$criteria->addJoin(self::MODULE_ID, ModulePeer::ID, Criteria::INNER_JOIN);
		$criteria->addJoin(self::MODULE_ID, ModuleI18nPeer::ID);
		$criteria->add(ModuleI18nPeer::CULTURE, sfContext::getInstance()->getUser()->getCulture());
	
		if ($visibilityId) {
			$criteria->addJoin(ModulePeer::ID, ModuleHasVisibilityPeer::MODULE_ID, Criteria::INNER_JOIN);
			$criteria->add(ModuleHasVisibilityPeer::MODULE_VISIBILITY_ID, $visibilityId);
		}

		if ($customerId) {
			$criteria->add(self::CUSTOMER_ID, $customerId);
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
	/**
	 * Renvoi les modules à ajouter au customer.
	 *
	 * @return Module[]
	 */
	public static function getModulesCanAdd($customerId, $visibilityId)
	{
		Assert::ok($customerId > 0);
		Assert::ok($visibilityId > 0);

		$criteria = new Criteria();
	
		// cherche tous mes modules de même visibilités
		$criteria->addJoin(ModulePeer::ID, ModuleI18nPeer::ID);
		$criteria->add(ModuleI18nPeer::CULTURE, sfContext::getInstance()->getUser()->getCulture());
	
		$criteria->addJoin(ModulePeer::ID, ModuleHasVisibilityPeer::MODULE_ID);
		$criteria->add(ModuleHasVisibilityPeer::MODULE_VISIBILITY_ID, $visibilityId);

		$notInCriteria = self::doCriteria(array("customerId" => $customerId, "visibilityId" => $visibilityId));
		/*$notInCriteria->add(self::CUSTOMER_ID, $customerId);
		$notInCriteria->addJoin(ModulePeer::ID, ModuleHasVisibilityPeer::MODULE_ID);
		$notInCriteria->add(ModuleHasVisibilityPeer::MODULE_VISIBILITY_ID, $visibilityId);
		*/
		CriteriaUtils::setSelectColumn($notInCriteria, self::MODULE_ID);
	
		$criteria->add(ModulePeer::ID, ModulePeer::ID." NOT IN(".CriteriaUtils::buidSqlFromCriteria($notInCriteria).")",
				Criteria::CUSTOM);
	
		CriteriaUtils::buildOrderBy($criteria, array(ModuleI18nPeer::TITLE => "asc"));
	
		return ModulePeer::doSelectWithI18n($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByModuleAndCustomer($module_id, $id_customer)
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, $id_customer);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::MODULE_ID, $module_id);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getMyModule($customer_id)
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, $customer_id);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::MODULE_ID);
		$modules = self::doSelect($c);

		return ($modules > 0 ? $modules : null);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getMyModuleForAdmin($customer_id)
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, $customer_id);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->addJoin(self::MODULE_ID, ModuleHasVisibilityPeer::MODULE_ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(ModuleHasVisibilityPeer::MODULE_VISIBILITY_ID, ModuleVisibilityPeer::__ADMIN);
		$c->addAscendingOrderByColumn(self::MODULE_ID);
		$modules = self::doSelect($c);

		return ($modules > 0 ? $modules : null);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getMyModuleForUser($customer_id)
	{
		$c = new Criteria();
		$c->add(self::CUSTOMER_ID, $customer_id);
		$c->addJoin(self::MODULE_ID, ModulePeer::ID);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::MODULE_ID);
		$modules = self::doSelect($c);

		return ($modules > 0 ? $modules : null);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getMyModuleIdInArray($customer_id)
	{
		$modules = self::getMyModule($customer_id);

		$modules_array = array();

		foreach ($modules as $module)
			$modules_array[] = $module->getModuleId();

		return $modules_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function deleteByCustomerId($customer_id)
	{
		$customer = CustomerPeer::retrieveByPk($customer_id);

		if($customer->getState() == CustomerPeer::__STATE_ACTIVE)
		{
			$c = new Criteria();
			$c->add(self::CUSTOMER_ID, $customer_id);

			self::doDelete($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function updateModule($module_id, $customer_id, $value)
	{
		$module = ModulePeer::retrieveByPk($module_id);
		$values = ModuleValuePeer::retrieveByModuleId($module->getId());
		$find = null;

		foreach($values as $value_)
		{
			if($value_->getValue() == $value_)
				$find = $value_;
		}

		if(!$find)
		{
			$find = new ModuleValue();
			$find->setModuleId($module->getId());
			$find->setValue($value);
			$find->setDescription($value);
			$find->save();
		}

		if($customerHasModule = self::retrieveByModuleAndCustomer($module->getId(), $customer_id))
		{
			$customerHasModule->setModuleValueId($find->getId());
			$customerHasModule->setActive(true);
		}
		else
		{
			$customerHasModule = new CustomerHasModule();
			$customerHasModule->setCustomerId($customer_id);
			$customerHasModule->setModuleId($module->getId());
			$customerHasModule->setModuleValueId($find->getId());
			$customerHasModule->setActive(true);
		}

		$customerHasModule->save();
	}
}
