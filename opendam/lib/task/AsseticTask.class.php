<?php 
class AsseticTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace        = 'assetic';
    $this->name             = 'build';
    $this->briefDescription = 'Does strictly nothing';
 
    //$this->addOption('env', null, sfCommandOption::PARAMETER_OPTIONAL, 'Changes the environment this task is run in', 'prod');
    $this->addOptions(array(
    	new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
    	new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
    	//new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));
    
    $this->detailedDescription = <<<EOF
This task is completely useless, and should be run as often as possible.
EOF;
  }
 
  protected function execute($arguments = array(), $options = array())
  {
	$this->generateStyles();
	$this->generateJavascripts();
  }
  
  /**
   * Generation du fichier production css.
   * 
   * @throws Exception
   */
  protected function generateStyles()
  {
  	$config = sfConfig::get("app_assetic_css");
  	$styles = $config["files"];
  	
  	$targetPath = sfConfig::get("sf_web_dir")."/css/".$config["path"];
  	
  	$yui = sfConfig::get("app_assetic_yui_path");
  	$tmpStyle = "/tmp/".uniqid().".css";
  	
  	// concatène tous les scripts
  	$str = "";
  	
  	foreach ($styles as $style) {
  		$src = sfConfig::get("sf_web_dir")."/css/".$style;
  		$str.= file_get_contents($src);
  		$str .= "\n";
  	}
  	
  	$this->logSection("file_put_contents:".$tmpStyle);
  	file_put_contents($tmpStyle, $str);
  	
  	// minification des script
  	$this->logSection("exec:"."java -jar $yui $tmpStyle -o $targetPath");
  	shell_exec("java -jar $yui $tmpStyle -o $targetPath");
  	unlink($tmpStyle);
  	
  	if (!file_exists($targetPath)) {
  		throw new Exception($targetPath." not found");
  	}
  }
  
  /**
   * Generation du fichier production javascript.
   *
   * @throws Exception
   */
  protected function generateJavascripts()
  {
  	$config = sfConfig::get("app_assetic_js");
  	$styles = $config["files"];
  	
  	$targetPath = sfConfig::get("sf_web_dir")."/js/".$config["path"];
  	
  	$yui = sfConfig::get("app_assetic_yui_path");
  	$tmpStyle = "/tmp/".uniqid().".js";
  	
  	// concatène tous les scripts
  	$str = "";
  	
  	foreach ($styles as $style) {
  		$src = sfConfig::get("sf_web_dir")."/js/".$style;
  		$str.= file_get_contents($src);
  		$str .= "\n";
  	}
  	
  	$this->logSection("file_put_contents:".$tmpStyle);
  	file_put_contents($tmpStyle, $str);
  	
  	// minification des script
  	$this->logSection("exec:"."java -jar $yui $tmpStyle -o $targetPath");
  	shell_exec("java -jar $yui $tmpStyle -o $targetPath");
  	unlink($tmpStyle);
  	
  	if (!file_exists($targetPath)) {
  		throw new Exception($targetPath." not found");
  	}
  }
}