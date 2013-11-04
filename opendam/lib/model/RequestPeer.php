<?php

/**
 * Subclass for performing query and update operations on the 'request' table.
 *
 * 
 *
 * @package lib.model
 */ 
class RequestPeer extends BaseRequestPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function retrieveByGroupId($group_id, $crit = false)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
	
		if($crit)
			return $c;
	
		return self::doSelect($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function retrieveByFolderId($folder_id)
	{
		$c = new Criteria();
		
		$c->add(self::FOLDER_ID, $folder_id);
		$c->addJoin(FolderPeer::ID, self::FOLDER_ID);
		$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		return self::doSelect($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function deleteRequest($group_id, $user_id)
	{
		$c = new Criteria();
		
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if(self::doCount($c) > 0) {
			$c = new Criteria();
			$c->add(self::GROUPE_ID, $group_id);
			$c->add(self::USER_ID, $user_id);
			self::doDelete($c);
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function deleteRequestFolder($folder_id, $user_id)
	{
		$c = new Criteria();
		$c->add(self::FOLDER_ID, $folder_id);
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(FolderPeer::ID, self::FOLDER_ID);
		$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
	
		if(self::doCount($c) > 0) {
			$c = new Criteria();
			$c->add(self::FOLDER_ID, $folder_id);
			$c->add(self::USER_ID, $user_id);
			self::doDelete($c);
		}
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getRequest($group_id, $user_id)
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, $group_id);
		$c->add(self::USER_ID, $user_id);
		$c->add(self::IS_REQUEST, 1);
		
		return self::doSelectOne($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getRequestFolder($folder_id, $user_id)
	{
		$c = new Criteria();
		
		$c->add(self::FOLDER_ID, $folder_id);
		$c->add(self::USER_ID, $user_id);
		$c->add(self::IS_REQUEST, 1);
		return self::doSelectOne($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	// request/send
	public static function isRequestSent($user_id, $message="")
	{
		$c = new Criteria();
		$c->add(self::GROUPE_ID, 0);
		$c->add(self::USER_ID, $user_id);
		$c->add(self::MESSAGE, $message);
		
		return self::doSelectOne($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	// global/_navigation
	public static function getNbAlerts($user_id)
	{
		$group_ids = UserGroupPeer::getGroupIds($user_id, RolePeer::__ADMIN);
		
		$c = new Criteria();
		
		if(!sfContext::getInstance()->getUser()->hasCredential("admin")){
			$c->add(self::GROUPE_ID, $group_ids, Criteria::IN);
		}
		
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->setDistinct();
		
		return self::doCount($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	// request/list
	public static function getRequestPager(Criteria $c)
	{
		$max = $c->getLimit();
		$offset = $c->getOffset();
	
		$map = $c->getMap();
		$user_id = $map[UserPeer::ID]->getValue();
	
		$group_ids = UserGroupPeer::getGroupIds($user_id, RolePeer::__ADMIN);
	
		$c = new Criteria();
	
		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
			$c->add(self::GROUPE_ID, $group_ids, Criteria::IN);
	
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::CREATED_AT);
		$c->setDistinct();
		$c->setLimit($max);
		$c->setOffset($offset);
		$requests = self::doSelect($c);
	
		return $requests;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getCountRequestPager(Criteria $c)
	{
		$map = $c->getMap();
		$user_id = $map[UserPeer::ID]->getValue();
	
		$group_ids = UserGroupPeer::getGroupIds($user_id, RolePeer::__ADMIN);
	
		$c = new Criteria();
	
		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
			$c->add(self::GROUPE_ID, $group_ids, Criteria::IN);
	
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$c->addAscendingOrderByColumn(self::CREATED_AT);
		$c->setDistinct();
		$requests = self::doSelect($c);
	
		return count($requests);
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getFilePager(Criteria $c)
	{
		$max = $c->getLimit();
		$offset = $c->getOffset();
	
		$map = $c->getMap();
		$user_id = $map[UserPeer::ID]->getValue();
	
		$group_ids = UserGroupPeer::getGroupIds($user_id, RolePeer::__ADMIN);
	
		$c = new Criteria();
		$c->setDistinct();
		$c->add(FileWaitingPeer::STATE, array(FileWaitingPeer::__STATE_WAITING_VALIDATE, FileWaitingPeer::__STATE_WAITING_DELETE), Criteria::IN);
		$c->addJoin(FileWaitingPeer::FILE_ID, FilePeer::ID);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			$c->addJoin(GroupePeer::ID, ValidatorUserGroup::GROUPE_ID);
			$c->add(ValidatorUserGroup::USER_ID, sfContext::getInstance()->getUser()->getId());
		}
	
		$c->setLimit($max);
		$c->setOffset($offset);
	
		$files = FilePeer::doSelect($c);
	
		return $files;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function getCountFilePager(Criteria $c)
	{
		$map = $c->getMap();
		$user_id = $map[UserPeer::ID]->getValue();
	
		$group_ids = UserGroupPeer::getGroupIds($user_id, RolePeer::__ADMIN);
	
		$c = new Criteria();
		$c->setDistinct();
		$c->add(FileWaitingPeer::STATE, array(FileWaitingPeer::__STATE_WAITING_VALIDATE, FileWaitingPeer::__STATE_WAITING_DELETE), Criteria::IN);
		$c->addJoin(FileWaitingPeer::FILE_ID, FilePeer::ID);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
		{
			$c->addJoin(GroupePeer::ID, ValidatorUserGroup::GROUPE_ID);
			$c->add(ValidatorUserGroup::USER_ID, sfContext::getInstance()->getUser()->getId());
		}
	
		$files = FilePeer::doSelect($c);
	
		return count($files);
	}
	
	/*________________________________________________________________________________________________________________*/
	// GroupePeer::getNoAccessGroupsInArray
	public static function getGroupIds($user_id)
	{
		$c = new Criteria();
		
		$c->add(self::GROUPE_ID, 0, Criteria::NOT_EQUAL);
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		$user_groups = self::doSelect($c);
		
		$group_ids = array();
		
		foreach ($user_groups as $user_group){
			$group_ids[] = $user_group->getGroupeId();
		}
		
		return $group_ids;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function deleteByGroupeId($group_id)
	{
		$c = new Criteria();
		
		$c->add(self::GROUPE_ID, $group_id);
		$c->addJoin(GroupePeer::ID, self::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if(self::doCount($c) > 0) {
			$c = new Criteria();
			$c->add(self::GROUPE_ID, $group_id);
			return self::doDelete($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function retrieveByFolderRequest($folder_id) {
		$criteria = new Criteria();
		$criteria->add(self::IS_REQUEST, true);
		$criteria->add(self::FOLDER_ID, $folder_id);
	
		return self::doSelect($criteria);
	}
}