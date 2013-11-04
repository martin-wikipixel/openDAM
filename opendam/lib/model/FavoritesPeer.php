<?php

/**
 * Subclass for performing query and update operations on the 'favorites' table.
 *
 * 
 *
 * @package lib.model
 */ 
class FavoritesPeer extends BaseFavoritesPeer
{
	const __TYPE_FOLDER = 1;
	const __TYPE_FILE = 2;
	const __TYPE_GROUP = 3;

	/*________________________________________________________________________________________________________________*/
	public static function getFavorite($object_id, $object_type, $user_id)
	{
		$c = new Criteria();
		$c->add(self::OBJECT_ID, $object_id);
		$c->add(self::OBJECT_TYPE, $object_type);

		switch($object_type)
		{
			case self::__TYPE_FOLDER:
			{
				$c->addJoin(FolderPeer::ID, self::OBJECT_ID);
				$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
				$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			}
			break;

			case self::__TYPE_FILE:
			{
				$c->addJoin(FilePeer::ID, self::OBJECT_ID);
				$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
				$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			}
			break;

			case self::__TYPE_GROUP:
			{
				$c->addJoin(GroupePeer::ID, self::OBJECT_ID);
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			}
		}

		
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);


		$c->add(self::USER_ID, $user_id);
		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function deleteFavorite($object_id, $object_type, $user_id)
	{
		$c = new Criteria();

		switch($object_type)
		{
			case self::__TYPE_FOLDER:
			{
				$c->addJoin(FolderPeer::ID, self::OBJECT_ID);
				$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
				$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
			}
			break;

			case self::__TYPE_FILE:
			{
				$c->addJoin(FilePeer::ID, self::OBJECT_ID);
				$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
				$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			}
			break;

			case self::__TYPE_GROUP:
			{
				$c->addJoin(GroupePeer::ID, self::OBJECT_ID);
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			}
			break;
		}

		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);

