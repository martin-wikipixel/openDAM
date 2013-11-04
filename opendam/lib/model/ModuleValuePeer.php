<?php

/**
 * Subclass for performing query and update operations on the 'module_value' table.
 *
 * 
 *
 * @package lib.model
 */ 
class ModuleValuePeer extends BaseModuleValuePeer
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function isDefaultValue($value_id)
	{
		$c = new Criteria();
		$c->add(ModulePeer::DEFAULT_VALUE, $value_id);

		return (ModulePeer::doCount($c) > 0 ? true : false);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByModuleId($module_id)
	{
		Assert::ok($module_id > 0);
		
		$c = new Criteria();
		$c->add(self::MODULE_ID, $module_id);
		
		return self::doSelectWithI18n($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function findBy(array $params = array(), array $orderBy = array(), $limit = 0)
	{
		$moduleId = isset($params["moduleId"]) ? (int) $params["moduleId"] : 0;
		
		$criteria = new Criteria();
		
		if ($moduleId) {
			$criteria->add(ModuleValuePeer::MODULE_ID, $moduleId);
		}
		
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
		
		if ($limit) {
			$criteria->setLimit($limit);
		}
	
		return ModuleValuePeer::doSelectWithI18n($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function retrieveByModuleIdInArray($module_id)
	{
		$values = self::retrieveByModuleId($module_id);

		$values_array = array();

		foreach ($values as $value)
			$values_array[$value->getId()] = $value->getDescription();

		return $values_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function retrieveByModuleAndName($module_id, $name)
	{
		$c = new Criteria();
		
		$c->addJoin(ModuleValueI18nPeer::ID, self::ID);
		$c->add(self::MODULE_ID, $module_id);
		$c->add(ModuleValueI18nPeer::DESCRIPTION, $name);

		return self::doSelectOne($c);
	}
}
