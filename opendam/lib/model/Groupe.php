<?php

/**
 * Subclass for representing a row from the 'groupe' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Groupe extends BaseGroupe
{
	protected $role_group = 0;
	protected $first_folder = null;

	/*________________________________________________________________________________________________________________*/
	/**
	 * Le nombre de fichier et la taille occupé peut être connu en même temps, pour un but d'optimisation, on récupère
	 * les deux en mêmes temps que l'on stocke dans les variables suivantes :
	 * 
	 * @see populateSizeAndCount
	 */
	protected $_fileCount = null;
	protected $_size = null;

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le credential de l'album.
	 * 
	 * @return Role
	 */
	public function getCredential()
	{
		return $this->getRole();
	}

	/*________________________________________________________________________________________________________________*/
	public function setRoleGroup($role)
	{
		$this->role_group = $role;
	}

	/*________________________________________________________________________________________________________________*/
	public function getRoleGroup()
	{
		return $this->role_group;
	}

	/*________________________________________________________________________________________________________________*/
	public function haveWaitingFiles($count = false)
	{
		$c = new Criteria();
		$c->addJoin(GroupePeer::ID, FilePeer::GROUPE_ID);
		$c->addJoin(FilePeer::ID, FileWaitingPeer::FILE_ID);
		$c->add(GroupePeer::ID, $this->getId());

		return $count ? FileWaitingPeer::doCount($c) : (FileWaitingPeer::doCount($c) > 0 ? true : false);
	}
	
	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		return $this->getName();
	}


	/*________________________________________________________________________________________________________________*/
	/**
	 * Calcul le nombre de fichiers d'un album et l'espace total utilisé.
	 */
	public function populateSizeAndCount()
	{
		$connection = Propel::getConnection();
	
		$query = "SELECT count(file.id) as count, sum(file.size) as size
				FROM file, folder
				WHERE folder.id = file.folder_id
				AND folder.state = ".FolderPeer::__STATE_ACTIVE."
				AND file.state = ".FilePeer::__STATE_VALIDATE."
				AND file.groupe_id = ".$this->getId();
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		
		$rs = $statement->fetch();
		
		$statement->closeCursor();
		$statement = null;
	
		$this->_fileCount = $rs["count"];
		$this->_size = $rs["size"];
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le nombre de fichier d'un album (récursif).
	 * 
	 * @return number
	 */
	public function getNumberOfFiles()
	{
		if ($this->_fileCount === null) {
			$this->populateSizeAndCount();
		}
		
		return $this->_fileCount;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie la taille total d'un album (récursif).
	 */
	public function getSize()
	{
		if ($this->_size === null) {
			$this->populateSizeAndCount();
		}
		
		return $this->_size;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le nombre de dossier (récursif).
	 * 
	 * @return number
	 */
	public function getNumberOfFolders()
	{
		$c = new Criteria();
		$c->add(FolderPeer::GROUPE_ID, $this->getId());
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);

		return FolderPeer::doCount($c);
	}

	/*________________________________________________________________________________________________________________*/
	public function getNumberOfFoldersFirstLevel()
	{
		$c = new Criteria();
		$c->add(FolderPeer::GROUPE_ID, $this->getId());
		$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
		$c->add(FolderPeer::SUBFOLDER_ID, null);

		return FolderPeer::doCount($c);
	}

	/*________________________________________________________________________________________________________________*/
	public function getTagsInside($limit = null, $offset = null)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT tag.*, count(tag.id) as rate
					FROM tag, file_tag
					WHERE tag.id = file_tag.tag_id
					AND (
							(
								file_tag.type = ".FileTagPeer::__TYPE_FOLDER."
								AND file_tag.file_id IN (	SELECT folder.id
															FROM folder
															WHERE folder.groupe_id = ".$this->getId()."
															AND folder.state = ".FolderPeer::__STATE_ACTIVE.")
							) OR
							(
								file_tag.type = ".FileTagPeer::__TYPE_FILE."
								AND file_tag.file_id IN (	SELECT file.id
															FROM file
															WHERE file.groupe_id = ".$this->getId()."
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
	public function getFirstFolder()
	{
		if($this->first_folder === null)
		{
			$c = new Criteria();
			$c->add(FolderPeer::GROUPE_ID, $this->getId());
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
			$c->add(FolderPeer::SUBFOLDER_ID, null);
			$c->addJoin(GroupePeer::ID, FolderPeer::GROUPE_ID);
			$c->add(GroupePeer::CUSTOMER_ID, sfContext::getInstance()->getUser()->getCustomerId());
			$c->addJoin(GroupePeer::CUSTOMER_ID, CustomerPeer::ID);
			$c->add(CustomerPeer::STATE, CustomerPeer::__STATE_ACTIVE);
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

			$folders = FolderPeer::doSelect($c);

			foreach ($folders as $folder)
			{
				if(!sfContext::getInstance()->getUser()->hasCredential("admin"))
				{
					if($role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $this->getId()))
					{
						if($role == RolePeer::__ADMIN)
						{
							if($this->isAllowedTo(ConstraintPeer::__UPDATE, RolePeer::__ADMIN))
								$this->first_folder = $folder;
						}
						elseif($role <= RolePeer::__CONTRIB)
							$this->first_folder = $folder;
					}
					else if($this->getFree() && $this->getFreeCredential() == RolePeer::__CONTRIB)
						$this->first_folder = $folder;
				}
				else
					$this->first_folder = $folder;
			}
		}

		return $this->first_folder;
	}

	/*________________________________________________________________________________________________________________*/
	public function isAllowedTo($constraint_id, $role_id = null)
	{
		if(sfContext::getInstance()->getUser()->hasCredential("admin"))
			return true;

		if($role_id)
		{
			$role = UserGroupPeer::getRole(sfContext::getInstance()->getUser()->getId(), $this->getId());

			if(!$role && $this->getFree() == true)
				$role = $this->getFreeCredential();

			if($role < $role_id)
				return true;
			else
				return GroupeConstraintPeer::isAllowedTo($this->getId(), $constraint_id);
		}
		else
			return GroupeConstraintPeer::isAllowedTo($this->getId(), $constraint_id);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path (disk) du fichier.
	 */
	public function getPath($absolute = true)
	{
		if($absolute)
			return sfConfig::get("app_path_upload_dir")."/".$this->getDisk()->getPath()."/cust-".
			$this->getCustomerId()."/groups";
		else
			return sfConfig::get("app_path_upload_dir_name")."/".$this->getDisk()->getPath()."/cust-".
			$this->getCustomerId()."/groups";
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

	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path + le nom du fichier (disk).
	 *
	 * @return string
	 */
	public function getPathname()
	{
		return $this->getPath().DIRECTORY_SEPARATOR.$this->getThumbnail();
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

	/*________________________________________________________________________________________________________________*/
	public function getTags()
	{
		$file_tags = FileTagPeer::retrieveByFileIdType(1, $this->getId());
	
		$tags_array = array();
	
		foreach ($file_tags as $file_tag){
			$tags_array[] = $file_tag->getTag();
		}

		return $tags_array;
	}

	/*________________________________________________________________________________________________________________*/
	public function save(PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(GroupePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$con->beginTransaction();
		
		try {
			$new = false;

			if ($this->isNew()) {
				$new = true;
				$this->setState(GroupePeer::__STATE_ACTIVE);
			}
			
			$this->getCustomer()->getDisk()->getId();
			
			if ($this->isNew() && !$this->getDiskId()) {
				if ($this->getCustomer() && $disk = $this->getCustomer()->getDisk()) {
					$this->setDiskId($disk->getId());
				}
				else {
					$disk = DiskPeer::getDefault();
					$this->setDiskId($disk->getId());
				}
			}

			$ret = parent::save($con);

			$con->commit();

			if ($new) {
				ConstraintPeer::initConstraint($this->getId());
			}
			
			return $ret;
		}
		catch (Exception $e) {
			$con->rollBack();
			throw $e;
		}
	}

	/*________________________________________________________________________________________________________________*/
	public function delete(PropelPDO $con = null)
	{
		GroupePeer::deleteAlbum($this);
	}
}
