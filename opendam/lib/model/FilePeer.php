<?php

/**
 * Subclass for performing query and update operations on the 'file' table.
 *
 * 
 *
 * @package lib.model
 */ 
class FilePeer extends BaseFilePeer
{
	const __TYPE_PHOTO = 1;
	const __TYPE_AUDIO = 2;
	const __TYPE_VIDEO = 3;
	const __TYPE_DOCUMENT = 4;

	const __STATE_WAITING_VALIDATE = 1;
	const __STATE_VALIDATE = 2;
	const __STATE_WAITING_DELETE = 3;
	const __STATE_DELETE = 4;

	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$state = isset($params["state"]) ? (int)$params["state"] : 0;
		
		$criteria = new Criteria();
	
		if ($state) {
			$criteria->add(self::STATE, $state);
		}
		
		if ($keyword) {
			$c1 = $criteria->getNewCriterion(self::NAME, $keyword."%", Criteria::LIKE);
			//$c2 = $criteria->getNewCriterion(self::ORIGINAL, "%".$keyword."%", Criteria::LIKE);
			$c3 = $criteria->getNewCriterion(self::CHECKSUM, "%".$keyword."%", Criteria::LIKE);
	
			//$c1->addOr($c2);
			$c1->addOr($c3);
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
	
		$pager = new sfPropelPager("File", $itemPerPage);
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
	public static function getDuplicatePager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;
		
		$connection = Propel::getConnection();
	
		$query = "	SELECT count(file.id), file.checksum
					FROM file, groupe, folder
					WHERE file.groupe_id = groupe.id
					AND file.folder_id = folder.id
					AND folder.state = ".FolderPeer::__STATE_ACTIVE."
					AND groupe.customer_id = ".$customerId."
					AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
					AND file.state = ".FilePeer::__STATE_VALIDATE."
					GROUP BY file.checksum
					HAVING count(*) > 1";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		$file_ids = Array();
		
		while ($rs = $statement->fetch()) {
			$files = self::retrieveByChecksumAndCustomerId($rs[1], $customerId);
	
			if (count($files) > 1) {
				foreach ($files as $file) {
					if (!in_array($file->getId(), $file_ids)) {
						$file_ids[] = $file->getId();
					}
				}
			}
		}
	
		$statement->closeCursor();
		$statement = null;
	
		// criteria
		
		$criteria = new Criteria();
		$criteria->add(self::ID, $file_ids, Criteria::IN);
	
		if ($keyword) {
			$c1 = $criteria->getNewCriterion(self::NAME, "%".$keyword."%", Criteria::LIKE);
			$c2 = $criteria->getNewCriterion(self::CHECKSUM, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c2);
			$criteria->add($c1);
		}
	
		CriteriaUtils::buildOrderBy($criteria, $orderBy);

		$pager = new sfPropelPager("File", $itemPerPage);
		$pager->setCriteria($criteria);
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le premier fichier d'un album
	 * @param unknown $albumId
	 * @return Ambigous <File, NULL, unknown, multitype:>
	 */
	public static function getFirstFileOfAlbum($albumId, array $orderBy = array(self::CREATED_AT => "asc"))
	{
		Assert::ok(is_numeric($albumId));

		$criteria = new Criteria();

		$criteria->add(self::GROUPE_ID, $albumId);
		$criteria->add(self::STATE, self::__STATE_VALIDATE);

		CriteriaUtils::buildOrderBy($criteria, $orderBy);

		return self::doSelectOne($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	/*public static function getAllFiles($keyword = "", $sort = "creation_date_desc", $page = 1)
	{
		$c = new Criteria();
	
		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
			$c1 = $c->getNewCriterion(self::NAME, $keyword."%", Criteria::LIKE);
			$c2 = $c->getNewCriterion(self::ORIGINAL, "%".$keyword."%", Criteria::LIKE);
			$c3 = $c->getNewCriterion(self::HASH, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c2);
			$c1->addOr($c3);
			$c->add($c1);
		}
	
		switch($sort)
		{
			case "creation_date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "creation_date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
	
			case "name_asc": $c->addDescendingOrderByColumn(self::NAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
	
			case "file_name_asc": $c->addDescendingOrderByColumn(self::ORIGINAL); break;
			case "file_name_desc": $c->addDescendingOrderByColumn(self::ORIGINAL); break;
	
			case "size_asc": $c->addDescendingOrderByColumn(self::SIZE); break;
			case "size_desc": $c->addDescendingOrderByColumn(self::SIZE); break;
	
			case "sate_asc": $c->addDescendingOrderByColumn(self::STATE); break;
			case "sate_desc": $c->addDescendingOrderByColumn(self::STATE); break;
	
			case "hash_asc": $c->addDescendingOrderByColumn(self::CHECKSUM); break;
			case "hash_desc": $c->addDescendingOrderByColumn(self::CHECKSUM); break;
		}
	
		$pager = new sfPropelPager('File', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
	
		return $pager;
	}*/
	
	// aide symfony
	//http://snippets.symfony-project.org/snippets/tagged/criteria/order_by/date
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi une liste de keywords.
	 *
	 * @return array
	 */
	private static function tokenizeKeyword($keyword)
	{
		if (!$keyword) {
			return array();
		}
	
		$keywords = explode(" ", $keyword);
		$res = array();
	
		array_map("trim", $keywords);
	
		foreach ($keywords as $keyword) {
			if (mb_strlen($keyword) < 3) {
				continue;
			}
	
			$res[] = $keyword;
		}
	
		return array_unique($res);
	}
	
	/*________________________________________________________________________________________________________________*/
	private static function toSqlWhere($column, $keywords, $operator = "AND", $token = "LIKE")
	{
		$con = Propel::getConnection();
	
		$str = "";
	
		foreach ($keywords as $keyword) {
			$str .= trim($operator). " ".$column." ".$token." ".$con->quote("%".$keyword."%");
		}
	
		$str = mb_substr($str, strlen($operator));
	
		return "(".$str.")";
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * ex: voiture bleu
	 *
	 * 2- chercher dans le nom de l'auteur (complet)
	 **/
	private static function filters($criteria, array $params, array $exclude = array())
	{
		self::search_filter_keyword($criteria, $params);
		self::search_filter_from($criteria, $params);
	
		if (!in_array("group", $exclude)) {
			self::search_filter_group($criteria, $params);
		}
	
		if (!in_array("addedByMe", $exclude)) {
			self::search_filter_addedByMe($criteria, $params);
		}
	
		if (!in_array("type", $exclude)) {
			self::search_filter_type($criteria, $params);
		}
	
		if (!in_array("orientation", $exclude)) {
			self::search_filter_orientation($criteria, $params);
		}
	
		if (!in_array("createdAtYear", $exclude)) {
			self::search_filter_createdAtYear($criteria, $params);
		}
	
		if (!in_array("size", $exclude)) {
			self::search_filter_size($criteria, $params);
		}
	
		if (!in_array("licence", $exclude)) {
			self::search_filter_licence($criteria, $params);
		}
	
		if (!in_array("usageUse", $exclude)) {
			self::search_filter_usageUse($criteria, $params);
		}
	
		if (!in_array("distribution", $exclude)) {
			self::search_filter_distribution($criteria, $params);
		}
	
		if (!in_array("tag", $exclude)) {
			self::search_filter_tag($criteria, $params);
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le nombre de fichiers pour le filtre addedByMe.
	 *
	 **/
	public static function countFilesAddedByMe(array $params)
	{
		$criteria = new Criteria();
	
		$params["addedByMe"] = 1;// force a execute le filtre addedByMe
	
		self::filters($criteria, $params);
	
		return FilePeer::doCount($criteria);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi les groups qui existes dans la recherche avec le nombre de fichiers.
	 *
	 * @return array("totalFilesCount"=> nombre total, "rows" => *array("instance" => Groups, "nbFiles" => int));
	 * */
	public static function getGroupsOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		//---------- filters
		self::filters($criteria, $params, array("group"));
		//--------- end filters
	
		$criteria->clearSelectColumns();
		GroupePeer::addSelectColumns($criteria);// ajout tous les colonnes du groupes
		$criteria->addAsColumn('nbFiles', 'COUNT('.FilePeer::GROUPE_ID.')');
	
		$criteria->addGroupByColumn(FilePeer::GROUPE_ID);
	
		//echo $criteria->toString();
	
		$stmt = GroupePeer::doSelectStmt($criteria);
	
		$groups = array("totalFilesCount" => 0, "rows" => array());
	
		// comme populateObjects !!
		//____________________________________________
		// set the class once to avoid overhead in the loop
		$cls = GroupePeer::getOMClass(false);
	
		// populate the object(s)
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$key = GroupePeer::getPrimaryKeyHashFromRow($row, 0);
			if (null !== ($obj = GroupePeer::getInstanceFromPool($key))) {
				// We no longer rehydrate the object, since this can cause data loss.
				// See http://propel.phpdb.org/trac/ticket/509
				// $obj->hydrate($row, 0, true); // rehydrate
				$results[] = $obj;
			} else {
				$obj = new $cls();
				$obj->hydrate($row);
				//$results[] = $obj;
	
				$nbFiles = $row[count($row)-1];
				$groups["totalFilesCount"] += $nbFiles;
	
				$groups["rows"][] = array("instance" => $obj, "nbFiles" => $nbFiles);
				GroupePeer::addInstanceToPool($obj, $key);
			} // if key exists
		}
	
		$stmt->closeCursor();
		//end
	
		return $groups;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des extensions de la recherche en cours.
	 **/
	public static function getExtensionsOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		//---------- filters
		self::filters($criteria, $params, array("type"));
		//--------- end filters
	
		$criteria->clearSelectColumns();
		$criteria->addSelectColumn(FilePeer::EXTENTION);
	
		$criteria->addAsColumn('nbFiles', 'COUNT(*)');
		$criteria->addGroupByColumn(FilePeer::EXTENTION);
	
		$stmt = FilePeer::doSelectStmt($criteria);
		$res = array("totalFilesCount" => 0, "rows" => array());
	
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$res["rows"][$row[0]] = $row[1];
			$res["totalFilesCount"] += $row[1];
		}
	
		$stmt->closeCursor();
	
		return $res;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi toutes les orientations possible et le nombre de fichier.
	 * */
	public static function countOrientationOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		//---------- filters
		self::filters($criteria, $params, array("orientation"));
		//--------- end filters
	
		// obligé de faire 3 requetes
		$res = array("totalFiles" => 0, "rows" => array(), "nbCategory" => 0);
	
		$criteria->add(FilePeer::WIDTH, FilePeer::WIDTH." = ".FilePeer::HEIGHT, Criteria::CUSTOM);
	
		$count = FilePeer::doCount($criteria);
		$res["rows"]["square"] = $count;
		$res["totalFiles"] += $count;
	
		if ($count) {
			$res["nbCategory"]++;
		}
	
		$criteria->add(FilePeer::WIDTH, FilePeer::WIDTH." > ".FilePeer::HEIGHT, Criteria::CUSTOM);
		$count = FilePeer::doCount($criteria);
		$res["rows"]["landscape"] = $count;
		$res["totalFiles"] += $count;
	
		if ($count) {
			$res["nbCategory"]++;
		}
	
		$criteria->add(FilePeer::WIDTH, FilePeer::WIDTH." < ".FilePeer::HEIGHT, Criteria::CUSTOM);
		$count = FilePeer::doCount($criteria);
		$res["rows"]["portrait"] = $count;
		$res["totalFiles"] += $count;
	
		if ($count) {
			$res["nbCategory"]++;
		}
	
		return $res;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi date min et max de la recherche en cours.
	 **/
	public static function getMinMaxDateOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		//---------- filters
		self::filters($criteria, $params, array("createdAtYear"));
		//--------- end filters
	
		$criteria->clearSelectColumns();
	
		$criteria->addAsColumn('minYear', "YEAR(min(".FilePeer::CREATED_AT."))");
		$criteria->addAsColumn('maxYear', "YEAR(max(".FilePeer::CREATED_AT."))");
	
		$stmt = FilePeer::doSelectStmt($criteria);
	
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$res = $stmt->fetchAll();
		$stmt->closeCursor();
	
		if (count($res)) {
			return $res[0];
		}
	
		return array("minYear" => date("Y"), "maxYear" => date("Y"));
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la taille min / max de la recherche en cours.
	 */
	public static function getMinMaxSizeOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		//---------- filters
		self::filters($criteria, $params, array("size"));
		//--------- end filters
	
		$criteria->clearSelectColumns();
	
		$criteria->addAsColumn('minSize', "min(".FilePeer::SIZE.")");
		$criteria->addAsColumn('maxSize', "max(".FilePeer::SIZE.")");
	
		$stmt = FilePeer::doSelectStmt($criteria);
	
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$res = $stmt->fetchAll();
		$stmt->closeCursor();
	
		if (count($res)) {
			return $res[0];
		}
	
		return array("minSize" => 0, "maxSize" => 0);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des licences de la recherche en cours.
	 **/
	public static function getLicenceOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		self::filters($criteria, $params, array("licence"));
			
		$criteria->clearSelectColumns();
		$criteria->addSelectColumn(FilePeer::LICENCE_ID);
	
		$criteria->addAsColumn('nbFiles', 'COUNT(*)');
		$criteria->addGroupByColumn(FilePeer::LICENCE_ID);
	
		//echo $criteria->toString();
	
		$stmt = FilePeer::doSelectStmt($criteria);
		$res = array("totalFilesCount" => 0, "rows" => array(), "null" => 0);
	
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$licenceId = $row[0];
			$countFiles = $row[1];
			$res["totalFilesCount"] += $countFiles;
				
			if (!$licenceId) {
				$res["null"] = $countFiles;
			}
			else {
				$res["rows"][$licenceId] = array("instance" => LicencePeer::retrieveByPk($licenceId[0]), 
						"countFiles" => $countFiles);
			}
		}
	
		$stmt->closeCursor();
	
		return $res;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des licences de la recherche en cours.
	 **/
	public static function getUsageUseOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		self::filters($criteria, $params, array("usageUse"));
			
		$criteria->clearSelectColumns();
		$criteria->addSelectColumn(FilePeer::USAGE_USE_ID);
	
		$criteria->addAsColumn('nbFiles', 'COUNT(*)');
		$criteria->addGroupByColumn(FilePeer::USAGE_USE_ID);
	
		//echo $criteria->toString();
		$stmt = FilePeer::doSelectStmt($criteria);
		$res = array("totalFilesCount" => 0, "rows" => array(), "null" => 0);
	
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$licenceId = $row[0];
			$countFiles = $row[1];
			$res["totalFilesCount"] += $countFiles;
				
			if (!$licenceId) {
				$res["null"] = $countFiles;
			}
			else {
				$res["rows"][$licenceId] = array("instance" => UsageUsePeer::retrieveByPk($licenceId[0]), 
						"countFiles" => $countFiles);
			}
		}
	
		$stmt->closeCursor();
	
		return $res;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des licences de la recherche en cours.
	 **/
	public static function getDistributionOfSearch(array $params)
	{
		$criteria = new Criteria();
	
		self::filters($criteria, $params, array("distribution"));
			
		$criteria->clearSelectColumns();
		$criteria->addSelectColumn(FilePeer::USAGE_DISTRIBUTION_ID);
	
		$criteria->addAsColumn('nbFiles', 'COUNT(*)');
		$criteria->addGroupByColumn(FilePeer::USAGE_DISTRIBUTION_ID);
	
		//echo $criteria->toString();
	
		$stmt = FilePeer::doSelectStmt($criteria);
		$res = array("totalFilesCount" => 0, "rows" => array(), "null" => 0);
	
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
			$licenceId = $row[0];
			$countFiles = $row[1];
			$res["totalFilesCount"] += $countFiles;
				
			if (!$licenceId) {
				$res["null"] = $countFiles;
			}
			else {
				$res["rows"][$licenceId] = array("instance" => UsageDistributionPeer::retrieveByPk($licenceId[0]), 
						"countFiles" => $countFiles);
			}
		}
	
		$stmt->closeCursor();
	
		return $res;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getFiltersOfSearch(array $params)
	{
		/*
		 * Pour trouver les autres tags des fichiers, il faut executer la requete générée
		* dans une sous requete IN. (cherche tous les fichiers qui match)
		*
		* Notes :
		* propel ne gère pas les sous requêtes, pour cela je génère la requête propel finale via les fonctions core
		* puis je termine la construction de la de la requête a la main
		**/
		$criteria = new Criteria();
		$subcriteria = new Criteria();
		$tagsId = isset($params["tagsId"]) ? (array) $params["tagsId"] : array();
		$limit = 25;
		$con = Propel::getConnection();
		$dbMap = Propel::getDatabaseMap($subcriteria->getDbName());
		$db = Propel::getDB($subcriteria->getDbName());
	
		self::filters($subcriteria, $params);
	
		$subcriteria->clearSelectColumns();
		$subcriteria->addSelectColumn(FilePeer::ID);
	
		$sqlParams = array();
		$subSql = BasePeer::createSelectSql($subcriteria, $sqlParams);
	
		function toInt($val) {
			return (int) $val;
		}
	
		// sql injection security check !!
		$tagsId = array_map("toInt", $tagsId);
	
		$tagsIdStr = "";
	
		if (count($tagsId)) {
			$tagsIdStr = implode(",", $tagsId);
			$tagsIdStr = FileTagPeer::TAG_ID." NOT IN (".$tagsIdStr.") AND ";
		}
	
		$sql = "
			SELECT tag.ID, tag.TITLE, tag.DESCRIPTION, tag.CUSTOMER_ID, tag.CREATED_AT, COUNT( * ) AS nbFiles
			FROM ".FileTagPeer::TABLE_NAME." INNER JOIN tag ON (".FileTagPeer::TAG_ID." = ".TagPeer::ID.")
			WHERE ".$tagsIdStr."
				".FileTagPeer::FILE_ID." IN (".$subSql.")
			GROUP BY ".TagPeer::ID."
				ORDER BY nbFiles DESC
				LIMIT $limit
				";
	
		$stmt = $con->prepare($sql);
		BasePeer::populateStmtValues($stmt, $sqlParams, $dbMap, $db);
		
		$stmt->execute();
	
		$tags = array("rows" => array());
	
		// comme populateObjects !!
		//____________________________________________
		// set the class once to avoid overhead in the loop
		$cls = TagPeer::getOMClass(false);
	
		// populate the object(s)
		while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
		$key = TagPeer::getPrimaryKeyHashFromRow($row, 0);
		if (null !== ($obj = GroupePeer::getInstanceFromPool($key))) {
		// We no longer rehydrate the object, since this can cause data loss.
			// See http://propel.phpdb.org/trac/ticket/509
			// $obj->hydrate($row, 0, true); // rehydrate
			$results[] = $obj;
		} else {
		$obj = new $cls();
		$obj->hydrate($row);
		//$results[] = $obj;
	
		$nbFiles = $row[count($row)-1];
	
		$tags["rows"][] = array("instance" => $obj, "nbFiles" => $nbFiles);
		TagPeer::addInstanceToPool($obj, $key);
		} // if key exists
		}
	
		$stmt->closeCursor();
		//end
	
		return $tags;
		}
	
		/*_________________________________________________________________________________________________________*/
		/*_________________________________________________________________________________________________________*/
		/*_________________________________________________________________________________________________________*/
		public static function makeAllUniversQuery($customerId, $userId)
		{
		if (!$customerId) {
		throw new RuntimeException("missing customerId");
		}
			
		if (!$userId) {
		throw new RuntimeException("missing customerId");
	}
	
	$query = "SELECT ".UserGroupPeer::ID.
	" FROM ".UserGroupPeer::TABLE_NAME." INNER JOIN ".GroupePeer::TABLE_NAME." as g2 ON ".UserGroupPeer::GROUPE_ID." = g2.id".
					" WHERE ". UserGroupPeer::USER_ID ."= ".$userId." AND g2.customer_id=".$customerId;
	
		return $query;
	}
	
	/*_________________________________________________________________________________________________________*/
	public static function search_filter_from($criteria, array& $params)
	{
	$userId = (int) isset($params["userId"]) ? $params["userId"] : O;
	$customerId = (int) isset($params["customerId"]) ? $params["customerId"] : O;
		$allUnivers = (bool) isset($params["allUnivers"]) ? $params["allUnivers"] : false;// search dans tous les univers que j'ai acces.
	
		$user = isset($params["user"]) ? $params["user"] : null;
	
	$criteria->addJoin(FilePeer::GROUPE_ID, GroupePeer::ID, Criteria::INNER_JOIN);
	
		if ($allUnivers) {// TODO supprimer allUnivers ??
			// univers non supprimés
			$criteria->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		// si admin, on cherche dans tous les univers du client
		if ($user && $user->getRoleId() == RolePeer::__ADMIN) {
		$criteria->addJoin(FilePeer::USER_ID, UserPeer::ID, Criteria::INNER_JOIN);
		$criteria->add(UserPeer::CUSTOMER_ID, $user->getCustomerId());
		}
		// sinon dans tous les univers que j'ai access
		else {
		// cherche dans mes groupes
		$c1 = $criteria->getNewCriterion(GroupePeer::USER_ID, $userId);
	
		// ou dans les groupes publiques de mon client
			$c2 = $criteria->getNewCriterion(GroupePeer::FREE, 1);
			$c2_2 = $criteria->getNewCriterion(GroupePeer::CUSTOMER_ID, $customerId);
			$c2->addAnd($c2_2);
	
			// on dans les groupes où j'ai les droits
			$c3 = $criteria->getNewCriterion(GroupePeer::ID, GroupePeer::ID." IN (".
					self::makeAllUniversQuery($customerId, $userId).")", Criteria::CUSTOM);
	
			$c1->addOr($c2);
			$c1->addOr($c3);
	
			$criteria->add($c1);
		}
		}
					else {
					// secure fallback mais inutile en pratique
					if ($userId) {
					$criteria->add(FilePeer::USER_ID, $userId);
		}
		}
		}
	
	/*________________________________________________________________________________________________________________*/
	public static function search_filter_group($criteria, array& $params)
	{
		$groupId = (int) isset($params["groupId"]) ? $params["groupId"] : 0;
	
		if ($groupId) {
			$criteria->add(FilePeer::GROUPE_ID, $groupId);
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function search_filter_orientation($criteria, array& $params)
	{
		$orientation = isset($params["orientation"]) ? $params["orientation"] : "";
	
		// orientation
		switch ($orientation) {
		case "square":
		$criteria->add(FilePeer::WIDTH, FilePeer::WIDTH." = ".FilePeer::HEIGHT, Criteria::CUSTOM);
		break;
			
			case "landscape":
		$criteria->add(FilePeer::WIDTH, FilePeer::WIDTH." > ".FilePeer::HEIGHT, Criteria::CUSTOM);
		break;
	
		case "portrait":
		$criteria->add(FilePeer::WIDTH, FilePeer::WIDTH." < ".FilePeer::HEIGHT, Criteria::CUSTOM);
		break;
		}
		}
	
	/*_________________________________________________________________________________________________________*/
	public static function search_filter_type($criteria, array& $params)
	{
		$type = (string) isset($params["type"]) ? $params["type"] : "";
	
		if ($type) {
			$criteria->add(FilePeer::EXTENTION, $type);
		}
	}
	
	/*_________________________________________________________________________________________________________*/
	public static function search_filter_addedByMe($criteria, array& $params)
	{
		$userId = (int) isset($params["userId"]) ? $params["userId"] : O;
		$addedByMe = (bool) isset($params["addedByMe"]) ? $params["addedByMe"] : false;
	
		// ajouté par moi
		if ($addedByMe) {
			$criteria->add(FilePeer::USER_ID, $userId);
		}
	}
	
	/*_________________________________________________________________________________________________________*/
	public static function search_filter_createdAtYear($criteria, array& $params)
	{
		$years = isset($params["years"]) ? $params["years"] : null;
		$con = Propel::getConnection();
	
		// ajouté par moi
		if ($years) {
		// si uniquement date min
		if (isset($years["min"]) && !isset($years["max"])) {
		$criteria->add(FilePeer::CREATED_AT, "YEAR(".FilePeer::CREATED_AT.") >= ".$con->quote($years["min"]), Criteria::CUSTOM);
		}
		// si uniquement date max
			else if (!isset($years["min"]) && isset($years["max"])) {
			$criteria->add(FilePeer::CREATED_AT, "YEAR(".FilePeer::CREATED_AT.") <= ".$con->quote($years["max"]), Criteria::CUSTOM);
			}
			// si date min et max
			else {
			$criteria->add(FilePeer::CREATED_AT, "YEAR(".FilePeer::CREATED_AT.") >= ".$con->quote($years["min"])." AND YEAR(".FilePeer::CREATED_AT.") <= ".$con->quote($years["max"]), Criteria::CUSTOM);
		}
		}
		}
	
	/*_________________________________________________________________________________________________________*/
	public static function search_filter_size($criteria, array& $params)
	{
		$sizes = isset($params["sizes"]) ? $params["sizes"] : null;
	
		// ajouté par moi
		if ($sizes && isset($sizes["min"]) && isset($sizes["max"])) {
			// si uniquement taille min
			if (isset($sizes["min"]) && !isset($sizes["max"])) {
			$criteria->add(FilePeer::SIZE, FilePeer::SIZE." >= ".(int)$sizes["min"], Criteria::CUSTOM);
			}
			// si uniquement taille max
			else if (!isset($sizes["min"]) && isset($sizes["max"])) {
				$criteria->add(FilePeer::SIZE, FilePeer::SIZE." <= ".(int)$sizes["max"], Criteria::CUSTOM);
				}
				// si taille min et max
				else {
				$criteria->add(FilePeer::SIZE, FilePeer::SIZE." >= ".(int)$sizes["min"]." AND ".FilePeer::SIZE." <= ".(int)$sizes["max"], Criteria::CUSTOM);
				}
				}
				}
	
				/*_________________________________________________________________________________________________________*/
				public static function getTagsIdQuery(array $params, array $keywords) {
				$criteria = new Criteria();
				$customerId = (int) isset($params["customerId"]) ? $params["customerId"] : O;
	
				$con = Propel::getConnection();
	
				$query =
				"SELECT ".FileTagPeer::FILE_ID.
				" FROM ".FileTagPeer::TABLE_NAME. " INNER JOIN ".TagPeer::TABLE_NAME." ON ".FileTagPeer::TAG_ID. " = ".TagPeer::ID.
				" WHERE ".TagPeer::CUSTOMER_ID. " = ".$customerId.
				" AND ".self::toSqlWhere(TagPeer::TITLE, $keywords, "OR");
				;
	
				return $query;
				}
	
	/*_________________________________________________________________________________________________________*/
		public static function search_filter_keyword($criteria, array& $params)
		{
		$keyword = (string) isset($params["keyword"]) ? $params["keyword"] : "";
				$keywords = self::tokenizeKeyword($keyword);
	
				if (count($keywords)) {
				$criteria->add(FilePeer::NAME, self::toSqlWhere(FilePeer::NAME, $keywords), Criteria::CUSTOM);
					
				// dans le filename
				$c1 = $criteria->getNewCriterion(FilePeer::NAME, self::toSqlWhere(FilePeer::NAME, $keywords), Criteria::CUSTOM);
					
				// dans la description
				$c1->addOr($criteria->getNewCriterion(FilePeer::DESCRIPTION, self::toSqlWhere(FilePeer::DESCRIPTION, $keywords), Criteria::CUSTOM));
					
				// firstname + lastname
				$c1->addOr($criteria->getNewCriterion(UserPeer::LASTNAME, self::toSqlWhere("CONCAT(".UserPeer::FIRSTNAME.", ' ', ".UserPeer::LASTNAME.")", $keywords), Criteria::CUSTOM));
					
				// dans email
				$c1->addOr($criteria->getNewCriterion(UserPeer::EMAIL, self::toSqlWhere(UserPeer::EMAIL, $keywords), Criteria::CUSTOM));
					
				// search dans les tags
				// TODO trop lent a voir ou améliorer
				$c1->addOr($criteria->getNewCriterion(FilePeer::ID, FilePeer::ID. " IN(".self::getTagsIdQuery($params, $keywords).")", Criteria::CUSTOM));
					
					
				$criteria->add($c1);
				}
	
				// les fichiers ok (non supprimés)
				$criteria->add(FilePeer::STATE, self::__STATE_VALIDATE);
				}
	
				/*_________________________________________________________________________________________________________*/
				public static function search_filter_licence($criteria, array& $params)
				{
				$licenceId = isset($params["licenceId"]) ? $params["licenceId"] : 0;
	
				if ($licenceId) {
				if ($licenceId == "null") {
				$criteria->add(FilePeer::LICENCE_ID, null, Criteria::ISNULL);
		}
		else {
		$criteria->add(FilePeer::LICENCE_ID, (int) $licenceId);
		}
		}
		}
	
		/*_________________________________________________________________________________________________________*/
		public static function search_filter_usageUse($criteria, array& $params)
		{
		$usageUseId = isset($params["usageUseId"]) ? $params["usageUseId"] : 0;
	
		if ($usageUseId) {
		if ($usageUseId == "null") {
		$criteria->add(FilePeer::USAGE_USE_ID, null, Criteria::ISNULL);
		}
		else {
		$criteria->add(FilePeer::USAGE_USE_ID, (int)$usageUseId);
		}
		}
		}
	
		/*_________________________________________________________________________________________________________*/
		public static function search_filter_distribution($criteria, array& $params)
		{
		$usageDistributionId = (int) isset($params["usageDistributionId"]) ? $params["usageDistributionId"] : 0;
	
		if ($usageDistributionId) {
		$criteria->add(FilePeer::USAGE_DISTRIBUTION_ID, (int)$usageDistributionId);
		}
		}
	
		/*_________________________________________________________________________________________________________*/
		public static function search_filter_tag($criteria, array& $params)
		{
		$tagsId = (array) isset($params["tagsId"]) ? $params["tagsId"] : array();
	
		$count = count($tagsId);
	
		if ($count) {
		$criteria->addJoin(FilePeer::ID, FileTagPeer::FILE_ID, Criteria::INNER_JOIN);
		 $criteria->add(FileTagPeer::TYPE, FileTagPeer::__TYPE_FILE);
		 	
		 // fait un "et" sur une table n-n
		 // on faire un where de la premiere valeur puis un existe
		 // pour savoir si le fichier apparait avec les autres tags
		 $criteria->add(FileTagPeer::TAG_ID, $tagsId[0]);
		 	
		 for ($i = 1; $i < $count; $i++) {
		$tagId = (int) $tagsId[$i];
	
		$criteria->add($tagId, "exists (
		SELECT 1 FROM ".FileTagPeer::TABLE_NAME."
		WHERE ".FileTagPeer::TAG_ID."=".$tagId." AND ".FileTagPeer::FILE_ID." = ".FilePeer::ID."
		)",
				Criteria::CUSTOM);
		}
		}
		}
	
		/*_________________________________________________________________________________________________________*/
		/**
		* Recherche des fichiers, renvoit le nombre total de résultat (pour une pagination).
		* */
	public static function searchEngine2Count(array $params)
		{
		$criteria = new Criteria();
	
		// filters
		self::filters($criteria, $params);
		// end filters
	
		return self::doCount($criteria);
		}
	
		/*_________________________________________________________________________________________________________*/
		/**
		* Recherche des fichiers.
		* */
		public static function searchEngine2(array $params)
		{
		$limit = (int) isset($params["limit"]) ? $params["limit"] : O;
		$page = (int) isset($params["page"]) ? $params["page"] : 1;
		$sort = (string) isset($params["sort"]) ? $params["sort"] : "name_asc";
	
		$criteria = new Criteria();
	
		// filters
		self::filters($criteria, $params);
		// end filters
	
		// trie
		switch ($sort) {
		case "name_asc":
		$criteria->addAscendingOrderByColumn(self::NAME);
		break;
	
			case "name_desc":
						$criteria->addDescendingOrderByColumn(self::NAME);
						break;
	
						case "date_asc":
						$criteria->addAscendingOrderByColumn(self::CREATED_AT);
						break;
	
						case "date_desc":
						$criteria->addDescendingOrderByColumn(self::CREATED_AT);
						break;
		}
	
						// limit
						if ($limit) {
								$offset = 0;
									
								if ($page) {
								$offset = $limit * ($page - 1);
			}
											
				$criteria->setOffset($offset);
				$criteria->setLimit($limit);
		}
	
		//echo $criteria->toString();
		//die();
		return FilePeer::doSelect($criteria);
		return FilePeer::doSelectJoinUser($criteria);// ne fct pas avec le addJoin user !!
		}
	
	
	
	/*________________________________________________________________________________________________________________*/
  # folder/show
  public static function getFiles($group_id, $folder_id, $sort="date_desc", $tag_ids=array())
  {
    $c = new Criteria();
    $c->add(self::GROUPE_ID, $group_id);
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
    $c->add(self::FOLDER_ID, $folder_id);
    
    switch ($sort) {
      default:
      case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
      case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
    	case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
    	case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
    	case "rate_asc": $c->addAscendingOrderByColumn(self::AVERAGE_POINT); break;
    	case "rate_desc": $c->addDescendingOrderByColumn(self::AVERAGE_POINT); break;
    }
    
    if(sizeof($tag_ids)){
      $tag_ids = array_unique($tag_ids);
      $c->add(FileTagPeer::TAG_ID, $tag_ids, Criteria::IN);
      $c->add(FileTagPeer::TYPE, 3);
      $c->addJoin(FileTagPeer::FILE_ID, self::ID);
      $c->addAsColumn('CNT', 'COUNT('.self::ID.')');
      $c->addGroupByColumn(self::ID);
      $c->addHaving($c->getNewCriterion(self::ID, 'CNT='.sizeof($tag_ids), Criteria::CUSTOM));
    }
    
    return self::doSelect($c);
  }
  

  /*________________________________________________________________________________________________________________*/
  # file/uploadify
  public static function getFile($path, $user_id, $folder_id, $groupe_id)
  {
    $c = new Criteria();
    $c->add(self::ORIGINAL, $path);
    $c->add(self::USER_ID, $user_id);
    $c->add(self::FOLDER_ID, $folder_id);
    $c->add(self::GROUPE_ID, $groupe_id);
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
    return self::doSelectOne($c);
  }

  /*________________________________________________________________________________________________________________*/
  # file/uploadifySuccess, group/manage
  public static function retrieveByFolderId($folder_id, $force = false)
  {
    $c = new Criteria();
    $c->add(self::FOLDER_ID, $folder_id);
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	if($force == false)
	{
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	}
    $c->addDescendingOrderByColumn(self::CREATED_AT);
    return self::doSelect($c);
  }

  /*________________________________________________________________________________________________________________*/
  public static function retrieveByGroupIdInArray($group_id)
  {
    $files = self::retrieveByGroupId($group_id);

    $files_array = array();
    $i = 1;
    foreach ($files as $file){
      $files_array[$i++] = $file->getId();
    }
    return $files_array;
  }

  /*________________________________________________________________________________________________________________*/
  # file/show
  public static function retrieveByFolderIdInArray($folder_id)
  {
    $c = new Criteria();
    $c->add(self::FOLDER_ID, $folder_id);
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

    $c->addDescendingOrderByColumn(self::CREATED_AT);

    $files = self::doSelect($c);

    $files_array = array();

    foreach ($files as $file){
      $files_array[] = $file;
    }
    return $files_array;
  }

  /*________________________________________________________________________________________________________________*/
  public static function retrieveByFolderIdInArrayPublic($folder_id)
  {
	$c = new Criteria();
	$c->add(self::FOLDER_ID, $folder_id);
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	$c->addDescendingOrderByColumn(self::CREATED_AT);
	$files = self::doSelect($c);

	$files_array = array();

	foreach($files as $file)
		$files_array[] = $file->getId();

	return $files_array;
  }

  /*________________________________________________________________________________________________________________*/
  # tag/fileSuccess
  public static function retrieveByFolderIdInSelect($folder_id)
  {
    $c = new Criteria();
    $c->add(self::FOLDER_ID, $folder_id);
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
    $c->addDescendingOrderByColumn(self::CREATED_AT);
    $files = self::doSelect($c);

    $files_array = array();
    foreach ($files as $file){
      $files_array[$file->getId()] = $file;
    }
    return $files_array;
  }

  /*________________________________________________________________________________________________________________*/
  # file/edit
  public static function updateFolderCover($folder_id)
  {
	$connection = Propel::getConnection();

	$query = "UPDATE file INNER JOIN groupe ON file.GROUPE_ID = groupe.ID
			SET folder_cover = 0
			WHERE file.FOLDER_ID = ".$connection->quote($folder_id)."
			AND groupe.CUSTOMER_ID = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

	$statement = $connection->query($query);
	$statement->setFetchMode(PDO::FETCH_ASSOC); 
	$rs = $statement->execute();
	$statement->closeCursor();
	$statement = null;

	$folder = FolderPeer::retrieveByPk($folder_id);

	$folder->setThumbnail(null);
	$folder->setThumbnail200(null);
	$folder->setDiskId(null);

	$folder->save();
  }
  
  # map/file
  public static function getBounds($file_ids=array())
  {
	$connection = Propel::getConnection();

    //preparing query
    $query = "SELECT
        MAX(file.lat) as max_lat, 
        MAX(file.lng) as max_long, 
        MIN(file.lat) as min_lat, 
        MIN(file.lng) as min_long
      FROM file, groupe, customer
      WHERE file.groupe_id = groupe.id
	  AND groupe.customer_id = customer.id
	  AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
	  AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
	  AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
	  AND file.lat <> '' AND file.lng <> '' ".(sizeof($file_ids) ? " AND file.id in (".join(', ', $file_ids).")" : "");

    //connection
	$statement = $connection->query($query);
	$statement->setFetchMode(PDO::FETCH_ASSOC); 
	$rs = $statement->fetchAll();
	$statement->closeCursor();
	$statement = null;

    if (count($rs) > 0){
      return array(
      "max" => array("lat" => $rs[0]["max_lat"], "long" => $rs[0]["max_long"]),
      "min" => array("lat" => $rs[0]["min_lat"], "long" => $rs[0]["min_long"])
      );
    } else {
      return false;
    }
  }

  /*________________________________________________________________________________________________________________*/
  # map/files
  public static function getMapFiles($user_id=0, $file_ids, $s_lat, $n_lat, $s_lng, $n_lng)
  {
    $c = new Criteria();
	$c->add(self::STATE, self::__STATE_VALIDATE);
    
    if($user_id)
    {
      $c->add(UserGroupPeer::USER_ID, $user_id);
      $c->addJoin(UserGroupPeer::GROUPE_ID, self::GROUPE_ID);
    }
    
    $c->add(self::ID, $file_ids, Criteria::IN);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
    
    // latitude, longitude
    if($s_lat && $n_lat && $s_lng && $n_lng){
      $c->add(self::LAT, $s_lat, Criteria::GREATER_EQUAL);
      $c->add(self::LAT, $n_lat, Criteria::LESS_EQUAL);
      $c->add(self::LNG, $s_lng, Criteria::GREATER_EQUAL);
      $c->add(self::LNG, $n_lng, Criteria::LESS_EQUAL);
      
      $c->add(self::LAT, "", Criteria::NOT_EQUAL);
      $c->add(self::LNG, "", Criteria::NOT_EQUAL);
    }

    return self::doSelect($c);
  }
  

  /*________________________________________________________________________________________________________________*/
  # slide/xml
  public static function getSlideFiles($user_id=0, $folder_id)
  {
    $c = new Criteria();
	$c->add(self::STATE, self::__STATE_VALIDATE);

    if($user_id)
    {
      $c->add(UserGroupPeer::USER_ID, $user_id);
      $c->addJoin(UserGroupPeer::GROUPE_ID, self::GROUPE_ID);
    }

    $c->add(self::FOLDER_ID, $folder_id);
	$c->add(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

    return self::doSelect($c);
  }  

  /*________________________________________________________________________________________________________________*/
  protected static function convertSize($size, $label){
    sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
    switch ($label) {
    	case __("Kb"): return intval($size*1024); break;
    	case __("Mb"): return intval($size*1024*1024); break;
    	default: return 0;
    }
  }

  /*________________________________________________________________________________________________________________*/
  # tag/fileSuccess
  public static function retrieveEarliest()
  {
    $c = new Criteria();
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
    $c->addDescendingOrderByColumn(self::CREATED_AT);
    return self::doSelectOne($c);
  }  

  /*________________________________________________________________________________________________________________*/
 # tag/fileSuccess
  public static function retrieveTotalSize($customer_id = null, $group_id = null, $user_id = null)
  {
	$connection = Propel::getConnection();

     //preparing query
    $query = "SELECT sum(size) as total FROM `file`, `groupe`";
	$query .= ", customer";
	$query .=" WHERE file.groupe_id = groupe.id AND file.state = ".$connection->quote(self::__STATE_VALIDATE)." AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);
	$query .= " AND groupe.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND groupe.customer_id = ".$connection->quote(($customer_id ? $customer_id : sfContext::getInstance()->getUser()->getCustomerId()));

	if($group_id)
		$query .= " AND groupe.id = ".$connection->quote($group_id);

	if($user_id)
		$query .= " AND file.user_id = ".$connection->quote($user_id);

    //connection
	$statement = $connection->query($query);
	$statement->setFetchMode(PDO::FETCH_ASSOC); 
	$rs = $statement->fetchAll();
	$statement->closeCursor();
	$statement = null;

    if (count($rs) > 0){
      return $rs[0]["total"];
    } else {
      return 0;
    }
  }  

  /*________________________________________________________________________________________________________________*/
  public static function search($engine, $keyword="", $user_id=0, $limit=1000, $tag_ids=array(), $author_id=0, 
  		$group_id=0, $file_type=0, $locations=array(), $usage_right=0, $years=array(), $sizes=array(), 
  		$sort="name_asc", $folder_id=0, $crit="N", $distribution = null, $constraint = null, 
  		$limitations_array = Array(), $licence = null, $use = null, $commercial = null, $creative_commons = null, 
  		$dates=array(), $orientation = null, $stars = 0, $states = Array())
  {
    $f0 = false;
    $f1 = false;
    $f2 = false;

	if(!empty($keyword) && $keyword == sfConfig::get("app_search_empty_media"))
	{
		$query = "	SELECT file.id
					FROM file LEFT JOIN file_tag ON (file_tag.file_id = file.id AND file_tag.type = ".FileTagPeer::__TYPE_FILE.")
					WHERE .file_tag.tag_id IS NULL";

		$connection = Propel::getConnection();
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$ids = array();

		while ($rs = $statement->fetch())
			$ids[] = $rs[0];

		$statement->closeCursor();
		$statement = null;

		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_VALIDATE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::ID, $ids, Criteria::IN);

			$groups = UserGroupPeer::getGroupIds(sfContext::getInstance()->getUser()->getId(), "", true);
			$groups2 = GroupePeer::getGroupsInArray2();
			$temp = Array();

			foreach($groups as $group)
				$temp[] = $group;

			foreach($groups2 as $group)
				$temp[] = $group->getId();

			$c->add(GroupePeer::ID, $temp, Criteria::IN);
	}
	else
	{
		$c = new Criteria();

		if(empty($states))
			$c->add(self::STATE, self::__STATE_VALIDATE);
		else
			$c->add(self::STATE, $states, Criteria::IN);

		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		if(!empty($orientation))
		{
			switch($orientation)
			{
				case 'square': $c->add(FilePeer::WIDTH, FilePeer::WIDTH.'='.FilePeer::HEIGHT, Criteria::CUSTOM); break;
				case 'landscape': $c->add(FilePeer::WIDTH, FilePeer::WIDTH.'>'.FilePeer::HEIGHT, Criteria::CUSTOM); break;
				case 'portrait': $c->add(FilePeer::WIDTH, FilePeer::WIDTH.'<'.FilePeer::HEIGHT, Criteria::CUSTOM); break;
			}
		}

		if(!empty($licence) && $licence > -1)
			$c->add(self::LICENCE_ID, $licence);

		if(!empty($creative_commons) && $creative_commons > -1)
			$c->add(self::CREATIVE_COMMONS_ID, $creative_commons);
		else
		{
			if(!empty($use) && $use > -1)
				$c->add(self::USAGE_USE_ID, $use);

			if(!empty($commercial) && $commercial > -1)
				$c->add(self::USAGE_COMMERCIAL_ID, $commercial);

			if(!empty($distribution) && $distribution > -1)
				$c->add(self::USAGE_DISTRIBUTION_ID, $distribution);

			if(!empty($constraint) && $constraint > -1)
				$c->add(self::USAGE_CONSTRAINT_ID, $constraint);

			if(!empty($limitations_array))
			{
				$c->addJoin(self::ID, FileRightPeer::OBJECT_ID);
				$c->add(FileRightPeer::TYPE, 3);

				$sql = "(SELECT file.id
						FROM file
						INNER JOIN file_right ON ( file_right.object_id = file.id AND file_right.type = '3')
						WHERE (";

				foreach($limitations_array as $limitation_id => $limitation_value)
				{
					$sql .= "(file_right.usage_limitation_id = ".$limitation_id." AND (";

					switch($limitation_id)
					{
						case UsageLimitationPeer::__TIME_LIMIT:
						case UsageLimitationPeer::__NB_VIEWS:
						case UsageLimitationPeer::__NB_PRINTS:
							$sql .= "file_right.value = ".$limitation_value."))";
						break;

						case UsageLimitationPeer::__GEO_LIMIT:
						case UsageLimitationPeer::__SUPPORT:
							$temp = explode(";", $limitation_value);

							foreach($temp as $value)
							{
								if(!empty($value))
									$sql .= "(file_right.value LIKE \"".$value.";%\" OR file_right.value LIKE \"%;".$value.";%\" OR file_right.value LIKE \"%;".$value."\") OR ";
							}

							$sql = substr($sql, 0, -3);
							$sql .= "))";
						break;
					}

					$sql .= " AND ";
				}

				$sql = substr($sql, 0, -4);
				$sql .= "))";

				$f0 = true;
				$c->addAlias('f0', $sql);
				$c->addJoin(self::ID, "f0.id", Criteria::INNER_JOIN);
			}
		}

			$groups2 = GroupePeer::getGroupsInArray2();
			$temp = Array();

			foreach($groups as $group)
				$temp[] = $group;

			foreach($groups2 as $group)
				$temp[] = $group->getId();

			$c->add(GroupePeer::ID, $temp, Criteria::IN);
		
		if(sizeof($tag_ids)){
		  $query = " (
			SELECT file.id
			FROM file
			INNER JOIN file_tag ON ( file_tag.FILE_ID = file.ID AND file_tag.TYPE = '3')
			WHERE file_tag.TAG_ID IN (".join(",", $tag_ids).") GROUP BY file.ID
			HAVING COUNT( file.id )=".sizeof($tag_ids)."
		  )";

		  $c->addAlias('f1', $query);
		  $c->addJoin(self::ID, "f1.id", Criteria::LEFT_JOIN);
		  $f1 = true;
		}
		  
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));

		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
		  $engine->setMode(SPH_MATCH_EXTENDED);
		  $engine->setIndex("files");
		  // $ids = $engine->search(utf8_decode($keyword)."|*".utf8_decode($keyword)."|".utf8_decode($keyword)."*|*".utf8_decode($keyword)."*");
		  $ids = $engine->search(utf8_decode($keyword));

		  if(count($ids) > 0 && is_array($ids))
		  {
			  $query = "(SELECT file.ID
						FROM file
						WHERE file.ID IN (".implode(",", $ids).")
						AND file.GROUPE_ID IN (".implode(",", $temp)."))";
		  }
		  else
		  {
			$query = "(SELECT file.ID
						FROM file
						WHERE file.ID IN (null))";
		  }

		  $c->addAlias('f2', $query);
		  $c->addJoin(self::ID, "f2.id", Criteria::LEFT_JOIN);
		  $f2 = true;
		}

