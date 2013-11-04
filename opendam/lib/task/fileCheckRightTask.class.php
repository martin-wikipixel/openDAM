<?php

class fileCheckRightTask extends sfBaseTask
{
  protected function getProductionRouting()
  {
	$routing = $this->getRouting();
	$routingOptions = $routing->getOptions();
	$routingOptions['context']['host'] = sfConfig::get("app_hostnames_default");
	$routing->initialize($this->dispatcher, $routing->getCache(), $routingOptions);
	return $routing;
  }

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace        = 'file';
    $this->name             = 'checkRight';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [file:checkRight|INFO] task does things.
Call it with:

  [php symfony file:checkRight|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $routing = $this->getProductionRouting();
	sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'I18N', 'Tag', 'Date'));

	/* FILES WHOSE RIGHTS EXPIRE IN 7 DAYS */
	$log = " ---- Début cron (".date("d/m/Y H:i:s").") : Fichiers fin de droit (J - 7) ----<br />";

	$c = new Criteria();
	$c->add(FileRightPeer::TYPE, 3);
	$c->add(FileRightPeer::USAGE_LIMITATION_ID, UsageLimitationPeer::__TIME_LIMIT);
	$c->add(FileRightPeer::VALUE, date("d/m/Y", mktime(0, 0, 0, date("m"), (date("d") + 7), date("Y"))));

	$file_rights = FileRightPeer::doSelect($c);

	foreach($file_rights as $file_right)
	{
		$file = FilePeer::retrieveByPk($file_right->getObjectId());

		/* SEND EMAIL TO ADMINS OF MAIN FOLDER */
		$to = Array();
		$userGroups = UserGroupPeer::retrieveAdmin($file->getGroupeId());

		foreach($userGroups as $userGroup)
		{
			$user = UserPeer::retrieveByPkNoCustomer($userGroup->getUserId());

			if($user && $user->getState() == UserPeer::__STATE_ACTIVE)
			{
				if($user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
					$to[$user->getEmail()] = $user->getEmail();
			}
		}

		if(empty($to))
		{
			$admins = UserPeer::retrieveByRoleIdsAndCustomerId(Array(RolePeer::__ADMIN), $file->getCustomerId());
			foreach($admins as $admin)
			{
				if($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
					$to[$admin->getEmail()] = $admin->getEmail();
			}
		}

		$search = Array("**URL**", "**FILE_NAME**");
		$replace = Array(url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true), $file->getName());

		$email = new myMailer("file_end_right_7days", "[wikiPixel] ".__("D-7 before the end of right for file")." ".$file->getName());
		$email->setTo($to);
		$email->setFrom(Array("no-reply@wikipixel.com" => "no-reply@wikipixel.com"));
		$email->compose($search, $replace);
		$email->send();

		$log .= "<li>Fin de droit atteint (".$file_right->getValue().") pour le fichier ".$file->getName()." : notification admins</li><br />";
	}

	$mailer = sfContext::getInstance()->getMailer();
	$message = $mailer->compose(Array("crontab@wikipixel.com" => "crontab@wikipixel.com"), 'julien@neobe.com', "[wikiPixel] Rapport cron : fichiers fin de droit J - 7");
	$message->setBody($log, 'text/html');
	$mailer->send($message);
	/**************************************/

	/* FILES WHOSE RIGHTS EXPIRE TOMORROW */
	$log = " ---- Début cron (".date("d/m/Y H:i:s").") : Fichiers fin de droit (J - 1) ----<br />";

	$c = new Criteria();
	$c->add(FileRightPeer::TYPE, 3);
	$c->add(FileRightPeer::USAGE_LIMITATION_ID, UsageLimitationPeer::__TIME_LIMIT);
	$c->add(FileRightPeer::VALUE, date("d/m/Y", mktime(0, 0, 0, date("m"), (date("d") + 1), date("Y"))));

	$file_rights = FileRightPeer::doSelect($c);

	foreach($file_rights as $file_right)
	{
		$file = FilePeer::retrieveByPk($file_right->getObjectId());

		/* SEND EMAIL TO ADMINS OF MAIN FOLDER */
		$to = Array();
		$userGroups = UserGroupPeer::retrieveAdmin($file->getGroupeId());

		foreach($userGroups as $userGroup)
		{
			$user = UserPeer::retrieveByPkNoCustomer($userGroup->getUserId());

			if($user && $user->getState() == UserPeer::__STATE_ACTIVE)
			{
				if($user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
					$to[$user->getEmail()] = $user->getEmail();
			}
		}

		if(empty($to))
		{
			$admins = UserPeer::retrieveByRoleIdsAndCustomerId(Array(RolePeer::__ADMIN), $file->getCustomerId());
			foreach($admins as $admin)
			{
				if($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
					$to[$admin->getEmail()] = $admin->getEmail();
			}
		}

		$search = Array("**URL**", "**FILE_NAME**");
		$replace = Array(url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true), $file->getName());

		$email = new myMailer("file_end_right_tomorrow", "[wikiPixel] ".__("D-1 before the end of right for file")." ".$file->getName());
		$email->setTo($to);
		$email->setFrom(Array("no-reply@wikipixel.com" => "no-reply@wikipixel.com"));
		$email->compose($search, $replace);
		$email->send();

		$log .= "<li>Fin de droit atteint (".$file_right->getValue().") pour le fichier ".$file->getName()." : notification admins</li><br />";
	}

	$mailer = sfContext::getInstance()->getMailer();
	$message = $mailer->compose(Array("crontab@wikipixel.com" => "crontab@wikipixel.com"), 'julien@neobe.com', "[wikiPixel] Rapport cron : fichiers fin de droit J - 1");
	$message->setBody($log, 'text/html');
	$mailer->send($message);
	/**************************************/

	/* FILES WHOSE RIGHTS EXPIRE TODAY */
	$log = " ---- Début cron (".date("d/m/Y H:i:s").") : Fichiers fin de droit (J) ----<br />";

	$c = new Criteria();
	$c->add(FileRightPeer::TYPE, 3);
	$c->add(FileRightPeer::USAGE_LIMITATION_ID, UsageLimitationPeer::__TIME_LIMIT);
	$c->add(FileRightPeer::VALUE, date("d/m/Y"));

	$file_rights = FileRightPeer::doSelect($c);

	foreach($file_rights as $file_right)
	{
		$file = FilePeer::retrieveByPk($file_right->getObjectId());

		$file->setUsageDistributionId(UsageDistributionPeer::__UNAUTH);
		$file->setUsageConstraintId(null);

		$file->save();

		FileRightPeer::deleteByType($file->getId(), 3);

		/* SEND EMAIL TO ADMINS OF MAIN FOLDER */
		$to = Array();
		$userGroups = UserGroupPeer::retrieveAdmin($file->getGroupeId());

		foreach($userGroups as $userGroup)
		{
			$user = UserPeer::retrieveByPkNoCustomer($userGroup->getUserId());

			if($user && $user->getState() == UserPeer::__STATE_ACTIVE)
			{
				if($user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
					$to[$user->getEmail()] = $user->getEmail();
			}
		}

		if(empty($to))
		{
			$admins = UserPeer::retrieveByRoleIdsAndCustomerId(Array(RolePeer::__ADMIN), $file->getCustomerId());
			foreach($admins as $admin)
			{
				if($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)
					$to[$admin->getEmail()] = $admin->getEmail();
			}
		}

		$search = Array("**URL**", "**FILE_NAME**");
		$replace = Array(url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true), $file->getName());

		$email = new myMailer("file_end_right", "[wikiPixel] ".__("End of right for file")." ".$file->getName());
		$email->setTo($to);
		$email->setFrom(Array("no-reply@wikipixel.com" => "no-reply@wikipixel.com"));
		$email->compose($search, $replace);
		$email->send();

		$log .= "<li>Fin de droit atteint (".$file_right->getValue().") pour le fichier ".$file->getName()." : notification admins</li><br />";
	}

	$mailer = sfContext::getInstance()->getMailer();
	$message = $mailer->compose(Array("crontab@wikipixel.com" => "crontab@wikipixel.com"), 'julien@neobe.com', "[wikiPixel] Rapport cron : fichiers fin de droit J");
	$message->setBody($log, 'text/html');
	$mailer->send($message);
	/**************************************/
  }
}
