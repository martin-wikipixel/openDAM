<?php

/**
 * page actions.
 *
 * @packagekhas
 * @subpackage page
 * @author Your name here
 * @versionSVN: $Id: actions.class.php 3335 2007-01-23 16:19:56Z fabien $
 */
class publicActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSetCulture()
	{
		$culture = $this->getRequestParameter('culture');
	
		$this->getUser()->setCulture($culture);
		$this->getUser()->saveCulture($culture);
	
		$redirectUri = $this->getRequest()->getReferer();
		
		if (!$redirectUri) {
			$redirectUri = "@homepage";
		}
		
		$this->redirect($redirectUri);
	}

	/*________________________________________________________________________________________________________________*/
	public function executeHome()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");
	
		$this->pop = 0;
		$breadCrumbs = array();

		array_push($breadCrumbs, array(
						"link"		=> path("@homepage"),
						"label"		=> __("Groups")." (".GroupePeer::getCountHomeGroups().")",
						"selected"	=> true
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@selection_list"),
						"label"		=> __("Selections")
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@favorite_list"),
						"label"		=> __("Favorites")
				)
		);

		array_push($breadCrumbs, array(
						"link"		=> path("@file_recent_list"),
						"label"		=> __("Recents")
				)
		);

		$this->getResponse()->setSlot('bread_crumbs', $breadCrumbs);

		$preferences = $this->getUser()->getPreferences("group/home", true, array("sort" => "activity_desc", "perPage" => 8));

		$this->getResponse()->setSlot("actions",$this->getPartial("public/breadcrumbActions", array(
			"results" => array("selected" => $preferences["perPage"], "values" => array(8 => 8, 16 => 16, 32 => 32, "all" => __("All"))),
			"sorts" => array("selected" => $preferences["sort"], "values" => array("name_asc" => __("Name ascending"), "name_desc" => __("Name descending"), "creation_asc" => __("Creation date ascending"), "creation_desc" => __("Creation date descending"), "activity_asc" => __("Last activity date ascending"), "activity_desc" => __("Last activity date descending")))
		)));

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeError()
	{
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGetIndicatif()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->getResponse()->setContentType('application/json');
			if(!$country = CountryPeer::retrieveByPk($this->getRequestParameter("id")))
				return $this->renderText(json_encode(array("errorCode" => 1, "message" => "Id : ".$this->getRequestParameter("id")." not found")));
			else
				return $this->renderText(json_encode(array("errorCode" => 0, "message" => "success", "phoneCode" => $country->getPhoneCode())));
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRedirect()
	{
		$this->forward404Unless($url = UrlPeer::retrieveByType($this->getRequestParameter("link")));
	
		return $this->redirect($url->getPath());
	}

	/*________________________________________________________________________________________________________________*/
	public function executeSaveOrderPreferences()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$i = 1;
			$tabs = $this->getRequestParameter("value");
	
			foreach($tabs as $tab)
			{
				$block = substr($tab, 5);
	
				if($preference = UserPreferencePeer::retrieveByUserAndTitle($this->getUser()->getId(), $block))
					$preference->setOrder($i);
				else
				{
					$preference = new UserPreference();
					$preference->setUserId($this->getUser()->getId());
					$preference->setTitle($block);
					$preference->setOrder($i);
				}
	
				$preference->save();
	
				$i++;
			}
	
			return sfView::NONE;
		}
	
		$this->redirect404();
	}
}
