<?php

/**
 * Subclass for representing a row from the 'request' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Request extends BaseRequest
{
	/*________________________________________________________________________________________________________________*/
	public function getGroupe()
	{
		return GroupePeer::retrieveByPK($this->getGroupeId());
	}
}