<?php

/**
 * Subclass for performing query and update operations on the 'module' table.
 *
 * 
 *
 * @package lib.model
 */ 
class ModulePeer extends BaseModulePeer
{
	const __MOD_GEOLOCALISATION = 10;
	const __MOD_RETOUCH = 11;
	const __MOD_EXTRACT_DATE = 12;
	const __MOD_EXTRACT_GPS = 13;
	const __MOD_EXTRACT_TAG = 14;
	const __MOD_VERSIONNING = 15;
	const __MOD_TYPE_ALLOWED = 16;
	const __MOD_MAX_FILE_SIZE = 17;
	const __MOD_TAG_HOME = 18;
	const __MOD_WATERMARK = 21;
	const __MOD_META_EXIF = 23;
	const __MOD_META_IPTC = 24;
	const __MOD_QR_CODE = 25;
	const __MOD_APPROVAL = 27;
	const __MOD_REINIT_PASSWORD = 31;
	const __MOD_EXPLORER = 33;
	const __MOD_PERMALINK = 39;
	const __MOD_FAVORITE = 41;
	const __MOD_THESAURUS = 44;
	const __MOD_NOTIFY_ACCESS = 48;
	const __MOD_VIDEO_HD = 51;
	const __MOD_SHOW_UNAUTH = 52;
	const __MOD_HIDE_COPYRIGHTS = 53;

	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";

		$criteria = new Criteria();
		$criteria->addJoin(self::ID, ModuleI18nPeer::ID, Criteria::LEFT_JOIN);
		$criteria->add(ModuleI18nPeer::CULTURE, sfContext::getInstance()->getUser()->getCulture());
		
		if ($keyword) {
			$c1 = $criteria->getNewCriterion(self::ID, "%".$keyword."%", Criteria::LIKE);
			$c2 = $criteria->getNewCriterion(ModuleI18nPeer::TITLE, "%".$keyword."%", Criteria::LIKE);
			$c3 = $criteria->getNewCriterion(ModuleI18nPeer::DESCRIPTION, "%".$keyword."%", Criteria::LIKE);

			$c1->addOr($c2);
			$c1->addOr($c3);
			$criteria->add($c1);
		}

		// pour éviter les conflits entre Module.id et ModulePeer.id
		if (($indexOf = array_search("id_asc", $orderBy)) !== false) {
			unset($orderBy[$indexOf]);
			$orderBy[ModulePeer::ID] = "asc";
		}
		else if (($indexOf = array_search("id_desc", $orderBy)) !== false) {
			unset($orderBy[$indexOf]);
			$orderBy[ModulePeer::ID] = "desc";
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
	
		$pager = new sfPropelPager("Module", $itemPerPage);
		$pager->setCriteria(self::doCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod("doSelectWithI18n");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function findBy(array $params = array(), array $orderBy = array(), $limit = 0)
	{	
		return self::doSelectWithI18n(self::doCriteria($params, $orderBy, $limit));
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByPkI18n($pk)
	{
		$criteria = new Criteria();
		
		$criteria->add(self::ID, $pk);
		
		$v = self::doSelectWithI18n($criteria);
		
		return !empty($v) > 0 ? $v[0] : null;
	}
	
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getModulePager($keyword="", $sort="name_asc", $page=1)
	{
		$c = new Criteria();
		$c->addJoin(ModuleI18nPeer::ID, self::ID);

		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));

		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);

			$c1 = $c->getNewCriterion(self::ID, "%".$keyword."%", Criteria::LIKE);
			$c2 = $c->getNewCriterion(ModuleI18nPeer::TITLE, "%".$keyword."%", Criteria::LIKE);
			$c3 = $c->getNewCriterion(ModuleI18nPeer::DESCRIPTION, "%".$keyword."%", Criteria::LIKE);

			$c1->addOr($c2);
			$c1->addOr($c3);
			$c->add($c1);
		}

