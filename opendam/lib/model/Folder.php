<?php

/**
 * Subclass for representing a row from the 'folder' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Folder extends BaseFolder
{
	protected $role = 0;
	protected $customer_id;

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi true si le dossier est verrouiller.
	 * @return boolean
	 */
	public function isLocked()
	{
		return $this->getFree() == 0;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * L'id du dossier parent (alias de getSubfolderId).
	 *
	 * @return number
	 */
	public function getParentId()
	{
		return $this->getSubfolderId();
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Le dossier parent (alias de getSubfolderId)
	 *
	 * @return Folder
	 */
	public function getParent()
	{
		return $this->getFolderRelatedBySubfolderId();
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Chemin du dossier (sans le nom).
	 *
	 * @param string $album
	 *
	 * @return string
	 */
	public function getPath($album = true)
	{
		$path = "";
		$current = $this;
	
		do {
			$parent = $current->getParent();
	
			if ($parent) {
				$path = $parent."/".$path;
			}
	
			$current = $parent;
		}
		while($parent);
	
		if ($album) {
			$album = GroupePeer::retrieveByPK($this->getGroupeId());
				
			$path = $album."/".$path;
		}
	
		return mb_substr($path, 0, mb_strlen($path)-1);
	}
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * Chemin et nom du dossier.
	 *
	 * @param string $univers
	 *
	 * @return string
	 */
	public function getPathname($album = true)
	{
		return $this->getPath($album)."/".$this->getName();
	}

	public function getPathToSave($absolute = true)
	{
		if($absolute)
			return sfConfig::get("app_path_upload_dir")."/".$this->getDisk()->getPath()."/cust-".
			$this->getCustomerId()."/folder-".$this->getId();
		else
			return sfConfig::get("app_path_upload_dir_name")."/".$this->getDisk()->getPath()."/cust-".
			$this->getCustomerId()."/folder-".$this->getId();
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path (disk) du fichier.
	 */
	public function getRealPath($absolute = true)
	{
		if($absolute)
			return sfConfig::get("app_path_upload_dir")."/".$this->getDisk()->getPath()."/cust-".
			$this->getCustomerId()."/folders";
		else
			return sfConfig::get("app_path_upload_dir_name")."/".$this->getDisk()->getPath()."/cust-".
			$this->getCustomerId()."/folders";
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le type mime du fichier.
	 *
	 * @return string
	 */
	public function getContentType($charset = false)
	{
		Assert::ok(file_exists($this->getRealPathname()));
	
		$finfo = new Finfo();
	
		return $finfo->file($this->getRealPathname(), $charset ? FILEINFO_MIME : FILEINFO_MIME_TYPE);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path + le nom du fichier (disk).
	 *
	 * @return string
	 */
	public function getRealPathname()
	{
		return $this->getRealPath().DIRECTORY_SEPARATOR.$this->getThumbnail();
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi true si le fichier existe sur le dique.
	 *
	 * @return string
	 */
	public function exists()
	{
		return file_exists($this->getRealPathname());
	}
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	/*________________________________________________________________________________________________________________*/
	public function getSize($recursive = true)
	{
		if ($recursive) {
			return FilePeer::countSizeFiles($this->getId());
		}
		else {
			$connection = Propel::getConnection();

			$query = "SELECT sum(file.size) as total
				FROM file
				WHERE file.state = ".$connection->quote(FilePeer::__STATE_VALIDATE)."
				AND file.folder_id = ".$connection->quote($this->getId());

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			return $rs[0]["total"];
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function getNumberOfFiles($recursive = true)
	{
		if ($recursive) {
			return FilePeer::countFiles($this->getId());
		}
		else {
			$c = new Criteria();
			
			$c->add(FilePeer::FOLDER_ID, $this->getId());
			$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);

			return FilePeer::doCount($c);
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function getNumberOfFolders($recursive = true, $api = false, $user = null)
	{
		if ($recursive) {
			return FolderPeer::countFolders($this->getId());
		}
		else {
			$c = new Criteria();
			
			$c->add(FolderPeer::SUBFOLDER_ID, $this->getId());
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

			if(!$api)
				return FolderPeer::doCount($c);
			else
			{
				$folders = FolderPeer::doSelect($c);
				$countFolders = 0;

				if(!in_array($user->getRoleId(), Array(RolePeer::__ADMIN)))
				{
					foreach($folders as $folder)
					{
						if($folder->getFree())
							$countFolders++;
						else
						{
							$c = new Criteria();
							$c->add(UserFolderPeer::USER_ID, $user->getId());
							$c->add(UserFolderPeer::FOLDER_ID, $folder->getId());

							$userFolder = UserFolderPeer::doSelectOne($c);

							if($userFolder)
								$countFolders++;
						}
					}
				}
				else
					$countFolders = count($folders);

				return $countFolders;
			}
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function getTagsInside($limit = null, $offset = null)
	{
		$connection = Propel::getConnection();

		$folders = FolderPeer::getRecursiveFoldersId($this->getId());
		$folders[] = $this->getId();

		$query = "	SELECT tag.*, count(tag.id) as rate
					FROM tag, file_tag
					WHERE tag.id = file_tag.tag_id
					AND (
							(
								file_tag.type = ".FileTagPeer::__TYPE_FOLDER."
								AND file_tag.file_id IN (	SELECT folder.id
															FROM folder
															WHERE folder.subfolder_id IN (".implode(",", $folders).")
															AND folder.state = ".FolderPeer::__STATE_ACTIVE.")
							) OR
							(
								file_tag.type = ".FileTagPeer::__TYPE_FILE."
								AND file_tag.file_id IN (	SELECT file.id
															FROM file
															WHERE file.folder_id IN (".implode(",", $folders).")
															AND file.state = ".FilePeer::__STATE_VALIDATE.")
							)
					)
					GROUP BY tag.id
					ORDER BY rate DESC";

		if(!empty($limit) || !empty($offset))
			$query .= "	LIMIT ".$offset.",".$limit;

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM); 

		$tags = array();
		while ($rs = $statement->fetch())
		{
			$tag = new Tag();
			$tag->hydrate($rs);
			$tags[] = serialize($tag);
		}

		$statement->closeCursor();
		$statement = null;

		return array_map("unserialize", $tags);
	}

	/*________________________________________________________________________________________________________________*/
	public function setRole($role)
	{
		$this->role = $role;
	}

	/*________________________________________________________________________________________________________________*/
	public function getRole()
	{
		return $this->role;
	}

	/*________________________________________________________________________________________________________________*/
	public function isEmpty()
	{
		$c = new Criteria();
		$c->add(FilePeer::FOLDER_ID, $this->getId());
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);

		return FilePeer::doCount($c) > 0 ? false : true;
	}

	/*________________________________________________________________________________________________________________*/
	public function getAllFiles($recurs = true)
	{
		$c = new Criteria();
		$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);

		if ($recurs) {
			$folders = Array();
			$folders = FolderPeer::retrieveAllSubfolder($this->getId());
			$folders[$this->getId()] = $this->getId();

			$c->add(FilePeer::FOLDER_ID, $folders, Criteria::IN);
		}
		else
			$c->add(FilePeer::FOLDER_ID, $this->getId());

		return FilePeer::doSelect($c);
	}

	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		return $this->getName();
	}

	/*________________________________________________________________________________________________________________*/
	public function getTags()
	{
		$file_tags = FileTagPeer::retrieveByFileIdType(2, $this->getId());
	
		$tags_array = array();
		
		foreach ($file_tags as $file_tag){
			$tags_array[] = $file_tag->getTag();
		}

		return $tags_array;
	}

	/*________________________________________________________________________________________________________________*/
	public function getCustomerId()
	{
		if ($this->customer_id == null) {
			$group = GroupePeer::retrieveByPKNoCustomer($this->getGroupeId());
			$this->customer_id = $group->getCustomerId();
		}

		return $this->customer_id;
	}

	/*________________________________________________________________________________________________________________*/
	public function save(PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(FolderPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$con->beginTransaction();
		try
		{
			if($this->isNew())
				$this->setFree(true);

			if($this->getSubfolderId() == $this->getId())
				$this->setSubfolderId(null);

			$ret = parent::save($con);

			$con->commit();

			return $ret;
		}
		catch (Exception $e)
		{
			$con->rollBack();
			throw $e;
		}
	}
}
