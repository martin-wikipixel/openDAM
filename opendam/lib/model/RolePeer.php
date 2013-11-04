<?php

/**
 * Subclass for performing query and update operations on the 'role' table.
 *
 * 
 *
 * @package lib.model
 */ 
class RolePeer extends BaseRolePeer
{
	const __ADMIN = 2;
	const __LABEL_ADMIN = "administrator";
	const __CONTRIB = 3;
	const __LABEL_CONTRIB = "contributor";
	const __READER = 4;
	const __LABEL_READER = "reader";

	const __NONE = 5;

	/*________________________________________________________________________________________________________________*/
	public static function getRolesInSelect()
	{
		$c = new Criteria();
		
		$c->addDescendingOrderByColumn(self::ID);
		$roles = self::doSelect($c);

		$roles_array = array();
		
		foreach ($roles as $role) {
			$roles_array[$role->getId()] = $role->getTitle();
		}
		
		return $roles_array;
	}
	
	/*________________________________________________________________________________________________________________*/
	public static function buildRole($id, $title)
	{
		$r = new Role();
		
		$r->setId($id);
		$r->setTitle($title);
		
		return $r;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des rôles pour un album.
	 *
	 * @return  Array<id => name>
	 */
	public static function getRolesAsArray()
	{
		return array(
			RolePeer::__ADMIN => __("Administration"),
			RolePeer::__CONTRIB => __("Writing"),
			RolePeer::__READER => __("Reading")
		);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des rôles pour un album.
	 *
	 */
	public static function getRole($roleId)
	{
		Assert::ok(is_numeric($roleId));

		switch ($roleId) {
			case self::__ADMIN:
				return self::buildRole(RolePeer::__ADMIN, __("Administration"));
			
			case self::__CONTRIB:
				return self::buildRole(RolePeer::__CONTRIB, __("Writing"));
				
			case self::__READER:
				return self::buildRole(RolePeer::__READER, __("Reading"));
		}

		return null;
		/*
		switch ($roleId) {
			case self::__ADMIN:
				return self::buildRole(RolePeer::__ADMIN, __("Manager"));
					
			case self::__CONTRIB:
				return self::buildRole(RolePeer::__CONTRIB, __("Contributor"));
		
			case self::__READER:
				return self::buildRole(RolePeer::__READER, __("Reader"));
		}*/
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des rôles pour un album.
	 *
	 */
	public static function getRoles()
	{
		return array(
			self::getRole(RolePeer::__ADMIN),
			self::getRole(RolePeer::__CONTRIB),
			self::getRole(RolePeer::__READER)
		);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi la liste des rôles pour un album.
	 * 
	 * @param Groupe $album
	 * @return Array<Role>
	 */
	public static function getRolesForAlbum(Groupe $album)
	{
		if ($album->getFree()) {
			$roles = array();
			$roles[] = self::getRole(RolePeer::__ADMIN);

			switch ($album->getFreeCredential()) {
				case RolePeer::__CONTRIB:
					$roles[] = self::getRole(RolePeer::__CONTRIB);
					break;
		
				case RolePeer::__READER:
					$roles[] = self::getRole(RolePeer::__READER);

					break;
			}
		}
		else {
			$roles = self::getRoles();
		}

		return $roles;
	}
}
