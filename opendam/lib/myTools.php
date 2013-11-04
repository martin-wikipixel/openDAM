<?php

class myTools
{
	/*________________________________________________________________________________________________________________*/
	/**
	 * Renvoie le pathname d'un fichier pour une archive zip.
	 * Le pathname est virtuel (dossier de application).
	 * 
	 * Note : 
	 * Convertit en ISO-8859-1 si l'option "zip_convert_iso_8859_1" est a true.
	 * 
	 * @param File $file
	 * @return string
	 */
	public static function getZipPathnameOfFile(File $file)
	{
		$convertToIso88591 = sfConfig::get("app_zip_convert_iso_8859_1");
		
		if ($convertToIso88591) {
			$pathname = @iconv("UTF-8","ISO-8859-1//IGNORE", $file->getVirtualPathname());
					
			if (!$pathname) {
				$pathname = $file->getId();
			}
		}
		else {
			$pathname = $file->getVirtualPathname();
		}
		
		return $pathname;
	}

	/*________________________________________________________________________________________________________________*/
	// generate short url
	public static function generateurl($numAlpha=6)
	{
		$listAlpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		return str_shuffle(substr(str_shuffle($listAlpha),0,$numAlpha));
	}

	/*________________________________________________________________________________________________________________*/
	public static function cropThumbnail($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale)
	{
		$ext = self::getFileExtension($image);
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
		if ($ext == 'jpg') {
			$source = imagecreatefromjpeg($image);
		}else {
			$source = imagecreatefrompng($image);
		}

		$thumb_image_name = self::changeFileExtension($thumb_image_name, $ext, 'jpg');

		imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);

		imagejpeg($newImage,$thumb_image_name, 90);

		chmod($thumb_image_name, 0777);

