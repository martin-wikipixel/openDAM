<?php

/**
 * Subclass for representing a row from the 'module' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Module extends BaseModule
{
	/*________________________________________________________________________________________________________________*/
	public function getVisibilitys()
	{
		return ModuleHasVisibilityPeer::getValues($this->getId());
	}

	/*________________________________________________________________________________________________________________*/
	public function isVisible($module_visibility_id)
	{
		$c = new Criteria();
		$c->add(ModuleHasVisibilityPeer::MODULE_ID, $this->getId());
		$c->add(ModuleHasVisibilityPeer::MODULE_VISIBILITY_ID, $module_visibility_id);

		return ModuleHasVisibilityPeer::doCount($c) > 0 ? true : false;
	}
}
