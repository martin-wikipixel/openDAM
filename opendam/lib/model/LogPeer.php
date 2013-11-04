<?php

/**
 * Subclass for performing query and update operations on the 'log' table.
 *
 * 
 *
 * @package lib.model
 */ 
class LogPeer extends BaseLogPeer
{
	/*________________________________________________________________________________________________________________*/
	# tag/fileSuccess
	public static function retrieveEarliest()
	{
		$c = new Criteria();
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	
		$c->addJoin(self::ID, LogI18nPeer::ID);
		$c->add(LogI18nPeer::CULTURE, sfContext::getInstance()->getUser()->getCulture());
		$c->addAscendingOrderByColumn(self::CREATED_AT);

		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getDistinctUser($customer_id = null)
	{
		//set_time_limit(0);
		$connection = Propel::getConnection();
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
	
		$query = 	"SELECT distinct(log.user_id), user.email
					FROM log, user";
	
		if ($customer_id) {
			$query .= ", customer";
		}
		
		$query .= "	WHERE log.user_id = user.id";
	
		if (!$customer_id) {
			$query .= " AND log.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND log.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());
		}
		elseif ($customer_id) {
			$query .= " AND log.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND log.customer_id = ".$connection->quote($customer_id);
		}

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		$users = array();
		$users[0] = __("ALL");
	
		foreach($rs as $key => $value)
			$users[$value["user_id"]] = $value["email"];
	
		return $users;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getLogPager($params = array(), $sort="date_desc", $page=1, $perPage=100)
	{
		$userId = isset($params["userId"]) ? (int) $params["userId"] : 0;
		$customerId = isset($params["customerId"]) ? (int) $params["customerId"] : 0;
		$albumId = isset($params["albumId"]) ? (int) $params["albumId"] : 0;
		$type = isset($params["type"]) ? $params["type"] : "";
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";

		$c = new Criteria();
		$c->addJoin(LogI18nPeer::ID, self::ID);

		if ($userId) {
			$c->add(self::USER_ID, $userId);
		}

		if ($customerId) {
			$c->addJoin(self::CUSTOMER_ID, $customerId);
		}

		if ($type != "all") {
			$c->add(self::LOG_TYPE, "%".$type."%", Criteria::LIKE);
		}

		if ($keyword) {
			$c->add(LogI18nPeer::CONTENT, "%".$keyword."%", Criteria::LIKE);
		}

		if ($albumId && $albumId != "all") {
			$criteriaAlbum = new Criteria();
			$criteriaAlbum->addJoin(GroupePeer::ID, self::OBJECT_ID);
			$criteriaAlbum->add(self::TYPE, 1);
			$criteriaAlbum->add(GroupePeer::ID, $albumId);
			$criteriaAlbum->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

			$criteriaFolder = new Criteria();
			$criteriaFolder->addJoin(FolderPeer::ID, self::OBJECT_ID);
			$criteriaFolder->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID);
			$criteriaFolder->add(self::TYPE, 2);
			$criteriaFolder->add(GroupePeer::ID, $albumId);
			$criteriaFolder->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

			$criteriaFile = new Criteria();
			$criteriaFile->addJoin(FilePeer::ID, self::OBJECT_ID);
			$criteriaFile->addJoin(FilePeer::GROUPE_ID, GroupePeer::ID);
			$criteriaFile->add(self::TYPE, 3);
			$criteriaFile->add(GroupePeer::ID, $albumId);
			$criteriaFile->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);

			if ($userId) {
				$criteriaAlbum->add(self::USER_ID, $userId);

				$criteriaFolder->add(self::USER_ID, $userId);

				$criteriaFile->add(self::USER_ID, $userId);
			}

			CriteriaUtils::setSelectColumn($criteriaAlbum, self::ID);
			CriteriaUtils::setSelectColumn($criteriaFolder, self::ID);
			CriteriaUtils::setSelectColumn($criteriaFile, self::ID);

			$subQuery = self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteriaAlbum).")";
			$subQuery .= " OR ".self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteriaFolder).")";
			$subQuery .= " OR ".self::ID." IN(".CriteriaUtils::buidSqlFromCriteria($criteriaFile).")";

