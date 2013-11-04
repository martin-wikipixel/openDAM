<?php

/**
 * Subclass for representing a row from the 'customer_has_module' table.
 *
 * 
 *
 * @package lib.model
 */ 
class CustomerHasModule extends BaseCustomerHasModule
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Get the associated Module object
	 *
	 * @param      PropelPDO Optional Connection object.
	 * @return     Module The associated Module object.
	 * @throws     PropelException
	 */
	public function getModule(PropelPDO $con = null)
	{
		if ($this->aModule === null && ($this->module_id !== null)) {
			$this->aModule = ModulePeer::retrieveByPkI18n($this->module_id);
		}
	
		return $this->aModule;
	}
}
