<?php

/**
 * Subclass for performing query and update operations on the 'permalink' table.
 *
 * 
 *
 * @package lib.model
 */ 
class PermalinkPeer extends BasePermalinkPeer
{
	const __STATE_DISABLED = 0;
	const __STATE_PUBLIC = 1;
	const __STATE_PRIVATE = 2;

	const __TYPE_WEB = 1;
	const __TYPE_ORIGINAL = 2;
	const __TYPE_CUSTOM = 3;

	const __OBJECT_FILE = 1;
	const __OBJECT_FOLDER = 2;
	const __OBJECT_GROUP = 3;

	/*________________________________________________________________________________________________________________*/
	public static function getUrl()
	{
		do{
			$out = myTools::generateurl();
			$c = new Criteria();
			$c->add(self::LINK, $out);
		}
		while (self::doCount($c) > 0);

		return $out;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getByObjectId($object_id, $type = self::__TYPE_WEB, $object_type = self::__OBJECT_FILE)
	{
		$c = new Criteria();
		
		$c->add(self::OBJECT_ID, $object_id);
		$c->add(self::TYPE, $type);
		$c->add(self::OBJECT_TYPE, $object_type);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getByLink($link)
	{
		$c = new Criteria();
		$c->add(self::LINK, $link);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function selectAdminPermalinks($user_id, $page = 1, $keyword="", $sort="")
	{
		$c = new Criteria();
		
		$c->addJoin(UserPeer::ID, self::USER_ID);
		$c->add(self::USER_ID, $user_id);
		$c->add(UserPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(UserPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		$pager = new sfPropelPager('Permalink', sfConfig::get('app_max_per_page'));
		$pager->setCriteria($c);
		$pager->setPage($this->getRequestParameter('page', $page));
		$pager->init();
		
		return $this->pager;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie les permaliens d'un utilisateur.
	 * 
	 * @param int $page
	 * @param int $itemPerPage
	 * @param array $params
	 * @param array $orderBy
	 * 
	 * @return sfPropelPager
	 */
	public static function getByUserPager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);
	
		Assert::ok(isset($params["userId"]));
		
		$userId = (int)$params["userId"];
		$objectType = isset($params["objectType"]) ? (int)$params["objectType"] : 0;
		
		$criteria = new Criteria();
		
		$criteria->add(self::USER_ID, $userId);

		if ($objectType) {
			switch ($objectType) {
				case self::__OBJECT_GROUP: 
					$criteria->addJoin(self::OBJECT_ID, GroupePeer::ID, Criteria::INNER_JOIN);
					$criteria->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
					$criteria->add(self::OBJECT_TYPE, self::__OBJECT_GROUP);
					break;
				
				case self::__OBJECT_FOLDER:
					$criteria->addJoin(self::OBJECT_ID, FolderPeer::ID, Criteria::INNER_JOIN);
					$criteria->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
					$criteria->add(self::OBJECT_TYPE, self::__OBJECT_FOLDER);
					break;
					
				case self::__OBJECT_FILE:
					$criteria->addJoin(self::OBJECT_ID, FilePeer::ID, Criteria::INNER_JOIN);
					$criteria->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
					$criteria->add(self::OBJECT_TYPE, self::__OBJECT_FILE);
			}
		}
		
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
		
		//echo $criteria->toString();
		
		$pager = new sfPropelPager("Permalink", $itemPerPage);
		
		$pager->setCriteria($criteria);
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see self::getByUserPager
	 * 
	 * @param unknown $user_id
	 * @param number $page
	 * @param string $group_id
	 * @return sfPropelPager
	 */
	/*public static function selectByUserId($user_id, $page = 1, $group_id = null)
	{
		$c = new Criteria();
		
		$c->addJoin(UserPeer::ID, self::USER_ID);
		$c->add(self::USER_ID, $user_id);
		$c->add(UserPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(UserPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		if($group_id && $group_id != "all")
		{
			$array = Array();
			$connection = Propel::getConnection();

			$query_group = "	SELECT permalink.id
								FROM permalink, groupe
								WHERE permalink.object_id = groupe.id
								AND permalink.object_type = 3
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
								AND groupe.id = ".$connection->quote($group_id)."
								AND permalink.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId());

			$query_folder = "	SELECT permalink.id
								FROM permalink, folder, groupe
								WHERE permalink.object_id = folder.id
								AND folder.groupe_id = groupe.id
								AND permalink.object_type = 2
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
								AND groupe.id = ".$connection->quote($group_id)."
								AND folder.state = ".$connection->quote(FolderPeer::__STATE_ACTIVE)."
								AND permalink.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId());

			$query_file = "		SELECT permalink.id
								FROM permalink, file, groupe
								WHERE permalink.object_id = file.id
								AND file.groupe_id = groupe.id
								AND permalink.object_type = 1
								AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
								AND groupe.id = ".$connection->quote($group_id)."
								AND file.state = ".$connection->quote(FilePeer::__STATE_VALIDATE)."
								AND permalink.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId());

			$statement = $connection->query($query_group);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 

			while ($rs = $statement->fetch())
			{
				if(!in_array($rs["id"], $array))
					$array[] = $rs["id"];
			}

			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($query_folder);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 

			while ($rs = $statement->fetch())
			{
				if(!in_array($rs["id"], $array))
					$array[] = $rs["id"];
			}

			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($query_file);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 

			while ($rs = $statement->fetch())
			{
				if(!in_array($rs["id"], $array))
					$array[] = $rs["id"];
			}

			$statement->closeCursor();
			$statement = null;

			$c->add(self::ID, $array, Criteria::IN);
		}

		$pager = new sfPropelPager('Permalink', sfConfig::get('app_max_per_page'));
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->init();
		return $pager;
	}*/

	/*________________________________________________________________________________________________________________*/
	/*public static function selectAll($page = 1, $group_id = null)
	{
		$c = new Criteria();
		
		$c->addJoin(UserPeer::ID, self::USER_ID);
		$c->add(UserPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(UserPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		$array = Array();
		$connection = Propel::getConnection();

		$query_group = "	SELECT permalink.id
							FROM permalink, groupe
							WHERE permalink.object_id = groupe.id
							AND permalink.object_type = 3
							AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
							AND permalink.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId());

		if ($group_id && $group_id != "all") {
			$query_group .= " AND groupe.id = ".$connection->quote($group_id);
		}

		$query_folder = "	SELECT permalink.id
							FROM permalink, folder, groupe
							WHERE permalink.object_id = folder.id
							AND folder.groupe_id = groupe.id
							AND permalink.object_type = 2
							AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
							AND folder.state = ".$connection->quote(FolderPeer::__STATE_ACTIVE)."
							AND permalink.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId());

		if ($group_id && $group_id != "all") {
			$query_folder .= " AND groupe.id = ".$connection->quote($group_id);
		}

		$query_file = "		SELECT permalink.id
							FROM permalink, file, groupe
							WHERE permalink.object_id = file.id
							AND file.groupe_id = groupe.id
							AND permalink.object_type = 1
							AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
							AND file.state = ".$connection->quote(FilePeer::__STATE_VALIDATE)."
							AND permalink.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId());

		if ($group_id && $group_id != "all") {
			$query_file .= " AND groupe.id = ".$connection->quote($group_id);
		}
		$statement = $connection->query($query_group);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 

		while ($rs = $statement->fetch())
		{
			if(!in_array($rs["id"], $array))
				$array[] = $rs["id"];
		}

		$statement->closeCursor();
		$statement = null;

		$statement = $connection->query($query_folder);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 

		while ($rs = $statement->fetch())
		{
			if(!in_array($rs["id"], $array))
				$array[] = $rs["id"];
		}

		$statement->closeCursor();
		$statement = null;

		$statement = $connection->query($query_file);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 

		while ($rs = $statement->fetch())
		{
			if(!in_array($rs["id"], $array))
				$array[] = $rs["id"];
		}

		$statement->closeCursor();
		$statement = null;

		$c->add(self::ID, $array, Criteria::IN);

		$pager = new sfPropelPager('Permalink', sfConfig::get('app_max_per_page'));
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->init();
		
		return $pager;
	}*/

	/*________________________________________________________________________________________________________________*/
	/**
	 * TODO a refaire
	 * 
	 * @return number
	 */
	public static function deleteExpiredPermalinks()
	{
		$c = new Criteria();
		
		$c->addJoin(self::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
		$c->add(UserPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());

		$c1 = $c->getNewCriterion(self::END_AT, date("Y-m-d"), Criteria::LESS_EQUAL);
		$c2 = $c->getNewCriterion(self::END_AT, "0000-00-00", Criteria::NOT_EQUAL);
		$c1->addAnd($c2);
		$c->add($c1);

		$permalinks = self::doSelect($c);
		
		foreach ($permalinks as $permalink) {
			$permalink->delete();
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function buildQrCode($object_id, $url, $object_type)
	{
		$continue = false;
		$file = null;
		$folder = null;
		$groupe = null;


		switch($object_type)
		{
			case self::__OBJECT_FILE:
				if ($file = FilePeer::retrieveByPk($object_id)) {
					$continue = true;
					sfContext::getInstance()->getController()->genUrl("@permalink_show?link=".$url, true);
				}
			break;

			case self::__OBJECT_FOLDER:
				if ($folder = FolderPeer::retrieveByPk($object_id)) {
					$continue = true;
					sfContext::getInstance()->getController()->genUrl("@permalink_folder?link=".$url, true);
				}
			break;

			case self::__OBJECT_GROUP:
				if ($groupe = GroupePeer::retrieveByPk($object_id)) {
					$continue = true;
					sfContext::getInstance()->getController()->genUrl("@permalink_group?link=".$url, true);
				}
			break;
		}

		if($continue)
		{
			$filename = md5($object_id.$url.time());

			$code = new QuickCode();

			if($file)
				$code->setFilename($file->getPath(true)."/".$filename.".png");
			else
				$code->setFilename(sfConfig::get("app_path_qrcode_dir")."/".$filename.".png");

			$code->setContent($url);
			$code->setErrorCorrectionLevel("H");
			$code->setMatrixPointSize(2);

			$code->getCode();

			return $filename;
		}

		return false;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * TODO a optimiser
	 * 
	 * @param unknown $user_id
	 * @param unknown $object_id
	 * @param unknown $object_type
	 */
	public static function deletByUserIdAndObjectId($user_id, $object_id, $object_type)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
		$c->add(self::OBJECT_ID, $object_id);
		$c->add(self::OBJECT_TYPE, $object_type);

		// on ne fait pas de doDelete, car il faut supprimer les qrcode
		$permalinks = self::doSelect($c);
		
		foreach ($permalinks as $permalink) {
			$permalink->delete();
		}
	}
}