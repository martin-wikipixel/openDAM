<?php
/**
 * Cette classe permet de g�rer le proccessus de t�l�chargement de fichiers. 
 * Elle supporte le download par section avec pause / reprise de t�l�chargement.
 * 
 * @author Guillaume Genet <contact@guillaume-genet.fr>
 */
class HttpDownload
{
	/**
	 * Le path du fichier
	 * @var string
	 */
	private $filePath = null;

	/**
	 * Le nom du fichier
	 * @var string
	 */
	private $filename = null;

	/**
	 * Enter description here ...
	 * @var string
	 */
	private $description = 'File Transfer';

	/**
	 * La taille du fichier.
	 * @var double
	 */
	private $size = null;

	/**
	 * Le mime type du fichier.
	 * @var string
	 */
	private $mimeType = null;

	/**
	 * La date de modification.
	 * @var string
	 */
	private $lastModified = null;

	/**
	 * vrai, le fichier sera si possible ouvert dans le navigateur (ex: fichier pdf).
	 * @var bool
	 */
	private $inline = null;

	/**
	 * Activate la fonctionnalit� de reprise de donwload.
	 * @var bool
	 */
	private $resume = false;

	/**
	 * Taille du buffer de lecture.
	 * @var int
	 */
	private $bufsize = 2048;

	/**
	 * Vitesse maximal de donwload (exprim� en kb).
	 * @var int
	 */
	private $speed = null;

	/*___________________________________________________________________________________________*/
	/**
	 * Renvoie vrai si le type mime correspond � un fichier binaire.
	 *
	 * @param string $mimeType Le mime type.
	 * @throws InvalidArgumentException
	 */
	public static function isBinary($mimeType)
	{
		if(!$mimeType)
			throw new InvalidArgumentException('param $mimeType undefined');

		list($mimeTypeMajor, $mimeTypeMinor) = mb_split('/', $mimeType);

		switch($mimeTypeMajor) 
		{
			case 'application':
			case 'image':
				return true;
			break;

			case 'text':
			default:
				return false;
		}

		return false;
	}

	/*___________________________________________________________________________________________*/ 
	/**
	 * D�tecte le type mime du fichier.
	 *
	 * @param string $filePath Le path du fichier.
	 * @return string Le typ mime du fichier. null si erreur.
	 */
	public static function detectMimeType($filePath)
	{
		if(!$filePath)
			throw new InvalidArgumentException('param $filePath undefined');

		$ext = pathinfo($filePath, PATHINFO_EXTENSION);
		$errorFix = Array("ai");

		if(in_array($ext, $errorFix))
		{
			switch($ext)
			{
				case "ai": $mimeType = "application/octet-stream"; break;
			}
		}
		else
		{
			if(extension_loaded('fileinfo'))
			{
				$finfo = finfo_open(FILEINFO_MIME); // Retourne le type mime avec l'extension file_info
				$mimeType = finfo_file($finfo, $filePath) ;
				finfo_close($finfo);
			}
			elseif(function_exists('mime_content_type'))
				$mimeType = mime_content_type($filePath);
		}

		return $mimeType;
	}

	/*__________________________________________________________________________________________________*/
	/**
	 * Affecter le path du fichier.
	 *
	 * @param string $filePath Le path du fichier.
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	public function setFilePath($filePath)
	{
		if(!$filePath)
			throw new InvalidArgumentException('undefined param $filePath');

		if(!file_exists($filePath))
			throw new Exception("Le r�pertoire $filePath n'existe pas.");

		if(!is_readable($filePath))
			throw new Exception("Le r�pertoire $filePath n'est pas accessible en �criture");

		$this->filePath = $filePath;
	}

	/*__________________________________________________________________________________________________*/
	/**
	 * Renvoie le path du fichier.
	 *
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->filePath;
	}

	/*__________________________________________________________________________________________________*/
	/**
	 * Affecter le nom du fichier.
	 *
	 * @param string $filename Le nom du fichier.
	 * @throws InvalidArgumentException
	 */
	public function setFilename($filename)
	{
		if(!$filename)
			throw new InvalidArgumentException('param $filename undefined');

		$this->filename = $filename;
	}

	/*__________________________________________________________________________________________________*/
	/**
	 * Renvoie le nom du fichier.
	 * 
	 * @return string
	 */
	public function getFilename()
	{
		if($this->filename === null)
			$this->filename = basename($this->filePath);
			
		return $this->filename;
	}

