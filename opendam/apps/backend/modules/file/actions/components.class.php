<?php

class fileComponents extends sfComponents
{
  /*public function executeHome()
  {
	if(!$this->getUser()->getAttribute("file_home_page"))
		$this->getUser()->setAttribute("file_home_page", 1);
	if(!$this->getUser()->getAttribute("file_home_item_page"))
		$this->getUser()->setAttribute("file_home_item_page", 5);
  }*/

  public function executeCustomDownload()
  {
	if($this->file->getType() == FilePeer::__TYPE_PHOTO)
		$this->formats = explode(";",ConfigurationPeer::retrieveByType("_picture_convert_to")->getValue());

	if($this->file->getType() == FilePeer::__TYPE_VIDEO)
		$this->formats = explode(";",ConfigurationPeer::retrieveByType("_video_convert_to")->getValue());

	asort($this->formats);
  }

  public function executeCopyrightSelected()
  {
	$this->distributions = UsageDistributionPeer::getDistributions();
	$this->limitations = UsageLimitationPeer::getLimitations();
	$this->licences = LicencePeer::getLicenceInArray();
	$this->uses = UsageUsePeer::getUses();
  }
}