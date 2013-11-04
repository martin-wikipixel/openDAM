<?php

/**
 * Subclass for representing a row from the 'file_tag' table.
 *
 * 
 *
 * @package lib.model
 */ 
class FileTag extends BaseFileTag
{
	/*________________________________________________________________________________________________________________*/
	public function getObject()
	{
		switch ($this->getType()) {
			case 1: return GroupePeer::retrieveByPK($this->getFileId()); break;
			case 2: return FolderPeer::retrieveByPK($this->getFileId()); break;
			case 3: return FilePeer::retrieveByPK($this->getFileId()); break;
		}
	}


	/*________________________________________________________________________________________________________________*/
	public function delete(PropelPDO $con = null)
	{
		if ($con === null) {
			$con = Propel::getConnection(FileTagPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
		}

		$con->beginTransaction();
		try
		{
			$ret = parent::delete($con);

			$con->commit();

			if($tag = TagPeer::retrieveByPk($this->getTagId()))
			{
				if($tag->getNb() == 0)
					$tag->delete();
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