<?php

/**
 * Subclass for representing a row from the 'user' table.
 *
 * 
 *
 * @package lib.model
 */ 
class User extends BaseUser
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Le salt d'un password.
	 * 
	 * @return string
	 */
	public function getSalt() 
	{
		return "";
	}

	/*________________________________________________________________________________________________________________*/
	public function haveAccessModule($module_id)
	{
		return UserHasModulePeer::haveAccessModule($module_id, $this->getId());
	}

	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		return $this->getUsername();
	}

	/*________________________________________________________________________________________________________________*/
	public function getFullname(){
		return ucfirst(strtolower($this->getFirstname()))." ".ucfirst(strtolower($this->getLastname()));
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Supprime un utilisateur.
	 * Note: 
	 * 	Les fichiers sont supprimés par une tâche (voir fileDeleteOldTask).
	 * 
	 * @see BaseUser::delete()
	 */
	public function delete(PropelPDO $con = null)
	{
		$this->setState(UserPeer::__STATE_DELETE);
		$this->save();
	}
}
