<?php

/**
 * Subclass for performing query and update operations on the 'configuration' table.
 *
 * 
 *
 * @package lib.model
 */ 
class ConfigurationPeer extends BaseConfigurationPeer
{
	/*________________________________________________________________________________________________________________*/
	public static function retrieveByType($type)
	{
		$requete_getConfig = new Criteria();
		$requete_getConfig->add(ConfigurationPeer::TYPE, $type);
		
		return ConfigurationPeer::doSelectOne($requete_getConfig);
	}
}
