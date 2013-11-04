<?php 
class adminComponents extends sfComponents
{
	/*________________________________________________________________________________________________________________*/
	public function executeLogUsersCsv()
	{
		$connection = Propel::getConnection();
	
		if($this->month != 'all')
		{
			$endDay = date("t", time(0,0,0,$this->month,1,$this->year));
			$date_s = $this->year.'-'.$this->month.'-'.'01 00:00:00';
			$date_f = $this->year.'-'.$this->month.'-'.$endDay.' 23:59:59';
		}
		else
		{
			$date_s = $this->year.'-01-01 00:00:00';
			$date_f = $this->year.'-12-31 23:59:59';
		}
	
		/* UPLOAD TRAFFIC */
		$upload = "
		SELECT sum(file.size) as total, count(file.id) as nb, file.user_id
		FROM `file`, `groupe`, `customer`
		WHERE file.groupe_id = groupe.id
		AND groupe.customer_id = customer.id
		AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
		AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)
			;
	
		if(empty($this->year) && empty($this->month))
			;
		else
			$upload .= "
			AND file.created_at > ".$connection->quote($date_s)."
			AND file.created_at < ".$connection->quote($date_f)
				;
	
		if($this->customer_id)
			$upload .= "
			AND customer.id = ".$connection->quote($this->customer_id)
				;
	
		$upload .= "
		GROUP BY file.user_id
	";
	
		$statement = $connection->query($upload);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
	
		$upload_user = Array();
	
		while($rs = $statement->fetch())
		{
			if(!empty($rs["user_id"]))
				$upload_user[$rs["user_id"]] = Array("total" => $rs["total"], "nb" => $rs["nb"]);
		}
	
		$statement->closeCursor();
		$statement = null;
		/*__________________________________________________*/
	
