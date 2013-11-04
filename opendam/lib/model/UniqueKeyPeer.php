<?php

/**
 * Subclass for performing query and update operations on the 'unique_key' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UniqueKeyPeer extends BaseUniqueKeyPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function exists($id)
	{
		$c = new Criteria();
		$c->add(self::ID, $id);

		return (self::doCount($c) == 1);
	}

	/*________________________________________________________________________________________________________________*/
	public static function generate($user_id = null, $expire = null, $uri = null)
	{
		$key = new UniqueKey();

		if($user_id)
			$key->setIdUser($idUser);

		if($expire)
			$key->setExpiredAt($expire);

		if($uri)
			$key->setUri($uri);

		if($idContact)
			$key->setIdContact($idContact); 

		$key->save();

		return $key;
	}
}
