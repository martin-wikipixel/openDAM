<?php

/**
 * Subclass for representing a row from the 'user_group' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UserGroup extends BaseUserGroup
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi l'album.
	 * 
	 * @param PropelPDO $con
	 * @return Groupe
	 */
	public function getAlbum(PropelPDO $con = null)
	{
		return parent::getGroupe($con);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le credential.
	 *
	 * @return Role
	 */
	public function getCredential()
	{
		//return RolePeer::getRole($this->getRole());
		return RolePeer::retrieveByPK($this->getRole());
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if ($user = UserPeer::retrieveByPK($this->getUserId())){
			return $user->getUsername();
		}
		
		return "";
	}
}