		switch ($sort) {
			default:;
			case "id_asc": $c->addAscendingOrderByColumn(self::ID); break;
			case "id_desc": $c->addDescendingOrderByColumn(self::ID); break;
			case "name_asc": $c->addAscendingOrderByColumn(ModuleI18nPeer::TITLE); break;
			case "name_desc": $c->addDescendingOrderByColumn(ModuleI18nPeer::TITLE); break;
			case "desc_asc": $c->addAscendingOrderByColumn(ModuleI18nPeer::DESCRIPTION); break;
			case "desc_desc": $c->addDescendingOrderByColumn(ModuleI18nPeer::DESCRIPTION); break;
		}

		$c->setDistinct();

		$pager = new sfPropelPager('Module', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();

		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function goToDefault($module_id)
	{
		$c = new Criteria();
		$c->add(ModuleValuePeer::MODULE_ID, $module_id);

		return (ModuleValuePeer::doCount($c) == 1 ? true : false);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getModuleForCustomer($customer_id)
	{
		$array_modules = Array();

		$modules_customer = CustomerHasModulePeer::getMyModuleIdInArray($customer_id);

		$c = new Criteria();
		$c->add(self::ID, $modules_customer, Criteria::NOT_IN);
		$modules = self::doSelect($c);

		foreach($modules as $module)
		{
			if($module->isVisible(ModuleVisibilityPeer::__CUSTOMER))
				$array_modules[] = $module;
		}

		return $array_modules;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getModuleForAdminInArray($customer_id)
	{
		$array_modules = Array();

		$modules_customer = CustomerHasModulePeer::getMyModuleIdInArray($customer_id);

		$c = new Criteria();
		$c->add(self::ID, $modules_customer, Criteria::NOT_IN);
		$modules = self::doSelect($c);

		foreach($modules as $module)
		{
			if($module->isVisible(ModuleVisibilityPeer::__ADMIN))
				$array_modules[$module->getId()] = $module->getTitle();
		}

		return $array_modules;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getModuleForUser($user_id)
	{
		$user = UserPeer::retrieveByPk($user_id);

		$array_modules = Array();

		$modules_user = UserHasModulePeer::getMyModuleIdInArray($user_id);

		$c = new Criteria();
		$c->addJoin(CustomerHasModulePeer::MODULE_ID, self::ID);
		$c->add(CustomerHasModulePeer::CUSTOMER_ID, $user->getCustomerId());
		$c->add(CustomerHasModulePeer::ACTIVE, true);
		$c->addJoin(CustomerHasModulePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(CustomerHasModulePeer::ID, $modules_user, Criteria::NOT_IN);
		$c->addAscendingOrderByColumn(CustomerHasModulePeer::MODULE_ID);

		$modules = self::doSelect($c);

		foreach($modules as $module)
		{
			if($module->isVisible(ModuleVisibilityPeer::__USER))
			{
				$user_module = UserHasModulePeer::retrieveByModuleAndUser($module->getId(), $user_id);

				if(!$user_module)
					$array_modules[] = $module;
			}
		}

		return $array_modules;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getModuleForCustomerInArray($customer_id)
	{
		$modules = self::getModuleForCustomer($customer_id);

		$modules_array = array();

		foreach ($modules as $module)
			$modules_array[$module->getId()] = $module->getTitle();

		return $modules_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getModuleForUserInArray($user_id)
	{
		$modules = self::getModuleForUser($user_id);

		$modules_array = array();

		foreach ($modules as $module)
			$modules_array[$module->getId()] = $module->getTitle();

		return $modules_array;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function formatRollOverExtensionAllowed($extensions)
	{
		$tab = unserialize(base64_decode($extensions));
		$return = "";
		$compt = 0;

		for($i = 0; $i < count($tab); $i++) {
			$return .= "*.".$tab[$i].", ";

			$compt++;

			if($compt == 6) {
				$compt = 0;
				$return .= "<br />";
			}
		}

		return $return;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function formatPopoverExtensionAllowed($extensions)
	{
		$tab = unserialize(base64_decode($extensions));
		$return = "";
	
		for($i = 0; $i < count($tab); $i++) {
			$return .= "*.".$tab[$i].", ";
		}
	
		return $return;
	}
	

	/*________________________________________________________________________________________________________________*/
	public static function formatForReadExtensionAllowed($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		return strtoupper("*.".implode(", *.", $tab));
	}

	/*________________________________________________________________________________________________________________*/
	public static function getPictureFormat($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		$pictures = explode(";",ConfigurationPeer::retrieveByType("_show_picture_format")->getValue());
		$temp = "";

		foreach($tab as $ext)
		{
			if(in_array(strtoupper($ext), $pictures))
				$temp .= /*"*.".*/strtoupper($ext).", ";
		}

		return substr($temp, 0, -2);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getVideoFormat($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		$videos = explode(";",ConfigurationPeer::retrieveByType("_show_video_format")->getValue());
		$temp = "";

		foreach($tab as $ext)
		{
			if(in_array(strtoupper($ext), $videos))
				$temp .= /*"*.".*/strtoupper($ext).", ";
		}

		return substr($temp, 0, -2);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getAudioFormat($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		$audios = explode(";",ConfigurationPeer::retrieveByType("_show_audio_format")->getValue());
		$temp = "";

		foreach($tab as $ext)
		{
			if(in_array(strtoupper($ext), $audios))
				$temp .= /*"*.".*/strtoupper($ext).", ";
		}

		return substr($temp, 0, -2);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getDocumentFormat($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		$documents = explode(";",ConfigurationPeer::retrieveByType("_show_document_format")->getValue());
		$temp = "";

		foreach($tab as $ext)
		{
			if(in_array(strtoupper($ext), $documents))
				$temp .= /*"*.".*/strtoupper($ext).", ";
		}

		return substr($temp, 0, -2);
	}

	/*________________________________________________________________________________________________________________*/
	public static function formatForFlashExtensionAllowed($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		return "*.".implode(":*.", $tab);
	}

	/*________________________________________________________________________________________________________________*/
	public static function formatForJavaExtensionAllowed($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		return implode(",", $tab);
	}

	/*________________________________________________________________________________________________________________*/
	public static function formatForJsExtensionAllowed($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		return implode("|", $tab);
	}

	/*________________________________________________________________________________________________________________*/
	public static function formatForArrayExtensionAllowed($extensions)
	{
		return unserialize(base64_decode($extensions));
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByName($name)
	{
		$c = new Criteria();
		$c->addJoin(ModuleI18nPeer::ID, self::ID);
		$c->add(ModuleI18nPeer::TITLE, $name);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function haveAccessModule($module_id, $customer_id, $user_id)
	{
		$user = UserPeer::retrieveByPk($user_id);

		$userModule = UserHasModulePeer::retrieveByModuleAndUser($module_id, $user_id);

		if($userModule)
			return $userModule->getActive(); 
		else
		{
			$customerModule = CustomerHasModulePeer::retrieveByModuleAndCustomer($module_id, $customer_id);

			if($customerModule)
				return $customerModule->getActive(); 
			else
				return false;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function addModuleToAll($module_id)
	{
		$c = new Criteria();
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		$customers = CustomerPeer::doSelect($c);

		foreach($customers as $customer)
		{
			if(!CustomerHasModulePeer::retrieveByModuleAndCustomer($module_id, $customer->getId()))
			{
				$customer_has_module = new CustomerHasModule();
				$customer_has_module->setCustomerId($customer->getId());
				$customer_has_module->setModuleId($module_id);
				$customer_has_module->setActive(true);

				$customer_has_module->save();
			}
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function formatForJavascriptExtensionAllowed($extensions)
	{
		$tab =  unserialize(base64_decode($extensions));
		return "'".implode("','", $tab)."'";
	}
}
