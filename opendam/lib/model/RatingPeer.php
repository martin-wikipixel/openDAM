<?php

/**
 * Subclass for performing query and update operations on the 'rating' table.
 *
 * 
 *
 * @package lib.model
 */ 
class RatingPeer extends BaseRatingPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function getFileRate($file_id)
	{
		$c = new Criteria();
		
		$c->add(self::FILE_ID, $file_id);
		$c->addJoin(FilePeer::ID, self::FILE_ID);
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
		$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		return self::doSelectOne($c);
	}

}