<?php


/**
 * This class defines the structure of the 'country' table.
 *
 *
 * This class was autogenerated by Propel 1.4.2 on:
 *
 * Thu Oct 31 14:46:59 2013
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    lib.model.map
 */
class CountryTableMap extends TableMap {

	/**
	 * The (dot-path) name of this class
	 */
	const CLASS_NAME = 'lib.model.map.CountryTableMap';

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
		$this->setName('country');
		$this->setPhpName('Country');
		$this->setClassname('Country');
		$this->setPackage('lib.model');
		$this->setUseIdGenerator(true);
		// columns
		$this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
		$this->addForeignKey('CONTINENT_ID', 'ContinentId', 'INTEGER', 'continent', 'ID', true, null, null);
		$this->addColumn('PHONE_CODE', 'PhoneCode', 'INTEGER', true, null, null);
		$this->addColumn('UE', 'Ue', 'BOOLEAN', true, null, null);
		$this->addColumn('CREATED_AT', 'CreatedAt', 'TIMESTAMP', true, null, null);
		// validators
	} // initialize()

	/**
	 * Build the RelationMap objects for this table relationships
	 */
	public function buildRelations()
	{
    $this->addRelation('Continent', 'Continent', RelationMap::MANY_TO_ONE, array('continent_id' => 'id', ), 'CASCADE', 'CASCADE');
    $this->addRelation('Customer', 'Customer', RelationMap::ONE_TO_MANY, array('id' => 'country_id', ), 'CASCADE', 'CASCADE');
    $this->addRelation('User', 'User', RelationMap::ONE_TO_MANY, array('id' => 'country_id', ), 'CASCADE', 'CASCADE');
    $this->addRelation('CountryI18n', 'CountryI18n', RelationMap::ONE_TO_MANY, array('id' => 'id', ), null, null);
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
			'symfony_i18n' => array('i18n_table' => 'country_i18n', ),
		);
	} // getBehaviors()

} // CountryTableMap
