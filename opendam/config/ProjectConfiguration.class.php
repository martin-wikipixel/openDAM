<?php

require_once dirname(__FILE__).'/../lib/vendor/symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

// composer vendor
require_once dirname(__FILE__)."/../vendor/autoload.php";

class ProjectConfiguration extends sfProjectConfiguration
{
	static protected $mailer  = null; # Symfony mailer system

	public function setup()
	{
		$this->enablePlugins('sfPropelPlugin');
		$this->enablePlugins('sfThumbnailPlugin');
		$this->enablePlugins('sfFormExtraPlugin');
		$this->enablePlugins('chCmsExposeRoutingPlugin');
		$this->enablePlugins('wpI18nPlugin');
		$this->enablePlugins('wpLessPlugin');
	}

	public static function getMailer()
	{
		if (null !== self::$mailer) {
			return self::$mailer;
		}
	
		// If sfContext has instance, returns the classic mailer resource
		if (sfContext::hasInstance() && sfContext::getInstance()->getMailer()) {
			self::$mailer = sfContext::getInstance()->getMailer();
		}
		else {
			// Else, initialization
			if (!self::hasActive()) {
				throw new sfException('No sfApplicationConfiguration loaded');
			}
			
			require_once sfConfig::get('sf_symfony_lib_dir').'/vendor/swiftmailer/classes/Swift.php';
			Swift::registerAutoload();
			sfMailer::initialize();
			$applicationConfiguration = self::getActive();
	
			$config = sfFactoryConfigHandler::getConfiguration($applicationConfiguration->getConfigPaths('config/factories.yml'));
	
			self::$mailer = new $config['mailer']['class']($applicationConfiguration->getEventDispatcher(), $config['mailer']['param']);
		}
		
		return self::$mailer;
	}
}