		if(self::doCount($c) > 0)
		{
			$c = new Criteria();
			$c->add(self::OBJECT_ID, $object_id);
			$c->add(self::OBJECT_TYPE, $object_type);
			$c->add(self::USER_ID, $user_id);

			return self::doDelete($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFavoritePager($user_id, $object_type=0, $sort="name_asc", $page=1, $per_page=100)
	{
		$c = new Criteria();
		$c->add(self::USER_ID, $user_id);
		$c->setDistinct();

		if($object_type)
		{
			$c->add(self::OBJECT_TYPE, $object_type);

			switch($object_type)
			{
				case self::__TYPE_FOLDER:
				{
					$c->addJoin(FolderPeer::ID, self::OBJECT_ID);
					$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
					$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
					$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
					$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
					$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
					$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				}
				break;

				case self::__TYPE_FILE:
				{
					$c->addJoin(FilePeer::ID, self::OBJECT_ID);
					$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
					$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
					$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
					$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
					$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
					$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				}
				break;

				case self::__TYPE_GROUP:
				{
					$c->addJoin(GroupePeer::ID, self::OBJECT_ID);
					$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
					$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
					$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
					$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				}
				break;
			}
		}
    
		switch ($sort)
		{
			default: 
			case "name_asc": $c->addAscendingOrderByColumn(self::OBJECT_ID); break;
			case "name_desc": $c->addDescendingOrderByColumn(self::OBJECT_ID); break;
			case "date_asc": $c->addAscendingOrderByColumn(self::CREATED_AT); break;
			case "date_desc": $c->addDescendingOrderByColumn(self::CREATED_AT); break;
		}

		if(!$object_type)
		{
			$favorites_array = Array();
			$favorites = self::doSelect($c);

			foreach($favorites as $favorite)
			{
				$c = new Criteria();
				$c->add(self::ID, $favorite->getId());

				switch($favorite->getObjectType())
				{
					case self::__TYPE_FOLDER:
					{
						$c->addJoin(FolderPeer::ID, self::OBJECT_ID);
						$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
						$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
						$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
						$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
						$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
						$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
					}
					break;

					case self::__TYPE_FILE:
					{
						$c->addJoin(FilePeer::ID, self::OBJECT_ID);
						$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
						$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
						$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
						$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
						$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
						$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
					}
					break;

					case self::__TYPE_GROUP:
					{
						$c->addJoin(GroupePeer::ID, self::OBJECT_ID);
						$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
						$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
						$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
						$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
					}
					break;
				}

				if(self::doCount($c) > 0)
					$favorites_array[] = $favorite->getId();
			}

			$c = new Criteria();
			$c->add(self::ID, $favorites_array, Criteria::IN);
		}

		$pager = new sfPropelPager('Favorites', $per_page);
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();

		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByObject($object_type, $object_id)
	{
		$c = new Criteria();
		$c->add(self::OBJECT_ID, $object_id);
		$c->add(self::OBJECT_TYPE, $object_type);

		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function getCountMax()
	{
		$connection = Propel::getConnection();

		$query = "	SELECT count(favorites.id)
					FROM favorites";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $rs[0][0];
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFavorites($types = Array(), $user_id, $limit, $offset)
	{
		$connection = Propel::getConnection();

		$favorites = Array();
		$c = 0;

		if(in_array(self::__TYPE_GROUP, $types))
		{
			$query = "	SELECT distinct favorites.*
						FROM favorites, groupe
						WHERE favorites.object_id = groupe.id
						AND favorites.user_id = ".$user_id."
						AND favorites.object_type = ".self::__TYPE_GROUP."
						AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
						ORDER BY favorites.object_id";

			$count = "	SELECT count(distinct favorites.id) as count
						FROM favorites, groupe
						WHERE favorites.object_id = groupe.id
						AND favorites.user_id = ".$user_id."
						AND favorites.object_type = ".self::__TYPE_GROUP."
						AND groupe.state = ".GroupePeer::__STATE_ACTIVE;

			if(count($types) == 1)
				$query .= " LIMIT ".$offset.", ".$limit;

			$statement = $connection->query($count);
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$result = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while($rs = $statement->fetch())
			{
				$favorite = new Favorites();
				$favorite->hydrate($rs);
				$favorites[] = serialize($favorite);
			}

			$statement->closeCursor();
			$statement = null;

			$c += $result[0]["count"];
		}

		if(in_array(self::__TYPE_FOLDER, $types))
		{
			$query = "	SELECT distinct favorites.*
						FROM favorites, folder
						WHERE favorites.object_id = folder.id
						AND favorites.user_id = ".$user_id."
						AND favorites.object_type = ".self::__TYPE_FOLDER."
						AND folder.state = ".FolderPeer::__STATE_ACTIVE."
						ORDER BY favorites.object_id";

			$count = "	SELECT count(distinct favorites.id) as count
						FROM favorites, folder
						WHERE favorites.object_id = folder.id
						AND favorites.user_id = ".$user_id."
						AND favorites.object_type = ".self::__TYPE_FOLDER."
						AND folder.state = ".FolderPeer::__STATE_ACTIVE;

			if(count($types) == 1)
				$query .= " LIMIT ".$offset.", ".$limit;

			$statement = $connection->query($count);
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$result = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while($rs = $statement->fetch())
			{
				$favorite = new Favorites();
				$favorite->hydrate($rs);
				$favorites[] = serialize($favorite);
			}

			$statement->closeCursor();
			$statement = null;

			$c += $result[0]["count"];
		}

		if(in_array(self::__TYPE_FILE, $types))
		{
			$query = "	SELECT distinct favorites.*
						FROM favorites, file
						WHERE favorites.object_id = file.id
						AND favorites.user_id = ".$user_id."
						AND favorites.object_type = ".self::__TYPE_FILE."
						AND file.state = ".FilePeer::__STATE_VALIDATE."
						ORDER BY favorites.object_id";

			$count = "	SELECT count(distinct favorites.id) as count
						FROM favorites, file
						WHERE favorites.object_id = file.id
						AND favorites.user_id = ".$user_id."
						AND favorites.object_type = ".self::__TYPE_FILE."
						AND file.state = ".FilePeer::__STATE_VALIDATE;

			if(count($types) == 1)
				$query .= " LIMIT ".$offset.", ".$limit;

			$statement = $connection->query($count);
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			$result = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_NUM); 

			while($rs = $statement->fetch())
			{
				$favorite = new Favorites();
				$favorite->hydrate($rs);
				$favorites[] = serialize($favorite);
			}

			$statement->closeCursor();
			$statement = null;

			$c += $result[0]["count"];
		}

		if(count($types) > 1)
			$favorites = array_slice($favorites, $offset, $limit);

		return Array("favorites" => array_map("unserialize", $favorites), "count" => $c);
	}
}