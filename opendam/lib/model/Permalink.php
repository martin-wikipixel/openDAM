<?php

/**
 * Subclass for representing a row from the 'permalink' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Permalink extends BasePermalink
{
	public function __toString(){
		return $this->getLink();
	}

	/*________________________________________________________________________________________________________________*/
	public function getUsersNotification($type)
	{
		$users = Array();
		$notifications = PermalinkNotificationPeer::retrieveByPermalinkId($this->getId());

		foreach($notifications as $notification)
		{
			$flag = false;

			switch($type)
			{
				case PermalinkNotificationPeer::__ADD_COMMENT:
					if($notification->getAddComment())
						$flag = true;
				break;

				case PermalinkNotificationPeer::__ADD_FILE:
					if($notification->getUploadFile())
						$flag = true;
				break;
			}

			if($flag)
			{
				$user = UserPeer::retrieveByPkNoCustomer($notification->getUserId());

				$users[$user->getId()] = $user;
			}
		}

		return $users;
	}
	
	/*________________________________________________________________________________________________________________*/
	public function delete(PropelPDO $con = null)
	{
		parent::delete($con);
		
		$filename = sfConfig::get("app_path_qrcode_dir")."/".$this->getQrcode().".png";
		
		if (file_exists($filename)) {
			unlink($filename);
		}
	}
}
