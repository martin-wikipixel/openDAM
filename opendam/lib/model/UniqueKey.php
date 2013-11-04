<?php

/**
 * Subclass for representing a row from the 'unique_key' table.
 *
 * 
 *
 * @package lib.model
 */ 
class UniqueKey extends BaseUniqueKey
{
	/*________________________________________________________________________________________________________________*/
	public function save(PropelPDO $con = null)
	{
		if ($this->isNew()) {
			srand((double)microtime()*1000000);
			$string = "abcdefghijklmnopkrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";

			do {
				$exists = false;
				$id = '';

				for ($i=0; $i<200; $i++) {
					$num = rand(0, mb_strlen($string)-1);
					$id .= $string[$num]; 
				}

				$exists = UniqueKeyPeer::exists($id);
			}
			while($exists);

			$this->setId($id);
		}

		return parent::save($con);
	}
}
