<?php 

class rightComponents extends sfComponents
{
	public function executeSupportList()
	{
		$this->supports = UsageSupportPeer::getSupports();
	}
}