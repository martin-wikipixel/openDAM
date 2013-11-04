<?php

/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class downloadActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		set_time_limit(0);
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDownload()
	{
		$this->forward404Unless($file = FilePeer::retrieveByPK($this->getRequestParameter("id")));

		$albumId = $file->getGroupeId();
		$folderId = $file->getFolderId();

		$this->forward404Unless($roleGroup = $this->getUser()->getRole($albumId));
		$this->forward404Unless($this->getUser()->getRole($albumId, $folderId));

		if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH)
			$this->redirect404();

		$path = $file->getPath();

		switch ($this->getRequestParameter("definition"))
		{
			case "original": $filename = $file->getOriginal(); break;
			case "web": 
				if ($file->getType() != FilePeer::__TYPE_PHOTO) {
					$filename = $file->getOriginal();
				}
				else {
					$filename = $file->getWeb();
				}
			break;
			default: return false;
		}

		myTools::addTags($file);

		$this->forward404Unless($filename);

		LogPeer::setLog($this->getUser()->getId(), $file->getId(), "file-download", "3");

		// download single file
		$path.=$filename;

		if(file_exists($path))
		{
			$download = new Httpdownload();
			$download->setInline(false);
			$download->setFilePath($path);
			$download->setFilename($filename);
			$download->executeDownload();
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	/*
	 * TODO a deplacer ou renommer
	 * 
	 * Uniquement utilisé pour la partie publique !!
	*/
	public function executeDownloadFile(sfWebRequest $request)
	{
		$definition = $request->getParameter("definition", "original");
		
		$file = FilePeer::retrieveByPK($this->getRequestParameter("id"));
		$this->forward404Unless($file);
	
		/*
		if ($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH) {
			$this->forward404();
		}*/
		
		$permalink = PermalinkPeer::getByLink($this->getRequestParameter("permalink_id"));
		$this->forward404Unless($permalink);
		
		// check rights
		$haveAccess = false;

		switch ($permalink->getObjectType()) {
			// permalien via un album : on verifie que le fichier appartient à un dossier de l'album
			case PermalinkPeer::__OBJECT_GROUP:
				if ($permalink->getObjectId() == $file->getGroupeId()) {// appartient a album courant (racine)
					$haveAccess = true;
				}
				else {// un des dossiers parents du fichier doit appartenir à album du permalien courant
					$currentFolder = $file->getFolder();
						
					while ($currentFolder !== null && !$haveAccess) {
						$haveAccess = $permalink->getObjectId() == $currentFolder->getGroupeId();
						$currentFolder = $currentFolder->getParent();
					}
				}
				break;
			// permalien via un dossier : on verifie que le fichier appartient au dossier ou a un sous-dossier
			case PermalinkPeer::__OBJECT_FOLDER:
				if ($permalink->getObjectId() == $file->getFolderId()) {// appartient au dossier courant (racine)
					$haveAccess = true;
				}
				else {// un des dossiers parents du fichier doit être le permalien courant
					$currentFolder = $file->getFolder();
					
					while ($currentFolder !== null && !$haveAccess) {
						$haveAccess = $permalink->getObjectId() == $currentFolder->getId();
						$currentFolder = $currentFolder->getParent();
					}
				}
				break;
			// permalien direct a un fichier
			case PermalinkPeer::__OBJECT_FILE:
				$haveAccess = $permalink->getObjectId() == $file->getId();
				break;

			default:
				$haveAccess = true;
		}
		
		if (!$haveAccess) {
			$this->forward404();
		}
		
		$path = $file->getPath().DIRECTORY_SEPARATOR;
		
		switch ($definition) {
			case "original": 
				$filename = $file->getOriginal();
				break;

			case "web":
				if ($file->getType() != FilePeer::__TYPE_PHOTO) {
					$filename = $file->getOriginal();
				}
				else {
					$filename = $file->getWeb();
				}
				break;
		}
	
		//myTools::addTags($file);
	
		$path .= $filename;
	
		LogPeer::setLog(null, $file->getId(), "file-download", "3", array(), $file->getCustomerId());
	
		$download = new Httpdownload();

		$download->setInline(false);
		$download->setFilePath($path);
		$download->setFilename($filename);
		$download->executeDownload();
	
		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeCustomDownload()
	{
		$original = false;
		$cmd = null;

		$this->forward404Unless($file = FilePeer::retrieveByPk($this->getRequestParameter("id")));

		if($file->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH)
			$this->redirect404();

		if($file->getType() == FilePeer::__TYPE_PHOTO)
			$formats = explode(";",ConfigurationPeer::retrieveByType("_picture_convert_to")->getValue());

		if($file->getType() == FilePeer::__TYPE_VIDEO)
			$formats = explode(";",ConfigurationPeer::retrieveByType("_video_convert_to")->getValue());

		$this->forward404Unless(in_array($this->getRequestParameter("format"), $formats));
		$this->forward404Unless($this->getRequestParameter("width") > 0);
		$this->forward404Unless($this->getRequestParameter("height") > 0);

		if($file->getType() == FilePeer::__TYPE_VIDEO)
		{
			$temp = explode("|", $this->getRequestParameter("format"));

			if(array_key_exists(1, $temp))
			{
				switch($temp[1])
				{
					case "Divx":
						$cmd = "-vcodec msmpeg4v2 -acodec libfaac";
					break;

					case "Xvid":
						$cmd = "-vcodec libxvid -acodec libfaac";
					break;

					case "H.264":
						$cmd = "-vcodec libx264 -acodec libfaac";
					break;

					default:
						$cmd = "-target dvd -acodec libfaac";
					break;
				}
			}
		}

		$file_name = time().".".$this->getRequestParameter("format");
		$path = sfConfig::get("app_path_temp_dir");

		if($file->getWidth() == $this->getRequestParameter("width") && $file->getHeight() == $this->getRequestParameter("height") && $this->getRequestParameter("format") == $file->getExtention())
		{
			$file_name = $file->getOriginal();
			$path = $file->getPath();

			$original = true;
		}
		else
		{
			$file_name = time().".".$this->getRequestParameter("format");
			$path = sfConfig::get("app_path_temp_dir");

			myTools::addTags($file);

			switch($file->getType())
			{
				case FilePeer::__TYPE_PHOTO:
					// if($file->getWidth() == $this->getRequestParameter("width") && $file->getHeight() == $this->getRequestParameter("height"))
						// shell_exec("convert ".escapeshellarg($file->getPath()."/".$file->getOriginal())." ".escapeshellarg($path."/".$file_name));
					// else
						shell_exec("convert ".escapeshellarg($file->getPath()."/".$file->getOriginal())." -resize ".$this->getRequestParameter("width")."x".$this->getRequestParameter("height")."\! ".escapeshellarg($path."/".$file_name));
				break;

				case FilePeer::__TYPE_VIDEO:
					if($file->getWidth() == $this->getRequestParameter("width") && $file->getHeight() == $this->getRequestParameter("height"))
						shell_exec("ffmpeg -i ".escapeshellarg($file->getPath()."/".$file->getOriginal())." ".($cmd ? $cmd : "")." -y ".escapeshellarg($path."/".$file_name));
					else
						shell_exec("ffmpeg -i ".escapeshellarg($file->getPath()."/".$file->getOriginal())." -s ".$this->getRequestParameter("width")."x".$this->getRequestParameter("height")." ".($cmd ? $cmd : "")." -y ".escapeshellarg($path."/".$file_name));
				break;
			}
		}

		if(file_exists($path."/".$file_name))
		{
			$download = new Httpdownload();
			$download->setInline(false);
			$download->setFilePath($path."/".$file_name);
			$download->setFilename($file_name);
			$download->executeDownload();
		}

		return sfView::NONE;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeDownloadNotice()
	{
		sfConfig::set('sf_debug', false);

		$fileId = $this->getRequestParameter("id");
		$limitations = UsageLimitationPeer::getLimitations();
		$file = FilePeer::retrieveByPK($fileId);

		$this->forward404Unless($file);

		$shootingDate = null;
		$path = sfConfig::get("app_path_temp_dir")."/".time().".txt";
		$fileName = myTools::getFileNameFile($file->getOriginal()).".txt";

		if ($exif = ExifPeer::getTag("Author", $file->getId())) {
			$author = $exif->getValue();
		}
		elseif ($iptc = IptcPeer::getTag("Writer/Editor", $file->getId())) {
			$author = $iptc->getValue();
		}
		else {
			$author = null;
		}

		if (!preg_match('/[a-zA-Z0-9]/', $author)) {
			$author = null;
		}

		$notice = "";

		if ($file->getName()) {
			$notice .= __("Name:")." ".$file->getName()."\n";
		}

		if ($file->getDescription()) {
			$notice .= __("Description:")." ".$file->getDescription()."\n";
		}

		if ($file->getExtention()) {
			$notice .= __("Format:")." ".$file->getExtention()."\n";
		}

		if ($file->getSize()) {
			$notice .= __("Size:")." ".MyTools::getSize($file->getSize())."\n";
		}

		if ($file->getCreatedAt()) {
			$notice .= __("Uploaded at:")." ".DateTimeUtils::formatDate($file->getCreatedAt())."\n";
		}

		if ($author) {
			$notice .= __("Author:")." ".$author."\n";
		}

		if ($file->getSource()) {
			$notice .= __("Source:")." ".$file->getSource()."\n";
		}

		if ($file->getType() == FilePeer::__TYPE_PHOTO) {
			if ($exif = ExifPeer::getTag("DateTimeOriginal", $file->getId())) {
				$shootingDate = $exif->getValue();
			}
			elseif ($iptc = IptcPeer::getTag("Date Created", $file->getId())) {
				$shootingDate = $iptc->getValue();
			}
		}

		if ($shootingDate) {
			$notice .= __("Shooting date:")." ".DateTimeUtils::formatDateTime($shootingDate)."\n";
		}

		if ($file->getLicenceId()) {
			$notice .= __("Licence:")." ".$file->getLicence()->getName()."\n";
		}

		if ($file->getLicenceId() == LicencePeer::__CREATIVE_COMMONS) {
			$notice .= __("Creative commons:")." ".$file->getCreativeCommons()->getTitle()."\n";
		}

		if ($file->getUsageUseId()) {
			$notice .= __("Usage:")." ".$file->getUsageUse()->getTitle()."\n";
		}

		if ($file->getUsageDistributionId()) {
			$notice .= __("Distribution:")." ".$file->getUsageDistribution()->getTitle()."\n";
		}

		foreach($limitations as $limitation) {
			$rightValue = null;

			$fileRight = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), FileRightPeer::__TYPE_FILE,
					$limitation->getId());

			if ($fileRight && $fileRight->getValue()) {
				switch ($limitation->getUsageTypeId()) {
					case UsageTypePeer::__TYPE_GEO:
						$countries = explode(";", $fileRight->getValue());
						$ids = array();

						foreach ($countries as $countryId) {
							if (!empty($countryId)) {
								$country = CountryPeer::retrieveByPk($countryId);
								$rightValue .= $country->getTitle().", ";
								$ids[] = $country->getId();
							}
						}

						$rightValue = substr($rightValue, 0, -2);

						if ($text = ContinentPeer::referToContinent($ids)) {
							$rightValue = $text;
						}
					break;

					case UsageTypePeer::__TYPE_SUPPORT:
						$supports = explode(";", $fileRight->getValue());

						foreach ($supports as $supportId) {
							if (!empty($supportId)) {
								$support = UsageSupportPeer::retrieveByPk($supportId);
								$rightValue .= $support->getTitle().", ";
							}
						}

						$rightValue = substr($rightValue, 0, -2);
					break;

					case UsageTypePeer::__TYPE_BOOLEAN:
						if ($fileRight->getValue()) {
							$rightValue = __("Yes");
						}
						else {
							$rightValue = __("No");
						}
					break;

					default:
						$rightValue = $fileRight->getValue();
					break;
				}

				$notice .= __("%limitation%:", array("%limitation%" => $limitation->getTitle()))." ".$rightValue."\n";
			}
		}

		$handle = fopen($path, "a+");

		if (fwrite($handle, $notice) !== false) {
			$download = new Httpdownload();
			$download->setInline(false);
			$download->setFilePath($path);
			$download->setFilename($fileName);
			$download->executeDownload();
		}

		return sfView::NONE;
	}
}