			$criteriaUnitGroup->add(self::ID, $subQuery, Criteria::CUSTOM);
		}

		switch ($sort) {
			default:;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
		}

		$pager = new sfPropelPager("Log", $perPage);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();

		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function setLog($user_id, $object_id, $log_type, $type, $ids=array(), $customer_id = null)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
	
		$log = new Log();
		$log->setUserId($user_id);
		$log->setType($type);
		$log->setObjectId($object_id);
		$log->setLogType($log_type);
		$log->setCustomerId($customer_id ? $customer_id : sfContext::getInstance()->getUser()->getCustomerId());
		$log->setIds(serialize(empty($ids) ? array($object_id) : $ids));
	
		$log->save();
	
		$culture = sfContext::getInstance()->getUser()->getCulture();
	
		sfContext::getInstance()->getUser()->setCulture("fr");
		$content = returnLogContent($user_id, $object_id, $log_type, $ids);
		$log->setContent($content);
		$log->save();
	
		sfContext::getInstance()->getUser()->setCulture("en");
		$content = returnLogContent($user_id, $object_id, $log_type, $ids);
		$log->setContent($content);
		$log->save();
	
		sfContext::getInstance()->getUser()->setCulture($culture);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getLog($user_id, $object_id, $log_type, $content)
	{
		$c = new Criteria();
		$c->addJoin(self::ID, LogI18nPeer::ID);
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(LogI18nPeer::CULTURE, sfContext::getInstance()->getUser()->getCulture());
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->add(self::USER_ID, $user_id);
		$c->add(self::OBJECT_ID, $object_id); // group, folder, file ids
		$c->add(self::LOG_TYPE, $log_type); // group, comment, folder, file etc...
		$c->add(LogI18nPeer::CONTENT, $content); 
		
		return self::doSelectOne($c);
	}

	public static function getUploadTraffic($year=0, $month=0, $group_id = null, $user_id = null)
	{
		$connection = Propel::getConnection();
	
		if ($month != 'all') {
			$endDay = date("t", time(0,0,0,$month,1,$year));
			$date_s = $year.'-'.$month.'-'.'01 00:00:00';
			$date_f = $year.'-'.$month.'-'.$endDay.' 23:59:59';
		}
		else {
			$date_s = $year.'-01-01 00:00:00';
			$date_f = $year.'-12-31 23:59:59';
		}
	
		//preparing query
		if (empty($year) && empty($month)) {
			$query = "SELECT sum(size) as total, count(file.id) as nb FROM `file`, `groupe`";
			$query .= ", customer";
	
			$query .= " WHERE file.groupe_id = groupe.id AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);
		}
		else {
			$query = "SELECT sum(size) as total, count(file.id) as nb FROM `file`, `groupe`";
			$query .= ", customer";
	
			$query .= " WHERE file.groupe_id = groupe.id AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)." AND file.created_at > \"".($date_s)."\" and file.created_at < \"".($date_f)."\"";
		}

		$query .= " AND groupe.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());
	
		if ($group_id)
			$query .= " AND groupe.id = ".$connection->quote($group_id);
	
		if ($user_id)
			$query .= " AND file.user_id = ".$connection->quote($user_id);
	
		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if (count($rs) > 0){
			return $rs[0];
		}
		else {
			return 0;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getDownloadTraffic($year=0, $month=0, $group_id = null, $user_id = null)
	{
		$connection = Propel::getConnection();
	
		if($month != 'all')
		{
			$endDay = date("t", time(0,0,0,$month,1,$year));
			$date_s = $year.'-'.$month.'-'.'01 00:00:00';
			$date_f = $year.'-'.$month.'-'.$endDay.' 23:59:59';
		}
		else
		{
			$date_s = $year.'-01-01 00:00:00';
			$date_f = $year.'-12-31 23:59:59';
		}
	
		//preparing query
		if(empty($year) && empty($month))
		{
			$query = "SELECT sum(size) as total, count(file.id) as nb FROM `file`, `log`";
	
			$query .= ", customer";
	
			$query .= " WHERE file.id = log.object_id AND log.type = '3' AND log_type IN ('file-download', 'files-download')";
		}
		else
		{
			$query = "SELECT sum(size) as total, count(file.id) as nb FROM `file`, `log`";
			$query .= ", customer";
	
			$query .= " WHERE file.id = log.object_id AND log.type = '3' AND log_type IN ('file-download', 'files-download') AND log.created_at > \"".($date_s)."\" and log.created_at < \"".($date_f)."\"";
		}
	
		$query .= " AND log.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND log.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());
	
		if($group_id)
			$query .= " AND file.groupe_id = ".$connection->quote($group_id);
	
		if($user_id)
			$query .= " AND file.user_id = ".$connection->quote($user_id);
	
		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if (count($rs) > 0){
			return $rs[0];
		}
		else {
			return 0;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getDistinctCustomer()
	{
		//set_time_limit(0);
		$connection = Propel::getConnection();
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));

		$query = 	"SELECT distinct(log.customer_id), customer.company
					FROM log, customer";

		$query .= "	WHERE log.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE);

		$query .= " AND log.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

		$query .= " ORDER BY customer.company ASC";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$customers = array();
		// $customers[0] = __("ALL");

		foreach ($rs as $key => $value) {
			$customers[$value["customer_id"]] = $value["company"];
		}

		return $customers;
	}
}