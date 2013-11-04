<?php

/**
 * right actions.
 *
 * @package    wikipixel
 * @subpackage right
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class rightActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGetSubUsageRight()
	{
		if($this->getRequest()->isXmlHttpRequest())
		{
			$this->forward404Unless($usage_rights = UsageRightPeer::retrieveByPk($this->getRequestParameter("id")));
	
			if($subRights = $usage_rights->getSubRight())
			{
				$select = "";
				$input = "";
	
				foreach($subRights as $right)
				{
					if($right->getEditable())
					{
						switch($right->getType())
						{
							case UsageRightTypePeer::__TYPE_NUM:
								$input .= "<span class='usage-right-select text' rel='".$this->getRequestParameter("rel")."'>".$right->getTitle()." :<br />";
								$input .= "<input type='text' rel='num' name='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' id='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' style='width: 100%;' />";
							break;
	
							case UsageRightTypePeer::__TYPE_TEXT:
								$input .= "<span class='usage-right-select text' rel='".$this->getRequestParameter("rel")."'>".$right->getTitle()." :<br />";
								$input .= "<input type='text' rel='text' name='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' id='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' style='width: 100%;' />";
							break;
	
							case UsageRightTypePeer::__TYPE_DATE:
								$input .= "<span class='usage-right-select text' rel='".$this->getRequestParameter("rel")."'>".$right->getTitle()."";
								$input .= "<input type='hidden' rel='date' name='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' id='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' />:<br /><input type='text' style='width: 100%;' id='date_value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' name='date_value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' readonly />";
							break;
	
							case UsageRightTypePeer::__TYPE_GEO:
								$input .= "<span class='usage-right-select text' rel='".$this->getRequestParameter("rel")."'>".$right->getTitle()."";
								$input .= "<input type='hidden' rel='geo' name='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' id='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' />:<br /><input type='text' style='width: 100%;' id='geo_value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' name='geo_value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' readonly />";
							break;
	
							case UsageRightTypePeer::__TYPE_SUPPORT:
								$input .= "<span class='usage-right-select text' rel='".$this->getRequestParameter("rel")."'>".$right->getTitle()."";
								$input .= "<input type='hidden' rel='support' name='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' id='value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' />:<br /><input type='text' style='width: 100%;' id='support_value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' name='support_value_right_".$right->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' readonly />";
							break;
						}
	
						$input .= "</span><br />";
					}
					else
						$select .= "<option value='".$right->getId()."'>".$right."</option>";
				}
	
				if(!empty($select))
					$select = "	<div class='left'><b>".__("Constraint")." : </b></div><select name='right_".$usage_rights->getId()."' id='right_".$usage_rights->getId()."' class='usage-right-select left' rel='".$this->getRequestParameter("rel")."' style='width: 110px; margin-top: -4px; margin-left: 5px;'>
								<option value='-1'>------</option>".$select."</select>";
	
				$array = Array();
				$array["html"] = $input.$select;
				$array["type"] = !empty($select) ? "select" : "input";
	
				if(!empty($select))
					$array["id"] = "right_".$usage_rights->getId();
	
				return $this->renderText(json_encode($array));
			}
			elseif($usage_rights->getEditable())
			{	
				$text = "<span class='usage-right-select text' rel='".$this->getRequestParameter("rel")."'>".__("Value")." : <input type='text' name='value_right_".$usage_rights->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' id='value_right_".$usage_rights->getId().($this->getRequestParameter("file_id") ? "_".$this->getRequestParameter("file_id") : "")."' /></span>";
				return $this->renderText(json_encode(array("type" => "input", "html" => $text)));
			}
	
			return sfView::NONE;
		}
	
		$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadGeo()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$this->continents = ContinentPeer::getContinents();
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLoadSupport()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$this->supports = UsageSupportPeer::getSupports();
		
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @see supportAdd
	 */
	public function executeSaveSupport()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			$this->forward404();
		}
		
		$this->getResponse()->setContentType('application/json');

		if(!UsageSupportPeer::exists($this->getRequestParameter("support"), $this->getUser()->getCustomerId()))
		{
			$support = new UsageSupport();
			$support->setCustomerId($this->getUser()->getCustomerId());
			$support->setTitle($this->getRequestParameter("support"));

			$support->save();

			$input = '	<div>
							<input type="checkbox" name="support_'.$support->getId().'" id="support_'.
								$support->getId().'" rel="'.$support->getTitle().'" value="'.
								$support->getId().'" class="left check_support" style="margin-right: 5px;" />
							<label for="support_'.$support->getId().'" style="width: auto;">'.
							$support->getTitle().'</label>
						</div>';

			return $this->renderText(json_encode(array("code" => 0, "html" => $input)));
		}
		else
			return $this->renderText(json_encode(array("code" => 1, "html" => __("Support already exists."))));
	}

	/*________________________________________________________________________________________________________________*/
	public function executeGetCreativeCommons()
	{
		$creative_commons = CreativeCommonsPeer::retrieveByPk($this->getRequestParameter("value"));
		$this->getResponse()->setContentType('application/json');

		return $this->renderText(json_encode(array("img" => $creative_commons->getImagePath(), 
				"description" => $creative_commons->getDescription())));
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeSupportAdd(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}
	
		$response = $this->getResponse();
		$response->setContentType("application/json");
		
		$name = $request->getParameter("name");
		$errors = array();
		
		if (empty($name)) {
			$errors[] = array("code" => 1, "message" => __("Name is required."));
		}
		elseif (UsageSupportPeer::exists($name, $this->getUser()->getCustomerId())) {
			$errors[] = array("code" => 2, "message" => __("Support already exists."));
		}

		if (count($errors)) {
			$response->setStatusCode(400);

			return $this->renderText(json_encode(array("errors" => $errors)));
		}
		else {
			$support = new UsageSupport();
			
			$support->setCustomerId($this->getUser()->getCustomerId());
			$support->setTitle($name);
	
			$support->save();
		}

		return $this->renderText(json_encode(array("html" => $this->getComponent("right", "supportList"))));
	}
}