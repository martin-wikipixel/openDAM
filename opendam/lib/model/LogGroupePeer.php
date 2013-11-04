<?php


/**
 * Skeleton subclass for performing query and update operations on the 'log_groupe' table.
 *
 * 
 *
 * This class was autogenerated by Propel 1.4.2 on:
 *
 * jeu. 23 févr. 2012 10:05:37 CET
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    lib.model
 */
class LogGroupePeer extends BaseLogGroupePeer
{
	/*________________________________________________________________________________________________________________*/
	public static function updateLog()
	{
		$connection = Propel::getConnection();
		set_time_limit(0);

		$date_s = date("Y-m-d H:i:s", mktime(0,0,0,date("m"),(date("d") - 1),date("Y")));
		$date_f = date("Y-m-d H:i:s", mktime(23,59,59,date("m"),(date("d") - 1),date("Y")));

		$c = new Criteria();
		$groups = GroupePeer::doSelect($c);

		foreach($groups as $group)
		{
			$used_space_disk = 0;
			$folders = 0;
			$files = 0;
			$upload_traffic = 0;
			$download_traffic = 0;
			$upload_traffic_files = 0;
			$download_traffic_files = 0;
			$views = 0;
			$unique_views = 0;

			/* ------------------------------------------------------ */
			$query = "	SELECT sum(size) as total
						FROM file, groupe
						WHERE file.groupe_id = groupe.id
						AND file.state = ".$connection->quote(FilePeer::__STATE_VALIDATE)."
						AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
						AND groupe.id = ".$connection->quote($group->getId())."
						AND file.created_at > ".$connection->quote($date_s)."
						AND file.created_at < ".$connection->quote($date_f);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$used_space_disk = $rs[0]["total"];

			/* ------------------------------------------------------ */
			$query = "	SELECT count(folder.id) as total
						FROM folder, groupe
						WHERE folder.groupe_id = groupe.id
						AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
						AND folder.state = ".$connection->quote(FolderPeer::__STATE_ACTIVE)."
						AND groupe.id = ".$connection->quote($group->getId())."
						AND folder.created_at > ".$connection->quote($date_s)."
						AND folder.created_at < ".$connection->quote($date_f);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$folders = $rs[0]["total"];

			/* ------------------------------------------------------ */
			$query = "	SELECT count(file.id) as total
						FROM file, groupe
						WHERE file.groupe_id = groupe.id
						AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
						AND file.state = ".$connection->quote(FilePeer::__STATE_VALIDATE)."
						AND groupe.id = ".$connection->quote($group->getId())."
						AND file.created_at > ".$connection->quote($date_s)."
						AND file.created_at < ".$connection->quote($date_f);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$files = $rs[0]["total"];

			/* ------------------------------------------------------ */
			$query = "	SELECT sum(size) as total, count(file.id) as nb
						FROM file, groupe
						WHERE file.groupe_id = groupe.id
						AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
						AND groupe.id = ".$connection->quote($group->getId())."
						AND file.created_at > ".$connection->quote($date_s)."
						AND file.created_at < ".$connection->quote($date_f);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			$upload_traffic = $rs[0]["total"];
			$upload_traffic_files = $rs[0]["nb"];

			/* ------------------------------------------------------ */
			$query = "	SELECT log.ids
						FROM log
						WHERE log.type = 3
						AND log_type IN ('file-download', 'files-download')
						AND log.created_at > ".$connection->quote($date_s)."
						AND log.created_at < ".$connection->quote($date_f);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			for($i = 0; $i < count($rs); $i++)
			{
				$array = unserialize($rs[$i]["ids"]);
				$files_a = FilePeer::retrieveByPKs($array);

				foreach($files_a as $file)
				{
					if($file->getGroupeId() == $group->getId() && $file->getState() == FilePeer::__STATE_VALIDATE)
					{
						$download_traffic += $file->getSize();
						$download_traffic_files++;
					}
				}
			}

			/* ------------------------------------------------------ */
			$query = "	SELECT log.ids
						FROM log
						WHERE log.type = 3
						AND log_type IN ('file-download', 'files-download', 'file-print', 'file-email', 'permalink-create')
						AND log.created_at > ".$connection->quote($date_s)."
						AND log.created_at < ".$connection->quote($date_f);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;

			for($i = 0; $i < count($rs); $i++)
			{
				$array = unserialize($rs[$i]["ids"]);
				$files_a = FilePeer::retrieveByPKs($array);

				foreach($files_a as $file)
				{
					if($file->getGroupeId() == $group->getId() && $file->getState() == FilePeer::__STATE_VALIDATE)
						$views++;
				}
			}

			/* ------------------------------------------------------ */
			$query = "	SELECT log.ids
						FROM log
						WHERE log.type = 3
						AND log_type IN ('file-download', 'files-download', 'file-print', 'file-email', 'permalink-create')
						AND log.created_at > ".$connection->quote($date_s)."
						AND log.created_at < ".$connection->quote($date_f);

			$statement = $connection->query($query);
			$statement->setFetchMode(PDO::FETCH_ASSOC); 
			$rs = $statement->fetchAll();
			$statement->closeCursor();
			$statement = null;
			$temp = Array();

			for($i = 0; $i < count($rs); $i++)
			{
				$array = unserialize($rs[$i]["ids"]);
				$files_a = FilePeer::retrieveByPKs($array);

				foreach($files_a as $file)
				{
					if($file->getGroupeId() == $group->getId() && $file->getState() == FilePeer::__STATE_VALIDATE)
					{
						if(!in_array($file->getId(), $temp))
						{
							$unique_views++;
							$temp[] = $file->getId();
						}
					}
				}
			}

			/* ------------------------------------------------------ */
			$log = new LogGroupe();
			$log->setGroupeId($group->getId());
			$log->setUsedSpaceDisk($used_space_disk);
			$log->setFolders($folders);
			$log->setFiles($files);
			$log->setUploadTraffic($upload_traffic);
			$log->setDownloadTraffic($download_traffic);
			$log->setUploadTrafficFiles($upload_traffic_files);
			$log->setDownloadTrafficFiles($download_traffic_files);
			$log->setViews($views);
			$log->setUniqueViews($unique_views);

			$log->save();
		}
	}

