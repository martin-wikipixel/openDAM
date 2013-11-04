<?php

/**
 * user actions.
 *
 * @packagetengis
 * @subpackage admin
 * @author Your name here
 * @versionSVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class userActions extends sfActions
{
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchUsers()
	{
		$this->getResponse()->setContentType('application/json');
		$users = UserPeer::fetchUsers($this->getRequestParameter("term"));

		$results = array();
		foreach ($users as $user)
		{
			$temp = Array();
			$temp["id"] = $user->getId();
			$temp["value"] = ucfirst(strtolower($user->getFirstname()))." ".ucfirst(strtolower($user->getLastname()));
			$temp["label"] = ucfirst(strtolower($user->getFirstname()))." ".ucfirst(strtolower($user->getLastname()));

			array_push($results, $temp);
		}

		return $this->renderText(json_encode($results));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchUsers2()
	{
		$this->getResponse()->setContentType('application/json');
		$users = UserPeer::fetchUsers($this->getRequestParameter("term"));
	
		$results = array();
		
		foreach ($users as $user) {
			$str = ucfirst(strtolower($user->getFirstname()))." ".strtoupper($user->getLastname())." <".$user->getEmail().">";
			
			$temp = Array();
			$temp["id"] = $user->getId();
			$temp["value"] = $str;
			$temp["label"] = $str;
	
			array_push($results, $temp);
		}
	
		return $this->renderText(json_encode($results));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchUsersUnit()
	{
		$this->getResponse()->setContentType('application/json');
		$users = UserPeer::fetchUsersUnit($this->getRequestParameter("term"),$this->getRequestParameter("unit_id"));
	
		$results = array();
		foreach ($users as $user)
		{
			$temp = Array();
			$temp["id"] = $user->getId();
			$temp["value"] = ucfirst(strtolower($user->getFirstname()))." / ".ucfirst(strtolower($user->getLastname()))." / ".$user->getEmail();
			$temp["label"] = ucfirst(strtolower($user->getFirstname()))." / ".ucfirst(strtolower($user->getLastname()))." / ".$user->getEmail();
	
			array_push($results, $temp);
		}
	
		return $this->renderText(json_encode($results));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchUser()
	{
		$this->getResponse()->setContentType('application/json');
		$users = UserPeer::fetchUser($this->getRequestParameter("term"));
		$units = UnitPeer::fetchUnit($this->getRequestParameter("term"));
	
		$results = array();
	
		foreach ($users as $user)
		{
			$temp = Array();
			$temp["id"] = "user-".$user->getId();
			$temp["value"] = $user->getEmail();
			$temp["label"] = "<i class='icon-user'></i> ".$user->getEmail();
	
			array_push($results, $temp);
		}
	
		foreach ($units as $unit)
		{
			$temp = Array();
			$temp["id"] = "unit-".$unit->getId();
			$temp["value"] = $unit->getTitle();
			$temp["label"] = "<i class='icon-group'></i> ".$unit->getTitle();
	
			array_push($results, $temp);
		}
	
		return $this->renderText(json_encode($results));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchUserGroup()
	{
		$this->forward404Unless($group = GroupePeer::retrieveByPk($this->getRequestParameter("id")));
	
		$this->getResponse()->setContentType('application/json');
		$users = UserPeer::fetchUserGroup($this->getRequestParameter("term"), $group->getId());
	
		$results = array();
		foreach ($users as $user)
		{
			$temp = Array();
			$temp["id"] = "user-".$user->getId();
			$temp["value"] = $user." / ".$user->getFirstname()." / ".$user->getEmail();
			$temp["label"] = $user." / ".$user->getFirstname()." / ".$user->getEmail();
	
			array_push($results, $temp);
		}
	
	
		return $this->renderText(json_encode($results));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchUserFolder()
	{
		$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("id")));
	
		$this->getResponse()->setContentType('application/json');
		$users = UserPeer::fetchUserFolder($this->getRequestParameter("term"), $folder->getId());
	
		$results = array();
	
		if (!$folder->getFree()) {
			if (preg_match("/".$this->getRequestParameter("term")."/i", __("unit.everybody"))) {
				$temp = array(
						"id" => "unit-everybody",
						"value" => __("unit.everybody"),
						"label" => "<i class='icon-group'></i> ".__("unit.everybody")
				);
	
				array_push($results, $temp);
			}
		}
	
		foreach ($users as $user)
		{
			$temp = Array();
			$temp["id"] = "user-".$user->getId();
			$temp["value"] = $user->getEmail();
			$temp["label"] = "<i class='icon-user'></i> ".$user->getEmail();
	
			array_push($results, $temp);
		}
	
	
		return $this->renderText(json_encode($results));
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see account/logout
	 * 
	 */
	public function executeLogout()
	{
		$this->getUser()->signOut();
	
		if ($this->getRequest()->isXmlHttpRequest()) {
			return $this->renderText("");
		}
		
		$this->redirect(__("http://www.wikipixel.com/"));
	}

	/*________________________________________________________________________________________________________________*/
	public function validateUpdate()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
		$valid = true;
		
		if ($this->getRequest()->getMethod() == sfRequest::POST){
			if(!$this->getRequestParameter('id') && !$this->getRequestParameter('password')){
				$this->getRequest()->setError('error', __('Password is required.'));
				$valid = false;
			}
		}
		
		return $valid;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeFetchUsersForAlbums()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$term = $this->getRequestParameter("term");
			$albumId = $this->getRequestParameter("album");
			$this->forward404Unless($group = GroupePeer::retrieveByPK($albumId));
	
			$users = UserPeer::fetchUserForAlbum($group->getId(), $term, "json");
			$results = array();
	
			foreach ($users as $user) {
				$str = ucfirst(strtolower($user->getFirstname()))." ".strtoupper($user->getLastname())." <".$user->getEmail().">";
					
				$temp = Array();
				$temp["id"] = $user->getId();
				$temp["value"] = $str;
				$temp["label"] = $str;
			
				array_push($results, $temp);
			}
	
			$this->getResponse()->setContentType('application/json');
			// return $this->renderText(json_encode(array_merge($users, $units)));
			return $this->renderText(json_encode($results));
		}
	
		$this->forward404();
	}

	/*________________________________________________________________________________________________________________*/
	/*______________________________________________public ___________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public function executeLogin(sfWebRequest $request)
	{
		if ($this->getUser()->isAuthenticated()) {
			$this->redirect('@homepage');
		}

		$referer = $request->getReferer();
	
		if (!$referer) {
			$referer = '@homepage';
		}
	
		// pour auto initialiser le champ email si on le passe en paramètre
		$username = $request->getParameter("email");
		
		$form = new UserLoginForm(
			array(
				"username" => $username
			)
		);
	
		if ($request->getMethod() == sfRequest::POST) {
			$form->bind($request->getParameter("data"));
			$this->getResponse()->setSlot("form", $form);
	
			if ($form->isValid()) {
				$user = $form->getOption("user");
				
				$this->getContext()->getUser()->signIn($user);
	
				$this->redirect($referer);
			}
		}
		
		$this->form = $form;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoginShowFolder()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($folder = FolderPeer::retrieveByPk($this->getRequestParameter("folder_id")));
	
			$this->getResponse()->setContentType('application/json');
			$user = UserPeer::retrieveByLogin($this->getRequestParameter("username"));
	
			if(!$user)
				return $this->renderText(json_encode(Array("code" => 1, "label" => __("Authentication failed."))));
			else
			{
				if(md5($this->getRequestParameter("password")) != $user->getPassword())
					return $this->renderText(json_encode(Array("code" => 1, "label" => __("Authentication failed."))));
				else
				{
					if($user->getCustomerId() != $folder->getCustomerId())
					{
						$customer = $user->getCustomer();
						$msg = __('You must be part of the company "%1%" to add files.', array("%1%" => 
								($customer->getCompany() ? strtoupper($customer->getCompany()) : ucfirst(strtolower($customer->getName())).
										" ".ucfirst(strtolower($customer->getFirstName())))));
						return $this->renderText(json_encode(Array("code" => 2, "label" => $msg)));
					}
					else
					{
						$this->getContext()->getUser()->signIn($user);
	
						return $this->renderText(json_encode(Array("code" => 0, "label" => "OK")));
					}
				}
			}
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeTokenConnect()
	{
		if($this->getRequestParameter("code"))
		{
			$this->forward404Unless($token = TokenAuthPeer::retrieveByCode($this->getRequestParameter("code")));
	
			if($token->getIp() == $_SERVER["REMOTE_ADDR"])
			{
				if(time() <= $token->getExpiredAt('U'))
				{
					$this->forward404Unless($user = UserPeer::retrieveByPKNoCustomer($token->getUserId()));
	
					/* if(CustomerBetaAccessPeer::retrieveByCustomerId($user->getCustomerId()))
						$url = sfConfig::get("app_beta_url");
					else */
						$url = "@homepage";
	
					$this->getContext()->getUser()->signIn($user);

					if ($this->getRequestParameter("backlink")) {
						$this->getUser()->setAttribute("backlink", true);
					}

					$this->redirect($url);
				}
				else
					$this->forward404();
			}
			else
				$this->forward404();
		}
		else
			$this->forward404();
	}
}
