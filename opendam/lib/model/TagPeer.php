<?php

/**
 * Subclass for performing query and update operations on the 'tag' table.
 *
 * 
 *
 * @package lib.model
 */ 
class TagPeer extends BaseTagPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function doCriteria(array $params = array(), array $orderBy = array(), $limit = null)
	{
		$keyword = isset($params["keyword"]) ? $params["keyword"] : "";
		$letter = isset($params["letter"]) ? $params["letter"] : "";
		$customerId = isset($params["customerId"]) ? (int)$params["customerId"] : 0;

		$criteria = new Criteria();

		if ($customerId) {
			$criteria->add(self::CUSTOMER_ID, $customerId);
		}

		if ($letter) {
			$criteria->add(self::TITLE, $letter.'%', Criteria::LIKE);
		}
	
		if ($keyword) {
			$criteria->add(self::TITLE, "%".$keyword."%", Criteria::LIKE);
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
	
		$pager = new sfPropelPager("Tag", $itemPerPage);
		
		$pager->setCriteria(self::doCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getFirstLettersOfName($customerId = null)
	{
		$connection = Propel::getConnection();
	
		$query = "SELECT DISTINCT UPPER(substr( title, 1, 1 )) AS letter
					FROM ".self::TABLE_NAME.
					" WHERE 1";

		if ($customerId) {
			$query .= " AND ".self::CUSTOMER_ID." = ".(int)$customerId;
		}
		
		$query .= " ORDER BY `letter` ASC";

		//connection
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		$l = array();
	
		for ($i = 0; $i < count($rs); $i++) {
			$l[] = $rs[$i]["letter"];
		}
	
		return $l;
	}

	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 */
	public static function fetchByKeyword($keyword, $limit=1000)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
		$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
		$c = new Criteria();
		
		$c->add(self::TITLE, '%'.$keyword.'%', Criteria::LIKE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->setLimit($limit);
		
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	# tag/observeSuccess
	/**
	 * @deprecated
	 */
	public static function fetchByTopKeyword($keyword, $limit=1000)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
		$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
	
		$c = new Criteria();
	
		$c->add(self::TITLE, '%'.strtolower($keyword).'%', Criteria::LIKE);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		//$c->add('LENGTH('.self::TITLE.') > 3', 3, Criteria::GREATER_THAN);
		$c->setLimit($limit);
		$tags = self::doSelect($c);
		
		$tags_array = array();
		
		foreach ($tags as $tag){
			$tags_array[] = $tag;
		}
		
		return $tags_array;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByTitle($title)
	{
		$c = new Criteria();
		
		$c->add(self::TITLE, $title);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		
		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see self::getPager
	 */
	public static function getTagPager($keyword="", $sort="date_asc", $page=1, $letter, $group_id = null)
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

		if ($letter != '') {
			$c->add(self::TITLE, $letter."%", Criteria::LIKE);
		}

		if (!empty($group_id)) {
			if ($group_id != "all") {
				// $c->addJoin(self::ID, FileTagPeer::TAG_ID);
				// $c->add(FileTagPeer::FILE_ID, $group_id);
				// $c->add(FileTagPeer::TYPE, FileTagPeer::__TYPE_GROUP);

				$tags_id = Array();

				$group = new Criteria();
				
				$group->add(FileTagPeer::FILE_ID, $group_id);
				$group->add(FileTagPeer::TYPE, FileTagPeer::__TYPE_GROUP);

				$group_tags = FileTagPeer::doSelect($group);

				foreach ($group_tags as $group_tag) {
					if (!in_array($group_tag->getTagId(), $tags_id)) {
						$tags_id[] = $group_tag->getTagId();
					}
				}

				$folder = new Criteria();
				
				$folder->addJoin(FolderPeer::ID, FileTagPeer::FILE_ID);
				$folder->add(FolderPeer::GROUPE_ID, $group_id);
				$folder->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
				$folder->add(FileTagPeer::TYPE, FileTagPeer::__TYPE_FOLDER);

				$folder_tags = FileTagPeer::doSelect($folder);

				foreach ($folder_tags as $folder_tag) {
					if (!in_array($folder_tag->getTagId(), $tags_id)) {
						$tags_id[] = $folder_tag->getTagId();
					}
				}

				$file = new Criteria();
				
				$file->addJoin(FilePeer::ID, FileTagPeer::FILE_ID);
				$file->add(FilePeer::GROUPE_ID, $group_id);
				$file->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
				$file->add(FileTagPeer::TYPE, FileTagPeer::__TYPE_FILE);

				$file_tags = FileTagPeer::doSelect($file);

				foreach ($file_tags as $file_tag) {
					if (!in_array($file_tag->getTagId(), $tags_id)) {
						$tags_id[] = $file_tag->getTagId();
					}
				}
	
				$c->add(self::ID, $tags_id, Criteria::IN);
			}
		}

		switch ($sort) {
			default: ;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
			case "name_asc": $c->addAscendingOrderByColumn(self::TITLE); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::TITLE); break;
		}
		
		$pager = new sfPropelPager('Tag', 50);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
		
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see self::$connection->quote(s
	 */
	public static function getLetters()
	{
		$connection = Propel::getConnection();
	
		$query = "SELECT DISTINCT UPPER(substr( title, 1, 1 )) AS letter
					FROM `tag`, customer
					WHERE tag.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND tag.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
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
	# tag/tags
	/**
	 * @deprecated
	 */
	public static function getTagsIn($keyword="", $type=0, $file_ids_tag=array())
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
	
		if ($keyword && $keyword != __("filter tags ...") && $keyword != __("Filter tags ...")) {
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
			$c->add(self::TITLE, $keyword."%", Criteria::LIKE);
		}
	
		if ($type) {
			 $c->add(FileTagPeer::TYPE, $type);
		}
		
		if (sizeof($file_ids_tag)) {
			$c->add(FileTagPeer::FILE_ID, $file_ids_tag, Criteria::IN);
			$c->addJoin(FileTagPeer::TAG_ID, self::ID);
		}
		
		$c->setDistinct();
		
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	# tag/tags
	public static function getHomeTags($keyword="")
	{
		$c = new Criteria();
		
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		sfContext::getInstance()->getConfiguration()->loadHelpers(array("I18N", "Global"));
	
		if ($keyword && $keyword != __("filter tags ...") && $keyword != __("Filter tags ...")) {
			$keyword = htmlentities(replaceAccentedCharacters($keyword), ENT_QUOTES);
			$c->add(self::TITLE, $keyword."%", Criteria::LIKE);
		}
	
		$c->addJoin(FileTagPeer::TAG_ID, self::ID);
		$c->setDistinct();
		
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountHomeTags()
	{
		$connection = Propel::getConnection();
	
		$query = "SELECT distinct tag.id, count(file_tag.id)
				FROM tag, file_tag, customer
				WHERE tag.id = file_tag.tag_id
				AND tag.customer_id = customer.id
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND tag.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
				GROUP BY tag.id";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		$tags = Array();
	
		while($rs = $statement->fetch()) {
			$tags[$rs[0]] = $rs[1];
		}
		
		$statement->closeCursor();
		$statement = null;
	
		return $tags;
	}

	/*________________________________________________________________________________________________________________*/
	// TODO: dynamic years array need?
	public static function getYears()
	{
		$connection = Propel::getConnection();
	
		//preparing file query
		$query = "SELECT 
			MAX(".FilePeer::CREATED_AT.") as file_max,
			MIN(".FilePeer::CREATED_AT.") as file_min
			FROM ".FilePeer::TABLE_NAME.", groupe, customer
			WHERE file.groupe_id = groupe.id
				AND groupe.customer_id = customer.id
				AND file.state = ".$connection->quote(FilePeer::__STATE_VALIDATE)."
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
				AND ".FilePeer::CREATED_AT."<>'' AND ".FilePeer::CREATED_AT."<>'' AND ".FilePeer::CREATED_AT."<>'0000-00-00' AND ".FilePeer::CREATED_AT."<>'0000-00-00';";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		//preparing folder query
		$query = "SELECT 
			MAX(".FolderPeer::CREATED_AT.") as folder_max,
			MIN(".FolderPeer::CREATED_AT.") as folder_min
			FROM ".FolderPeer::TABLE_NAME.", groupe, customer
			WHERE folder.groupe_id = groupe.id
				AND groupe.customer_id = customer.id
				AND folder.state = ".$connection->quote(FolderPeer::__STATE_ACTIVE)."
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
				AND ".FolderPeer::CREATED_AT."<>'' AND ".FolderPeer::CREATED_AT."<>'' AND ".FolderPeer::CREATED_AT."<>'0000-00-00' AND ".FolderPeer::CREATED_AT."<>'0000-00-00';";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs1 = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;
	
		if (count($rs) > 0 && count($rs1) > 0) {
			$max = (int) max($rs1[0]["folder_max"], $rs[0]["file_max"]); 
			$min = (int) min($rs1[0]["folder_min"], $rs[0]["file_min"]);
			
			$years = array();
			
			for ($i=$min; $i<=$max; $i++){
				$years[$i] = $i;
			}
	
			return $years;
	
		} 
		else {
			return array();
		}
	}

	/*________________________________________________________________________________________________________________*/
	// TODO: dynamic sizes array need?
	public static function getSizes()
	{
		$connection = Propel::getConnection();
		
		//preparing file query
		$query = "SELECT 
			MAX(".FilePeer::CREATED_AT.") as file_max,
			MIN(".FilePeer::CREATED_AT.") as file_min
			FROM ".FilePeer::TABLE_NAME.", groupe, customer
			WHERE file.groupe_id = groupe.id
				AND groupe.customer_id = customer.id
				AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
				AND file.state = ".$connection->quote(FilePeer::__STATE_VALIDATE)."
				AND groupe.customer_id = ".$connection->quote(sfContext::getInstance()->getUser()->getCustomerId())."
				AND ".FilePeer::CREATED_AT."<>'' AND ".FilePeer::CREATED_AT."<>'' AND ".FilePeer::CREATED_AT."<>'0000-00-00' AND ".FilePeer::CREATED_AT."<>'0000-00-00';";
		
			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;
	
		if (count($rs) > 0){
			$sizes = array();
			
			for ($i=$rs[0]["file_min"]; $i<=$rs[0]["file_max"]; $i+=100){
				$sizes[$i] = $i;
			}
			
			return $sizes;
		}
		else {
			return array();
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getTagPagerHome($page=1, $per_page=100)
	{
		$c = new Criteria();
		
		$tags = FileTagPeer::doSelect($c);
		$listTags = array();
		$list = array();
	
		foreach($tags as $tag) {
			switch($tag->getType()) {
				case '1':
					$group = GroupePeer::retrieveByPk($tag->getFileId());
					if(!empty($group)) {
						if($group->getUserId() == sfContext::getInstance()->getUser()->getId()) {
							if(!array_key_exists($tag->getTagId(), $listTags))
								$listTags[$tag->getTagId()] = 1;
							else
								$listTags[$tag->getTagId()] += 1;
						}
					}
				break;
				case '2':
					$folder = FolderPeer::retrieveByPk($tag->getFileId());
					if(!empty($folder)) {
						if($folder->getUserId() == sfContext::getInstance()->getUser()->getId()) {
							if(!array_key_exists($tag->getTagId(), $listTags))
								$listTags[$tag->getTagId()] = 1;
							else
								$listTags[$tag->getTagId()] += 1;
						}
					}
				break;
				case '3':
					$file = FilePeer::retrieveByPk($tag->getFileId());
					if(!empty($file)) {
						if($file->getUserId() == sfContext::getInstance()->getUser()->getId()) {
							if(!array_key_exists($tag->getTagId(), $listTags))
								$listTags[$tag->getTagId()] = 1;
							else
								$listTags[$tag->getTagId()] += 1;
						}
					}
				break;
			}
		}
	
		arsort($listTags);
	
		foreach ($listTags as $key=>$value) {
			$list[] = $key;
		}
	
		$c = new Criteria();
		
		$c->add(self::ID, $list, Criteria::IN);
		$c->add(self::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(self::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::TITLE);
		
		$pager = new sfPropelPager('Tag', $per_page);
		
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
		
		return $pager;
	}

	

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByPKNoCustomer($pk, PropelPDO $con = null)
	{
		return parent::retrieveByPK($pk, $con);
	}
}