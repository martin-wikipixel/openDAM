<?php

class videoEncodeTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('name', sfCommandArgument::REQUIRED, 'Name'),
      new sfCommandArgument('original', sfCommandArgument::REQUIRED, 'Original'),
      new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'path')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      // add your own options here
    ));

    $this->namespace        = 'video';
    $this->name             = 'encode';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [video:encode|INFO] task does things.
Call it with:

  [php symfony video:encode|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

	$path = $arguments["path"];
	$input = $path.$arguments["original"];

	$tempMp4 = $path.time().".mp4";
	$tempWebm = $path.time().".webm";

	imageTools::encodeVideo($input, $tempMp4, "mp4");
	rename($tempMp4, $path.$arguments["original"]."ToMp4.mp4");

	imageTools::encodeVideo($input, $tempWebm, "webm");
	rename($tempWebm, $path.$arguments["original"]."ToWebm.webm");
  }
}
