<?php

/**
 * preset actions.
 *
 * @package    wikipixel
 * @subpackage preset
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class presetActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}


	/*________________________________________________________________________________________________________________*/
	public function executeExistsPresetName()
	{
	if($this->getRequest()->isXmlHttpRequest())
	{
		$this->getResponse()->setContentType('application/json');
		$preset = PresetPeer::retrieveByNameAndCustomerId($this->getRequestParameter('name'), 
				$this->getUser()->getCustomerId());

		return $preset ? $this->renderText(json_encode(array("code" => 1, "msg" => "exists"))) : 
			$this->renderText(json_encode(array("code" => 0, "msg" => "success")));
	}

	$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	public function executeApplyPreset()
	{
		if (!$this->getRequest()->isXmlHttpRequest()){
			$this->forward404();
		}

		$this->forward404Unless($preset = PresetPeer::retrieveByPk($this->getRequestParameter("id")));
		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("file_id")));
		$this->forward404Unless($roleGroup = $this->getUser()->getRole($file->getGroupeId()));
		$this->forward404Unless($roleGroup < RolePeer::__ADMIN || 
				($roleGroup == RolePeer::__ADMIN && ($this->getUser()->hasCredential("admin") 
						|| $this->getUser()->getConstraint($file->getGroupeId(), 
								ConstraintPeer::__UPDATE, RolePeer::__ADMIN) 
						|| $file->getFolder()->getUserId() == $this->getUser()->getId())) 
				|| ($roleGroup == RolePeer::__CONTRIB && $this->getUser()->getConstraint($file->getGroupeId(), 
						ConstraintPeer::__USER, RolePeer::__CONTRIB) 
			&& ($this->getUser()->getConstraint($file->getGroupeId(), ConstraintPeer::__UPDATE_FILE_FOLDER, RolePeer::__CONTRIB) 
					|| $file->getFolder()->getUserId() == $this->getUser()->getId())));

		$file->setLicenceId($preset->getLicenceId());
		$file->setUsageDistributionId($preset->getUsageDistributionId());
		$file->setUsageUseId($preset->getUsageUseId());
		$file->setCreativeCommonsId($preset->getCreativeCommonsId());

		$file->save();

		if($preset_right = FileRightPeer::retrieveByType($preset->getId(), 4))
		{
			$file_right = FileRightPeer::retrieveByType($file->getId(), 3);

			if($file_right)
				$file_right->delete();

			$new_file_right = $preset_right[0]->copy(true);
			$new_file_right->setType(3);
			$new_file_right->setObjectId($file->getId());

			$new_file_right->save();
		}

		$this->getUser()->setFlash("success", __("\"%1%\" preset's was successfully applyed.", array("%1%" => $preset->getName())), true);

		return $this->renderText("");
	}
}
