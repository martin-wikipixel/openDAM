<?php

/**
 * news actions.
 *
 * @packagesf_sandbox
 * @subpackage news
 * @author Your name here
 * @versionSVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class rateActions extends sfActions
{
	// TODO: check user rights ?
	public function executeRate()
	{
		$file_id = $this->getRequestParameter('file_id');
		$star= $this->getRequestParameter('star');
		$star = (int) ($star > 0 && $star < 6) ? $star : 3;
		
		$this->forward404Unless($file = FilePeer::retrieveByPK($file_id));
		$this->forward404Unless(UserPeer::isAllowed($file_id, "file"));
	
		$rate = RatingPeer::getFileRate($file_id);
		$is_rated = false;
		
		if (!$rate)
		{
			$rate = new Rating();
			$rate->setFileId($file_id);
		}
		else{
			// user has already rated this file
			$user_ids = explode(", ", $rate->getUserIds());
			if(in_array($this->getUser()->getId(), $user_ids)) $is_rated = true;
		}
		
		// user haven't rated this file yet
		if (!$is_rated)
		{
			$rate->setNbRate((int) ($rate->getNbRate() + 1));
			$rate->setTotalRate((int) ($rate->getTotalRate() + $star));
			$rate->setUserIds($rate->getUserIds() ? $rate->getUserIds().', '.
					$this->getUser()->getId() : $this->getUser()->getId());
			$rate->save();
			
			// save file average point
			$file->setAveragePoint(($rate->getTotalRate()/$rate->getNbRate()));
			$file->save();
			
			$is_rated = true;
		}
		
		$this->file_id = $file_id;
		
		return sfView::SUCCESS;
	}
}