		return $thumb_image_name;
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFileNameFile($fileName)
	{
		return substr($fileName, 0, strrpos($fileName, "."));
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFileName($fileName)
	{
		return substr($fileName, strrpos($fileName,'/')+1,strlen($fileName)-strrpos($fileName,'/'));
	}

	/*________________________________________________________________________________________________________________*/
	public static function getFileExtension($fileName)
	{
		return strtolower(substr(strrchr($fileName, '.'), 1));
	}

	/*________________________________________________________________________________________________________________*/
	public static function changeFileExtension($fileName, $old_ext, $new_ext)
	{
		return str_replace(".".$old_ext, ".".$new_ext, $fileName);
	}

	public static function longword_break_old($str, $i)
	{
		if ((empty($str))||(strlen($str)<$i)){
			return $str;
		}
		$words_arr = explode(' ', $str);
		foreach ($words_arr as &$word){
			$br_word = explode("\n", $word);
			//Process some run-in words conected by "\n"
			foreach ($br_word as &$r_word){
				$j=$i;

				while($j < strlen($r_word)) {

					$r_word = substr($r_word, 0, $j) . ' '. substr($r_word, $j);

					$j+= ($i +1);
				}
			}
			$word =implode("\n", $br_word);
			unset($r_word);
		}
		unset($word);
		$str = implode(' ',$words_arr);

		return  $str;
	}

	public static function getExifData($path)
	{
		$data = '';
		if(file_exists($path))
		{
			$exif = exif_read_data($path, 0, true);

			foreach ($exif as $key => $section) {
				foreach ($section as $name => $val) {
					$data .= $key.".".$name." : ".$val."<br />";
				}
			}
		}

		return $data;
	}

	public static function getGpsData($exif)
	{
		if(!empty($exif["GPSLatitude"]) && !empty($exif["GPSLongitude"])) {
			$degLat = explode('/',$exif["GPSLatitude"][0]);
			$minLat = explode('/',$exif["GPSLatitude"][1]);
			$secLat = explode('/',$exif["GPSLatitude"][2]);
			$lat = (($degLat[0] / $degLat[1]) + (($minLat[0] / $minLat[1]) / 60) + (($secLat[0] / $secLat[1]) / 3600));
			if($exif["GPSLatitudeRef"] == "S")
				$lat *= -1;

			$degLon = explode('/',$exif["GPSLongitude"][0]);
			$minLon = explode('/',$exif["GPSLongitude"][1]);
			$secLon = explode('/',$exif["GPSLongitude"][2]);
			$lon = (($degLon[0] / $degLon[1]) + (($minLon[0] / $minLon[1]) / 60) + (($secLon[0] / $secLon[1]) / 3600));
			if($exif["GPSLongitudeRef"] == "W")
				$lon *= -1;

			return (array($lat, $lon));
		} else
			return false;
	}

	public static function makeRandomPassword() 
	{
		$salt = "aceghjkprtuvwxyz123456789";
		srand((double)microtime()*1000000);
		$z = 0;
		$pass = "";
		
		while ($z < 7) {
			$num = rand() % mb_strlen($salt);
			$tmp = substr($salt, $num, 1);
			$pass = $pass . $tmp;
			$z++;
		}
		
		return $pass;
	}

	public static function createIdUrl()
	{
		$string = "";
		$chaine = "abcdefghijklmnpqrstuvwxy";

		srand((double)microtime()*1000000);

		for($i=0; $i<5; $i++) {
			$string .= $chaine[rand()%mb_strlen($chaine)];
		}

		$num = "";
		$chaine = "0123456789";

		srand((double)microtime()*1000000);
		
		for($i=0; $i<5; $i++) {
			$num .= $chaine[rand()%mb_strlen($chaine)];
		}

		$string = $string.$num;
	
		$c = new Criteria();
		$c->add(UrlPeer::TYPE, $string);
		$nb = UrlPeer::doCount($c);

		if($nb == 0)
			return $string;
		else
			return self::createIdUrl();
	}

	public static function loadUrl($path)
	{
		$id = self::createIdUrl();

		$newUrl = new Url();
		$newUrl->setType($id);
		$newUrl->setPath($path);

		$newUrl->save();

		return $id;
	}

	public static function addFile($fileUp, $folder, $collision, $flag_thumbnail, $profile = null, $_name = null, $_description = null, $_date = null, $_tags = Array())
	{
		function regexAccentsPath($chaine){
			$accent = array('à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ð','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ý','ÿ',' ','"',"'");
			$inter =  array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y','y','_','_','_');

			$temp = str_replace($accent,$inter,$chaine);

			return preg_replace('/[^a-zA-Z0-9.-]/', "_", $temp);
		}

		$filename = regexAccentsPath($fileUp["name"]);

		$path = $folder->getPathToSave().DIRECTORY_SEPARATOR;
		@mkdir($path, 0777, true);
		$ext = strtolower(self::getFileExtension($filename));
		$name =  self::getFileNameFile($filename);
		$mime = ($ext == "jpg") ? "jpeg" : $ext;
		$mime = strtolower($mime);

		$exts = unserialize(base64_decode(sfContext::getInstance()->getUser()->getModuleValue(ModulePeer::__MOD_TYPE_ALLOWED)));

		if(in_array($ext, $exts) && !empty($name))
		{
			$h = 0;

			$ls = "ls ".$path." | grep -c '^".$name.".".$ext."$'";
			$ls_repsonse = shell_exec($ls);
			$ls_original = trim($ls_repsonse);

			$ls = "ls ".$path." | grep -c '^".$name."_[0-9]*.".$ext."$'";
			$ls_repsonse = shell_exec($ls);
			$ls_version = trim($ls_repsonse);

			$ls_repsonse = $ls_original + $ls_version;

			if($ls_original == 1 && $ls_version == 0)
				$h = 1;
			elseif($ls_original == 1 && $ls_version > 0)
				$h = $ls_repsonse;

			if($h > 0)
			{
				switch($collision)
				{
					case "skip":
						return false;
					break;

					case "overwrite":
					{
						$path = $folder->getPathToSave().DIRECTORY_SEPARATOR;

						$c = new Criteria();
						$c->add(FilePeer::ORIGINAL, $name."_".$h.".".$ext);
						$c->add(FilePeer::FOLDER_ID, $folder->getId());
						$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
						$file = FilePeer::doSelectOne($c);

						try
						{
							@unlink($path.$file->getFileName().".poster.jpeg");
							@unlink($path.$file->getFileName().".flv");
							@unlink($path.$file->getOriginal());
							@unlink($path.$file->getWeb());
							@unlink($path.$file->getThumb200());
							@unlink($path.$file->getThumb100());
							@unlink($path.$file->getThumbMob());
							@unlink($path.$file->getThumbMobW());
							@unlink($path.$file->getThumbTab());
							@unlink($path.$file->getThumbTabW());
							@unlink($path.$file->getThumb400());
							@unlink($path.$file->getThumb400W());

							$file->setState(FilePeer::__STATE_DELETE);
							$file->setUpdatedAt(time());

							$file->save();
						}catch (Exception $e)
						{

						}

						$original = $name.".".$ext;
					}
					break;

					case "rename":
					default:
					{
						$find = false;

						do
						{
							$c = new Criteria();
							$c->add(FilePeer::ORIGINAL, $name."_".$h.".".$ext);
							$c->add(FilePeer::FOLDER_ID, $folder->getId());
							$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
							$nbFile = FilePeer::doCount($c);

							if(empty($nbFile))
								$find = true;

							$h++;
						} while($find == false);

						$original = $name."_".($h - 1).".".$ext;
						$name = $name."_".($h - 1);
					}
					break;
				}
			}
			else
				$original = $name.".".$ext;

			rename($fileUp['tmp_name'], $path.$original);
			@chmod($path.$original, 0666);

			$thumbMob = null;
			$thumbMobW = null;
			$thumbTab = null;
			$thumbTabW = null;
			$thumb400 = null;
			$thumb400W = null;

			$videoFormat = explode(";",ConfigurationPeer::retrieveByType("video_format_allowed")->getValue());
			$audioFormat = explode(";",ConfigurationPeer::retrieveByType("audio_format_allowed")->getValue());
			$documentFormat = explode(";",ConfigurationPeer::retrieveByType("document_format_allowed")->getValue());
			$previewType = explode(";",ConfigurationPeer::retrieveByType("_no_preview_format")->getValue());
			$convert2png = explode(";",ConfigurationPeer::retrieveByType("_file_convert2png")->getValue());
			$convert2jpeg = explode(";",ConfigurationPeer::retrieveByType("_file_convert2jpeg")->getValue());
			$convert2flv = explode(";",ConfigurationPeer::retrieveByType("_video_convert2flv")->getValue());

			if(in_array($ext, $previewType))
			{
				$web = $name."_thumb.png";
				$thumb200 = $name."_thumb200.png";
				$thumb100 = $name."_thumb100.png";

				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$web);
				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$thumb200);
				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$thumb100);
				$mime = "png";

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
					imageTools::writeWatermark($path.$web,$folder->getCustomerId());
			}
			elseif(in_array($ext, array('cr2')))
			{
				$temp = $path.$name.'.jpeg';
				$mime = "jpeg";

				shell_exec("dcraw -T -w -c -v ".escapeshellarg($path.$original)." > ".escapeshellarg($path.$name.".tiff"));
				shell_exec("convert ".escapeshellarg($path.$name.'.tiff')." ".escapeshellarg($temp));

				@unlink($path.$name.'.tiff');

				$web = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
				$thumb100 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
				$thumb200 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
				$thumbMob = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
				$thumbTab = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
				$thumb400 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
				{
					imageTools::writeWatermark($path.$web,$folder->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$folder->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$folder->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$folder->getCustomerId());
				}

				@unlink($temp);
			}
			elseif(in_array($ext, $convert2png))
			{
				$temp = $path.$name.'.png';
				$mime = "png";

				if($ext == "psd") {
					shell_exec("convert ".escapeshellarg($path.$original)."[0] ".escapeshellarg($temp));
				}
				else {
					shell_exec("convert ".escapeshellarg($path.$original)." -layers flatten ".escapeshellarg($temp));
				}

				$web = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb");
				$thumb100 = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_100");
				$thumb200 = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_200");
				$thumbMob = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_mob");
				$thumbTab = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_tab");
				$thumb400 = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400");

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
				{
					imageTools::writeWatermark($path.$web,$folder->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$folder->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$folder->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$folder->getCustomerId());
				}

				@unlink($temp);
			}
			elseif(in_array($ext, $convert2jpeg))
			{
				$temp = $path.$name.'.jpeg';
				$mime = "jpeg";

				if(in_array(strtolower($ext), Array('eps', 'tif', 'tiff')))
				{
					shell_exec("convert -density 300 ".escapeshellarg($path.$original)." -resize 5000x5000 ".escapeshellarg($temp));
				}
				else
					shell_exec("convert ".escapeshellarg($path.$original.'[0]')." ".escapeshellarg($temp));

				$web = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
				$thumb100 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
				$thumb200 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
				$thumbMob = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
				$thumbTab = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
				$thumb400 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
				{
					imageTools::writeWatermark($path.$web,$folder->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$folder->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$folder->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$folder->getCustomerId());
				}

				@unlink($temp);
			}
			elseif(in_array($ext, $convert2flv))
			{
				$temp = $path.$name.'.jpeg';
				$poster = $path.$name.'.poster.jpeg';
				$mime = "jpeg";

				shell_exec("ffmpeg -i ".escapeshellarg($path.$original)." -vcodec mjpeg -vframes 1 -an -f rawvideo -ss 2 ".escapeshellarg($temp));
				shell_exec("ffmpeg -i ".escapeshellarg($path.$original)." -vcodec mjpeg -vframes 1 -an -f rawvideo -s qvga -ss 1 ".escapeshellarg($poster));

				$web = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
				$thumb100 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
				$thumb200 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
				$thumbMob = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
				$thumbTab = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
				$thumb400 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

				imageTools::createVideo($name, $original, $path);

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
				{
					imageTools::writeWatermark($path.$web,$folder->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$folder->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$folder->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$folder->getCustomerId());
				}

				@unlink($temp);
			}
			else if(in_array($ext, $audioFormat)) {
				$web = $name."_thumb.png";
				$thumb200 = $name."_thumb200.png";
				$thumb100 = $name."_thumb100.png";
			
				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$web);
				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$thumb200);
				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$thumb100);
			
				imageTools::createAudio($name, $original, $path);
			
				if (sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($path.$web,$folder->getCustomerId());
				}
			}
			else
			{
				$exec = shell_exec("identify -verbose ".escapeshellarg($path.$original)." | grep \"Colorspace\" | awk '{print $2}'");
				$exec = trim($exec);

				$exif = @exif_read_data($path.$original);
				$orientation = @$exif['Orientation'];

				switch($orientation)
				{
					case "1": break;
					case "2": shell_exec("convert ".escapeshellarg($path.$original)." -flop ".escapeshellarg($path.$original)); break;
					case "3": shell_exec("convert ".escapeshellarg($path.$original)." -rotate 180 ".escapeshellarg($path.$original)); break;
					case "4": shell_exec("convert ".escapeshellarg($path.$original)." -flip ".escapeshellarg($path.$original)); break;
					case "5": shell_exec("convert ".escapeshellarg($path.$original)." -transpose ".escapeshellarg($path.$original)); break;
					case "6": shell_exec("convert ".escapeshellarg($path.$original)." -rotate 90 ".escapeshellarg($path.$original)); break;
					case "7": shell_exec("convert ".escapeshellarg($path.$original)." -transverse ".escapeshellarg($path.$original)); break;
					case "8": shell_exec("convert ".escapeshellarg($path.$original)." -rotate 270 ".escapeshellarg($path.$original)); break;
					default: break;
				}

				$origN = $path.$original;

				$web = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb");
				$thumb100 = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_100");
				$thumb200 = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_200");
				$thumbMob = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_mob");
				$thumbTab = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_tab");
				$thumb400 = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_400");

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
				{
					imageTools::writeWatermark($path.$web,$folder->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$folder->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$folder->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$folder->getCustomerId());
				}
			}

			$fileDb = FilePeer::getFile($original, sfContext::getInstance()->getUser()->getId(), $folder->getId(), $folder->getGroupeId());

			if(!$fileDb)
			{
				$exif = @exif_read_data($path.$original);

				$file = new File();

				if(!empty($_name))
				{
					$c = new Criteria();
					$c->add(FilePeer::NAME, $_name);
					$c->add(FilePeer::FOLDER_ID, $folder->getId());
					$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);

					if(FilePeer::doCount($c) > 0)
					{
						$find = false;
						$i = 1;

						do
						{
							$c = new Criteria();
							$c->add(FilePeer::NAME, $_name."_".$i);
							$c->add(FilePeer::FOLDER_ID, $folder->getId());
							$c->add(FilePeer::STATE, FilePeer::__STATE_VALIDATE);
							$nbFile = FilePeer::doCount($c);

							if(empty($nbFile))
								$find = true;

							$i++;
						} while($find == false);

						$file->setName($_name."_".($i - 1));
					}
					else
						$file->setName($_name);
				}
				else {
					$file->setName($fileUp["name"]);
				}

				if(!empty($_description))
					$file->setDescription($_description);

				$file->setDiskId(sfContext::getInstance()->getUser()->getDisk()->getId());

				$group = $folder->getGroupe();
				$roleGroup = sfContext::getInstance()->getUser()->getRole($group->getId());

				
					if (sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_APPROVAL)) {
						if ($roleGroup && $roleGroup <= RolePeer::__ADMIN) {
							$file->setState(FilePeer::__STATE_VALIDATE);
						}
						else {
							$file->setState(FilePeer::__STATE_WAITING_VALIDATE);
						}
					}
					else {
						$file->setState(FilePeer::__STATE_VALIDATE);
					}
				

				if(in_array(trim($ext), $videoFormat))
					$file->setType(FilePeer::__TYPE_VIDEO);
				elseif(in_array(trim($ext), $audioFormat))
					$file->setType(FilePeer::__TYPE_AUDIO);
				elseif(in_array(trim($ext), $documentFormat))
					$file->setType(FilePeer::__TYPE_DOCUMENT);
				else
					$file->setType(FilePeer::__TYPE_PHOTO);

				if($file->getType() == FilePeer::__TYPE_PHOTO)
				{
					$sizes = getimagesize($path.$original);

					$file->setWidth($sizes[0]);
					$file->setHeight($sizes[1]);
				}

				$file->setOriginal($original);
				$file->setWeb($web);
				$file->setThumb200($thumb200);
				$file->setThumb100($thumb100);
				$file->setThumbMob($thumbMob);
				$file->setThumbMobW($thumbMobW);
				$file->setThumbTab($thumbTab);
				$file->setThumbTabW($thumbTabW);
				$file->setThumb400($thumb400);
				$file->setThumb400W($thumb400W);
				$file->setExtention($ext);
				$file->setSize(filesize($path.$original));
				$file->setUserId(sfContext::getInstance()->getUser()->getId());
				$file->setGroupeId($folder->getGroupeId());
				$file->setFolderId($folder->getId());

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_EXTRACT_GPS) && $file->getType() == FilePeer::__TYPE_PHOTO)
				{
					if($gpsData = self::getGpsData($exif))
					{
						$file->setLat($gpsData[0]);
						$file->setLng($gpsData[1]);
					}
					else
					{
						$file->setLat($folder->getLat());
						$file->setLng($folder->getLng());
					}
				}
				else
				{
					$file->setLat($folder->getLat());
					$file->setLng($folder->getLng());
				}

				if($folder->getState() != FolderPeer::__STATE_ACTIVE)
					return false;

				$file->save();

				GeolocationPeer::saveGeolocation($file, GeolocationPeer::__TYPE_FILE);

				if ($roleGroup && $roleGroup <= RolePeer::__ADMIN && sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_APPROVAL)) {
					$fileWaiting = new FileWaiting();
					$fileWaiting->setFileId($file->getId());
					$fileWaiting->setUserId(sfContext::getInstance()->getUser()->getId());
					$fileWaiting->setState(FileWaitingPeer::__STATE_WAITING_VALIDATE);
					
					$fileWaiting->save();
				}

				$file->setLicenceId($folder->getLicenceId());
				$file->setUsageUseId($folder->getUsageUseId());
				$file->setCreativeCommonsId($folder->getCreativeCommonsId());
				$file->setUsageDistributionId($folder->getUsageDistributionId() ? $folder->getUsageDistributionId() : UsageDistributionPeer::__AUTH);

				$file->save();

				if($folder_usage_rights = FileRightPeer::retrieveByType($folder->getId(), 2))
				{
					foreach($folder_usage_rights as $folder_usage_right)
					{
						$usage_right = $folder_usage_right->copy(true);
						$usage_right->setObjectId($file->getId());
						$usage_right->setType(3);

						$usage_right->save();
					}
				}

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_EXIF) && $file->getType() == FilePeer::__TYPE_PHOTO)
					ExifPeer::setExif($exif, $file->getId());

				$iptc_parsed = null;

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_IPTC) && $file->getType() == FilePeer::__TYPE_PHOTO)
				{
					GetImageSize($path.$original, $iptc);
					if($iptc && array_key_exists("APP13", $iptc))
					{
						$iptc_parsed = iptcparse($iptc["APP13"]);
						IptcPeer::setIptc($iptc_parsed, $file->getId());

						if($iptc_parsed && array_key_exists("2#005", $iptc_parsed) && !$_name)
						{
							$trimed = trim($iptc_parsed["2#005"][0]);

							if(!empty($trimed))
								$file->setName($iptc_parsed["2#005"][0]);
						}

						if($iptc_parsed && array_key_exists("2#116", $iptc_parsed))
							$file->setSource($iptc_parsed["2#116"][0]);

						$file->save();
					}
				}

				if(empty($_description) && $file->getType() == FilePeer::__TYPE_PHOTO)
				{
					if($exif && array_key_exists("ImageDescription", $exif))
					{
						$file->setDescription($exif["ImageDescription"]);
						$file->save();
					}
					elseif($iptc_parsed && array_key_exists("2#120", $iptc_parsed))
					{
						$file->setDescription($iptc_parsed["2#120"][0]);
						$file->save();
					}
				}

				$file_tags = FileTagPeer::retrieveByFileIdType(2, $folder->getId());
				foreach ($file_tags as $file_tag)
				{
					if(!FileTagPeer::getFileTag(3, $file->getId(), $file_tag->getTagId()))
					{
						$fileTag = new FileTag();
						$fileTag->setType(3);
						$fileTag->setFileId($file->getId());
						$fileTag->setTagId($file_tag->getTagId());
						$fileTag->save();
					}
				}

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_EXTRACT_TAG) && $file->getType() == FilePeer::__TYPE_PHOTO)
				{
					if(!empty($_tags))
					{
						foreach($_tags as $keyword)
						{
							$tag = TagPeer::retrieveByTitle(rtrim(ltrim($keyword)));
							if(!$tag && (rtrim(ltrim($keyword))) != '')
							{
								$tag = new Tag();
								$tag->setCustomerId(sfContext::getInstance()->getUser()->getCustomerId());
								$tag->setTitle(rtrim(ltrim($keyword)));
								$tag->save();
							}

							if($tag)
							{
								if(!FileTagPeer::getFileTag(3, $file->getId(), $tag->getId()))
								{
									$fileTag = new FileTag();
									$fileTag->setType(3);
									$fileTag->setFileId($file->getId());
									$fileTag->setTagId($tag->getId());
									$fileTag->save();
								}
							}
						}
					}

					if($exif && array_key_exists("Keywords", $exif))
					{
						$keywords = explode(';',$exif["Keywords"]);

						if(count($keywords) > 1)
							;
						else
						{
							$temp = explode(',',$exif["Keywords"]);

							if(count($temp) > count($keywords))
								$keywords = $temp;
						}

						foreach($keywords as $keyword)
						{
							$tag = TagPeer::retrieveByTitle(rtrim(ltrim($keyword)));
							if(!$tag && (rtrim(ltrim($keyword))) != '')
							{
								$tag = new Tag();
								$tag->setCustomerId(sfContext::getInstance()->getUser()->getCustomerId());
								$tag->setTitle(rtrim(ltrim($keyword)));
								$tag->save();
							}

							if($tag)
							{
								if(!FileTagPeer::getFileTag(3, $file->getId(), $tag->getId()))
								{
									$fileTag = new FileTag();
									$fileTag->setType(3);
									$fileTag->setFileId($file->getId());
									$fileTag->setTagId($tag->getId());
									$fileTag->save();
								}
							}
						}
					}

					if($iptc_parsed && array_key_exists("2#025", $iptc_parsed))
					{
						$keywords = $iptc_parsed["2#025"];

						foreach($keywords as $keyword)
						{
							$tag = TagPeer::retrieveByTitle($keyword);
							if(!$tag && $keyword != '')
							{
								$tag = new Tag();
								$tag->setCustomerId(sfContext::getInstance()->getUser()->getCustomerId());
								$tag->setTitle($keyword);
								$tag->save();
							}

							if($tag)
							{
								if(!FileTagPeer::getFileTag(3, $file->getId(), $tag->getId()))
								{
									$fileTag = new FileTag();
									$fileTag->setType(3);
									$fileTag->setFileId($file->getId());
									$fileTag->setTagId($tag->getId());
									$fileTag->save();
								}
							}
						}
					}
				}

				$contents = FieldContentPeer::retrieveByObjectIdAndObjectType($folder->getId(), FieldContentPeer::__FOLDER);

				foreach($contents as $content)
				{
					$new_content = $content->copy(true);

					$new_content->setObjectId($file->getId());
					$new_content->setObjectType(FieldContentPeer::__FILE);

					$new_content->save();
				}

				if($flag_thumbnail == "true")
				{
					if(!FileTmpPeer::getFileTmp($file->getId(), sfContext::getInstance()->getUser()->getId()))
					{
						$fileTmp = new FileTmp();
						$fileTmp->setFileId($file->getId());
						$fileTmp->setFolderId($folder->getId());
						$fileTmp->setUserId(sfContext::getInstance()->getUser()->getId());
						$fileTmp->save();
					}
				}

				self::addTags($file, false);

				LogPeer::setLog(sfContext::getInstance()->getUser()->getId(), $folder->getId(), "file-upload", "3", array($file->getId()));

				if($file->getState() == FilePeer::__STATE_WAITING_VALIDATE)
				{
					$to = Array();
					$admins = UserPeer::retrieveByRoleIds(array(RolePeer::__ADMIN));
					foreach ($admins as $admin)
					{
						if ($admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $admin->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1) {
							$to[$admin->getEmail()] = $admin->getEmail();
						}
					}

					$validators = UserGroupPeer::getUsers($file->getGroupeId(), RolePeer::__ADMIN);
					foreach ($validators as $validator)
					{
						if ($validator->getUserId()) {
							$user = $validator->getUser();
							if ($user && ($user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)) {
								if (!in_array($user->getEmail(), $to)) {
									$to[$user->getEmail()] = $user->getEmail();
								}
							}
						}
					}

					$unitsValidators = UnitGroupPeer::getEffectiveByGroupIdAndRole($file->getGroupeId(), RolePeer::__ADMIN);
					foreach ($unitsValidators as $unitsValidator)
					{
						$user = $unitsValidator->getUser();
						if ($user && ($user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == true || $user->haveAccessModule(ModulePeer::__MOD_NOTIFY_ACCESS) == -1)) {
							if (!in_array($user->getEmail(), $to)) {
								$to[$user->getEmail()] = $user->getEmail();
							}
						}
					}

					sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
					$search = Array("**URL_FILE**", "**FILE_NAME**", "**USER**", "**URL**");
					$replace = Array(url_for("file/show?folder_id=".$file->getFolderId()."&id=".$file->getId(), true), $file->getName(), sfContext::getInstance()->getUser()->getInstance()->getFullname(), $_SERVER["SERVER_NAME"], url_for("group/waiting?id=".$file->getGroupeId(), true));

					$email = new myMailer("request_add_file", "[wikiPixel] ".__("Request for add file")." \"".$file."\"");
					$email->setTo($to);
					$email->setFrom(Array("no-reply@wikipixel.com" => CustomerPeer::getFromEmail(sfContext::getInstance()->getUser()->getCustomerId())));
					$email->compose($search, $replace);
					$email->send();
				}

				return $file;
			}
		}

		return false;
	}

	public static function getSize($size, $force = null, $without_unit = false)
	{
		$size = (int)$size;
		
		if(!$force)
		{
			if($size > (1024 * 1024 * 1024))
				return round($size / 1024 / 1024 / 1024, 2).($without_unit ? "" : " ".__("GB"));
			elseif($size > (1024 * 1024))
				return round($size / 1024 / 1024, 2).($without_unit ? "" : " ".__("MB"));
			elseif($size > 1024)
				return round($size / 1024, 2).($without_unit ? "" : " ".__("KB"));
			else
				return $size." ".__("B");
		}
		else
		{
			switch($force)
			{
				case "gb": return round($size / 1024 / 1024 / 1024, 2).($without_unit ? "" : " ".__("GB")); break;
				case "mb": return round($size / 1024 / 1024, 2).($without_unit ? "" : " ".__("MB")); break;
				case "kb": return round($size / 1024, 2).($without_unit ? "" : " ".__("KB")); break;
				case "b": return $size.($without_unit ? "" : " ".__("B")); break;
			}
		}
	}

	public static function writeTag($file, $tag)
	{
			$path = $file->getPathname();

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_EXIF) && $file->getType() == FilePeer::__TYPE_PHOTO)
			{
				if(!$exif = ExifPeer::getTag("Keywords", $file->getId()))
				{
					$exif = new Exif();
					$exif->setTitle("Keywords");
					$exif->setValue($tag);
					$exif->setFileId($file->getId());
					$exif->setCreatedAt(time());

					$separator = "";
					$keywords = "";
				} else {
					$keywords = $exif->getValue();
					$separator = strstr($keywords, ",") !== false ? "," : ";";

					$exif->setValue($keywords.$separator.$tag);
				}

				$exif->save();

				ExifPeer::writeExif(array("Keywords" => $keywords.$separator.$tag), $path);
			}

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_IPTC) && $file->getType() == FilePeer::__TYPE_PHOTO)
			{
				if(!$iptc = IptcPeer::getTag("Keywords", $file->getId()))
				{
					$iptc = new Iptc();
					$iptc->setTitle("Keywords");
					$iptc->setValue($tag);
					$iptc->setFileId($file->getId());
					$iptc->setCreatedAt(time());

					$separator = "";
					$keywords = "";
				} else {
					$keywords = $iptc->getValue();
					$separator = strstr($keywords, ",") !== false ? "," : ";";

					$iptc->setValue($keywords.$separator.$tag);
				}

				$iptc->save();

				IptcPeer::writeIptc(array('2#025' => $keywords.$separator.$tag), $path);
			}
	}

	public static function removeTag($file, $tag)
	{
		$path = $file->getPathname();

		if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_EXIF) && $file->getType() == FilePeer::__TYPE_PHOTO)
		{
			if($exif = ExifPeer::getTag("Keywords", $file->getId()))
			{
				$new_keywords = "";

				$separator = ";";
				$keywords = explode(";", $exif->getValue());

				if(count($keywords) > 1)
					;
				else
				{
					$temp = explode(',',$exif->getValue());

					if(count($temp) > count($keywords))
					{
						$separator = ",";
						$keywords = $temp;
					}
				}

				foreach($keywords as $keyword)
				{
					if($keyword != $tag)
						$new_keywords .= $keyword.$separator;
				}

				$new_keywords = substr($new_keywords, 0, -1);

				$exif->setValue($new_keywords);
				$exif->save();
			}
		}

		if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_IPTC) && $file->getType() == FilePeer::__TYPE_PHOTO)
		{
			if($iptc = IptcPeer::getTag("Keywords", $file->getId()))
			{
				$new_keywords = "";

				$separator = ";";
				$keywords = explode(";", $iptc->getValue());

				if(count($keywords) > 1)
					;
				else
				{
					$temp = explode(',',$iptc->getValue());

					if(count($temp) > count($keywords))
					{
						$separator = ",";
						$keywords = $temp;
					}
				}

				foreach($keywords as $keyword)
				{
					if($keyword != $tag)
						$new_keywords .= $keyword.$separator;
				}

				$new_keywords = substr($new_keywords, 0, -1);

				$iptc->setValue($new_keywords);
				$iptc->save();
			}
		}
	}

	public static function formatDateForComment($date)
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
		sfContext::getInstance()->getConfiguration()->loadHelpers("Date");
		
		return format_date($date, 'f');
	}

	public static function addTags($file, $write = true)
	{
		$path = $file->getPathname();

		$tags = FileTagPeer::retrieveByFileIdType(FileTagPeer::__TYPE_FILE, $file->getId());

		if(empty($tags))
			return;
		else
		{
			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_EXIF) && $file->getType() == FilePeer::__TYPE_PHOTO)
			{
				if($exif = ExifPeer::getTag("Keywords", $file->getId()))
				{
					$keywords = "";
					$separator = strstr($keywords, ",") !== false ? "," : ";";
				}
				else
				{
					$separator = ";";
					$keywords = "";
				}

				foreach($tags as $file_tag)
				{
					$tag = TagPeer::retrieveByPk($file_tag->getTagId());

					if(!empty($keywords))
						$keywords .= $separator.$tag->getTitle();
					else
						$keywords .= $tag->getTitle();
				}

				if(!$exif)
				{
					$exif = new Exif();
					$exif->setTitle("Keywords");
					$exif->setValue($keywords);
					$exif->setFileId($file->getId());
					$exif->setCreatedAt(time());
				}
				else
					$exif->setValue($keywords);

				$exif->save();
			}

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_META_IPTC) && $file->getType() == FilePeer::__TYPE_PHOTO)
			{
				if($iptc = IptcPeer::getTag("Keywords", $file->getId()))
				{
					$keywords = "";
					$separator = strstr($keywords, ",") !== false ? "," : ";";
				}
				else
				{
					$separator = ";";
					$keywords = "";
				}

				foreach($tags as $file_tag)
				{
					$tag = TagPeer::retrieveByPk($file_tag->getTagId());

					if(!empty($keywords))
						$keywords .= $separator.$tag->getTitle();
					else
						$keywords .= $tag->getTitle();
				}

				if(!$iptc)
				{
					$iptc = new Iptc();
					$iptc->setTitle("Keywords");
					$iptc->setValue($keywords);
					$iptc->setFileId($file->getId());
					$iptc->setCreatedAt(time());
				}
				else
					$iptc->setValue($keywords);

				$iptc->save();
			}
		}
	}

	public static function weekStarDate($week, $year, $format = 'Ymd', $date = FALSE)
	{
		if ($date) {
			$week = date("W", strtotime($date));
			$year = date("o", strtotime($date));
		}

		$week = sprintf("%02s", $week);

		$desiredMonday = date($format, strtotime("$year-W$week-1"));

		return $desiredMonday;
	}
}
?>