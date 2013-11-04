<?php

/**
 * Subclass for performing query and update operations on the 'comment' table.
 *
 * 
 *
 * @package lib.model
 */ 
class CommentPeer extends BaseCommentPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function getCommentPager($file_id, $page=1)
	{
		$c = new Criteria();
		$c->addJoin(FilePeer::ID, self::FILE_ID);
		$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(self::FILE_ID, $file_id);
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
	
		$pager = new sfPropelPager('Comment', sfConfig::get('app_max_per_page', 100));
		$pager->setCriteria($c);
		$pager->setPage($page);
		$pager->setPeerMethod('doSelect');
		$pager->init();
		
		return $pager;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getComment($user_id, $file_id, $comment)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
		$c->add(self::FILE_ID, $file_id);
		$c->add(self::CONTENT, $comment);
	
		$c->addJoin(FilePeer::ID, self::FILE_ID);
		$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
		
		return self::doSelectOne($c);
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByFileId($file_id, $limit = null, $offset = null)
	{
		$c = new Criteria();
		$c->add(self::FILE_ID, $file_id);
		$c->addDescendingOrderByColumn(self::CREATED_AT);

		if(!empty($limit) || !empty($offset))
		{
			if(empty($limit))
				$limit = 0;

			$c->setLimit($limit);
			$c->setOffset($offset);
		}

		return self::doSelect($c);
	}
}