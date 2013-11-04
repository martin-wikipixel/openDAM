<?php

class groupLogTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      // add your own options here
    ));

    $this->namespace        = 'group';
    $this->name             = 'log';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [group:log|INFO] task does things.
Call it with:

  [php symfony group:log|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
	// initialize the database connection
	$databaseManager = new sfDatabaseManager($this->configuration);
	$connection = $databaseManager->getDatabase($options['connection'])->getConnection();

	LogGroupePeer::updateLog();
  }
}
