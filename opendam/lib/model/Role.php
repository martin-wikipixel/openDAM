<?php

/**
 * Subclass for representing a row from the 'role' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Role extends BaseRole
{
	/*________________________________________________________________________________________________________________*/
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
}
