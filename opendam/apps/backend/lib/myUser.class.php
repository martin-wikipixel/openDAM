<?php
class myUser extends sfBasicSecurityUser
{
	private $user = null;
	private $customer = null;
	
	/*________________________________________________________________________________________________________________*/
	public function getCsrfToken($name = "csrfToken")
	{
		if (!$this->hasAttribute($name)) {
			$this->setAttribute($name, SecurityUtils::getCsrfToken());
		}
	
		return $this->getAttribute($name);
	}
	
	/*________________________________________________________________________________________________________________*/
	public function removeCsrfToken($name = "csrfToken")
	{
		$this->setAttribute($name, null);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie true si le user est administrateur.
	 * 
	 * @return boolean
	 */
	public function isAdmin()
	{
		return $this->hasCredential("admin");
	}

	/*________________________________________________________________________________________________________________*/
	public function getId()
	{
		return $this->getAttribute("id", 0);
	}

	/*________________________________________________________________________________________________________________*/
	public function getEmail()
	{
		return $this->getAttribute("email", '');
	}

	/*________________________________________________________________________________________________________________*/
	public function getUsername()
	{
		return $this->getAttribute("username", '');
	}

	/*________________________________________________________________________________________________________________*/
	public function getFirstname()
	{
		return $this->getAttribute("firstname", '');
	}

	/*________________________________________________________________________________________________________________*/
	public function getLastname()
	{
		return $this->getAttribute("lastname", '');
	}

	/*________________________________________________________________________________________________________________*/
	public function getCustomerId()
	{
		return $this->getAttribute("customerId", 0);
	}

	/*________________________________________________________________________________________________________________*/
	public function getFullname()
	{
		return (($this->getFirstname() && $this->getLastname()) ? $this->getFirstname()." ".
				$this->getLastname() : $this->getUsername());
	}

	/*________________________________________________________________________________________________________________*/
	public function getBasket()
	{
		return $this->getAttribute("basket");
	}
	
	/*________________________________________________________________________________________________________________*/
	public function setBasket($basket)
	{
		$this->setAttribute("basket", $basket);
	}
	
	/*________________________________________________________________________________________________________________*/
	public function getDisk()
	{
		return $this->getAttribute("disk", "");
	}

	/*________________________________________________________________________________________________________________*/
	public function setInstance($admin)
	{
		$this->admin = $admin;
	}

	/*________________________________________________________________________________________________________________*/
	public function getInstance()
	{
		if (!$this->user){
			$this->user = UserPeer::retrieveByPk($this->getId());
		}

		return $this->user;
	}

	/*________________________________________________________________________________________________________________*/
	public function getCustomerInstance()
	{
		if (!$this->customer){
			$this->customer = CustomerPeer::retrieveByPk($this->getCustomerId());
		}
	
		return $this->customer;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Permet de mettre à jour les champs de /account qui sont aussi présent en session
	 * 
	 * @param User $user
	 */
	public function setAccountAttributes(User $user)
	{
		$this->setAttribute("id", $user->getId());
		$this->setAttribute('username', $user->getUsername());
		$this->setAttribute("firstname", $user->getFirstname());
		$this->setAttribute("lastname", $user->getLastname());
		$this->setAttribute("email", $user->getEmail());

		$this->setCulture(CulturePeer::retrieveByPk($user->getCulture())->getCode());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function signIn($user)
	{
		Assert::ok(!is_null($user));
		
		$customer = $user->getCustomer();
		$this->setAuthenticated(true);
	
		$this->setAccountAttributes($user);

		$this->setAttribute('customerId', $user->getCustomerId());
		$this->setAttribute('disk', $customer->getDisk());

		$culture = CulturePeer::retrieveByPK($user->getCulture());
		$this->setCulture($culture->getCode());

		switch ($user->getRoleId()) {
			case 1:
			case 2: $credential = "admin"; break;
			
			case 3: $credential = "contributer"; break;
			case 4: $credential = "reader"; break;
			case 5: $credential = "none"; break;
			default: $credential = "none"; break;
		}
	
		$this->addCredential($credential);
	
		$user->setLastLoginAt(date('Y-m-d H:i:s'));
		$user->save();
	
		LogUserPeer::setUserLog($user->getId());
	}
	
	/*________________________________________________________________________________________________________________*/
	public function signOut()
	{
		$this->setAttribute('id', 0);
		$this->setAttribute('email', null);
		$this->setAttribute('username', null);
		$this->setAttribute('firstname', null);
		$this->setAttribute('lastname', null);
		$this->setAttribute('customerId', null);
		$this->setAttribute('disk', null);
	
		unset($this->user);
	
		$this->clearCredentials();
		$this->setAuthenticated(false);
		$this->getAttributeHolder()->clear();
	}
	
	
	private $haveAccessModuleCache = array();

	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/**
	 * rue si le user courant a access au module
	 * @param integer $module_id
	 * @return boolean
	 */
	public function haveAccessModule($module_id)
	{
		$haveAccessModuleCache = $this->getAttribute('haveAccessModuleCache', array());

		if (isset($haveAccessModuleCache[$module_id])) {
			return $haveAccessModuleCache[$module_id];
		}
	
		$customerModule = CustomerHasModulePeer::retrieveByModuleAndCustomer($module_id, $this->getCustomerId());
	
		$haveAccess = false;
		
		if ($customerModule && $customerModule->getActive() == true) {
			$haveAccess = true;
		}
		
		$haveAccessModuleCache[$module_id] = $haveAccess;
		
		$this->setAttribute('haveAccessModuleCache', $haveAccessModuleCache);
		return $haveAccess;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Get module value.
	 * @param unknown $module_id
	 * @return string|boolean
	 */
	public function getModuleValue($module_id)
	{
		$customerModule = CustomerHasModulePeer::retrieveByModuleAndCustomer($module_id, $this->getCustomerId());
	
		if($customerModule)
		{
			if(in_array($module_id, Array(ModulePeer::__MOD_TYPE_ALLOWED)))
			{
				if($customerModule->getCustomerValue())
					return $customerModule->getCustomerValue();
			}
	
			$value = ModuleValuePeer::retrieveByPk($customerModule->getModuleValueId());
	
			if(!$value)
				return false;
			else
				return $value->getValue();
			
		}
		else
			return false;
	}

	/*________________________________________________________________________________________________________________*/
	public function saveCulture($culture)
	{
		$culture = CulturePeer::retrieveByCode($culture);

		if ($culture && $this->isAuthenticated()) {
			$this->getInstance()->setCulture($culture->getId());
			$this->getInstance()->save();
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function getDiskSpace($unit = "mb")
	{
		return 0;
	}


	/*________________________________________________________________________________________________________________*/
	public function getRole($group_id, $folder_id = null)
	{
		$role = false;
		$roleCache = $this->getAttribute("roleCache", array());
	
		/* Si l'on veut connaitre le droit sur un album (lecteur / contributeur / manager) */
		if(empty($folder_id)) {
			/* Recherche dans le tableau de cache de l'index de l'album */
			if(isset($roleCache[$group_id])) {
				return $roleCache[$group_id]["role"];
			}
		}
		/* Si l'on veut connaitre le droit sur un dossier (true / false) */
		else {
			/* Recherche dans le tableau de cache de l'index du dossier */
			if(isset($roleCache[$group_id]["folders"][$folder_id])) {
				return $roleCache[$group_id]["folders"][$folder_id]["role"];
			}
		}

		/* Si l'album n'est pas encore rÃ©pertoriÃ© en cache */
		if(!isset($roleCache[$group_id])) {
			/* On rÃ©cupÃ¨re les droits en fonction des autorisations de l'utilisateur */
			$group = GroupePeer::retrieveByPKNoCustomer($group_id);

			if($group->getCustomerId() == $this->getCustomerId()) {
				if($this->isAdmin()) {
					$role = RolePeer::__ADMIN;
				}
				else {
					$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($group->getId(), $this->getId());
			
					if($userGroup) {
						$role = $userGroup->getRole();
					}
					else {
						$unitGroup = UnitGroupPeer::retrieveMinRoleByGroupIdAndUserId($group->getId(), $this->getId());
			
						if($unitGroup) {
							$role = $unitGroup->getRole();
						}
						else {
							if($group->getFree()) {
								$role = $group->getFreeCredential();
							}
							else {
								$role = false;
							}
						}
					}
				}
			}
			else {
				$userGroup = UserGroupPeer::retrieveByGroupIdAndUserId($group->getId(), $this->getId());
	
				if($userGroup) {
					if(($userGroup->getExpiration() && $userGroup->getExpiration("U") >= time()) || !$userGroup->getExpiration()) {
						$role = $userGroup->getRole();
					}
					else {
						$role = false;
					}
				}
				else {
					$role = false;
				}
			}

			/* Inscription des droits */
			$roleCache[$group_id] = array(
				"role" => $role,
				"folders" => array()
			);
		}

		/* Si l'on souhaite connaitre l'accÃ¨s Ã  un dossier */
		if(empty($folder_id)) {
			/* Enregistrement des droits en cache */
			$this->setAttribute('roleCache', $roleCache);
	
			return $role;
		}
		else {
				$folder = FolderPeer::retrieveByPK($folder_id);
	
				if($folder->getCustomerId() == $this->getCustomerId()) {
					if($this->hasCredential("admin")) {
						$role = true;
					}
					else {
						$folderObject = RightUtils::getAccessForFolderAndUser($folder, $this->getInstance());

						if (array_key_exists("access", $folderObject)) {
							$role = $folderObject["access"];
						}
						else {
							$role = false;
						}
					}
				}
				else {
					$userFolder = UserFolderPeer::retrieveByUserAndFolder($this->getId(), $folder->getId());

					if (($folder->getFree() && !$userFolder) || (!$folder->getFree() && $userFolder)) {
						$role = true;
					}
					else {
						$role = false;
					}
				}
	
			/* Inscription des droits */
			$roleCache[$group_id]["folders"][$folder_id] = array("role" => $role);
		}
	
		/* Enregistrement des droits en cache */
		$this->setAttribute('roleCache', $roleCache);

		return $role;
	}

	/*________________________________________________________________________________________________________________*/
	public function getConstraint($group_id, $constraint_id, $role = null)
	{
		$constraint = false;
		$constraintCache = $this->getAttribute("constraintCache", array());
	
		if (isset($constraintCache[$group_id][$constraint_id])) {
			return $constraintCache[$group_id][$constraint_id];
		}
	
		if (!empty($role)) {
			$roleGroup = $this->getRole($group_id);
	
			if ($roleGroup < $role) {
				$constraint = true;
			}
			else {
				$constraint = GroupeConstraintPeer::isAllowedTo($group_id, $constraint_id);
			}
		}
		else {
			$constraint = GroupeConstraintPeer::isAllowedTo($group_id, $constraint_id);
		}

		if (!isset($constraintCache[$group_id])) {
			$constraintCache[$group_id] = array();
		}
	
		$constraintCache[$group_id][$constraint_id] = $constraint;
	
		$this->setAttribute('constraintCache', $constraintCache);
	
		return $constraint;
	}

	/*________________________________________________________________________________________________________________*/
	public function getPreferences($page, $save = false, $params = array())
	{
		$preferences = $this->getAttribute('preferences', array());
	
		if (isset($preferences[$page])) {
			return $preferences[$page];
		}
		else {
			if (empty($preferences)) {
				$userPreference = UserPreferencePeer::retrieveByUserId($this->getId());
		
				if ($userPreference) {
					$preferences = $this->getAttribute('preferences', unserialize(base64_decode($userPreference->getValue())));
		
					if (isset($preferences[$page])) {
						return $preferences[$page];
					}
				}
			}
	
			if ($save == true) {
				$this->savePreferences($page, $params["sort"], $params["perPage"]);
				return $this->getPreferences($page);
			}
		}
	
		return false;
	}

	/*________________________________________________________________________________________________________________*/
	public function savePreferences($page, $sort, $perPage)
	{
		if (!$userPreference = UserPreferencePeer::retrieveByUserId($this->getId())) {
			$userPreference = new UserPreference();
			$userPreference->setTitle(UserPreferencePeer::__PAGES_PREFERENCES);
			$userPreference->setUserId($this->getId());

			$userPreference->save();
		}

		$preferences = $this->getAttribute('preferences', array());
		$preferences[$page] = array("sort" => $sort, "perPage" => $perPage);
	
		$this->setAttribute('preferences', $preferences);

		$userPreference->setValue(base64_encode(serialize($preferences)));
		$userPreference->save();
	}

	private $canCreateAlbumCache = null;

	/*________________________________________________________________________________________________________________*/
	public function canCreateAlbum()
	{
		if ($this->canCreateAlbumCache === null) {
			$this->canCreateAlbumCache = $this->isAdmin();
		}

		return $this->canCreateAlbumCache;
	}
}
