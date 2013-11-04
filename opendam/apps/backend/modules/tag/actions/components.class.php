<?php

class tagComponents extends sfComponents
{
  public function executeHome()
  {
	if(!$this->getUser()->getAttribute("tag_home_page"))
		$this->getUser()->setAttribute("tag_home_page", 1);
	if(!$this->getUser()->getAttribute("tag_home_item_page"))
		$this->getUser()->setAttribute("tag_home_item_page", 20);
  }
}