	/*__________________________________________________________________________________________________*/
	/**
	 * Affecter une description au fichier.
	 *
	 * @param string $filename
	 * @throws InvalidArgumentException
	 */
	public function setDescription($description)
	{
		if(!is_string($filename))
			throw new InvalidArgumentException('param $filename must be a string');
	
		$this->description = $description;
	}

	/*__________________________________________________________________________________________________*/
	/**
	 * Renvoie la description du fichier
	 *
	 * @return string 
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/*__________________________________________________________________________________________________*/
	/**
	 * Affecte la taille du fichier
	 *
	 * @param int $size
	 * @throws InvalidArgumentException
	 */
	/*protected function setSize($size)
	{
		if(!is_numeric($size))
			throw new InvalidArgumentException('param $size must be a number');
		
		$this->size = $size;
	}
	*/
	/*__________________________________________________________________________________________________*/
	/**
	 * Renvoie la taille du fichier
	 * 
	 * @return double
	 */
	public function getSize()
	{
		if($this->size === null)
			$this->size = filesize($this->filePath);
		
		return $this->size;
	}
	
	/*__________________________________________________________________________________________________*/
	/**
	 * Affecter le type mime
	 * 
	 * @param unknown_type $mimeType
	 * @throws InvalidArgumentException
	 */
	public function setMimeType($mimeType)
	{
		if(!$mimeType)
			throw new InvalidArgumentException('param $mimeType undefined');
			
		$this->mimeType = $mimeType;
	}
	
	/*__________________________________________________________________________________________________*/
	/**
	 * 
	 * Enter description here ...
	 */
	public function getMimeType()
	{
		if($this->mimeType === null)
			$this->mimeType = self::detectMimeType($this->filePath);
		
		return ((!$this->mimeType) ? 'application/force-download' : $this->mimeType);	
	}
	
	/*__________________________________________________________________________________________________*/
	/**
	 * Renvoie la derni�re date de modification du fichier.
	 * 
	 * @return string
	 */
	public function getLastModified()
	{
		if($this->lastModified === null)
		{
			$this->lastModified = filemtime($this->filePath);
			if($this->lastModified === false)
				$this->lastModified  = gmdate("D, d M Y H:i:s");
		}	
		
		return $this->lastModified;
	}
	
	/*___________________________________________________________________________________________*/
	/**
	 * Renvoie true si le fichier doit �tre ouvert dans le navigateur.
	 * 
	 * @return bool
	 */
	public function isInline()
	{
		return $this->inline;
	}
	
	/*___________________________________________________________________________________________*/
	/**
	 * Affecte le flag inline qui d�finie si on ouvre le fichier depuis le navigateur.
	 * 
	 * @param bool $inline
	 * @throws InvalidArgumentException
	 */
	public function setInline($inline)
	{
		if(!is_bool($inline))
			throw new InvalidArgumentException('$inline must be a boolean');	
		
		$this->inline = $inline;
	}
	
	/*___________________________________________________________________________________________*/
	/**
	 * Active ou non la fonctionnalit� de reprise de donwload
	 * 
	 * @param bool $resume
	 */
	public function setResume($resume)
	{
		if(!is_bool($resume))
			throw new InvalidArgumentException('param $resume must be a boolean');
			
		$this->resume = $resume;
	}
	
	/*___________________________________________________________________________________________*/
	/**
	 * Renvoie true si la fonctionnalit� de reprise de donwload est activ�e
	 * 
	 * @return bool
	 */
	public function getResume()
	{
		return $this->resume;	
	}
	
	/*___________________________________________________________________________________________*/
	/**
	 * Renvoie la vitess max de download
	 * 
	 * @return int
	 */
	public function getSpeed()
	{
		return $this->speed;	
	}
	
	/*___________________________________________________________________________________________*/
	/**
	 * Affecter la vitesse max de donwload (exprim� en kb/s)
	 * 
	 * @param int $speed
	 */
	public function setSpeed($speed)
	{
		if(!is_numeric($speed))
			throw new InvalidArgumentException('param $speed must be an integer');
						
		if($speed <= 0)
			throw new InvalidArgumentException('param $speed must be superior at 0');
			
		$this->speed = $speed;
	}
	  						