		/* DOWNLOAD TRAFFIC */
		$download = "
		SELECT sum(file.size) as total, count(file.id) as nb, file.user_id
		FROM `file`, `log`, `customer`
		WHERE file.id = log.object_id
		AND log.customer_id = customer.id
		AND log.type = '3'
		AND log_type IN ('file-download', 'files-download')
		AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)
			;
	
		if(empty($this->year) && empty($this->month))
			;
		else
			$download .= "
			AND log.created_at > ".$connection->quote($date_s)."
			AND log.created_at < ".$connection->quote($date_f)
				;
	
		if($this->customer_id)
			$download .= "
			AND customer.id = ".$connection->quote($this->customer_id)
				;
	
		$download .= "
		GROUP BY file.user_id
	";
	
		$statement = $connection->query($download);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
	
		$download_user = Array();
	
		while($rs = $statement->fetch())
		{
			if(!empty($rs["user_id"]))
				$download_user[$rs["user_id"]] = Array("total" => $rs["total"], "nb" => $rs["nb"]);
		}
	
		$statement->closeCursor();
		$statement = null;
		/*__________________________________________________*/
	
		/* TOTAL SIZE */
		$size = "
		SELECT sum(file.size) as total, file.user_id
		FROM `file`, `groupe`, `customer`
		WHERE file.groupe_id = groupe.id
		AND groupe.customer_id = customer.id
		AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
		AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)
			;
	
		if($this->customer_id)
			$size .= "
			AND customer.id = ".$connection->quote($this->customer_id)
				;
	
		$size .= "
		GROUP BY file.user_id
	";
	
		$statement = $connection->query($size);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
	
		$size_user = Array();
	
		while($rs = $statement->fetch())
		{
			if(!empty($rs["user_id"]))
				$size_user[$rs["user_id"]] = Array("total" => $rs["total"]);
		}
	
		$statement->closeCursor();
		$statement = null;
		/*__________________________________________________*/
	
		/* NUMBER OF FOLDER */
		$folder = "
		SELECT count(folder.id) as total, folder.user_id
		FROM `folder`, `groupe`, `customer`
		WHERE folder.groupe_id = groupe.id
		AND groupe.customer_id = customer.id
		AND folder.state = ".$connection->quote(FolderPeer::__STATE_ACTIVE)."
		AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
		AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)
			;
	
		if($this->customer_id)
			$folder .= "
			AND customer.id = ".$connection->quote($this->customer_id)
				;
	
		$folder .= "
		GROUP BY folder.user_id
	";
	
		$statement = $connection->query($folder);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
	
		$folder_user = Array();
	
		while($rs = $statement->fetch())
		{
			if(!empty($rs["user_id"]))
				$folder_user[$rs["user_id"]] = Array("total" => $rs["total"]);
		}
	
		$statement->closeCursor();
		$statement = null;
		/*__________________________________________________*/
	
		/* NUMBER OF FILE */
		$file = "
		SELECT count(file.id) as total, file.user_id
		FROM `file`, `groupe`, `customer`
		WHERE file.groupe_id = groupe.id
		AND groupe.customer_id = customer.id
		AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
		AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)
			;
	
		if($this->customer_id)
			$file .= "
			AND customer.id = ".$connection->quote($this->customer_id)
				;
	
		$file .= "
		GROUP BY file.user_id
	";
	
		$statement = $connection->query($file);
		$statement->setFetchMode(PDO::FETCH_ASSOC);
	
		$file_user = Array();
	
		while($rs = $statement->fetch())
		{
			if(!empty($rs["user_id"]))
				$file_user[$rs["user_id"]] = Array("total" => $rs["total"]);
		}
	
		$statement->closeCursor();
		$statement = null;
		/*__________________________________________________*/
	
		$this->upload_user = $upload_user;
		$this->download_user = $download_user;
		$this->size_user = $size_user;
		$this->folder_user = $folder_user;
		$this->file_user = $file_user;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeLogCustomer()
	{
		if(($this->year >= 2012 && $this->month >= 3) || $this->year > 2012)
			$this->logs = LogGroupePeer::getForMonth($this->group->getId(), $this->month, $this->year);
		else
		{
			$this->logs = Array();
	
			$this->logs["views"] = FilePeer::getGlobalView($this->year, $this->month, 0, $this->group->getId());
	
			$this->logs["unique_views"] = FilePeer::getUniqueView($this->year, $this->month, 0, $this->group->getId());
	
			$temp = LogPeer::getUploadTraffic($this->year, $this->month, 0, $this->group->getId());
			$this->logs["upload_traffic"] = $temp["total"];
			$this->logs["upload_traffic_files"] = $temp["nb"];
	
			$temp = LogPeer::getDownloadTraffic($this->year, $this->month, 0, $this->group->getId());
			$this->logs["download_traffic"] = $temp["total"];
			$this->logs["download_traffic_files"] = $temp["nb"];
	
			$this->logs["used_space_disk"] = FilePeer::retrieveTotalSize(0, null, $this->group->getId());
	
			$c = new Criteria();
			$c->add(FolderPeer::STATE, FolderPeer::__STATE_ACTIVE);
			$c->addJoin(FolderPeer::GROUPE_ID, GroupePeer::ID);
			$c->add(GroupePeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			$c->add(GroupePeer::ID, $this->group->getId());
	
			$this->logs["folders"] = FolderPeer::doCount($c);
	
			$c = new Criteria();
			$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
			$c->addJoin(FilePeer::GROUPE_ID, GroupePeer::ID);
			$c->add(GroupePeer::ID, $this->group->getId());
			$c->add(GroupePeer::STATE, GroupePeer::__STATE_ACTIVE);
			$c->add(GroupePeer::CUSTOMER_ID, $this->getUser()->getCustomerId());
	
			$this->logs["files"] = FilePeer::doCount($c);
		}
	
		$c = new Criteria();
		$c->add(UserGroupPeer::GROUPE_ID, $this->group->getId());
		$c->add(UserGroupPeer::ROLE, null);
		$c->add(UserGroupPeer::USER_ID, null);
	
		$this->nb_users = $this->group->getFree() ? "-" : UserGroupPeer::getCountAllUsersAndUnits($c);
	}
	
	/*________________________________________________________________________________________________________________*/
	public function executeThesaurusRandomTags()
	{
		$tags = Array();
	
		$connection = Propel::getConnection();
	
		$query = "	SELECT *
					FROM
					(
						SELECT DISTINCT tag.*
						FROM tag, file_tag
						WHERE tag.id = file_tag.tag_id
						AND tag.customer_id = ".$this->getUser()->getCustomerId()."
						AND tag.title NOT IN (	SELECT thesaurus.title
												FROM thesaurus
												WHERE thesaurus.customer_id = ".$this->getUser()->getCustomerId()."
						)
						ORDER BY RAND()
						LIMIT 0, 50
					) Q1
					ORDER BY title ASC";
	
		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_NUM);
	
		while($rs = $statement->fetch())
		{
			$tag = new Tag();
			$tag->hydrate($rs);
			$tags[] = $tag;
		}
	
		$statement->closeCursor();
		$statement = null;
	
		$this->tags = $tags;
	}
}
