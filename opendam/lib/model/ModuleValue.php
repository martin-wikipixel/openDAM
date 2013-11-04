<?php

/**
 * Subclass for representing a row from the 'module_value' table.
 *
 * 
 *
 * @package lib.model
 */ 
class ModuleValue extends BaseModuleValue
{
	/*________________________________________________________________________________________________________________*/
	public function save(PropelPDO $con = null)
	{
		if($this->isNew()) {

			$c = new Criteria();
			$c->add(ModuleValuePeer::MODULE_ID, $this->getModuleId());
			$nb = ModuleValuePeer::doCount($c);

			$this->setRanking($nb + 1);
		}

		parent::save($con);
	}
}
