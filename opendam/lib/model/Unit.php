<?php

/**
 * Subclass for representing a row from the 'unit' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Unit extends BaseUnit
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Le nom du group. (Alias de getTitle()).
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->getTitle();
	}
	
	/*________________________________________________________________________________________________________________*/
	public function setName($name)
	{
		$this->setTitle($name);
	}

	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		return $this->getTitle();
	}

	/*________________________________________________________________________________________________________________*/
	public function getEffective()
	{
		return UnitPeer::getEffective($this->getId());
	}

	private $users = null;

	/*________________________________________________________________________________________________________________*/
	public function getUsers()
	{
		if ($this->users === null) {
			$criteria = new Criteria();
			
			$criteria->addJoin(UserUnitPeer::USER_ID, UserPeer::ID);
			$criteria->add(UserUnitPeer::UNIT_ID, $this->getId());
			$criteria->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);

			$this->users = UserPeer::doSelect($criteria);
		}
		
		return $this->users;
	}
	
	private $countUsers = null;
	
	/*________________________________________________________________________________________________________________*/
	public function countUsers()
	{
		if ($this->countUsers === null) {
			$criteria = new Criteria();
			
			$criteria->addJoin(UserUnitPeer::USER_ID, UserPeer::ID);
			$criteria->add(UserUnitPeer::UNIT_ID, $this->getId());
			$criteria->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);

			$this->countUsers = UserPeer::doCount($criteria);
		}

		return $this->countUsers;
	}
}
