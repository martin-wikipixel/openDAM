<?php

/**
 * Subclass for performing query and update operations on the 'file_tmp' table.
 *
 * 
 *
 * @package lib.model
 */ 
class FileTmpPeer extends BaseFileTmpPeer
{
	/*________________________________________________________________________________________________________________*/
	# upload/uploadify
	public static function getFileTmp($file_id, $user_id)
	{
		$c = new Criteria();
		
		$c->add(self::FILE_ID, $file_id);
		$c->add(self::USER_ID, $user_id);
		$c->addJoin(FilePeer::ID, self::FILE_ID);
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
		$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		return self::doSelectOne($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	# upload/option
	public static function retrieveByUserIdInArray($user_id, $folder_id)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
		$c->add(self::FOLDER_ID, $folder_id);
		$c->addJoin(FolderPeer::ID, self::FOLDER_ID);
		$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
		
		$file_tmps = self::doSelect($c);
	
		$file_tmps_array = array();
		$i = 1;
		
		foreach ($file_tmps as $file_tmp){
			$file_tmps_array[$i++] = $file_tmp->getFileId();
		}
	
		return $file_tmps_array;
	}
	
	/*________________________________________________________________________________________________________________*/
	# file/editAll, file/edit
	public static function deleteByUserId($user_id, $folder_id)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
		$c->add(self::FOLDER_ID, $folder_id);
		$c->addJoin(FolderPeer::ID, self::FOLDER_ID);
		$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
		$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
		$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
		$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
	
		if(self::doCount($c) > 0) {
			$c = new Criteria();
			$c->add(self::USER_ID, $user_id);
			$c->add(self::FOLDER_ID, $folder_id);
			self::doDelete($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	# upload/uploadifySuccess
	public static function retrieveByUserIdFolderId($user_id, $folder_id)
	{
		$c = new Criteria();
		
		$c->add(self::USER_ID, $user_id);
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
	
	public static function deleteByFolderId($folder_id)
	{
		$criteria = new Criteria();
		$criteria->add(self::FOLDER_ID, $folder_id);

		self::doDelete($criteria);
	}
}