	/*________________________________________________________________________________________________________________*/
	public static function getForMonth($group_id, $month, $year)
	{
		$connection = Propel::getConnection();

		$from = date("Y-m-d H:i:s", mktime(0,0,0,$month,1,$year));
		$to = date("Y-m-d H:i:s", mktime(23,59,59,$month,date("t", mktime(0,0,0,$month,1,$year)),$year));

		$query = "SELECT sum(log_groupe.used_space_disk) as used_space_disk, sum(log_groupe.folders) as folders, sum(log_groupe.files) as files, sum(log_groupe.upload_traffic) as upload_traffic, sum(log_groupe.download_traffic) as download_traffic, sum(log_groupe.upload_traffic_files) as upload_traffic_files, sum(log_groupe.download_traffic_files) as download_traffic_files, sum(log_groupe.views) as views, sum(log_groupe.unique_views) as unique_views
					FROM log_groupe, groupe
					WHERE log_groupe.groupe_id = groupe.id
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE)."
					AND log_groupe.groupe_id = ".$connection->quote($group_id)."
					AND log_groupe.created_at BETWEEN ".$connection->quote($from)." AND ".$connection->quote($to);

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $rs[0];
	}

	/*________________________________________________________________________________________________________________*/
	public static function getForMonthForCustomer($customer_id = null, $month, $year)
	{
		$connection = Propel::getConnection();

		$from = date("Y-m-d H:i:s", mktime(0,0,0,$month,1,$year));
		$to = date("Y-m-d H:i:s", mktime(23,59,59,$month,date("t", mktime(0,0,0,$month,1,$year)),$year));

		$query = "SELECT sum(log_groupe.used_space_disk) as used_space_disk, sum(log_groupe.folders) as folders, sum(log_groupe.files) as files, sum(log_groupe.upload_traffic) as upload_traffic, sum(log_groupe.download_traffic) as download_traffic, sum(log_groupe.upload_traffic_files) as upload_traffic_files, sum(log_groupe.download_traffic_files) as download_traffic_files, sum(log_groupe.views) as views, sum(log_groupe.unique_views) as unique_views
					FROM log_groupe, groupe, customer
					WHERE log_groupe.groupe_id = groupe.id
					AND groupe.customer_id = customer.id
					AND customer.state = ".$connection->quote(CustomerPeer::__STATE_ACTIVE)."
					AND groupe.state = ".$connection->quote(GroupePeer::__STATE_ACTIVE);

		if($customer_id)
			$query .= " AND customer.id = ".$connection->quote($customer_id);

		$query .= " AND log_groupe.created_at BETWEEN ".$connection->quote($from)." AND ".$connection->quote($to);

		$statement = $connection->query($query);
		$statement->setFetchMode(PDO::FETCH_ASSOC); 
		$rs = $statement->fetchAll();
		$statement->closeCursor();
		$statement = null;

		return $rs[0];
	}
}