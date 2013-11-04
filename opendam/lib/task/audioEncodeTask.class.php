<?php

class audioEncodeTask extends sfBaseTask
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

    $this->namespace        = 'audio';
    $this->name             = 'encode';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [audio:encode|INFO] task does things.
Call it with:

  [php symfony audio:encode|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

	$path = $arguments["path"];
	$input = $path.$arguments["original"];

	$tempMp3 = $path.time().".mp3";
	$tempWav = $path.time().".wav";

	imageTools::encodeAudio($input, $tempMp3, "mp3");
	rename($tempMp3, $path.$arguments["original"]."ToMp3.mp3");

	imageTools::encodeAudio($input, $tempWav, "wav");
	rename($tempWav, $path.$arguments["original"]."ToWav.wav");
  }
}
