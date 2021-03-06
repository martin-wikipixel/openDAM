<?php


/**
 * Skeleton subclass for performing query and update operations on the 'unit_folder' table.
 *
 * 
 *
 * This class was autogenerated by Propel 1.4.2 on:
 *
 * Tue Oct  1 09:47:14 2013
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    lib.model
 */
class UnitFolderPeer extends BaseUnitFolderPeer {
	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$letter = isset($params["letter"]) ? $params["letter"] : "";
		$state = isset($params["state"]) ? $params["state"] : "";
		$albumId = isset($params["albumId"]) ? (int)$params["albumId"] : 0;
		$folderId = isset($params["folderId"]) ? (int)$params["folderId"] : 0;
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;

		$currentFolder = FolderPeer::retrieveByPK($folderId);
		$currentAlbum = GroupePeer::retrieveByPK($albumId);

		$criteria = new Criteria();

		if ($customerId) {
			$criteria->add(UnitPeer::CUSTOMER_ID, $customerId);
		}

		if ($letter) {
			$criteria->add(UnitPeer::TITLE, $letter.'%', Criteria::LIKE);
		}

		if ($keyword) {
			$c1 = $criteria->getNewCriterion(UnitPeer::TITLE, "%".$keyword."%", Criteria::LIKE);
			$criteria->add($c1);
		}

		if (!$currentFolder->getSubfolderId()) {
			$criteriaUnit = new Criteria();
			$criteriaUnit->addJoin(UnitPeer::ID, UnitGroupPeer::UNIT_ID);
			$criteriaUnit->add(UnitGroupPeer::GROUPE_ID, $currentAlbum->getId());
			$criteriaUnit->add(UnitGroupPeer::ROLE, "", Criteria::NOT_EQUAL);

			CriteriaUtils::setSelectColumn($criteriaUnit, UnitPeer::ID);

			$subQuery = UnitPeer::ID." IN (".CriteriaUtils::buidSqlFromCriteria($criteriaUnit).")";

			$criteria->add(UnitPeer::ID, $subQuery, Criteria::CUSTOM);
		}
		else {
			$recursiveUnits = self::prepareRecursiveQuery($currentFolder);

			$subQuery = implode(" AND ", $recursiveUnits);

			$criteria->add(UnitPeer::ID, $subQuery, Criteria::CUSTOM);
		}

		CriteriaUtils::buildOrderBy($criteria, $orderBy);

		if ($limit) {
			$criteria->setLimit($limit);
		}

		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	public static function prepareRecursiveQuery(Folder $folder, array &$sql = array())
	{
		if ($folder->getSubfolderId()) {
			$currentFolder = FolderPeer::retrieveByPK($folder->getSubfolderId());

			$criteriaUnitFolder = new Criteria();
			$criteriaUnitFolder->add(self::FOLDER_ID, $currentFolder->getId());
			$criteriaUnitFolder->add(self::ROLE, RolePeer::__READER);

			CriteriaUtils::setSelectColumn($criteriaUnitFolder, self::UNIT_ID);

			$sqlLine = 

			$sql[] = UnitPeer::ID." IN (".CriteriaUtils::buidSqlFromCriteria($criteriaUnitFolder).")";

			self::prepareRecursiveQuery($currentFolder, $sql);
		}

		return $sql;
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
		$criteria->addSelectColumn("DISTINCT UPPER(substr(".UnitPeer::TITLE.", 1, 1 )) AS letter");
		$criteria->addAscendingOrderByColumn("letter");

		$letters = self::doSelectStmt($criteria);

		return $letters->fetchAll(PDO::FETCH_COLUMN, 0);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByFolderId($folder_id)
	{
		$criteria = new Criteria();
		$criteria->add(self::FOLDER_ID, $folder_id);

		return self::doSelect($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByUnitIdAndFolderId($unit_id, $folder_id)
	{
		$criteria = new Criteria();
		$criteria->add(self::FOLDER_ID, $folder_id);
		$criteria->add(self::UNIT_ID, $unit_id);

		return self::doSelectOne($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByUserIdAndFolderId($user_id, $folder_id)
	{
		$criteria = new Criteria();
		$criteria->addJoin(self::UNIT_ID, UserUnitPeer::UNIT_ID);
		$criteria->add(self::FOLDER_ID, $folder_id);
		$criteria->add(UserUnitPeer::USER_ID, $user_id);
		$criteria->add(self::ROLE, RolePeer::__READER);

		return self::doSelect($criteria);
	}
} // UnitFolderPeer
