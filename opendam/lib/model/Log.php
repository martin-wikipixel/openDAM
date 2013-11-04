<?php

/**
 * Subclass for representing a row from the 'log' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Log extends BaseLog
{
	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		return $this->getContent();
	}
}
