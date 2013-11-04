<?php

/**
 * Subclass for performing query and update operations on the 'file_tag' table.
 *
 * 
 *
 * @package lib.model
 */ 
class FileTagPeer extends BaseFileTagPeer
{
	const __TYPE_GROUP = 1;
	const __TYPE_FOLDER = 2;
	const __TYPE_FILE = 3;

	/*________________________________________________________________________________________________________________*/
	private static function getAlbumsOfTagCriteria(array $params = array(), array $orderBy = array())
	{
		$tagId = isset($params["tagId"]) ? (int)$params["tagId"] : 0;

		$criteria = new Criteria();
		
		$criteria->addJoin(self::FILE_ID, GroupePeer::ID, Criteria::INNER_JOIN);
		
		$criteria->add(self::TAG_ID, $tagId);
		$criteria->add(self::TYPE, self::__TYPE_GROUP);
		$criteria->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
		
		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie tous les albums associés au tag passé en paramètre.
	 *
	 * @return sfPropelPager<Groupe>
	 */
	public static function getAlbumsOfTagPager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);
	
		$pager = new sfPropelPager("Groupe", $itemPerPage);
	
		$pager->setCriteria(self::getAlbumsOfTagCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function countAlbumsOfTag($tagId)
	{
		return self::doCount(self::getAlbumsOfTagCriteria(array("tagId" => $tagId)));
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFoldersOfTagCriteria(array $params = array(), array $orderBy = array())
	{
		$tagId = isset($params["tagId"]) ? (int)$params["tagId"] : 0;
		
		$criteria = new Criteria();
		
		$criteria->addJoin(self::FILE_ID, FolderPeer::ID, Criteria::INNER_JOIN);
		
		$criteria->add(self::TAG_ID, $tagId);
		$criteria->add(self::TYPE, self::__TYPE_FOLDER);
		$criteria->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		
		CriteriaUtils::buildOrderBy($criteria, $orderBy);
		
		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie tous les dossiers associés au tag passé en paramètre.
	 *
	 * @return sfPropelPager<Groupe>
	 */
	public static function getFoldersOfTagPager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);
	
		$pager = new sfPropelPager("Folder", $itemPerPage);
	
		$pager->setCriteria(self::getFoldersOfTagCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function countFoldersOfTag($tagId)
	{
		return self::doCount(self::getFoldersOfTagCriteria(array("tagId" => $tagId)));
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFilesOfTagCriteria(array $params = array(), array $orderBy = array())
	{
		$tagId = isset($params["tagId"]) ? (int)$params["tagId"] : 0;
	
		$criteria = new Criteria();
	
		$criteria->addJoin(self::FILE_ID, FilePeer::ID, Criteria::INNER_JOIN);
	
		$criteria->add(self::TAG_ID, $tagId);
		$criteria->add(self::TYPE, self::__TYPE_FILE);
		$criteria->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);

		CriteriaUtils::buildOrderBy($criteria, $orderBy);
	
		return $criteria;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie tous les fichiers associés au tag passé en paramètre.
	 *
	 * @return sfPropelPager<Groupe>
	 */
	public static function getFilesOfTagPager($page, $itemPerPage, array $params = array(), array $orderBy = array())
	{
		Assert::ok($page > 0);
		Assert::ok($itemPerPage > 0);

		$pager = new sfPropelPager("File", $itemPerPage);
	
		$pager->setCriteria(self::getFilesOfTagCriteria($params, $orderBy));
		$pager->setPage($page);
		$pager->setPeerMethod("doSelect");
		$pager->init();
	
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function countFilesOfTag($tagId)
	{
		return self::doCount(self::getFilesOfTagCriteria(array("tagId" => $tagId)));
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByTagAndAlbum($tagId, $albumId)
	{
		$criteria = new Criteria();
	
		$criteria->add(self::TAG_ID, $tagId);
		$criteria->add(self::FILE_ID, $albumId);
		$criteria->add(self::TYPE, self::__TYPE_GROUP);
	
		return self::doSelectOne($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByTagAndFolder($tagId, $folderId)
	{
		$criteria = new Criteria();
	
		$criteria->add(self::TAG_ID, $tagId);
		$criteria->add(self::FILE_ID, $folderId);
		$criteria->add(self::TYPE, self::__TYPE_FOLDER);
	
		return self::doSelectOne($criteria);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByTagAndFile($tagId, $fileId)
	{
		$criteria = new Criteria();
		
		$criteria->add(self::TAG_ID, $tagId);
		$criteria->add(self::FILE_ID, $fileId);
		$criteria->add(self::TYPE, self::__TYPE_FILE);
		
		return self::doSelectOne($criteria);
	}

	
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param unknown $type
	 * @param unknown $file_id
	 */
	public static function deletByTypeFileId($type, $file_id)
	{
		$c = new Criteria();
	
		switch($type)
		{
			case 1:
				$c->addJoin(GroupePeer::ID, self::FILE_ID);
				$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
				$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			break;
	
			case 2:
				$c->addJoin(FolderPeer::ID, self::FILE_ID);
				$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
				$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
				$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
				$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			break;
	
			case 3:
				$c->addJoin(FilePeer::ID, self::FILE_ID);
				$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
				$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
				$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
				$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			break;
		}
		
		if(self::doCount($c) > 0) {
			$c = new Criteria();
			$c->add(self::TYPE, $type);
			$c->add(self::FILE_ID, $file_id);
			self::doDelete($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param unknown $tag_id
	 */
	public static function deleteByTagId($tag_id)
	{
		$c = new Criteria();
		
		$c->add(self::TAG_ID, $tag_id);
		$c->addJoin(TagPeer::ID, self::TAG_ID);
		$c->addJoin(TagPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(TagPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	
		//TODO pk un doCount ?? !!
		if (self::doCount($c) > 0) {
			$c = new Criteria();
			$c->add(self::TAG_ID, $tag_id);
			
			self::doDelete($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param unknown $type
	 * @param unknown $file_id
	 * @param unknown $tag_id
	 */
	public static function deleteFileTag($type, $file_id, $tag_id)
	{
		$c = new Criteria();
		
		$c->addJoin(TagPeer::ID, self::TAG_ID);
		$c->addJoin(TagPeer::CUSTOMER_ID, CustomerPeer::ID);
		
		$c->add(self::TYPE, $type);
		$c->add(self::FILE_ID, $file_id);
		$c->add(self::TAG_ID, $tag_id);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(TagPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		
		//TODO pk un doCount ?? !!
		if (self::doCount($c) > 0) {
			$c = new Criteria();
			
			$c->add(self::TYPE, $type);
			$c->add(self::FILE_ID, $file_id);
			$c->add(self::TAG_ID, $tag_id);
			
			self::doDelete($c);
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param unknown $tag_id
	 * @return Ambigous <multitype:, multitype:unknown Ambigous <FileTag, NULL, multitype:> >
	 */
	public static function retrieveByTagId($tag_id)
	{
		$c = new Criteria();
		
		$c->add(self::TAG_ID, $tag_id);
		$c->addJoin(TagPeer::ID, self::TAG_ID);
		$c->addJoin(TagPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(TagPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see
	 * @param unknown $type
	 * @param unknown $file_id
	 * @param unknown $tag_id
	 * @return Ambigous <FileTag, NULL, unknown, multitype:>
	 */
	public static function getFileTag($type, $file_id, $tag_id)
	{
		$c = new Criteria();
		
		$c->add(self::TYPE, $type);
		$c->add(self::FILE_ID, $file_id);
		$c->add(self::TAG_ID, $tag_id);
		$c->addJoin(TagPeer::ID, self::TAG_ID);
		$c->addJoin(TagPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(TagPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
	
		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param unknown $type
	 * @param unknown $file_id
	 * @param number $limit
	 * @return Ambigous <multitype:, multitype:unknown FileTag >
	 */
	public static function retrieveByFileIdType($type, $file_id, $limit=0)
	{
		$c = new Criteria();
		
		$c->add(self::TYPE, $type);
		$c->add(self::FILE_ID, $file_id);
	
		switch($type)
		{
			case 1:
				$c->addJoin(GroupePeer::ID, self::FILE_ID);
				$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
				$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			break;
	
			case 2:
				$c->addJoin(FolderPeer::ID, self::FILE_ID);
				$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
				$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
				$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
				$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			break;
	
			case 3:
				$c->addJoin(FilePeer::ID, self::FILE_ID);
				$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
				$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
				$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
				$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
				$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
				$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			break;
		}
	
		if ($limit) {
			$c->setLimit($limit);
		}
		
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByTagIdType($type, $tag_id, $limit=0)
	{
		$c = new Criteria();
		
		$c->add(self::TYPE, $type);
		$c->add(self::TAG_ID, $tag_id);
		
		if ($limit) {
			$c->setLimit($limit);
		}
		
		return self::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @param unknown $tag_id
	 * @return number
	 */
	public static function doCountByTagId($tag_id)
	{
		$c = new Criteria();
		
		$c->add(self::TAG_ID, $tag_id);
		$c->addJoin(TagPeer::ID, self::TAG_ID);
		$c->addJoin(TagPeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(TagPeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		
		return self::doCount($c);
	}
}