<?php

/**
 * Subclass for representing a row from the 'customer' table.
 *
 * 
 *
 * @package lib.model
 */ 
class Customer extends BaseCustomer
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi vrai si le customer peut ajouter un utilisteur selon les options.
	 * 
	 * @return boolean
	 */
	public function canAddUser()
	{
		return true;
	}

	/*________________________________________________________________________________________________________________*/
	public function __toString()
	{
		if($this->getCompany())
			return ucfirst($this->getCompany());
		else
			return strtoupper($this->getName())." ".ucfirst($this->getFirstName());
	}

	/*________________________________________________________________________________________________________________*/
	public function getLastActivity()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
		$connection = Propel::getConnection();

		$query = "SELECT UNIX_TIMESTAMP(customer_memo.created_at) as date, customer_memo.id
					FROM customer_memo
					WHERE customer_memo.customer_id = ".$connection->quote($this->getId())."
					ORDER BY UNIX_TIMESTAMP(customer_memo.created_at) DESC
					LIMIT 0, 1";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$memoDate = !empty($rs) ? $rs[0]["date"] : null;
		$memoId = !empty($rs) ? $rs[0]["id"] : null;

		$query = "SELECT UNIX_TIMESTAMP(customer_event.created_at) as date, customer_event.id
					FROM customer_event
					WHERE customer_event.customer_id = ".$connection->quote($this->getId())."
					ORDER BY UNIX_TIMESTAMP(customer_event.created_at) DESC
					LIMIT 0, 1";

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		$eventDate = !empty($rs) ? $rs[0]["date"] : null;
		$eventId = !empty($rs) ? $rs[0]["id"] : null;

		if(!$eventDate && !$memoDate)
		{
			$date = "-";
			$content = "";
		}
		else
		{
			$date = date("d/m/Y", ($memoDate > $eventDate ? $memoDate : $eventDate));

			if($memoDate > $eventDate)
			{
				$memo = CustomerMemoPeer::retrieveByPk($memoId);
				$content = "<strong>".__("Last memo")."</strong><br /><br />";
				$content .= $memo->getCustomerMemoTypeId() ? "<u>".__("Type:")."</u> ".$memo->getCustomerMemoType()->getTitle()."<br />" : "";
				$content .= "<u>".__("Content:")."</u><br />".nl2br($memo->getMemo());
			}
			else
			{
				$event = CustomerEventPeer::retrieveByPk($eventId);
				$content = "<strong>".__("Last event")."</strong><br /><br />";
				$content .= $event->getCustomerEventTitleId() ? "<u>".__("Title:")."</u> ".$event->getCustomerEventTitle()->getTitle()."<br />" : "";
				$content .= "<u>".__("Content:")."</u><br />".nl2br($event->getDescription());
			}
		}

		return array("date" => $date, "object" => $content);
	}

	/*________________________________________________________________________________________________________________*/
	public function getLogsType($limit)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Global");

		$connection = Propel::getConnection();

		$query = "	SELECT count(log.id) as count, log.log_type
					FROM log
					WHERE log.customer_id = ".$this->getId()."
					GROUP BY log.log_type
					ORDER BY count DESC
					LIMIT 0, ".$limit;

		$connection = Propel::getConnection();
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 

		$log = Array();

		while($rs = $statement->fetch())
		{
			$log[] = returnLogTypes($rs["log_type"]);
		}

		$statement->closeCursor();
		$statement = null;

		return $log;
	}

	/*________________________________________________________________________________________________________________*/
	public function getFilesType($limit)
	{
		$connection = Propel::getConnection();

		$query = "	SELECT count(file.id) as count, file.extention
					FROM file, groupe
					WHERE file.groupe_id = groupe.id
					AND file.state = ".FilePeer::__STATE_VALIDATE."
					AND groupe.state = ".GroupePeer::__STATE_ACTIVE."
					AND groupe.customer_id = ".$this->getId()."
					GROUP BY file.extention
					ORDER BY count DESC
					LIMIT 0, ".$limit;

		$connection = Propel::getConnection();
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 

		$ext = Array();

		while($rs = $statement->fetch())
		{
			$ext[] = $rs["extention"];
		}

		$statement->closeCursor();
		$statement = null;

		return $ext;
	}

	/*________________________________________________________________________________________________________________*/
	public function getTotalSize($unit = "mb")
	{
		return 0;
	}

	/*________________________________________________________________________________________________________________*/
	public function getUsedSize()
	{
		return FilePeer::retrieveTotalSize(0, $this->getId());
	}

	private $countAlbums = null;

	/*________________________________________________________________________________________________________________*/
	public function countAlbums(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		return $this->countGroupes($criteria, $distinct, $con);
	}

	/*________________________________________________________________________________________________________________*/
	public function countGroupes(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if (!$criteria) {
			$criteria = new Criteria();
		}
		
		$criteria->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);

		return parent::countGroupes($criteria, $distinct, $con);
	}

	/*________________________________________________________________________________________________________________*/
	public function getCountGroup()
	{
		return $this->countAlbums();
	}

	private $countFolders = null;

	/*________________________________________________________________________________________________________________*/
	/**
	 * Compte le nombre de dossiers.
	 * 
	 * @return number
	 */
	public function countFolders()
	{
		if (!$this->countFolders) {
			$this->countFolders = FolderPeer::countByCustomerId($this->getId());
		}

		return $this->countFolders;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @return number
	 */
	public function getCountFolder()
	{
		return $this->countFolders();
	}

	private $countFiles = null;
	
	/*________________________________________________________________________________________________________________*/
	public function countFiles()
	{
		if (!$this->countFiles) {
			$this->countFiles = FilePeer::countByCustomerId($this->getId());
		}

		return $this->countFiles;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @return number
	 */
	public function getCountFile()
	{
		return $this->countFiles();
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * Compte le nombre d'utilisateur du customer.
	 * 
	 * @return number
	 */
	public function countUsers(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
	{
		if (!$criteria) {
			$criteria = new Criteria();
			$criteria->add(UserPeer::STATE, UserPeer::__STATE_ACTIVE);
		}
		
		return parent::countUsers($criteria, $distinct, $con);
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @return number
	 */
	public function getCountUser()
	{
		return $this->countUsers();
	}

	private $countUsersLogin = null;
	
	/*________________________________________________________________________________________________________________*/
	/**
	 * 
	 * @return number
	 */
	public function countLoginUsers()
	{
		if (!$this->countUsersLogin) {
			$this->countUsersLogin = UserPeer::countByCustomerId($this->getId(), true, true);
		}
		
		return $this->countUsersLogin;
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * @return number
	 */
	public function getCountUserLogin()
	{
		return $this->countLoginUsers();
	}

	/*________________________________________________________________________________________________________________*/
	/**
	 * @deprecated
	 * 
	 * @param unknown $format
	 * @return Ambigous <number, string>
	 */
	public function getLastConnection($format)
	{
		$max = 0;
		$users = UserPeer::retrieveByCustomerId($this->getId());

		foreach($users as $user)
			$max = $user->getLastLoginAt("U") > $max ? $user->getLastLoginAt("U") : $max;

		return $max > 0 ? date($format, $max) : 0;
	}

	/*________________________________________________________________________________________________________________*/
	public function getDisk()
	{
		return CustomerPeer::getDisk($this->getId());
	}

	/*________________________________________________________________________________________________________________*/
	public function hasSuspendUser($all = true)
	{
		$c = new Criteria();
		$c->add(UserPeer::CUSTOMER_ID, $this->getId());
		$c->add(UserPeer::STATE, UserPeer::__STATE_SUSPEND);

		$suspend = UserPeer::doCount($c);

		if($this->countUsers() > 0)
		{
			if($all)
				return $suspend == $this->countUsers() ? true : false;
			else
				return $suspend > 0 ? true : false;
		}

		return false;
	}

	/*________________________________________________________________________________________________________________*/
	public function getModuleValue($module_id)
	{
		$customerModule = CustomerHasModulePeer::retrieveByModuleAndCustomer($module_id, $this->getId());

		if($customerModule)
		{
			$value = ModuleValuePeer::retrieveByPk($customerModule->getModuleValueId());

			if(!$value)
				return false;
			else
				return $value->getValue();
		}
		else
			return false;
	}
}
