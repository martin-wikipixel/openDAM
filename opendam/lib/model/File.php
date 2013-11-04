<?php

/**
 * Subclass for representing a row from the 'file' table.
 *
 * 
 *
 * @package lib.model
 */ 
class File extends BaseFile
{
	protected $customer_id;

	/*________________________________________________________________________________________________________________*/
	public function isFirstFileOfGroup()
	{
		$connection = Propel::getConnection();

		$query = "	SELECT file.id as id
					FROM file
					WHERE file.groupe_id = ".$this->getGroupeId()."
					AND file.state = ".FilePeer::__STATE_VALIDATE."
					ORDER BY file.created_at ASC
					LIMIT 0, 1";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$result = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		if($result && $result[0]["id"] == $this->getId())
			return true;
		else
			return false;
	}

	/*________________________________________________________________________________________________________________*/
	public function hasLimitations()
	{
		$c = new Criteria();
		$c->add(FileRightPeer::OBJECT_ID, $this->getId());
		$c->add(FileRightPeer::TYPE, 3);

		return FileRightPeer::doCount($c) > 0 ? true : false;
	}

	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		return $this->getName() ? $this->getName() : $this->getOriginal();
	}

	/*________________________________________________________________________________________________________________*/
	public function hasCopyright()
	{
		if($exif = ExifPeer::getTag("Author", $this->getId()))
			$author = myTools::longword_break_old($exif->getValue(), 22);
		elseif($iptc = IptcPeer::getTag("Writer/Editor", $this->getId()))
			$author = myTools::longword_break_old($iptc->getValue(), 22);
		else
			$author = "";

		if(!preg_match('/[a-zA-Z0-9]/', $author))
			$author = "";

		$source = $this->getSource();
		$licence = $this->getLicenceId();
		$distribution = $this->getUsageDistributionId();
		$constraint = $this->getUsageConstraintId();
		$use = $this->getUsageUseId();
		$commercial = $this->getUsageCommercialId();
		$creative_commons = $this->getCreativeCommonsId();
		$usage_rights = FileRightPeer::retrieveByType($this->getId(), '3');

		if(
			!empty($author) ||
			!empty($source) ||
			!empty($licence) ||
			!empty($distribution) ||
			!empty($constraint) ||
			!empty($use) ||
			!empty($commercial) ||
			!empty($creative_commons) ||
			!empty($usage_rights)
		)
			return true;
		else
			return false;
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le nom du fichier (disk) sans l'extension.
	 */
	public function getFileName()
	{
		return substr($this->getOriginal(), 0, strrpos($this->getOriginal(), "."));
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path (disk) du fichier.
	 */
	public function getPath($absolute = true)
	{
		if($absolute)
			return sfConfig::get("app_path_upload_dir")."/".$this->getDisk()->getPath()."/cust-".
				$this->getCustomerId()."/folder-".$this->getFolderId();
		else
			return sfConfig::get("app_path_upload_dir_name")."/".$this->getDisk()->getPath()."/cust-".
				$this->getCustomerId()."/folder-".$this->getFolderId();
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path + le nom du fichier (disk).
	 * 
	 * @return string
	 */
	public function getPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal();
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path + le nom du fichier (en base de données).
	 *
	 * @return string
	 */
	public function getVirtualPathname()
	{
		return $this->getFolder()->getPathname()."/".$this->getName();
		//return $this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal();
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi true si le fichier existe sur le dique.
	 *
	 * @return string
	 */
	public function exists()
	{
		return file_exists($this->getPathname());
	}

	public function existsThumb100()
	{
		if (!$this->getThumb100()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb100()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb100());
	}
	
	public function getThumb100Pathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumb100();
	}

	public function existsThumb200()
	{
		if (!$this->getThumb200()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb200()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb200());
	}

	public function getThumb200Pathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumb200();
	}

	public function existsThumbWeb()
	{
		if (!$this->getWeb()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getWeb()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getWeb());
	}

	public function getThumbWebPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getWeb();
	}

	public function existsThumb400()
	{
		if (!$this->getThumb400()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb400()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb400());
	}

	public function getThumb400Pathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumb400();
	}

	public function existsThumb400W()
	{
		if (!$this->getThumb400W()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb400W()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumb400W());
	}
	
	public function getThumb400WPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumb400W();
	}

	public function existsThumbMob()
	{
		if (!$this->getThumbMob()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbMob()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbMob());
	}

	public function getThumbMobPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumbMob();
	}

	public function existsThumbMobW()
	{
		if (!$this->getThumbMobW()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbMobW()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbMobW());
	}

	public function getThumbMobWPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumbMobW();
	}

	public function existsThumbTab()
	{
		if (!$this->getThumbTab()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbTab()) &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbTab());
	}

	public function getThumbTabPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumbTab();
	}

	public function existsThumbTabW()
	{
		if (!$this->getThumbTabW()) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbTabW()) &&
				id_file($this->getPath().DIRECTORY_SEPARATOR.$this->getThumbTabW());
	}

	public function getThumbTabWPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumbTabW();
	}

	public function existsVideoMp4()
	{
		if ($this->getType() != FilePeer::__TYPE_VIDEO) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToMp4.mp4") && 
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToMp4.mp4");
	}

	public function getVideoMp4Pathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToMp4.mp4";
	}

	public function existsVideoWebm()
	{
		if ($this->getType() != FilePeer::__TYPE_VIDEO) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToWebm.webm") &&
				is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToWebm.webm");
	}

	public function getVideoWebmPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToWebm.webm";
	}

	public function existsAudioMp3()
	{
		if ($this->getType() != FilePeer::__TYPE_AUDIO) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToMp3.mp3") &&
		is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToMp3.mp3");
	}

	public function getAudioMp3Pathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToMp3.mp3";
	}

	public function existsAudioWav()
	{
		if ($this->getType() != FilePeer::__TYPE_AUDIO) {
			return false;
		}

		return file_exists($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToWav.wav") &&
		is_file($this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToWav.wav");
	}

	public function getVideoWavPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getOriginal()."ToWav.wav";
	}
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le type mime du fichier.
	 * 
	 * @return string
	 */
	public function getContentType($charset = false)
	{
		Assert::ok(file_exists($this->getPathname()));

		$finfo = new Finfo();
		
		return $finfo->file($this->getPathname(), $charset ? FILEINFO_MIME : FILEINFO_MIME_TYPE);
	}
	
	public function getContentTypeForThumbnail($path, $charset = false)
	{
		Assert::ok(file_exists($path));
	
		$finfo = new Finfo();
	
		return $finfo->file($path, $charset ? FILEINFO_MIME : FILEINFO_MIME_TYPE);
	}
	
	/*________________________________________________________________________________________________________________*/
	public function getTags()
	{
		$file_tags = FileTagPeer::retrieveByFileIdType(3, $this->getId());

		$tags_array = array();
		
		foreach ($file_tags as $file_tag)
		{
			$tags_array[] = $file_tag->getTag()->getTitle();
		}

		return $tags_array;
	}

	/*________________________________________________________________________________________________________________*/
	public function getCustomerId()
	{
		if($this->customer_id === null)
		{
			$group = GroupePeer::retrieveByPKNoCustomer($this->getGroupeId());
			$this->customer_id = $group->getCustomerId();
		}
		return $this->customer_id;
	}

	/*________________________________________________________________________________________________________________*/
	public function save(PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(FilePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$this->setUpdatedAt(time());
		
		$con->beginTransaction();
		try
		{
			if($this->isNew())
				$this->setChecksum(sha1_file($this->getPath().'/'.$this->getOriginal()));

			if($this->getUsageDistributionId() == UsageDistributionPeer::__UNAUTH)
			{
				$this->setFolderCover(false);
				$this->setGroupeCover(false);
			}

			$ret = parent::save($con);
			$new = false;

			$con->commit();

			if(sfContext::getInstance()->getUser()->isAuthenticated())
			{
				if($this->isFirstFileOfGroup())
				{
					$groupe = $this->getGroupe();

					if(!$groupe->getDiskId())
					{
						$groupe->setDiskId(sfContext::getInstance()->getUser()->getDisk()->getId());
						$groupe->save();
					}

					$path = sfConfig::get('app_path_upload_dir')."/".$groupe->getDisk()->getPath()."/cust-".$groupe->getCustomerId()."/groups";
					
					if(file_exists($path))
					{
						if(file_exists($this->getPath()."/".$this->getOriginal()) && is_file($this->getPath()."/".$this->getOriginal()))
						{
							if($this->getType() == FilePeer::__TYPE_PHOTO)
							{
								$cover = ImageTools::setThumbnailForFolder($path, $this);

								$groupe->setThumbnail($cover);
								$groupe->save();
							}
						}
					}
				}
			}

			return $ret;
		}
		catch (Exception $e)
		{
			$con->rollBack();
			throw $e;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function delete(PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(FilePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$con->beginTransaction();
		try
		{
			$ret = parent::delete($con);

			$favorites = FavoritesPeer::retrieveByObject(2, $this->getId());
			foreach($favorites as $favorite)
				$favorite->delete();

			$con->commit();

			return $ret;
		}
		catch (Exception $e)
		{
			$con->rollBack();
			throw $e;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function getUser(PropelPDO $con = null)
	{
		if ($this->aUser === null && ($this->user_id !== null)) {
			$this->aUser = UserPeer::retrieveByPKNoCustomer($this->user_id);
		}
		return $this->aUser;
	}
}