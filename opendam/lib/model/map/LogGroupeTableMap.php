<?php


/**
 * This class defines the structure of the 'log_groupe' table.
 *
 *
 * This class was autogenerated by Propel 1.4.2 on:
 *
 * Thu Oct 31 14:47:02 2013
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    lib.model.map
 */
class LogGroupeTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.LogGroupeTableMap';

	/**
	 * Initialize the table attributes, columns and validators
	 * Relations are not initialized by this method since they are lazy loaded
	 *
	 * @return     void
	 * @throws     PropelException
	 */
	public function initialize()
	{
	  // attributes
		$this->setName('log_groupe');
		$this->setPhpName('LogGroupe');
		$this->setClassname('LogGroupe');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('GROUPE_ID', 'GroupeId', 'INTEGER', 'groupe', 'ID', true, null, null);
		$this->addColumn('USED_SPACE_DISK', 'UsedSpaceDisk', 'INTEGER', true, null, null);
		$this->addColumn('FOLDERS', 'Folders', 'INTEGER', true, null, null);
		$this->addColumn('FILES', 'Files', 'INTEGER', true, null, null);
		$this->addColumn('UPLOAD_TRAFFIC', 'UploadTraffic', 'INTEGER', true, null, null);
		$this->addColumn('DOWNLOAD_TRAFFIC', 'DownloadTraffic', 'INTEGER', true, null, null);
		$this->addColumn('UPLOAD_TRAFFIC_FILES', 'UploadTrafficFiles', 'INTEGER', true, null, null);
		$this->addColumn('DOWNLOAD_TRAFFIC_FILES', 'DownloadTrafficFiles', 'INTEGER', true, null, null);
		$this->addColumn('VIEWS', 'Views', 'INTEGER', true, null, null);
		$this->addColumn('UNIQUE_VIEWS', 'UniqueViews', 'INTEGER', true, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', true, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('Groupe', 'Groupe', RelationMap::MANY_TO_ONE, array('groupe_id' => 'id', ), 'CASCADE', 'CASCADE');
	} // buildRelations()

	/**
	 * 
	 * Gets the list of behaviors registered for this table
	 * 
	 * @return array Associative array (name => parameters) of behaviors
	 */
	public function getBehaviors()
	{
		return array(
			'symfony' => array('form' => 'true', 'filter' => 'true', ),
			'symfony_behaviors' => array(),
			'symfony_timestampable' => array('create_column' => 'created_at', ),
		);
	} // getBehaviors()

} // LogGroupeTableMap
