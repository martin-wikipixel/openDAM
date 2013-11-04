<?php

class favoriteComponents extends sfComponents
{
  public function executeHome()
  {
    $this->getUser()->setAttribute("favorites_sort", "name_asc");
    $this->getUser()->setAttribute("favorite_home_page", 1);
	if(!$this->getUser()->getAttribute("favorite_home_item_page"))
		$this->getUser()->setAttribute("favorite_home_item_page", 5);
  }
}