<?php

/**
 * Subclass for representing a row from the 'tag' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Tag extends BaseTag
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

	/*________________________________________________________________________________________________________________*/
	public function getNb()
	{
		$c = new Criteria();
		$c->add(FileTagPeer::TAG_ID, $this->getId());

		return FileTagPeer::doCount($c);
	}
}