		if($f1 && $f2 && $f0)
		  $c->add(self::ID, "(f1.id && f2.id && f0.id)=1", Criteria::CUSTOM);
		elseif($f1 && $f2)
		  $c->add(self::ID, "(f1.id && f2.id)=1", Criteria::CUSTOM);
		elseif($f0 && $f2)
		  $c->add(self::ID, "(f0.id && f2.id)=1", Criteria::CUSTOM);
		elseif($f0 && $f1)
		  $c->add(self::ID, "(f0.id && f1.id)=1", Criteria::CUSTOM);
		elseif($f1)
		  $c->add(self::ID, "f1.id", Criteria::CUSTOM);
		elseif($f2)
		  $c->add(self::ID, "f2.id", Criteria::CUSTOM);
		elseif($f0)
		  $c->add(self::ID, "f0.id", Criteria::CUSTOM);
		
		if($folder_id) $c->add(self::FOLDER_ID, $folder_id);
		
		if($group_id)
		{
			$c->add(self::GROUPE_ID, $group_id);

			if(!UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $group_id))
			{
				if(!sfContext::getInstance()->getUser()->hasCredential("admin")){
					if($author_id)
						$c->add(self::USER_ID, sfContext::getInstance()->getUser()->getId());
					elseif($user_id)
					{
						$c->add(self::USER_ID, $user_id);
						// $c->add(UserGroupPeer::USER_ID, $user_id);
						// $c->addJoin(UserGroupPeer::GROUPE_ID, self::GROUPE_ID);
					}
				}
			}
		}
		else
		{
			if(!sfContext::getInstance()->getUser()->hasCredential("admin")){
				if($author_id)
					$c->add(self::USER_ID, sfContext::getInstance()->getUser()->getId());
				elseif($user_id)
				{
					$c->add(self::USER_ID, $user_id);
					// $c->add(UserGroupPeer::USER_ID, $user_id);
					// $c->addJoin(UserGroupPeer::GROUPE_ID, self::GROUPE_ID);
				}    
			}
		}
		
		if($file_type) $c->add(self::EXTENTION, $file_type);
		
		if(sizeof($locations) && $locations[0] && $locations[1]){
		  $c1 = $c->getNewCriterion(self::LAT, $locations[0]);
		  $c2 = $c->getNewCriterion(self::LAT, $locations[1]);
		  
		  $c1->addAnd($c2);
		  $c->add($c1);
		}
		
		if(sizeof($years) && $years[0] && $years[1]){
		  $c1 = $c->getNewCriterion(self::CREATED_AT, $years[0]."-01-01", Criteria::GREATER_EQUAL);
		  $c2 = $c->getNewCriterion(self::CREATED_AT, $years[1]."-12-31", Criteria::LESS_EQUAL);
		  
		  $c1->addAnd($c2);
		  $c->add($c1);
		}

		if(sizeof($dates) && $dates[0] && $dates[1]){
			$connection = Propel::getConnection();
			$from = mktime(0,0,0,1,1,$dates[0]);
			$to = mktime(0,0,0,12,31,$dates[1]);

			$fileIds = Array();

			$query = "SELECT DISTINCT exif.file_id, exif.value
					FROM exif, file, groupe, customer
					WHERE exif.file_id = file.id
					AND file.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
					AND exif.title = 'DateTimeOriginal'";

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while ($rs = $statement->fetch())
			{
				$temp = explode(" ", $rs[1]);

				if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
				{
					$date = explode('/', $temp[0]);

					if(mktime(0,0,0,$date[1],$date[0],$date[2]) >= $from && mktime(0,0,0,$date[1],$date[0],$date[2]) <= $to)
						$fileIds[] = $rs[0];
				}
			}

			$statement->closeCursor();
			$statement = null;

			$query = "SELECT DISTINCT iptc.file_id, iptc.value
					FROM iptc, file, groupe, customer
					WHERE iptc.file_id = file.id
					AND file.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
					AND iptc.title = 'Date Created'";

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while ($rs = $statement->fetch())
			{
				$temp = explode(" ", $rs[1]);

				if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
				{
					$date = explode('/', $temp[0]);

					if(mktime(0,0,0,$date[1],$date[0],$date[2]) >= $from && mktime(0,0,0,$date[1],$date[0],$date[2]) <= $to)
						$fileIds[] = $rs[0];
				}
			}

			$statement->closeCursor();
			$statement = null;

			$fileIds = array_unique($fileIds);

			$c->add(self::ID, $fileIds, Criteria::IN);
		}
		
		if(sizeof($sizes) && $sizes[0] && $sizes[1] && ($sizes[0] != $sizes[1]))
		{
		  /*$tmp_min = explode(" ", $sizes[0]); // 10 Kb     
		  $min = self::convertSize($tmp_min[0], $tmp_min[1]);
		  $tmp_max = explode(" ", $sizes[1]);
		  $max = self::convertSize($tmp_max[0], $tmp_max[1]);*/
		  
		  $c1 = $c->getNewCriterion(self::SIZE, $sizes[0], Criteria::GREATER_EQUAL);
		  $c2 = $c->getNewCriterion(self::SIZE, $sizes[1], Criteria::LESS_EQUAL);
		  
		  $c1->addAnd($c2);
		  $c->add($c1);
		}
		
		switch ($sort) {
		  default: ;
			case "name_asc": $c->addAscendingOrderByColumn(self::NAME); $c->addAscendingOrderByColumn(self::ORIGINAL); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); $c->addDescendingOrderByColumn(self::ORIGINAL); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			case "rate_asc": $c->addAscendingOrderByColumn(self::AVERAGE_POINT); break;
			case "rate_desc": $c->addDescendingOrderByColumn(self::AVERAGE_POINT); break;
		}
		
		$c->setLimit($limit);

		if(!empty($stars))
		{
			$files = self::doSelect($c);
			$files_ok = Array();

			foreach($files as $file)
			{
				if($rate = RatingPeer::getFileRate($file->getId()))
				{
					if($rate->getNbRate() > 0)
					{
						$star = round($rate->getTotalRate() / $rate->getNbRate());

						if($stars == $star)
							$files_ok[] = $file->getId();
					}
				}
			}

			$c = new Criteria();
			$c->add(self::ID, $files_ok, Criteria::IN);
		}
	}

	if($crit == "Y")
		return $c;
	else
		return self::doSelect($c);
  }

  /*________________________________________________________________________________________________________________*/
  public static function getSizes($customer_id = null)
  {
	$connection = Propel::getConnection();

    //preparing query
    $query = "SELECT size FROM file, groupe, customer WHERE groupe.id = file.groupe_id AND groupe.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND groupe.customer_id = ".$connection->quote($customer_id ? $customer_id : sfContext::getInstance()->getUser()->getCustomerId())." AND size <> 0 AND file.state = ".$connection->quote(self::__STATE_VALIDATE);

	$statement = $connection->query($query);
	$statement->setFetchMode(PDO::FETCH_ASSOC); 
	$rs = $statement->fetchAll();
	$statement->closeCursor();
	$statement = null;

	$sizes = array();
	for($i = 0; $i < count($rs); $i++) {
	  $sizes[] = $rs[$i]["size"];
	}

    $sizes = array_unique($sizes); 
    sort($sizes);
    return $sizes;
  }
  
  public static function getYears()
  {
	$connection = Propel::getConnection();

    //preparing query
	$query = "SELECT DISTINCT EXTRACT(YEAR FROM file.created_at) AS file_year, EXTRACT(YEAR FROM folder.created_at) AS folder_year
			FROM file, folder, groupe, customer
			WHERE groupe.id = file.groupe_id
			AND file.folder_id = folder.id
			AND groupe.customer_id = customer.id
			AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
			AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
			AND file.state = ".$connection->quote(self::__STATE_VALIDATE);

    //connection
	$statement = $connection->query($query);
	$statement->setFetchMode(PDO::FETCH_ASSOC); 
	$rs = $statement->fetchAll();
	$statement->closeCursor();
	$statement = null;

	$years = array();
	for($i = 0; $i < count($rs); $i++)
	{
		$years[$rs[$i]["file_year"]] = $rs[$i]["file_year"];
		$years[$rs[$i]["folder_year"]] = $rs[$i]["folder_year"];
	}

    $years = array_unique($years);
    sort($years);

    return $years;
  }

  /*________________________________________________________________________________________________________________*/
  public static function checkMapFiles($group_id=0, $folder_id=0)
  {
	if($folder_id)
	{
		$folders = FolderPeer::retrieveAllSubfolder($folder_id);
		$folders[$folder_id] = $folder_id;

		$files_array = Array();
		$i = 1;

		foreach($folders as $folder_id)
		{
			$c = new Criteria();
			$c->add(self::STATE, self::__STATE_VALIDATE);
			$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			$c->add(self::FOLDER_ID, $folder_id);

			$c1 = $c->getNewCriterion(self::LAT, "", Criteria::NOT_EQUAL);
			$c2 = $c->getNewCriterion(self::LNG, "", Criteria::NOT_EQUAL);
			$c1->addAnd($c2);
			$c->add($c1);

			$files = self::doSelect($c);

			foreach($files as $file)
				$files_array[$i++] = $file->getId();
		}

		return $files_array;
	}
	else
	{
		$c = new Criteria();
		$c->add(self::STATE, self::__STATE_VALIDATE);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		if($group_id) $c->add(self::GROUPE_ID, $group_id);
		
		$c1 = $c->getNewCriterion(self::LAT, "", Criteria::NOT_EQUAL);
		$c2 = $c->getNewCriterion(self::LNG, "", Criteria::NOT_EQUAL);
		$c1->addAnd($c2);
		$c->add($c1);
		
		$files = self::doSelect($c);

		$files_array = array();
		$i = 1;
		foreach ($files as $file){
		  $files_array[$i++] = $file->getId();
		}
		return $files_array;
	}
  }

  /*________________________________________________________________________________________________________________*/
  public static function getView($file_id)
  {
	$requete_getView = new Criteria();
	$requete_getView->addJoin(LogPeer::OBJECT_ID, self::ID);
	$requete_getView->add(self::STATE, self::__STATE_VALIDATE);
	$requete_getView->add(LogPeer::OBJECT_ID, $file_id);
	$requete_getView->add(LogPeer::TYPE, 3);
	$requete_getView->add(LogPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$requete_getView->addJoin(LogPeer::CUSTOMER_ID, CustomerPeer::ID);
	$requete_getView->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$requete_getView->add(LogPeer::LOG_TYPE, array("file-download", "files-download", "file-print", "file-email", "permalink-create"), Criteria::IN);
	$views = LogPeer::doCount($requete_getView);

	return $views;
  }

  /*________________________________________________________________________________________________________________*/
  public static function getUniqueView($year=0, $month=0, $group_id = null)
  {
	$connection = Propel::getConnection();

	if($month != "all")
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

	if(empty($year) && empty($month))
	{
		$requete_getUniqueView = "SELECT count(distinct log.object_id) as total FROM log, file";
		$requete_getUniqueView .= ", customer";
		$requete_getUniqueView .= " WHERE log.object_id = file.id AND log.type = '3' AND file.state = ".$connection->quote(self::__STATE_VALIDATE)." AND log.log_type IN ('file-download','files-download','file-print','file-email','permalink-create'";
	}
	else
	{
		$requete_getUniqueView = "SELECT count(distinct log.object_id) as total FROM log, file";
		$requete_getUniqueView .= ", customer";

		$requete_getUniqueView .= " WHERE log.object_id = file.id AND log.type = '3' AND file.state = ".$connection->quote(self::__STATE_VALIDATE)." AND log.created_at >= \"".$connection->quote($date_s)."\" AND log.created_at <= \"".$connection->quote($date_f)."\" AND log.log_type IN ('file-download','files-download','file-print','file-email','permalink-create')";
	}

	$requete_getUniqueView .= " AND log.customer_id = customer.id AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)." AND customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

	if($group_id)
		$requete_getUniqueView .= " AND file.groupe_id = ".$connection->quote($group_id);

	$statement = $connection->query($requete_getUniqueView);
	$statement->setFetchMode(PDO::FETCH_ASSOC); 
	$rs = $statement->fetchAll();
	$statement->closeCursor();
	$statement = null;

	if (count($rs) > 0)
		return $rs[0]["total"];
	else
		return 0;
  }

  /*________________________________________________________________________________________________________________*/
  public static function getGlobalView($year=0, $month=0, $group_id = null) {

	if($month != "all")
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

	$requete_getView = new Criteria();
	$requete_getView->addJoin(LogPeer::OBJECT_ID, self::ID);
	$requete_getView->add(self::STATE, self::__STATE_VALIDATE);

	if($group_id)
		$requete_getView->add(self::GROUPE_ID, $group_id);

	if(!empty($year) && !empty($month))
	{
		$crit0 = $requete_getView->getNewCriterion(LogPeer::CREATED_AT, $date_s, Criteria::GREATER_EQUAL);
		$crit1 = $requete_getView->getNewCriterion(LogPeer::CREATED_AT, $date_f, Criteria::LESS_EQUAL);
		$crit0->addAnd($crit1);
		$requete_getView->add($crit0);
	}

	$requete_getView->add(LogPeer::TYPE, 3);
	$requete_getView->add(LogPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$requete_getView->addJoin(LogPeer::CUSTOMER_ID, CustomerPeer::ID);
	$requete_getView->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$requete_getView->add(LogPeer::LOG_TYPE, array("file-download", "files-download", "file-print", "file-email", "permalink-create"), Criteria::IN);
	return LogPeer::doCount($requete_getView);
  }

  /*________________________________________________________________________________________________________________*/
  public static function getFilePagerMethod(Criteria $c)
  {
	$connection = Propel::getConnection();
	$map = $c->getMap();
	$group_id = $map[self::GROUPE_ID]->getValue();
	$max = $c->getLimit();
	$offset = $c->getOffset();

	$query = "SELECT file.*
			FROM file, groupe, customer
			WHERE file.groupe_id = groupe.id
			AND groupe.customer_id = customer.id
			AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
			AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
			AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
			AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

	if(!empty($group_id) && $group_id != "all")
		$query .= " AND file.groupe_id = ".$connection->quote($group_id);

	if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
	{
		$query .= " AND file.folder_id NOT IN (SELECT user_folder.folder_id
												FROM user_folder
												WHERE user_folder.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")
					AND ((groupe.free_credential IS NOT NULL) OR (groupe.free_credential IS NULL AND file.groupe_id IN (SELECT user_group.groupe_id
												FROM user_group
												WHERE user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")))";
	}

	$query .= " ORDER BY file.created_at DESC
			LIMIT ".$offset.",".$max;

	$statement = $connection->query($query);
	$statement->setFetchMode(PDO::FETCH_NUM); 

	$files = array();
	while ($rs = $statement->fetch())
	{
		$file = new File();
		$file->hydrate($rs);
		$files[] = $file;
	}
	$statement->closeCursor();
	$statement = null;

	return $files;
  }

  /*________________________________________________________________________________________________________________*/
  public static function getFilePagerCount(Criteria $c)
  {
	$connection = Propel::getConnection();

	$map = $c->getMap();
	$group_id = $map[self::GROUPE_ID]->getValue();

	$query = "SELECT count(file.id) as total
		FROM file, groupe, customer
		WHERE file.groupe_id = groupe.id
		AND groupe.customer_id = customer.id
		AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
		AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
		AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
		AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

	if(!empty($group_id) && $group_id != "all")
		$query .= " AND file.groupe_id = ".$connection->quote($group_id);

	if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
	{
		$query .= " AND file.folder_id NOT IN (SELECT user_folder.folder_id
												FROM user_folder
												WHERE user_folder.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")
					AND ((groupe.free_credential IS NOT NULL) OR (groupe.free_credential IS NULL AND file.groupe_id IN (SELECT user_group.groupe_id
												FROM user_group
												WHERE user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")))";
	}

	$query .= " ORDER BY file.created_at DESC";

	$folders = FolderPeer::doSelect($c);

	$statement = $connection->query($query);
	$statement->setFetchMode(PDO::FETCH_ASSOC); 
	$rs = $statement->fetchAll();
	$statement->closeCursor();
	$statement = null;

	return $rs[0]["total"];
  }

  /*________________________________________________________________________________________________________________*/
  public static function getFilePager($page=1, $per_page=100, $group)
  {
	$c = new Criteria();
	$c->add(self::GROUPE_ID, $group);

	$pager = new sfPropelPager('File', $per_page);
	$pager->setCriteria($c);
	$pager->setPage($page);
	$pager->setPeerMethod('getFilePagerMethod');
	$pager->setPeerCountMethod('getFilePagerCount');
	$pager->init();

	return $pager;
  }

  /*________________________________________________________________________________________________________________*/
  public static function hasHistory($path, $file) {
	$a_mask = "DATET-*".$file->getOriginal();
	$dir = @dir("$path");

	if (!$dir) {
		return false;
	}

	while (($fileD = $dir->read()) !== false) {
		if($fileD !="." && $fileD!=".." && fnmatch($a_mask, $fileD))
			return true;
	}

	$dir->close();

	return false; 
  }

  /*________________________________________________________________________________________________________________*/
  public static function getHistory($path, $file) {
	$a_mask = "DATET-*".$file->getOriginal();
	$res = array();
	$old = $file->getCreatedAt("U");
	$dircontent = scandir($path);
	$arr = array();

	foreach($dircontent as $filename) {
		if ($filename != '.' && $filename != '..') {
			if (filemtime($path.$filename) === false) return false;
			$dat = date("YmdHis", filemtime($path.$filename));
			$arr[$dat] = $filename;
		}
	}

	if (!ksort($arr)) return false;

	foreach($arr as $key => $value) {
		if(fnmatch($a_mask, $value)) {
			$res[$old] = substr($value, 6, 10);
			$old = substr($value, 6, 10);
		}
	}

	$res[$old] = "";
	krsort($res);

	$limit = sfContext::getInstance()->getUser()->getModuleValue(ModulePeer::__MOD_VERSIONNING);
	if($limit > 0)
		array_splice($res, $limit);

	return $res;
  }

  /*________________________________________________________________________________________________________________*/
  public static function getCustomerFiles($customer_id = null)
  {
	$c = new Criteria();
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);

	if($customer_id)
		$c->add(GroupePeer::CUSTOMER_ID, $customer_id);
	else
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());

	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

	return self::doCount($c);
  }

  /*________________________________________________________________________________________________________________*/
  public static function advancedSearch($ids = null, $added_by = null, $file_type = null, $color = null, 
  		$extension = null, $condition = null, $size = null, $group_id = 0, $folder_id = 0)
  {
	$c = new Criteria();
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());


	if($ids)
	{
		$temp = array_values($ids);

		if(!empty($temp))
			$c->add(self::ID, $ids, Criteria::IN);
	}

	if($file_type)
		$c->add(self::TYPE, $file_type);

	if($extension)
		$c->add(self::EXTENTION, $extension);

	if($added_by)
		$c->add(self::USER_ID, $added_by);

	if($color)
		$c->add(self::MAIN_COLOR, $color);

	if($size)
	{
		$search_size = SearchPictureSizePeer::retrieveByPk($size);

		switch($condition)
		{
			case "equal":
				$c->add(self::WIDTH, $search_size->getWidth());
				$c->add(self::HEIGHT, $search_size->getHeight());
			break;

			case "more":
				$c->add(self::WIDTH, $search_size->getWidth(), Criteria::GREATER_THAN);
				$c->add(self::HEIGHT, $search_size->getHeight(), Criteria::GREATER_THAN);
			break;

			case "less":
				$c->add(self::WIDTH, $search_size->getWidth(), Criteria::LESS_THAN);
				$c->add(self::HEIGHT, $search_size->getHeight(),  Criteria::LESS_THAN);
			break;
			
			case "more_equal":
				$c->add(self::WIDTH, $search_size->getWidth(), Criteria::GREATER_EQUAL);
				$c->add(self::HEIGHT, $search_size->getHeight(), Criteria::GREATER_EQUAL);
			break;

			case "less_equal":
				$c->add(self::WIDTH, $search_size->getWidth(), Criteria::LESS_EQUAL);
				$c->add(self::HEIGHT, $search_size->getHeight(), Criteria::LESS_EQUAL);
			break;

			case "different":
				$c->add(self::WIDTH, $search_size->getWidth(), Criteria::NOT_EQUAL);
				$c->add(self::HEIGHT, $search_size->getHeight(), Criteria::NOT_EQUAL);
			break;
		}
	}

	return self::doSelect($c);
  }

  /*________________________________________________________________________________________________________________*/
  public static function getWaitingFile($group_id = null)
  {
	$c = new Criteria();
	$c->setDistinct();
	$c->add(FileWaitingPeer::STATE, array(FileWaitingPeer::__STATE_WAITING_VALIDATE, FileWaitingPeer::__STATE_WAITING_DELETE), Criteria::IN);
	$c->addJoin(FileWaitingPeer::FILE_ID, self::ID);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

	if($group_id)
		$c->add(self::GROUPE_ID, $group_id);

	return self::doSelect($c);
  }

  /*________________________________________________________________________________________________________________*/
  public static function getWaitingFilePager($group_id = null, $page = 1, $type)
  {
	$c = new Criteria();
	$c->setDistinct();
	$c->add(FileWaitingPeer::STATE, $type);
	$c->addJoin(FileWaitingPeer::FILE_ID, self::ID);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

	if($group_id)
		$c->add(self::GROUPE_ID, $group_id);

	$pager = new sfPropelPager('File', 15);
	$pager->setCriteria($c);
	$pager->setPage($page);
	$pager->setPeerMethod('doSelect');
	$pager->init();

	return $pager;
  }

  /*________________________________________________________________________________________________________________*/
  public static function getDeleteFilePager($page = 1, $item = 20, $sort = "name_asc")
  {
	$c = new Criteria();
	$c->setDistinct();
	$c->add(FileWaitingPeer::STATE, FileWaitingPeer::__STATE_DELETE);
	$c->addJoin(FileWaitingPeer::FILE_ID, self::ID);
	$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
	$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
	$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

	switch ($sort) {
		default:
		case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
		case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
		case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
		case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
	}

	$pager = new sfPropelPager('File', $item);
	$pager->setCriteria($c);
	$pager->setPage($page);
	$pager->setPeerMethod('doSelect');
	$pager->init();

	return $pager;
  }

  /*________________________________________________________________________________________________________________*/
  public static function deleteOldFile($delay = 30)
  {
	$begin = mktime(0,0,0,date("m"), (date("d") - $delay), date("Y"));
	$end = mktime(23,59,59,date("m"), (date("d") - $delay), date("Y"));

	$c = new Criteria();
	$crit0 = $c->getNewCriterion(self::UPDATED_AT, $begin, Criteria::GREATER_EQUAL);
	$crit1 = $c->getNewCriterion(self::UPDATED_AT, $end, Criteria::LESS_EQUAL);
	$crit0->addAnd($crit1);
	$c->add($crit0);
	$c->add(self::UPDATED_AT, "0000-00-00 00:00:00", Criteria::NOT_EQUAL);
	$c->add(self::STATE, self::__STATE_DELETE);

	$files = self::doSelect($c);

	foreach($files as $file)
	{
		$path = sfConfig::get('app_path_upload_dir').'/'.$file->getDisk().'/cust-'.$file->getCustomerId().'/folder-'.$file->getFolderId().'/';

		@unlink($path.$file->getThumb100());
		@unlink($path.$file->getThumb200());
		@unlink($path.$file->getWeb());
		@unlink($path.$file->getOriginal());
		@unlink($path.$file->getFileName().".poster.jpeg");
		@unlink($path.$file->getFileName().".flv");
	}
  }

  /*________________________________________________________________________________________________________________*/
  public static function searchPublic(Criteria $c)
  {
	$max = $c->getLimit();
	$offset = $c->getOffset();

	$map = $c->getMap();
	$folder_id = $map[FolderPeer::ID]->getValue();

	/*$c = new Criteria();
	$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
	$c->add(FolderPeer::SUBFOLDER_ID, $folder_id);
	$c->addDescendingOrderByColumn(FolderPeer::NAME);

	$folders = FolderPeer::doSelect($c);*/

	$c = new Criteria();
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->add(self::FOLDER_ID, $folder_id);
	$c->addDescendingOrderByColumn(self::NAME);
	$c->addDescendingOrderByColumn(self::ORIGINAL);

	$files = self::doSelect($c);

	// return array_slice(array_merge($folders, $files), $offset, $max);
	return array_slice($files, $offset, $max);
  }

  /*________________________________________________________________________________________________________________*/
  public static function countSearchPublic(Criteria $c)
  {
	$map = $c->getMap();
	$folder_id = $map[FolderPeer::ID]->getValue();

	/*$c = new Criteria();
	$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
	$c->add(FolderPeer::SUBFOLDER_ID, $folder_id);
	$c->addDescendingOrderByColumn(FolderPeer::NAME);

	$folders = FolderPeer::doSelect($c);*/

	$c = new Criteria();
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->add(self::FOLDER_ID, $folder_id);
	$c->addDescendingOrderByColumn(self::NAME);
	$c->addDescendingOrderByColumn(self::ORIGINAL);

	$files = self::doSelect($c);

	// return count(array_merge($folders, $files));
	return count($files);
  }

  /*________________________________________________________________________________________________________________*/
  public static function isUnderFolder($file_id, $folder_id)
  {
	$c = new Criteria();
	$c->add(self::ID, $file_id);
	$c->add(self::STATE, self::__STATE_VALIDATE);
	$c->add(self::FOLDER_ID, $folder_id);

	if(!self::doSelect($c))
	{
		$c = new Criteria();
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(FolderPeer::SUBFOLDER_ID, $folder_id);

		$folders = FolderPeer::doSelect($c);

		foreach($folders as $folder)
			return self::isUnderFolder($file_id, $folder->getId());
	}
	else
		return true;
  }

	/*________________________________________________________________________________________________________________*/
	public static function getSizeOfArray($files)
	{
		if(empty($files))
			return 0;
	
		$connection = Propel::getConnection();
	
		$query = "SELECT sum(file.size) as total
				FROM file
				WHERE file.state = ".$connection->quote(self::__STATE_VALIDATE)."
				AND file.id IN (".implode(",",$files).")";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		return $rs[0]["total"];
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByCustomerId($customer_id)
	{
		$c = new Criteria();
		$c->addJoin(self::GROUPE_ID, GroupePeer::ID);
		$c->add(GroupePeer::CUSTOMER_ID, $customer_id);
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_VALIDATE);
	
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function countByCustomerId($customer_id)
	{
		$c = new Criteria();

		$c->addJoin(self::GROUPE_ID, GroupePeer::ID);
		$c->add(GroupePeer::CUSTOMER_ID, $customer_id);
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::STATE, self::__STATE_VALIDATE);
	
		return self::doCount($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getMaxIndex()
	{
		return self::doCount(new Criteria());
	}

	/*________________________________________________________________________________________________________________*/
	public static function getExtensions($customer_id, $keyword = null)
	{
		$connection = Propel::getConnection();
	
		$query = "SELECT distinct file.extention
				FROM file, groupe, customer
				WHERE file.groupe_id = groupe.id
				AND groupe.customer_id = customer.id
				AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
				AND customer.id = ".$connection->quote($customer_id);
	
		if($keyword)
			$query .= " AND file.extention LIKE '%".$keyword."%'";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		$extensions = array();
		while ($rs = $statement->fetch())
			$extensions[$rs[0]] = $rs[0];
	
		$statement->closeCursor();
		$statement = null;
	
		return $extensions;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getAllFiles($keyword = "", $sort = "creation_date_desc", $page = 1)
	{
		$c = new Criteria();
	
		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
			$c1 = $c->getNewCriterion(self::NAME, $keyword."%", Criteria::LIKE);
			$c2 = $c->getNewCriterion(self::ORIGINAL, "%".$keyword."%", Criteria::LIKE);
			$c3 = $c->getNewCriterion(self::HASH, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c2);
			$c1->addOr($c3);
			$c->add($c1);
		}
	
		switch($sort)
		{
			case "creation_date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "creation_date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
	
			case "name_asc": $c->addDescendingOrderByColumn(self::NAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
	
			case "file_name_asc": $c->addDescendingOrderByColumn(self::ORIGINAL); break;
			case "file_name_desc": $c->addDescendingOrderByColumn(self::ORIGINAL); break;
	
			case "size_asc": $c->addDescendingOrderByColumn(self::SIZE); break;
			case "size_desc": $c->addDescendingOrderByColumn(self::SIZE); break;
	
			case "sate_asc": $c->addDescendingOrderByColumn(self::STATE); break;
			case "sate_desc": $c->addDescendingOrderByColumn(self::STATE); break;
	
			case "hash_asc": $c->addDescendingOrderByColumn(self::CHECKSUM); break;
			case "hash_desc": $c->addDescendingOrderByColumn(self::CHECKSUM); break;
		}
	
		$pager = new sfPropelPager('File', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getDuplicateFiles($type, $page)
	{
		$connection = Propel::getConnection();
	
		switch($type)
		{
			case "name":
				$query = "	SELECT count(*) , file.name
							FROM file
							GROUP BY file.name
							HAVING count(*) > 1";
			break;
	
			case "filename":
				$query = "	SELECT count(*) , file.original
							FROM file
							GROUP BY file.original
							HAVING count(*) > 1";
			break;
	
			case "size":
				$query = "	SELECT count(*) , file.size
							FROM file
							GROUP BY file.size
							HAVING count(*) > 1";
			break;
	
			case "hash":
				$query = "	SELECT count(*) , file.checksum
							FROM file
							GROUP BY file.checksum
							HAVING count(*) > 1";
			break;
		}
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
		$file_ids = Array();
	
		while($rs = $statement->fetch())
		{
			$var = $rs[1];
	
			switch($type)
			{
				case "name": $files = self::retrieveByName($var); break;
				case "filename": $files = self::retrieveByOriginal($var); break;
				case "size": $files = self::retrieveBySize($var); break;
				case "hash": $files = self::retrieveByChecksum($var); break;
			}
	
			foreach($files as $file)
				$file_ids[] = $file->getId();
		}
	
		$c = new Criteria();
		$c->add(self::ID, $file_ids, Criteria::IN);
	
		switch($type)
		{
			case "name": $c->addDescendingOrderByColumn(self::NAME); break;
			case "filename": $c->addDescendingOrderByColumn(self::ORIGINAL); break;
			case "size": $c->addDescendingOrderByColumn(self::SIZE); break;
			case "hash": $c->addDescendingOrderByColumn(self::CHECKSUM); break;
		}
	
		$pager = new sfPropelPager('File', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByName($name)
	{
		$c = new Criteria();
		$c->add(self::NAME, $name);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByOriginal($original)
	{
		$c = new Criteria();
		$c->add(self::ORIGINAL, $original);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveBySize($size)
	{
		$c = new Criteria();
		$c->add(self::SIZE, $size);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByChecksum($checksum)
	{
		$c = new Criteria();
		$c->add(self::CHECKSUM, $checksum);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getShootingDate()
	{
		$connection = Propel::getConnection();
		$dates = array();
	
		$query = "SELECT DISTINCT exif.value
				FROM exif, file, groupe, customer
				WHERE exif.file_id = file.id
				AND file.groupe_id = groupe.id
				AND groupe.customer_id = customer.id
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
				AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
				AND exif.title = 'DateTimeOriginal'";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 
	
		while ($rs = $statement->fetch())
		{
			$temp = explode(" ", $rs[0]);
	
			if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
			{
				$years = explode('/', $temp[0]);
				$dates[] = $years[2];
			}
		}
	
		$statement->closeCursor();
		$statement = null;
	
		$query = "SELECT DISTINCT iptc.value
				FROM iptc, file, groupe, customer
				WHERE iptc.file_id = file.id
				AND file.groupe_id = groupe.id
				AND groupe.customer_id = customer.id
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
				AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
				AND iptc.title = 'Date Created'";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 
	
		while ($rs = $statement->fetch())
		{
			$temp = explode(" ", $rs[0]);
	
			if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
			{
				$years = explode('/', $temp[0]);
				$dates[] = $years[2];
			}
		}
	
		$statement->closeCursor();
		$statement = null;
	
		$dates = array_unique($dates);
		sort($dates);
	
		return $dates;
	}

	
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function getDuplicate($customer_id, $page,  $sort="hash_asc", $keyword="")
	{
		$connection = Propel::getConnection();
	
		$query = "	SELECT count(file.id), file.checksum
					FROM file, groupe, folder
					WHERE file.groupe_id = groupe.id
					AND file.folder_id = folder.id
					AND folder.state = ".FolderPeer::__STATE_ACTIVE."
					AND groupe.customer_id = ".$customer_id."
					AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
					AND file.state = ".FilePeer::__STATE_VALIDATE."
					GROUP BY file.checksum
					HAVING count(*) > 1";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		$file_ids = Array();
		while ($rs = $statement->fetch())
		{
			$files = self::retrieveByChecksumAndCustomerId($rs[1], $customer_id);
	
			if(count($files) > 1)
			{
				foreach($files as $file)
				{
					if(!in_array($file->getId(), $file_ids))
						$file_ids[] = $file->getId();
				}
			}
		}
	
		$statement->closeCursor();
		$statement = null;
	
		$c = new Criteria();
		$c->add(self::ID, $file_ids, Criteria::IN);
	
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
	
		if($keyword && $keyword != __("search") && $keyword != __("Search"))
		{
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
			$c1 = $c->getNewCriterion(self::NAME, "%".$keyword."%", Criteria::LIKE);
			$c2 = $c->getNewCriterion(self::CHECKSUM, "%".$keyword."%", Criteria::LIKE);
	
			$c1->addOr($c2);
			$c->add($c1);
		}
	
		switch ($sort) {
			default:
			case "name_asc": $c->addAscendingOrderByColumn(self::NAME); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::NAME); break;
			case "size_asc": $c->addAscendingOrderByColumn(self::SIZE); break;
			case "size_desc": $c->addDescendingOrderByColumn(self::SIZE); break;
			case "hash_asc": $c->addAscendingOrderByColumn(self::CHECKSUM); break;
			case "hash_desc": $c->addDescendingOrderByColumn(self::CHECKSUM); break;
	    }
	
		$pager = new sfPropelPager('File', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByChecksumAndCustomerId($checksum, $customer_id = null)
	{
		$c = new Criteria();
		$c->add(self::CHECKSUM, $checksum);
		$c->add(self::STATE, self::__STATE_VALIDATE);
	
		if(!empty($customer_id))
		{
			$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			$c->add(CustomerPeer::ID, $customer_id);
		}
	
		return self::doSelect($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getOrientationOfFiles($file_ids)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
		$orientations = Array();
	
		$connection = Propel::getConnection();
	
		$query = "	SELECT count(file.id) as square
					FROM file
					WHERE file.width > 0
					AND file.height > 0
					AND file.width = file.height
					AND file.id IN (".implode(",", $file_ids).")";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if($result[0]["square"] > 0)
			$orientations["square"] = __("Square");
	
		$query = "	SELECT count(file.id) as landscape
					FROM file
					WHERE file.width > 0
					AND file.height > 0
					AND file.width > file.height
					AND file.id IN (".implode(",", $file_ids).")";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if($result[0]["landscape"] > 0)
			$orientations["landscape"] = __("Landscape");
	
		$query = "	SELECT count(file.id) as portrait
					FROM file
					WHERE file.width > 0
					AND file.height > 0
					AND file.width < file.height
					AND file.id IN (".implode(",", $file_ids).")";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if($result[0]["portrait"] > 0)
			$orientations["portrait"] = __("Portrait");
	
		return $orientations;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getRatingOfFiles($file_ids)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
		$ratings = Array();
	
		$connection = Propel::getConnection();
	
		$query = "	SELECT distinct ROUND(rating.total_rate / rating.nb_rate) as rating
					FROM rating
					WHERE rating.file_id IN (".implode(",", $file_ids).")";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
	
		while($result = $statement->fetch())
		{
			$star = $result["rating"];
	
			if(!array_key_exists((string)$star, $ratings))
				$ratings[$star] = $star." ".($star > 1 ? __("stars") : __("star"));
		}
	
		$statement->closeCursor();
		$statement = null;
	
		return $ratings;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountMax()
	{
		$connection = Propel::getConnection();

		$query = "	SELECT count(file.id)
					FROM file";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $rs[0][0];
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByFolderIdRecursive($folder_id)
	{
		$folders = FolderPeer::retrieveAllSubfolder($folder_id);
		$folders[$folder_id] = $folder_id;

		$files_array = Array();

		foreach($folders as $folder_id)
		{
			$c = new Criteria();
			$c->add(self::FOLDER_ID, $folder_id);
			$c->add(self::STATE, self::__STATE_VALIDATE);
			$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			$c->addDescendingOrderByColumn(self::CREATED_AT);

			$files = self::doSelect($c);

			foreach($files as $file)
				$files_array[] = $file;
		}

		return $files_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function countFiles($folderId, &$count = 0)
	{
		$folder = FolderPeer::retrieveByPk($folderId);

		if($folder->getState() == FolderPeer::__STATE_ACTIVE)
		{
			$c = new Criteria();
			$c->addJoin(FolderPeer::ID, FilePeer::FOLDER_ID);
			$c->add(self::FOLDER_ID, $folder->getId());
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
			$c->add(self::STATE, self::__STATE_VALIDATE);

			$count += self::doCount($c);

			$c = new Criteria();
			$c->add(FolderPeer::SUBFOLDER_ID, $folder->getId());
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

			$folders = FolderPeer::doSelect($c);

			foreach($folders as $folder)
				self::countFiles($folder->getId(), $count);
		}

		return $count;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByGroupId($group_id, $tag_ids=array(), $offset = null, $limit = null)
	{
		$connection = Propel::getConnection();

		if(sizeof($tag_ids))
		{
			$tag_ids = array_unique($tag_ids);

			$query = "SELECT file.*, count(file.id) as CNT
					FROM file, file_tag, groupe, customer
					WHERE file.id = file_tag.file_id
					AND file.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND file_tag.type = 3
					AND file_tag.tag_id IN (".$connection->quote(join(",",$tag_ids)) .")
					GROUP BY file.id
					HAVING CNT = ".$connection->quote(sizeof($tag_ids))."
					ORDER BY file.created_at ASC";
		}
		else
		{
			$query = "SELECT file.*
					FROM file, groupe, customer
					WHERE file.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND groupe.id = ".$connection->quote($group_id);
		}

		if(!empty($limit) || !empty($offset))
		{
			if(empty($offset))
				$offset = 0;

			$query .= " LIMIT ".$offset.",".$limit;
		}

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$files = array();
		while ($rs = $statement->fetch())
		{
			$file = new File();
			$file->hydrate($rs);
			$files[] = $file;
		}

		$statement->closeCursor();
		$statement = null;

		return $files;
	}

	/*________________________________________________________________________________________________________________*/
	public static function updateGroupeCover($groupe_id)
	{
		$connection = Propel::getConnection();

		$query = "UPDATE file INNER JOIN groupe ON file.GROUPE_ID = groupe.ID
				SET groupe_cover = 0
				WHERE file.GROUPE_ID = ".$connection->quote($groupe_id)."
				AND groupe.CUSTOMER_ID = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId());

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->execute();
		$statement->closeCursor();
		$statement = null;

		$groupe = GroupePeer::retrieveByPk($groupe_id);

		$groupe->setThumbnail("");

		$groupe->save();
	}

	/*________________________________________________________________________________________________________________*/
	public static function searchEngine($keyword = null, $userId = null, $limit = 50, $tagIds = Array(), 
		$authorId = false, $groupId = null, $fileType = null, $locations = Array(), $years = Array(), 
		$sizes = Array(), $sort = "name_asc", $folderId = null, $distribution = null, $limitationsArray = Array(), 
		$licence = null, $use = null, $creative_commons = null, $dates = Array(), $orientation = null, 
		$stars = 0, $states = Array(), $offset = null, $exclude_extentions = Array(), $include_types = Array(), 
		$uploadedDates = Array())
	{
		function processFileIndex($operator, $array)
		{
			$return = Array();

			switch($operator)
			{
				case '':
				case "OR":
				{
					foreach($array as $values)
						$return = array_merge($return, $values);
				}
				break;

				case "AND":
				{
					$oldValue = Array();
					foreach($array as $values)
					{
						if(!empty($oldValue))
							$return = array_merge($return, array_intersect($oldValue, $values));

						$oldValue = $values;
					}
				}
				break;
			}

			return array_unique($return);
		}

		$connection = Propel::getConnection();

		$currentUserId = sfContext::getInstance()->getUser()->getId();
		$customerId = sfContext::getInstance()->getUser()->getCustomerId();
		$culture = $connection->quote(sfContext::getInstance()->getUser()->getCulture());

		if(empty($states))
			$states = Array(self::__STATE_VALIDATE);

		$groups = Array();
		$ids = Array();
		$files = Array();

		if(!empty($keyword) && $keyword == sfConfig::get("app_search_empty_media"))
		{
			$query = "	SELECT file.id
						FROM file LEFT JOIN file_tag ON (file_tag.file_id = file.id AND file_tag.type = ".FileTagPeer::__TYPE_FILE.")
						WHERE .file_tag.tag_id IS NULL";

			$connection = Propel::getConnection();
			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			$ids = array();

			while ($rs = $statement->fetch())
				$ids[] = $rs[0];

			$statement->closeCursor();
			$statement = null;

			$c = new Criteria();
			$c->add(self::STATE, self::__STATE_VALIDATE);
			$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			$c->add(self::ID, $ids, Criteria::IN);

			$groups = UserGroupPeer::getGroupIds($currentUserId, "", true);
			$groups2 = GroupePeer::getGroupsInArray2($currentUserId);
			$temp = Array();

			foreach($groups as $group)
				$temp[] = $group;

			foreach($groups2 as $group)
				$temp[] = $group->getId();

			$c->add(GroupePeer::ID, $temp, Criteria::IN);

			return self::doSelect($c);
		}
		else
		{
			if(!empty($tagIds))
			{
				$query = "	SELECT distinct file.id
							FROM file, file_tag, tag, groupe
							WHERE groupe.id = file.groupe_id
							AND file.id = file_tag.file_id
							AND file_tag.tag_id = tag.id
							AND file_tag.type = ".FileTagPeer::__TYPE_FILE."
							AND file.state IN (".implode($states, ",").")
							AND groupe.customer_id = ".$customerId."
							AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
							AND tag.customer_id = ".$customerId."
							AND tag.id IN (".implode($tagIds, ",").")";

				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_ASSOC);

				while($rs = $statement->fetch())
				{
					if(!in_array($rs["id"], $ids))
						$ids[] = $rs["id"];
				}

				$statement->closeCursor();
				$statement = null;

				if(empty($ids))
					$ids[] = -1;
			}

			if(!empty($keyword) && $keyword != __("search") && $keyword != __("Search"))
			{
				$filename = sfConfig::get("app_search_not_filename");
				$searchFilename = true;

				$searchExpression = Array();
				$searchOperator = "";

				if(substr($keyword, 0, 1) == '"' && substr($keyword, -1) == '"')
					$searchExpression[] = $keyword;
				else
				{
					$temp = explode(" OR ", $keyword);
					if(count($temp) > 1)
					{
						$searchOperator = "OR";

						foreach($temp as $t)
						{
							if(!empty($t))
							{
								if($t == $filename)
									$searchFilename = false;
								else
								{
									if(substr($t, 0, 1) == '*')
										$t = "%".$t;
									elseif(substr($t, -1) == '*')
										$t = $t."%";
									else
										$t = "%".$t."%";

									$searchExpression[] = $t;
								}
							}
						}
					}

					if(empty($searchExpression))
					{
						$temp = explode(" AND ", $keyword);
						if(count($temp) > 1)
						{
							$searchOperator = "AND";

							foreach($temp as $t)
							{
								if(!empty($t))
								{
									if($t == $filename)
										$searchFilename = false;
									else
									{
										if(substr($t, 0, 1) == '*')
											$t = "%".$t;
										elseif(substr($t, -1) == '*')
											$t = $t."%";
										else
											$t = "%".$t."%";

										$searchExpression[] = $t;
									}
								}
							}
						}
					}

					if(empty($searchExpression))
					{
						$temp = explode(" ", $keyword);
						if(count($temp) > 1)
						{
							$searchOperator = "AND";

							foreach($temp as $t)
							{
								if(!empty($t))
								{
									if($t == $filename)
										$searchFilename = false;
									else
									{
										if(substr($t, 0, 1) == '*')
											$t = "%".$t;
										elseif(substr($t, -1) == '*')
											$t = $t."%";
										else
											$t = "%".$t."%";

										$searchExpression[] = $t;
									}
								}
							}
						}
					}

					if(empty($searchExpression))
					{
						$searchOperator = "";

						if($keyword != $filename)
						{
							if(substr($keyword, 0, 1) == '*')
								$keyword = "%".$keyword;
							elseif(substr($keyword, -1) == '*')
								$keyword = $keyword."%";
							else
								$keyword = "%".$keyword."%";

							$searchExpression[] = $keyword;
						}
					}
				}

				/**
					Search tags.

					Search on TAG.TITLE
				**/
				$temp = Array();
				foreach($searchExpression as $searchTerm)
				{
					$temp[$searchTerm] = Array();

					$searchTag = str_replace("%", "", $searchTerm);

					$query = "	SELECT distinct file.id
								FROM file, file_tag, tag, groupe
								WHERE groupe.id = file.groupe_id
								AND file.id = file_tag.file_id
								AND file_tag.tag_id = tag.id
								AND file_tag.type = ".FileTagPeer::__TYPE_FILE."
								AND file.state IN (".implode($states, ",").")
								AND groupe.customer_id = ".$customerId."
								AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
								AND tag.customer_id = ".$customerId."
								AND tag.title = '".$searchTag."'";

					$statement = $connection->query($query);
					$statement->setFetchMode(PDO::FETCH_ASSOC);

					while($rs = $statement->fetch())
					{
						if(!in_array($rs["id"], $temp[$searchTerm]))
							$temp[$searchTerm][] = $rs["id"];
					}

					$statement->closeCursor();
					$statement = null;
				}

				$idsTags = processFileIndex($searchOperator, $temp);

				/**
					Search file.

					Search on FILE.NAME
					Search on FILE.DESCRIPTION
					Search on FILE.ORIGINAL
					Search on FILE.SOURCE
					Search on USER.FIRSTNAME
					Search on USER.LASTNAME
					Search on USER.EMAIL
				**/
				$temp = Array();
				foreach($searchExpression as $searchTerm)
				{
					$temp[$searchTerm] = Array();

					$query = "	SELECT distinct file.id
								FROM file, groupe, user
								WHERE groupe.id = file.groupe_id
								AND file.user_id = user.id
								AND file.state IN (".implode($states, ",").")
								AND groupe.customer_id = ".$customerId."
								AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
								AND
									(
										(file.name LIKE '".$searchTerm."')
										OR
										(file.description LIKE '".$searchTerm."')";

					if($searchFilename)
						$query .= "		OR
										(file.original LIKE '".$searchTerm."')";

					$query .= "			OR
										(file.source LIKE '".$searchTerm."')
										OR
										(user.firstname LIKE '".$searchTerm."')
										OR
										(user.lastname LIKE '".$searchTerm."')
										OR
										(user.email LIKE '".$searchTerm."')
									)";

					if(!$searchFilename)
						$query .= "	AND file.name != file.original";

					$statement = $connection->query($query);
					$statement->setFetchMode(PDO::FETCH_ASSOC);

					while($rs = $statement->fetch())
					{
						if(!in_array($rs["id"], $temp[$searchTerm]))
							$temp[$searchTerm][] = $rs["id"];
					}

					$statement->closeCursor();
					$statement = null;
				}

				$idsFiles = processFileIndex($searchOperator, $temp);

				/**
					Search field content.

					Search on FIELD_CONTENT.VALUE
				**/
				$temp = Array();
				foreach($searchExpression as $searchTerm)
				{
					$temp[$searchTerm] = Array();

					$query = "	SELECT distinct file.id
								FROM file, groupe, field_content
								WHERE groupe.id = file.groupe_id
								AND file.id = field_content.object_id
								AND field_content.object_type = ".FieldContentPeer::__FILE."
								AND file.state IN (".implode($states, ",").")
								AND groupe.customer_id = ".$customerId."
								AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
								AND field_content.value LIKE '".$searchTerm."'";

					$statement = $connection->query($query);
					$statement->setFetchMode(PDO::FETCH_ASSOC);

					while($rs = $statement->fetch())
					{
						if(!in_array($rs["id"], $temp[$searchTerm]))
							$temp[$searchTerm][] = $rs["id"];
					}

					$statement->closeCursor();
					$statement = null;
				}

				$idsFields = processFileIndex($searchOperator, $temp);

				/**
					Search person.

					Search on PERSON.NAME
				**/
				$temp = Array();
				foreach($searchExpression as $searchTerm)
				{
					$temp[$searchTerm] = Array();

					$query = "	SELECT distinct file.id
								FROM file, groupe, file_person, person
								WHERE groupe.id = file.groupe_id
								AND file.id = file_person.file_id
								AND file_person.person_id = person.id
								AND file.state IN (".implode($states, ",").")
								AND groupe.customer_id = ".$customerId."
								AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
								AND person.name LIKE '".$searchTerm."'";

					$statement = $connection->query($query);
					$statement->setFetchMode(PDO::FETCH_ASSOC);

					while($rs = $statement->fetch())
					{
						if(!in_array($rs["id"], $temp[$searchTerm]))
							$temp[$searchTerm][] = $rs["id"];
					}

					$statement->closeCursor();
					$statement = null;
				}

				$idsPersons = processFileIndex($searchOperator, $temp);

				/**
					Search geolocation.

					Search on GEOLOCATION_I18N.VALUE
				**/
				$temp = Array();
				foreach($searchExpression as $searchTerm)
				{
					$temp[$searchTerm] = Array();

					$query = "	SELECT distinct file.id
								FROM file, groupe, geolocation, geolocation_i18n
								WHERE groupe.id = file.groupe_id
								AND file.id = geolocation.object_id
								AND geolocation.id = geolocation_i18n.id
								AND file.state IN (".implode($states, ",").")
								AND groupe.customer_id = ".$customerId."
								AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
								AND geolocation.object_type = ".GeolocationPeer::__TYPE_FILE."
								AND geolocation_i18n.culture = ".$culture."
								AND geolocation_i18n.value LIKE '".$searchTerm."'";

					$statement = $connection->query($query);
					$statement->setFetchMode(PDO::FETCH_ASSOC);

					while($rs = $statement->fetch())
					{
						if(!in_array($rs["id"], $temp[$searchTerm]))
							$temp[$searchTerm][] = $rs["id"];
					}

					$statement->closeCursor();
					$statement = null;
				}

				$idsGeolocations = processFileIndex($searchOperator, $temp);

				$ids = array_merge($ids, $idsTags, $idsFiles, $idsFields, $idsPersons, $idsGeolocations);

				if(empty($ids))
					$ids[] = -1;
			}

			if(!empty($limitationsArray) && !empty($ids))
			{
				$temp = $ids;
				$ids = Array();

				$query = "	SELECT distinct file.id
							FROM file, file_right
							WHERE file.id = file_right.object_id
							AND file_right.type = 3
							AND file.id IN (".implode($temp, ",").")";

				foreach($limitationsArray as $limitation_id => $limitation_value)
				{
					$query .= " AND (file_right.usage_limitation_id = ".$limitation_id." AND ";

					switch($limitation_id)
					{
						case UsageLimitationPeer::__INTERNAL:
						case UsageLimitationPeer::__TIME_LIMIT:
						case UsageLimitationPeer::__NB_VIEWS:
						case UsageLimitationPeer::__NB_PRINTS:
							$query .= "file_right.value = ".$limitation_value;
						break;

						case UsageLimitationPeer::__GEO_LIMIT:
						case UsageLimitationPeer::__SUPPORT:
							$temp = explode(";", $limitation_value);

							foreach($temp as $value)
							{
								if(!empty($value))
									$query .= "(file_right.value LIKE \"".$value.";%\" OR file_right.value LIKE \"%;".$value.";%\" OR file_right.value LIKE \"%;".$value."\") OR ";
							}

							$query = substr($sql, 0, -3);
						break;
					}

					$query .= ") AND ";
				}

				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_ASSOC);

				while($rs = $statement->fetch())
				{
					if(!in_array($rs["id"], $ids))
						$ids[] = $rs["id"];
				}

				$statement->closeCursor();
				$statement = null;
			}

			if(empty($groupId))
			{
				$groups_ = UserGroupPeer::getGroupIds($currentUserId, "", true);
				$groups2 = GroupePeer::getGroupsInArray2($currentUserId);

				foreach($groups_ as $group)
					$groups[] = $group;

				foreach($groups2 as $group)
					$groups[] = $group->getId();
			}
			else
				$groups[] = $groupId;

			if(!empty($dates) && $dates[0] && $dates[1] && !empty($ids))
			{
				$from = mktime(0,0,0,1,1,$dates[0]);
				$to = mktime(23,59,59,12,31,$dates[1]);

				$tempIds = $ids;
				$ids = Array();

				$query = "SELECT DISTINCT exif.file_id, exif.value
						FROM exif, file, groupe, customer
						WHERE exif.file_id = file.id
						AND file.groupe_id = groupe.id
						AND groupe.customer_id = customer.id
						AND customer.state = ".CustomerPeer::__STATE_ACTIVE."
						AND groupe.customer_id = ".$customerId."
						AND file.state IN (".implode($states, ",").")
						AND file.id IN (".implode($tempIds, ",").")
						AND exif.title = 'DateTimeOriginal'";

				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_NUM); 

				while ($rs = $statement->fetch())
				{
					$temp = explode(" ", $rs[1]);

					if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
					{
						$date = explode('/', $temp[0]);

						if(mktime(0,0,0,$date[1],$date[0],$date[2]) >= $from && mktime(0,0,0,$date[1],$date[0],$date[2]) <= $to)
						{
							if(!in_array($rs[0], $ids))
								$ids[] = $rs[0];
						}
					}
					else
					{
						if(!in_array($rs[0], $ids))
							$ids[] = $rs[0];
					}
				}

				$statement->closeCursor();
				$statement = null;

				$query = "	SELECT DISTINCT iptc.file_id, iptc.value
							FROM iptc, file, groupe, customer
							WHERE iptc.file_id = file.id
							AND file.groupe_id = groupe.id
							AND groupe.customer_id = customer.id
							AND customer.state = ".CustomerPeer::__STATE_ACTIVE."
							AND groupe.customer_id = ".$customerId."
							AND file.state IN (".implode($states, ",").")
							AND file.id IN (".implode($tempIds, ",").")
							AND iptc.title = 'Date Created'";

				$statement = $connection->query($query);
				$statement->setFetchMode(PDO::FETCH_NUM); 

				while ($rs = $statement->fetch())
				{
					$temp = explode(" ", $rs[1]);

					if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
					{
						$date = explode('/', $temp[0]);

						if(mktime(0,0,0,$date[1],$date[0],$date[2]) >= $from && mktime(0,0,0,$date[1],$date[0],$date[2]) <= $to)
						{
							if(!in_array($rs[0], $ids))
								$ids[] = $rs[0];
						}
					}
					else
					{
						if(!in_array($rs[0], $ids))
							$ids[] = $rs[0];
					}
				}

				$statement->closeCursor();
				$statement = null;

				if(empty($ids))
					$ids[] = -1;
			}

			$select = "	SELECT distinct file.*";
			$count = "	SELECT count(distinct file.id) as count";
			$extended = "	SELECT distinct file.extention, file.type, count(file.id) as count";
			$date = "	SELECT UNIX_TIMESTAMP(min(file.created_at)) as min, UNIX_TIMESTAMP(max(file.created_at)) as max";
			$licenceQ = "	SELECT distinct file.licence_id, count(file.id) as count";
			$useQ = "	SELECT distinct file.usage_use_id, count(file.id) as count";
			$distributionQ = "	SELECT distinct file.usage_distribution_id, count(file.id) as count";

			$query = "	FROM file, groupe
						WHERE file.groupe_id = groupe.id
						AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
						AND file.state IN (".implode($states, ",").")
						AND groupe.customer_id = ".$customerId."
						AND groupe.id IN (".implode($groups, ",").")";

			if(!empty($ids))
				$query .= " AND file.id IN (".implode($ids, ",").")";

			if(!empty($fileType))
				$query .= " AND file.extention = ".$connection->quote($fileType);

			if(!empty($userId))
				$query .= " AND file.user_id = ".$connection->quote($userId);
			elseif(!empty($authorId))
				$query .= " AND file.user_id = ".$connection->quote($currentUserId);

			if(!empty($locations) && $locations[0] && $locations[1])
				$query .= " AND file.lat = ".$connection->quote($locations[0])." AND file.lng = ".$connection->quote($locations[1]);

			if(!empty($years) && $years[0] && $years[1])
				$query .= " AND file.created_at >= '".$years[0]."-01-01' AND file.created_at <= '".$years[1]."-12-31'";

			if(!empty($folderId))
				$query .= " AND file.folder_id = ".$connection->quote($folderId);

			if(!empty($licence))
				$query .= " AND (file.licence_id NOT IN (".implode($licence, ", ").") OR file.licence_id IS NULL)";

			if(!empty($distribution))
				$query .= " AND (file.usage_distribution_id NOT IN (".implode($distribution, ", ").") OR file.usage_distribution_id IS NULL)";

			if(!empty($use))
				$query .= " AND (file.usage_use_id NOT IN (".implode($use, ", ").") OR file.usage_use_id IS NULL)";

			if(!empty($creative_commons) && $creative_commons > -1)
				$query .= " AND file.creative_commons_id = ".$connection->quote($creative_commons);

			if(!empty($stars))
				$query .= " AND file.average_point = ".$connection->quote($stars);

			if(!empty($orientation))
			{
				$query .= " AND (";
				$temp = Array();

				foreach($orientation as $or)
				{
					switch($or)
					{
						case 'square': 
							$temp[] = "file.width = file.height";
						break;
						
						case 'landscape':
							$temp[] = "file.width > file.height";
						break;

						case 'portrait':
							$temp[] = "file.width < file.height";
						break;
					}
				}

				$query .= implode($temp, " OR ").")";
			}

			if(!empty($sizes))
			{
				$query .= " AND (";
				$temp = Array();

				foreach($sizes as $si)
				{
					switch($si)
					{
						case '-5': 
							$temp[] = "(file.size < 5242880)";
						break;
						
						case '5':
							$temp[] = "(file.size >= 5242880 AND file.size < 26214400)";
						break;

						case '25':
							$temp[] = "(file.size >= 26214400 AND file.size < 52428800)";
						break;

						case '50':
							$temp[] = "(file.size >= 52428800 AND file.size < 104857600)";
						break;

						case '100':
							$temp[] = "(file.size >= 104857600 AND file.size < 262144000)";
						break;

						case '250':
							$temp[] = "(file.size >= 262144000)";
						break;
					}
				}

				$query .= implode($temp, " OR ").")";
			}

			if(!empty($uploadedDates))
			{
				$min = null;
				$max = null;

				if(!empty($uploadedDates["min"]))
				{
					$temp = explode("/", $uploadedDates["min"]);
					$min = $temp[2]."-".$temp[1]."-".$temp[0]." 00:00:00";
				}

				if(!empty($uploadedDates["max"]))
				{
					$temp = explode("/", $uploadedDates["max"]);
					$max = $temp[2]."-".$temp[1]."-".$temp[0]." 23:59:59";
				}

				if(!empty($min) && !empty($max))
					$query .= " AND file.created_at >= '".$min."' AND file.created_at <= '".$max."'";
				elseif(!empty($min))
					$query .= " AND file.created_at >= '".$min."'";
				elseif(empty($max))
					$query .= " AND file.created_at <= '".$max."'";
			}

			if(!empty($exclude_extentions))
				$query .= " AND file.extention NOT IN ('".implode($exclude_extentions, "','")."')";

			$statement = $connection->query($date.$query);
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$date = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($extended.$query." GROUP BY file.extention, file.type ORDER BY file.type ASC");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$extended = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($licenceQ.$query." AND file.licence_id IS NOT NULL GROUP BY file.licence_id");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$licenceR = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($useQ.$query." AND file.usage_use_id IS NOT NULL GROUP BY file.usage_use_id");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$useR = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($distributionQ.$query." AND file.usage_distribution_id IS NOT NULL GROUP BY file.usage_distribution_id");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$distributionR = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query);
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$result = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.width = file.height");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$square = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.width > file.height");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$landscape = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.width < file.height");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$portrait = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.size < 5242880");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$critSize1 = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.size >= 5242880 AND file.size < 26214400");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$critSize2 = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.size >= 26214400 AND file.size < 52428800");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$critSize3 = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.size >= 52428800 AND file.size < 104857600");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$critSize4 = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.size >= 104857600 AND file.size < 262144000");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$critSize5 = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($count.$query." AND file.size >= 262144000");
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$critSize6 = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			if(!empty($include_types))
			{
				$types = Array();

				if(in_array("pictures", $include_types))
					$types[] =  self::__TYPE_PHOTO;

				if(in_array("audios", $include_types))
					$types[] =  self::__TYPE_AUDIO;

				if(in_array("videos", $include_types))
					$types[] =  self::__TYPE_VIDEO;

				if(in_array("documents", $include_types))
					$types[] =  self::__TYPE_DOCUMENT;

				if (!$types) {
					$types[] = -1;
				}

				$query .= " AND file.type IN (".implode(",", $types).")";
			}

			switch ($sort)
			{
				default: ;
				case "name_asc":
					$query .= " ORDER BY file.name, file.original ASC";
				break;

				case "name_desc":
					$query .= " ORDER BY file.name, file.original DESC";
				break;

				case "date_asc":
					$query .= " ORDER BY file.created_at ASC";
				break;

				case "date_desc": 
					$query .= " ORDER BY file.created_at DESC";
				break;

				case "rate_asc":
					$query .= " ORDER BY file.average_point ASC";
				break;

				case "rate_desc":
					$query .= " ORDER BY file.average_point DESC";
				break;
			}

			if(!empty($limit) && empty($offset))
				$query .= " LIMIT 0, ".$limit;
			elseif(!empty($offset) && !empty($limit))
				$query .= " LIMIT ".$offset.", ".$limit;

			$statement = $connection->query($select.$query);
			$statement->setFetchMode(PDO::FETCH_NUM);

			while($rs = $statement->fetch())
			{
				$file = new File();
				$file->hydrate($rs);
				$files[] = serialize($file);
			}

			return Array("files" => array_map("unserialize", $files), "count" => $result[0]["count"], 
					"extended" => $extended, "orientation" => Array("square" => $square[0]["count"], 
					"landscape" => $landscape[0]["count"], "portrait" => $portrait[0]["count"]), 
					"size" => Array("-5" => $critSize1[0]["count"], "5" => $critSize2[0]["count"], 
					"25" => $critSize3[0]["count"], "50" => $critSize4[0]["count"], "100" => $critSize5[0]["count"], 
					"250" => $critSize6[0]["count"]), "date" => Array("min" => $date[0]["min"], 
					"max" => $date[0]["max"]), "licence" => $licenceR, "use" => $useR, 
					"distribution" => $distributionR);
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getHomeFiles($limit, $offset, $sort = null)
	{
		switch($sort) {
			case "name_asc": $sort = "file.name ASC, file.original ASC"; break;
			case "name_desc": $sort = "file.name DESC, file.original DESC"; break;
			case "uploaded_asc": $sort = "file.created_at ASC"; break;
			case "uploaded_desc": $sort = "file.created_at DESC"; break;
		}

		$connection = Propel::getConnection();

		$query = "	SELECT file.*
					FROM file, groupe, customer
					WHERE file.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

		$count = "	SELECT count(file.id) as count
					FROM file, groupe, customer
					WHERE file.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND file.state = ".$connection->quote(self::__STATE_VALIDATE)."
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			$query .= " AND file.folder_id NOT IN (SELECT user_folder.folder_id
													FROM user_folder
													WHERE user_folder.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")
						AND ((groupe.free_credential IS NOT NULL) OR (groupe.free_credential IS NULL AND file.groupe_id IN (SELECT user_group.groupe_id
													FROM user_group
													WHERE user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")))";

			$count .= " AND file.folder_id NOT IN (SELECT user_folder.folder_id
													FROM user_folder
													WHERE user_folder.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")
						AND ((groupe.free_credential IS NOT NULL) OR (groupe.free_credential IS NULL AND file.groupe_id IN (SELECT user_group.groupe_id
													FROM user_group
													WHERE user_group.user_id = ".$connection->quote(sfContext::getInstance()->getUser()->getId()).")))";
		}

		if(!empty($sort)) {
			$query .= " ORDER BY ".$sort;
		}

		$query .= " LIMIT ".$offset.",".$limit;

		$statement = $connection->query($count);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$files = array();
		while ($rs = $statement->fetch())
		{
			$file = new File();
			$file->hydrate($rs);
			$files[] = serialize($file);
		}

		$statement->closeCursor();
		$statement = null;

		return Array("files" => array_map("unserialize", $files), "count" => $result[0]["count"]);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFilesInFolder($folder_id, $tag_ids = Array(), $user_id = 0, $author_id = 0, 
			$dates = Array(), $shooting = Array(), $sizes = Array(), $sort = "date_desc", 
			$states = Array(FilePeer::__STATE_VALIDATE), $limit = null, $offset = null)
	{
		$connection = Propel::getConnection();
		$files = Array();

		$select = "	SELECT distinct file.*";
		$count = "	SELECT count(file.id) as count";
		$from = "	FROM file, folder, groupe";
		$where = "	WHERE file.folder_id = folder.id
					AND folder.groupe_id = groupe.id";

		if(!empty($tag_ids))
		{
			$from .= ", file_tag";
			$where .= "	AND file.id = file_tag.file_id
						AND file_tag.type = ".FileTagPeer::__TYPE_FILE."
						AND file_tag.tag_id IN (".implode(",", $tag_ids).")";
		}

		$where .= "	AND file.state IN (".implode(",", $states).")
					AND folder.state = ".FolderPeer::__STATE_ACTIVE."
					AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
					AND folder.id = ".$folder_id;

		if($author_id)
			$where .= "	AND file.user_id = ".sfContext::getInstance()->getUser()->getId();
		elseif($user_id)
			$where .= "	AND file.user_id = ".$user_id;

		if(sizeof($dates) && $dates["min"] && $dates["max"])
		{
			$min = explode("/", $dates["min"]);
			$max = explode("/", $dates["max"]);

			$where .= "	AND file.created_at >= \"".$min[2]."-".$min[1]."-".$min[0]." 00:00:00\"
						AND file.created_at <= \"".$max[2]."-".$max[1]."-".$max[0]." 23:59:59\"";
		}

		if(sizeof($sizes) && $sizes["min"] && $sizes["max"])
		{
			$where .= "	AND file.size >= ".$sizes["min"]."
						AND file.size <= ".$sizes["max"];
		}

		switch ($sort)
		{
			default: ;
			case "name_asc": $where .= "	ORDER BY file.name ASC, file.original ASC"; break;
			case "name_desc": $where .= "	ORDER BY file.name DESC, file.original DESC"; break;
			case "activity_asc":
			case "creation_asc":
			case "date_asc": $where .= "	ORDER BY file.created_at ASC"; break;
			case "activity_desc":
			case "creation_desc":
			case "date_desc": $where .= "	ORDER BY file.created_at DESC"; break;
		}

		$statement = $connection->query($count.$from.$where);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(!empty($offset) || !empty($limit))
			$where .= "	LIMIT ".$offset.", ".$limit;

		$statement = $connection->query($select.$from.$where);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		while($rs = $statement->fetch())
		{
			$file = new File();
			$file->hydrate($rs);
			$files[] = serialize($file);
		}

		$statement->closeCursor();
		$statement = null;

		return Array("files" => array_map("unserialize", $files), "count" => $result[0]["count"]);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getDateRange($group_id, $folder_id)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT UNIX_TIMESTAMP(min(file.created_at)) as min, UNIX_TIMESTAMP(max(file.created_at)) as max
					FROM file
					WHERE file.folder_id = ".$folder_id."
					AND file.state = ".self::__STATE_VALIDATE;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$folder = FolderPeer::getDateRange($group_id, $folder_id);
		$file = $result[0];

		return Array("min" => ($file["min"] < $folder["min"] ? $file["min"] : $folder["min"]), 
				"max" => ($file["max"] > $folder["max"] ? $file["max"] : $folder["max"]));
	}

	/*________________________________________________________________________________________________________________*/
	public static function getShootingDateRange($folder_id)
	{
		$connection = Propel::getConnection();
		$dates = array();

		$query = "SELECT DISTINCT exif.value
				FROM exif, file
				WHERE exif.file_id = file.id
				AND file.state = ".self::__STATE_VALIDATE."
				AND file.folder_id = ".$folder_id."
				AND exif.title = 'DateTimeOriginal'";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		while ($rs = $statement->fetch())
		{
			$temp = explode(" ", $rs[0]);

			if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
			{
				$date = explode('/', $temp[0]);
				$dates[] = mktime(0,0,0,$date[1],$date[0],$date[2]);
			}
		}

		$statement->closeCursor();
		$statement = null;

		$query = "SELECT DISTINCT iptc.value
				FROM iptc, file
				WHERE iptc.file_id = file.id
				AND file.state = ".self::__STATE_VALIDATE."
				AND file.folder_id = ".$folder_id."
				AND iptc.title = 'Date Created'";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		while ($rs = $statement->fetch())
		{
			$temp = explode(" ", $rs[0]);

			if(preg_match('/^[0-9]{2,2}\/[0-9]{2,2}\/[0-9]{4,4}$/', $temp[0]))
			{
				$years = explode('/', $temp[0]);
				$dates[] = mktime(0,0,0,$date[1],$date[0],$date[2]);
			}
		}

		$statement->closeCursor();
		$statement = null;

		$dates = array_unique($dates);

		sort($dates);

		if(!empty($dates))
			return Array("min" => $dates[0], "max" => mktime(23,59,59,date("m",$dates[count($dates) - 1]),
					date("d",$dates[count($dates) - 1]),date("Y",$dates[count($dates) - 1])));

		return Array("min" => "", "max" => "");
	}

	/*________________________________________________________________________________________________________________*/
	public static function getSizeRange($folder_id)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT min(file.size) as min, max(file.size) as max
					FROM file
					WHERE file.folder_id = ".$folder_id."
					AND file.state = ".self::__STATE_VALIDATE;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return Array("min" => $result[0]["min"], "max" => $result[0]["max"]);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see getFirstFileOfAlbum
	 */
	public static function getFirstFileOfGroupe($group_id)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT file.*
					FROM file
					WHERE file.groupe_id = ".$group_id."
					AND file.state = ".FilePeer::__STATE_VALIDATE."
					ORDER BY file.created_at ASC
					LIMIT 0, 1";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if(!empty($result) && !empty($result[0]))
		{
			$file = new File();
			$file->hydrate($result[0]);

			return $file;
		}

		return null;
	}

	/*________________________________________________________________________________________________________________*/
	public static function countSizeFiles($folderId, &$count = 0)
	{
		$folder = FolderPeer::retrieveByPk($folderId);

		if($folder->getState() == FolderPeer::__STATE_ACTIVE)
		{
			$c = new Criteria();
			$c->clearSelectColumns();
			$c->addSelectColumn("sum(".self::SIZE.") as total");
			$c->addJoin(FolderPeer::ID, FilePeer::FOLDER_ID);
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
			$c->add(self::FOLDER_ID, $folder->getId());
			$c->add(self::STATE, self::__STATE_VALIDATE);

			$statement = self::doSelectStmt($c);
			$result = $statement->fetch(PDO::FETCH_ASSOC);

			$count += $result["total"];

			$c = new Criteria();
			$c->add(FolderPeer::SUBFOLDER_ID, $folder->getId());
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

			$folders = FolderPeer::doSelect($c);

			foreach($folders as $folder)
				self::countSizeFiles($folder->getId(), $count);
		}

		return $count;
	}
}