	/*__________________________________________________________________________________________________*/
	/**
	 * Constructeur
	 * 
	 * @param string $filePath Le path du fichier
	 * @param string $filename Le nom du fichier
	 */
	public function __construct($filePath = null, $filename = null)
	{
		if($filePath !== null)
			$this->setFilePath($filePath);
			
		if($filename !== null)
			$this->setFilename($filename);
	}
	
	/*__________________________________________________________________________________________________*/	
	/**
	 * Envoie des headers
	 * 
	 * @param int $seekStart position de d�part de lecture
	 * @param int $seekEnd position de fin de lecture
	 * @param bool $sendPartialHeader si true, envoie des header sp�cifiques pour la reprise du download
	 */
	protected function sendHeader($seekStart, $seekEnd, $sendPartialHeader) 
	{
    	$fileName = $this->getFilename();
	    if(array_key_exists('HTTP_USER_AGENT', $_SERVER) && strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) 
        	$fileName= preg_replace('/\./', '%2e', $fileName, substr_count($fileName,'.') - 1);

		header(sprintf('Content-Description: %s', $this->getDescription()));
		if(!strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 7.0") && !strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 8.0"))
			header(sprintf('Content-Type: %s', $this->getMimeType()));
	    header(sprintf('Content-Disposition: %s; filename="%s"', $this->isInline() ? 'inline' : 'attachment', addslashes($fileName)));//prot�ger contrer les quotes
	    header(sprintf("Last-Modified: %s GMT", $this->getLastModified()));
		
	    if($this->getMimeType() && self::isBinary($this->getMimeType()))
    	{     
     		//header('Content-Transfer-Encoding: binary\n');
			header('Content-Transfer-Encoding: binary');
    	} 
    	
		if($sendPartialHeader)
		{
      header('HTTP/1.1 206 Partial Content');
			//header("HTTP/1.0 206 Partial Content");
			header("Status: 206 Partial Content");
			header('Accept-Ranges: bytes');
			header('Content-Range: bytes '.$seekStart.'-'.$seekEnd.'/'.$this->getSize());
		}
		
		header('Content-Length: '.($seekEnd - $seekStart + 1));
	}
	
	/*__________________________________________________________________________________________________*/
	/**
	 * 
	 * 
	 * @throws RuntimeException
	 */
	public function executeDownload()
	{	
		$filePath = $this->getFilePath();
		if(!$filePath)
			throw new RuntimeException('undefined filePath');
				
		while(ob_get_level())		
		  ob_end_clean();
		
    //$oldStatus = ignore_user_abort(true);
		set_time_limit(0);
	
		$size = $this->getSize();			
		
		$sendPartialHeader = false;
    	
    	if($this->getResume() && array_key_exists('HTTP_RANGE', $_SERVER))
    	{
	        list($sizeUnit, $rangeOrig) = explode('=', $_SERVER['HTTP_RANGE'], 2);
	        if ($sizeUnit == 'bytes')//http://tools.ietf.org/id/draft-ietf-http-range-retrieval-00.txt
	        {
	        	$range = explode('-', $rangeOrig);
	            list($seekStart, $seekEnd) = explode('-', $rangeOrig, 2);
	            $sendPartialHeader = true;
	        }
    	}
	    
	    $seekEnd = (empty($seekEnd)) ? ($size - 1) : min(abs(intval($seekEnd)),($size - 1));
	    $seekStart = (empty($seekStart) || $seekEnd < abs(intval($seekStart))) ? 0 : max(abs(intval($seekStart)),0);

	    $this->sendHeader($seekStart, $seekEnd, $sendPartialHeader); 
	    
	    $fd = fopen($filePath, 'rb');
	    fseek($fd, $seekStart);
	    		
		$size = $seekEnd - $seekStart + 1;
		$bandwidth = 0;

		while (!feof($fd) && $size > 0 && !connection_aborted())
		{
			if ($size < $this->bufsize)
			{
				echo fread($fd , $size);
				$bandwidth += $size;
			}
			else
			{
				echo fread($fd , $this->bufsize);
				$bandwidth += $this->bufsize;
			}
				
			$size -= $this->bufsize;
			flush();
				
			if ($this->speed > 0)
				if($bandwidth > $this->speed*1024)
				{
					sleep(1);
					$bandwidth = 0;
				}
		}
		
		fclose($fd);
		
		//restore old status
		//ignore_user_abort($oldStatus);
		set_time_limit(ini_get("max_execution_time"));
	}
}
?>