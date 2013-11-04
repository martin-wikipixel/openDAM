<?php

/**
 * Subclass for representing a row from the 'comment' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Comment extends BaseComment
{
	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		return $this->getTitle();
	}
}
