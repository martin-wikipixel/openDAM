<?php

class UserComponents extends sfComponents
{
	/*________________________________________________________________________________________________________________*/
	public function executeExpired()
	{
		$user = $this->getUser()->getInstance();
		$this->customer = CustomerPeer::retrieveByPk($user->getCustomerId());
	}

	/*________________________________________________________________________________________________________________*/
	/*public function executeRightGroup()
	{
		$this->unit = null;
		$this->roles = array(
			RolePeer::__ADMIN => __("Administration"),
			RolePeer::__CONTRIB => __("Writing"),
			RolePeer::__READER => __("Reading")
		);
	
		if($this->group->getCustomerId() == $this->user->getCustomerId()) {
			if($this->user->getRoleId() <= RolePeer::__ADMIN) {
				$this->role = RolePeer::__ADMIN;
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($this->group->getId(), $this->user->getId());
					
				if($userGroup) {
					if($this->group->getFree()) {
						$this->role = $userGroup->getRole() < $this->group->getFreeCredential() ? $userGroup->getRole() : $this->group->getFreeCredential();
					}
					else {
						$this->role = $userGroup->getRole();
					}
				}
				else {
					$unitGroup = UnitGroupPeer::retrieveMinRoleByGroupIdAndUserId($this->group->getId(), $this->user->getId());
	
					if($unitGroup) {
						if($this->group->getFree()) {
							if ($unitGroup->getRole() < $this->group->getFreeCredential()) {
								$this->role = $unitGroup->getRole();
								$this->unit = $unitGroup->getUnit();
							}
							else {
								$this->role = $this->group->getFreeCredential();
							}
						}
						else {
							$this->role = $unitGroup->getRole();
							$this->unit = $unitGroup->getUnit();
						}
					}
					else {
						if($this->group->getFree()) {
							$this->role = $this->group->getFreeCredential();
						}
						else {
							$this->role = false;
						}
					}
				}
			}
		}
		else {
			$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($this->group->getId(), $this->user->getId());
	
			if($userGroup) {
				if(($userGroup->getExpiration() && $userGroup->getExpiration("U") >= time()) || !$userGroup->getExpiration()) {
					$this->role = $userGroup->getRole();
				}
				else {
					$this->role = false;
				}
			}
			else {
				$this->role = false;
			}
		}
	}
	*/
	/*________________________________________________________________________________________________________________*/
	/*public function executeRightGroup2()
	{
		$this->unit = null;
		$this->roles = array(
				RolePeer::__ADMIN => __("Administration"),
				RolePeer::__CONTRIB => __("Writing"),
				RolePeer::__READER => __("Reading")
		);
	
		if($this->group->getCustomerId() == $this->user->getCustomerId()) {
			if($this->user->getRoleId() <= RolePeer::__ADMIN) {
				$this->role = RolePeer::__ADMIN;
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($this->group->getId(), $this->user->getId());
					
				if($userGroup) {
					if($this->group->getFree()) {
						$this->role = $userGroup->getRole() < $this->group->getFreeCredential() ? $userGroup->getRole() : $this->group->getFreeCredential();
					}
					else {
						$this->role = $userGroup->getRole();
					}
				}
				else {
					$unitGroup = UnitGroupPeer::retrieveMinRoleByGroupIdAndUserId($this->group->getId(), $this->user->getId());
	
					if($unitGroup) {
						if($this->group->getFree()) {
							if ($unitGroup->getRole() < $this->group->getFreeCredential()) {
								$this->role = $unitGroup->getRole();
								$this->unit = $unitGroup->getUnit();
							}
							else {
								$this->role = $this->group->getFreeCredential();
							}
						}
						else {
							$this->role = $unitGroup->getRole();
							$this->unit = $unitGroup->getUnit();
						}
					}
					else {
						if($this->group->getFree()) {
							$this->role = $this->group->getFreeCredential();
						}
						else {
							$this->role = false;
						}
					}
				}
			}
		}
		else {
			$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($this->group->getId(), $this->user->getId());
	
			if($userGroup) {
				if(($userGroup->getExpiration() && $userGroup->getExpiration("U") >= time()) || !$userGroup->getExpiration()) {
					$this->role = $userGroup->getRole();
				}
				else {
					$this->role = false;
				}
			}
			else {
				$this->role = false;
			}
		}
	}
	*/
	/*________________________________________________________________________________________________________________*/
	/*public function executeRightFolder()
	{
		$this->roles = array(
			RolePeer::__ADMIN => __("Administration"),
			RolePeer::__CONTRIB => __("Writing"),
			RolePeer::__READER => __("Reading")
		);
	
		$this->group = GroupePeer::retrieveByPK($this->folder->getGroupeId());
	
		if($this->group->getCustomerId() == $this->getUser()->getCustomerId()) {
			if ($this->user->getRoleId() <= RolePeer::__ADMIN) {
				$this->userRole = RolePeer::__ADMIN;
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($this->group->getId(), $this->user->getId());
					
				if ($userGroup) {
					if ($this->group->getFree()) {
						$this->userRole = $userGroup->getRole() < $this->group->getFreeCredential() ? $userGroup->getRole() : $this->group->getFreeCredential();
					}
					else {
						$this->userRole = $userGroup->getRole();
					}
				}
				else {
					$unitGroup = UnitGroupPeer::retrieveMinRoleByGroupIdAndUserId($this->group->getId(), $this->user->getId());
			
					if($unitGroup) {
						if($this->group->getFree()) {
							$this->userRole = $unitGroup->getRole() < $this->group->getFreeCredential() ? $unitGroup->getRole() : $this->group->getFreeCredential();
						}
						else {
							$this->userRole = $unitGroup->getRole();
						}
					}
					else {
						if($this->group->getFree()) {
							$this->userRole = $this->group->getFreeCredential();
						}
						else {
							$this->userRole = false;
						}
					}
				}
			}
		}
		else {
			$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($this->group->getId(), $this->user->getId());
	
			if($userGroup) {
				if(($userGroup->getExpiration() && $userGroup->getExpiration("U") >= time()) || !$userGroup->getExpiration()) {
					$this->userRole = $userGroup->getRole();
				}
				else {
					$this->userRole = false;
				}
			}
			else {
				$this->userRole = false;
			}
		}
	}*/

	/*________________________________________________________________________________________________________________*/
	/*public function executeRightFoldersUser()
	{
		$c = new Criteria();
		$c->add(GroupePeer::ID, GroupePeer::preGetUserGroupPager2($this->user->getId()), Criteria::IN);
		$c->addAscendingOrderByColumn(GroupePeer::NAME);
		
		$this->groups = GroupePeer::doSelect($c);
	
		$this->roles = array(
			RolePeer::__ADMIN => __("Administration"),
			RolePeer::__CONTRIB => __("Writing"),
			RolePeer::__READER => __("Reading")
		);
	
		$this->perPages = array(
			10 => 10,
			20 => 20,
			50 => 50,
			100 => 100,
			"all" => __("All")
		);
	}*/
}