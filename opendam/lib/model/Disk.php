<?php


/**
 * Skeleton subclass for representing a row from the 'disk' table.
 *
 * 
 *
 * This class was autogenerated by Propel 1.4.2 on:
 *
 * Mon Sep 26 12:14:49 2011
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package    lib.model
 */
class Disk extends BaseDisk 
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoi le path absolute depuis le dossier racine /
	 * Ex: /home/data/disk
	 */
	public function getAbsolutePath()
	{
		return sfConfig::get("app_path_upload_dir").DIRECTORY_SEPARATOR.$this->getPath();
	}

	/*________________________________________________________________________________________________________________*/
	public function save(PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(DiskPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$con->beginTransaction();
		try
		{
			$ret = parent::save($con);

			$con->commit();

			if($this->getByDefault() === true)
			{
				$disks = DiskPeer::doSelect(new Criteria());
				foreach($disks as $disk)
				{
					if($disk->getId() != $this->getId())
					{
						$disk->setByDefault(false);
						$disk->save();
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
}