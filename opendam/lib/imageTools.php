<?php

class imageTools {

  public static function resizeImage($image,$width,$height,$scale) {
  	list($imagewidth, $imageheight, $imageType) = getimagesize($image);
  	$imageType = image_type_to_mime_type($imageType);
  	$newImageWidth = ceil($width * $scale);
  	$newImageHeight = ceil($height * $scale);
  	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
  	switch($imageType) {
  		case "image/gif":
  			$source=imagecreatefromgif($image); 
  			break;
  	    case "image/pjpeg":
  		case "image/jpeg":
  		case "image/jpg":
  			$source=imagecreatefromjpeg($image); 
  			break;
  	    case "image/png":
  		case "image/x-png":
  			$source=imagecreatefrompng($image); 
  			break;
    	}
  	imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$imagewidth,$imageheight);
  	
  	switch($imageType) {
  		case "image/gif":
  	  		imagegif($newImage,$image); 
  			break;
        	case "image/pjpeg":
  		case "image/jpeg":
  		case "image/jpg":
  	  		imagejpeg($newImage,$image,90); 
  			break;
  		case "image/png":
  		case "image/x-png":
  			imagepng($newImage,$image);  
  			break;
      }
  	
  	chmod($image, 0777);
  	return $image;
  }
 
  public static function writeWatermark($path, $customerId = 0) {
	$web_src = $path;

	$wOrientation = "SouthWest";

	$watermark_src = sfConfig::get('app_path_images_dir').'/watermarkTransparent.png';

	list($oWidth, $oHeight, $type, $attr) = getimagesize($web_src);
	list($wWidth, $wHeight, $type, $attr) = getimagesize($watermark_src);

	if($oWidth > $wWidth && $oHeight > $wHeight)
		$percent = 1;
	else
	{
		if($oWidth < $oHeight)
			$percent = $oWidth / $wWidth;
		else
			$percent = $oHeight / $wHeight;
	}

	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		shell_exec('composite ( '.escapeshellarg($watermark_src).' -resize '.$wWidth*$percent.'x'.$wHeight*$percent.'! ) -gravity '.$wOrientation.' -quality 100 '.escapeshellarg($web_src).' '.escapeshellarg($web_src));
	else
		shell_exec('composite \( '.escapeshellarg($watermark_src).' -resize '.$wWidth*$percent.'x'.$wHeight*$percent.'! \) -gravity '.$wOrientation.' -quality 100 '.escapeshellarg($web_src).' '.escapeshellarg($web_src));
  }

  public static function createVideo($name, $original, $path)
  {
  	proc_close(proc_open("php ".sfConfig::get("sf_root_dir")."/symfony video:encode ".escapeshellarg($name)." ".escapeshellarg($original)." ".escapeshellarg($path)." 2>&1 > /dev/null &", array(), $foo));
  }

  public static function createAudio($name, $original, $path)
  {
  	proc_close(proc_open("php ".sfConfig::get("sf_root_dir")."/symfony audio:encode ".escapeshellarg($name)." ".escapeshellarg($original)." ".escapeshellarg($path)." 2>&1 > /dev/null &", array(), $foo));
  }

  public static function encodeAudio($input, $output, $type)
  {
  	switch ($type) 	{
  		case "mp3":
  			exec("ffmpeg -i ".escapeshellarg($input)." ".escapeshellarg($output)." &");
  		break;
  	
  		case "wav":
  			exec("ffmpeg -i ".escapeshellarg($input)." -vn -acodec pcm_s16le -ar 16000 -ac 1 -f ".escapeshellarg($type)." ".escapeshellarg($output)." &");
  		break;
  	}
  }

  public static function encodeVideo($input, $output, $type)
  {
	$resolution=shell_exec("ffmpeg -i ".escapeshellarg($input)." 2>&1 | grep Video | head -n 1 | awk '{ print $7 }'");
	$arrayResolution = explode("x", $resolution);
	$width = $arrayResolution[0];
	$height = $arrayResolution[1];

	switch ($type) 	{
  		case "mp4":
  			if ($width > 1280 || $height > 720) {
  				exec("ffmpeg -i ".escapeshellarg($input)." -vcodec libx264 -vprofile high -s hd720 -f ".escapeshellarg($type)." ".escapeshellarg($output)." &");
  			}
  			else {
  				exec("ffmpeg -i ".escapeshellarg($input)." -vcodec libx264 -vprofile high -f ".escapeshellarg($type)." ".escapeshellarg($output)." &");
  			}
  		break;

  		case "webm":
  			if ($width > 1280 || $height > 720) {
  				exec("ffmpeg -i ".escapeshellarg($input)." -vcodec libvpx -cpu-used 0 -b:v 600k -maxrate 600k -bufsize 1200k -qmin 10 -qmax 42 -vf scale=-1:480 -threads 4 -b:a 128k -s hd720 -strict -2 -f ".escapeshellarg($type)." ".escapeshellarg($output)." &");
  			}
  			else {
  				exec("ffmpeg -i ".escapeshellarg($input)." -vcodec libvpx -cpu-used 0 -b:v 600k -maxrate 600k -bufsize 1200k -qmin 10 -qmax 42 -vf scale=-1:480 -threads 4 -b:a 128k -strict -2 -f ".escapeshellarg($type)." ".escapeshellarg($output)." &");
  			}
  		break;
  	}
  }

  public static function createThumbnail($name, $ext, $original, $path, $mime, $type)
  {
	$sizes = self::getSize($original);

	switch($type)
	{
		case 'thumb':
		{
			$file_name = $name."_thumb.".$ext;
			// $dimension = getimagesize($original);
			// $thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 720, $dimension[1], true);
			if($sizes[0] > 720)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 720, $sizes[1], true);
			else
				$thumbSizes = Array($sizes[0], $sizes[1]);
		}
		break;

		case 'thumb_100':
		{
			$file_name = $name."_thumb100.".$ext;

			if($sizes[0] > 100)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 100, 100, true);
			else
				$thumbSizes = Array($sizes[0], $sizes[1]);
		}
		break;

		case 'thumb_200':
		{
			$file_name = $name."_thumb200.".$ext;

			if($sizes[0] > 200)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 200, 200, true);
			else
				$thumbSizes = Array($sizes[0], $sizes[1]);
		}
		break;

		case 'thumb_mob':
		{
			$file_name = $name."_thumbMob.".$ext;
			$dimension = getimagesize($original);

			if($dimension[0] >= 1280 || $dimension[0] >= 720)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 1280, 720, true);
			else
				return null;
		}
		break;

		case 'thumb_mob_w':
		{
			$file_name = $name."_thumbMobW.".$ext;
			$dimension = getimagesize($original);

			if($dimension[0] >= 1280 || $dimension[0] >= 720)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 1280, 720, true);
			else
				return null;
		}
		break;

		case 'thumb_400':
		{
			$file_name = $name."_thumb400.".$ext;
			$dimension = getimagesize($original);

			if($dimension[0] >= 400 || $dimension[0] >= 400)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 400, 400, true);
			else
				return null;
		}
		break;

		case 'thumb_400_w':
		{
			$file_name = $name."_thumb400W.".$ext;
			$dimension = getimagesize($original);

			if($dimension[0] >= 400 || $dimension[0] >= 400)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 400, 400, true);
			else
				return null;
		}
		break;

		case 'thumb_tab':
		{
			$file_name = $name."_thumbTab.".$ext;
			$dimension = getimagesize($original);

			if($dimension[0] >= 2560 || $dimension[0] >= 1600)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 2560, 1600, true);
			else
				return null;
		}
		break;

		case 'thumb_tab_w':
		{
			$file_name = $name."_thumbTabW.".$ext;
			$dimension = getimagesize($original);

			if($dimension[0] >= 2560 || $dimension[0] >= 1600)
				$thumbSizes = self::getThumbSize($sizes[0], $sizes[1], 2560, 1600, true);
			else
				return null;
		}
		break;
	}

	shell_exec("convert ".escapeshellarg($original)." -resize ".$thumbSizes[0]."x".$thumbSizes[1]." ".escapeshellarg($path.$file_name));

	return $file_name;
  }

  public static function rotateImage($angle, $file)
  {
	$ext = strtolower(myTools::getFileExtension($file->getOriginal()));
	$name =  myTools::getFileNameFile($file->getOriginal());
	$mime = ($ext == "jpg") ? "jpeg" : $ext;
	$mime = strtolower($mime);

	$path = $file->getPath().DIRECTORY_SEPARATOR;

	shell_exec("convert ".escapeshellarg($path.$file->getOriginal())." -rotate ".$angle." ".escapeshellarg($path.$file->getOriginal()));

	$web = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb");
	$thumb100 = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_100");
	$thumb200 = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_200");
	$thumbMob = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_mob");
	$thumbTab = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_tab");
	$thumb400 = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_400");

	if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
	{
		self::writeWatermark($path.$web, $file->getCustomerId());

		$thumbMobW = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_mob_w");
		self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

		$thumbTabW = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_tab_w");
		self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

		$thumb400W = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_400_w");
		self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
	}
  }
  
  public static function cropImage($file, $width, $height, $x, $y)
  {
	if($x > 0)
		$x = "+".$x;

	if($y > 0)
		$y = "+".$y;

	$ext = strtolower(myTools::getFileExtension($file->getOriginal()));
	$name =  myTools::getFileNameFile($file->getOriginal());
	$mime = ($ext == "jpg") ? "jpeg" : $ext;
	$mime = strtolower($mime);

	$path = $file->getPath().DIRECTORY_SEPARATOR;

	shell_exec("convert ".escapeshellarg($path.$file->getOriginal())." -crop ".$width."x".$height.$x.$y." ".escapeshellarg($path.$file->getOriginal()));

	$web = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb");
	$thumb100 = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_100");
	$thumb200 = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_200");
	$thumbMob = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_mob");
	$thumbTab = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_tab");
	$thumb400 = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_400");

	if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
	{
		self::writeWatermark($path.$web, $file->getCustomerId());

		$thumbMobW = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_mob_w");
		self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

		$thumbTabW = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_tab_w");
		self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

		$thumb400W = self::createThumbnail($name, $ext, $path.$file->getOriginal(), $path, $mime, "thumb_400_w");
		self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
	}
  }

  	public static function initThumb($sourceWidth, $sourceHeight, $maxWidth, $maxHeight, $scale=false, $inflate=false)
	{
		if($maxWidth > 0)
			$ratioWidth = $maxWidth / $sourceWidth;

		if($maxHeight > 0)
			$ratioHeight = $maxHeight / $sourceHeight;

		if($scale)
		{
			if($maxWidth && $maxHeight)
			{
				if($ratioWidth < $ratioHeight)
				{
					$ratio = $ratioWidth;
					$use = "ratioWidth";
				}
				else
				{
					$ratio = $ratioHeight;
					$use = "ratioHeight";
				}
			}

			if($maxWidth xor $maxHeight)
			{
				if(isset($ratioWidth))
				{
					$ratio = $ratioWidth;
					$use = "ratioWidth";
				}
				else
				{
					$ratio = $ratioHeight;
					$use = "ratioHeight";
				}
			}

			if((!$maxWidth && !$maxHeight) || (!$inflate && $ratio > 1))
				$ratio = 1;

			if(floor($ratio * $sourceWidth) < $maxWidth)
			{
				switch($use)
				{
					case "ratioWidth": $ratio = $ratioHeight; break;
					case "ratioHeight": $ratio = $ratioWidth; break;
				}
			}
			elseif(ceil($ratio * $sourceHeight) < $maxHeight)
			{
				switch($use)
				{
					case "ratioWidth": $ratio = $ratioHeight; break;
					case "ratioHeight": $ratio = $ratioWidth; break;
				}
			}

			return Array("width" => floor($ratio * $sourceWidth), "height" => ceil($ratio * $sourceHeight));
		}
		else
		{
			if(!isset($ratioWidth) || (!$inflate && $ratioWidth > 1))
				$ratioWidth = 1;

			if(!isset($ratioHeight) || (!$inflate && $ratioHeight > 1))
				$ratioHeight = 1;

			return Array("width" => floor($ratioWidth * $sourceWidth), "height" => ceil($ratioHeight * $sourceHeight));
		}
	}

	public static function regenerateThumbnail($file)
	{
		$path = $file->getPath().'/';
		$ext = strtolower(myTools::getFileExtension($file->getOriginal()));
		$name =  myTools::getFileNameFile($file->getOriginal());
		$mime = ($ext == "jpg") ? "jpeg" : $ext;
		$mime = strtolower($mime);
		$original = $name.'.'.$ext;

		@unlink($path.$file->getWeb());
		@unlink($path.$file->getThumb200());
		@unlink($path.$file->getThumb100());
		@unlink($path.$file->getThumbMob());
		@unlink($path.$file->getThumbMobW());
		@unlink($path.$file->getThumbTab());
		@unlink($path.$file->getThumbTabW());
		@unlink($path.$file->getThumb400());
		@unlink($path.$file->getThumb400W());

		$videoFormat = explode(";",ConfigurationPeer::retrieveByType("video_format_allowed")->getValue());
		$audioFormat = explode(";",ConfigurationPeer::retrieveByType("audio_format_allowed")->getValue());
		$documentFormat = explode(";",ConfigurationPeer::retrieveByType("document_format_allowed")->getValue());
		$previewType = explode(";",ConfigurationPeer::retrieveByType("_no_preview_format")->getValue());
		$convert2png = explode(";",ConfigurationPeer::retrieveByType("_file_convert2png")->getValue());
		$convert2jpeg = explode(";",ConfigurationPeer::retrieveByType("_file_convert2jpeg")->getValue());
		$convert2flv = explode(";",ConfigurationPeer::retrieveByType("_video_convert2flv")->getValue());

		$thumbMob = null;
		$thumbMobW = null;
		$thumbTab = null;
		$thumbTabW = null;
		$thumb400 = null;
		$thumb400W = null;

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
				self::writeWatermark($path.$web,$file->getCustomerId());
		}
		elseif(in_array($ext, array('cr2')))
		{
			$temp = $path.$name.'.jpeg';
			$mime = "jpeg";

			shell_exec("dcraw -T -w -c -v ".escapeshellarg($path.$original)." > ".escapeshellarg($path.$name.".tiff"));
			shell_exec("convert ".escapeshellarg($path.$name.'.tiff')." ".escapeshellarg($temp));

			@unlink($path.$name.'.tiff');

			$web = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
			$thumb100 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
			$thumb200 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
			$thumbMob = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
			$thumbTab = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
			$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				self::writeWatermark($path.$web,$file->getCustomerId());

				$thumbMobW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
				self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

				$thumbTabW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
				self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

				$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
			}

			@unlink($temp);
		}
		elseif(in_array($ext, $convert2png))
		{
			$temp = $path.$name.'.png';
			$mime = "png";

			shell_exec("convert ".escapeshellarg($path.$original)." -layers flatten ".escapeshellarg($temp));

			$web = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb");
			$thumb100 = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_100");
			$thumb200 = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_200");
			$thumbMob = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_mob");
			$thumbTab = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_tab");
			$thumb400 = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				self::writeWatermark($path.$web,$file->getCustomerId());

				$thumbMobW = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_mob_w");
				self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

				$thumbTabW = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_tab_w");
				self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

				$thumb400W = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
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

			$web = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
			$thumb100 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
			$thumb200 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
			$thumbMob = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
			$thumbTab = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
			$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				self::writeWatermark($path.$web,$file->getCustomerId());

				$thumbMobW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
				self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

				$thumbTabW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
				self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

				$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
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

			$web = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
			$thumb100 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
			$thumb200 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
			$thumbMob = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
			$thumbTab = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
			$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

			self::createVideo($name, $original, $path);

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				self::writeWatermark($path.$web,$file->getCustomerId());

				$thumbMobW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
				self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

				$thumbTabW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
				self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

				$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
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

			self::createAudio($name, $original, $path);

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

			if ($exec == 'CMYK')
			{
				$temp = $path.$name.'_RGB.'.$ext;
				shell_exec("cp ".escapeshellarg($path.$original)." ".escapeshellarg($temp));

				shell_exec("convert ".escapeshellarg($temp)." -colorspace sRGB -modulate 110 ".escapeshellarg($temp));

				$origN = $temp;
				$doDel = true;
			}
			else
			{
				$origN = $path.$original;
				$doDel = false;
			}

			$web = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb");
			$thumb100 = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_100");
			$thumb200 = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_200");
			$thumbMob = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_mob");
			$thumbTab = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_tab");
			$thumb400 = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				self::writeWatermark($path.$web,$file->getCustomerId());

				$thumbMobW = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_mob_w");
				self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

				$thumbTabW = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_tab_w");
				self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

				$thumb400W = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
			}

			if($doDel)
				@unlink($origN);
		}

		$file->setWeb($web);
		$file->setThumb200($thumb200);
		$file->setThumb100($thumb100);
		$file->setThumbMob($thumbMob);
		$file->setThumbMobW($thumbMobW);
		$file->setThumbTab($thumbTab);
		$file->setThumbTabW($thumbTabW);
		$file->setThumb400($thumb400);
		$file->setThumb400W($thumb400W);

		$file->save();
	}

	public static function regenerateThumbnailWithProfile($file, $profile)
	{
		$path = $file->getPath().'/';
		$ext = strtolower(myTools::getFileExtension($file->getOriginal()));
		$name =  myTools::getFileNameFile($file->getOriginal());
		$mime = ($ext == "jpg") ? "jpeg" : $ext;
		$mime = strtolower($mime);
		$original = $name.'.'.$ext;

		$thumbMobW = null;
		$thumbTabW = null;
		$thumb400W = null;

		@unlink($path.$file->getWeb());
		@unlink($path.$file->getThumb200());
		@unlink($path.$file->getThumb100());
		@unlink($path.$file->getThumbMob());
		@unlink($path.$file->getThumbMobW());
		@unlink($path.$file->getThumbTab());
		@unlink($path.$file->getThumbTabW());
		@unlink($path.$file->getThumb400());
		@unlink($path.$file->getThumb400W());

		$temp = $path.$name.'.jpeg';

		shell_exec("convert -density 300 ".escapeshellarg($path.$original)." -resize 5000X5000 ".escapeshellarg($temp));

		$web = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
		$thumb100 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
		$thumb200 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
		$thumbMob = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
		$thumbTab = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
		$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

		if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
		{
			self::writeWatermark($path.$web, $file->getCustomerId());

			$thumbMobW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
			self::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

			$thumbTabW = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
			self::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

			$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
			self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
		}

		@unlink($temp);

		$file->setWeb($web);
		$file->setThumb200($thumb200);
		$file->setThumb100($thumb100);
		$file->setThumbMob($thumbMob);
		$file->setThumbMobW($thumbMobW);
		$file->setThumbTab($thumbTab);
		$file->setThumbTabW($thumbTabW);
		$file->setThumb400($thumb400);
		$file->setThumb400W($thumb400W);

		$file->save();
	}

	public static function getSize($image)
	{
		$imgData = @getimagesize($image);
		if(!$imgData)
		{
			exec('identify '.escapeshellarg($image), $stdout, $retval);
			if($retval === 1)
			{
				throw new Exception('Image could not be identified. ('.$image.')');
			}
			else
			{
				// get image data via identify
				list($img, $type, $dimen) = explode(' ', $stdout[0]);
				list($width, $height) = explode('x', $dimen);

				return array($width, $height);
			}
		}
		else
		{
			return array($imgData[0], $imgData[1]);
		}
	}

	public static function getThumbSize($sourceWidth, $sourceHeight, $maxWidth, $maxHeight, $scale)
	{
		if($maxWidth > 0)
		{
			$ratioWidth = $maxWidth / $sourceWidth;
		}
		if($maxHeight > 0)
		{
			$ratioHeight = $maxHeight / $sourceHeight;
		}

		if($scale)
		{
			if($maxWidth && $maxHeight)
			{
				$ratio = ($ratioWidth < $ratioHeight) ? $ratioWidth : $ratioHeight;
			}
			if($maxWidth xor $maxHeight)
			{
				$ratio = (isset($ratioWidth)) ? $ratioWidth : $ratioHeight;
			}
			if(!$maxWidth && !$maxHeight)
			{
				$ratio = 1;
			}

			return array(floor($ratio * $sourceWidth), ceil($ratio * $sourceHeight));
		}
		else
		{
			if(!isset($ratioWidth))
			{
				$ratioWidth = 1;
			}
			if(!isset($ratioHeight))
			{
				$ratioHeight = 1;
			}

			return array(floor($ratioWidth * $sourceWidth), ceil($ratioHeight * $sourceHeight));
		}
	}

	public static function writeWatermarkThumb($path, $customerId = 0)
	{
		$web_src = $path;

		$wOrientation = "SouthWest";

		if(file_exists($web_src) && is_file($web_src))
		{
			if(empty($customerId))
				$customerId = sfContext::getInstance()->getUser()->getCustomerId();

			$watermark_src = sfConfig::get('app_path_images_dir').'/watermarkTransparent.png';

			list($oWidth, $oHeight, $type, $attr) = getimagesize($web_src);
			list($wWidth, $wHeight, $type, $attr) = getimagesize($watermark_src);

			if($oWidth > $wWidth && $oHeight > $wHeight)
				$percent = 1;
			else
			{
				if($oWidth < $oHeight)
					$percent = $oWidth / $wWidth;
				else
					$percent = $oHeight / $wHeight;
			}

			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				shell_exec('composite ( '.escapeshellarg($watermark_src).' -resize '.$wWidth*$percent.'x'.$wHeight*$percent.'! ) -gravity '.$wOrientation.' -quality 100 '.escapeshellarg($web_src).' '.escapeshellarg($web_src));
			else
				shell_exec('composite \( '.escapeshellarg($watermark_src).' -resize '.$wWidth*$percent.'x'.$wHeight*$percent.'! \) -gravity '.$wOrientation.' -quality 100 '.escapeshellarg($web_src).' '.escapeshellarg($web_src));
		}
	}

	public static function setThumbnailForFolder($path, $file)
	{
		$name = time();
		$ext = strtolower(myTools::getFileExtension($file->getOriginal()));

		if (!file_exists($path)) {
			@mkdir($path, 0755);
		}

		@copy($file->getPath()."/".$file->getOriginal(), $path."/".$name.'.'.$ext);

		$path .= '/';
		$mime = ($ext == "jpg") ? "jpeg" : $ext;
		$mime = strtolower($mime);
		$original = $name.'.'.$ext;

		$videoFormat = explode(";",ConfigurationPeer::retrieveByType("video_format_allowed")->getValue());
		$audioFormat = explode(";",ConfigurationPeer::retrieveByType("audio_format_allowed")->getValue());
		$documentFormat = explode(";",ConfigurationPeer::retrieveByType("document_format_allowed")->getValue());
		$previewType = explode(";",ConfigurationPeer::retrieveByType("_no_preview_format")->getValue());
		$convert2png = explode(";",ConfigurationPeer::retrieveByType("_file_convert2png")->getValue());
		$convert2jpeg = explode(";",ConfigurationPeer::retrieveByType("_file_convert2jpeg")->getValue());
		$convert2flv = explode(";",ConfigurationPeer::retrieveByType("_video_convert2flv")->getValue());

		$thumbMob = null;
		$thumbMobW = null;
		$thumbTab = null;
		$thumbTabW = null;
		$thumb400 = null;
		$thumb400W = null;

		if(in_array($ext, $previewType))
		{
			$web = $name."_thumb.png";

			@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$web);
		}
		elseif(in_array($ext, $documentFormat))
		{
			if(in_array($ext, $convert2jpeg))
			{
				$temp = $path.$name.'.jpeg';
				$mime = "jpeg";

				shell_exec("convert ".escapeshellarg($path.$original.'[0]')." ".escapeshellarg($temp));

				$web = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");

				@unlink($temp);
			}
			else
			{
				$web = $name."_thumb.png";

				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$web);
			}
		}
		elseif(in_array($ext, array('cr2')))
		{
			$temp = $path.$name.'.jpeg';
			$mime = "jpeg";

			shell_exec("dcraw -T -w -c -v ".escapeshellarg($path.$original)." > ".escapeshellarg($path.$name.".tiff"));
			shell_exec("convert ".escapeshellarg($path.$name.'.tiff')." ".escapeshellarg($temp));

			@unlink($path.$name.'.tiff');

			$web = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");

			@unlink($temp);
		}
		elseif(in_array($ext, $convert2png))
		{
			$temp = $path.$name.'.png';
			$mime = "png";

			shell_exec("convert ".escapeshellarg($path.$original)." -layers flatten ".escapeshellarg($temp));

			$web = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb");

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

			$web = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");

			@unlink($temp);
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

			if ($exec == 'CMYK')
			{
				$temp = $path.$name.'_RGB.'.$ext;
				shell_exec("cp ".escapeshellarg($path.$original)." ".escapeshellarg($temp));

				shell_exec("convert ".escapeshellarg($temp)." -colorspace sRGB -modulate 110 ".escapeshellarg($temp));

				$origN = $temp;
				$doDel = true;
			}
			else
			{
				$origN = $path.$original;
				$doDel = false;
			}

			$web = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb");

			if($doDel)
				@unlink($origN);
		}

		unlink($path."/".$name.'.'.$ext);

		return $web;
	}

	public static function regenerateThumbnail400($file)
	{
		$path = $file->getPath().'/';
		$ext = strtolower(myTools::getFileExtension($file->getOriginal()));
		$name =  myTools::getFileNameFile($file->getOriginal());
		$mime = ($ext == "jpg") ? "jpeg" : $ext;
		$mime = strtolower($mime);
		$original = $name.'.'.$ext;


		$videoFormat = explode(";",ConfigurationPeer::retrieveByType("video_format_allowed")->getValue());
		$audioFormat = explode(";",ConfigurationPeer::retrieveByType("audio_format_allowed")->getValue());
		$documentFormat = explode(";",ConfigurationPeer::retrieveByType("document_format_allowed")->getValue());
		$previewType = explode(";",ConfigurationPeer::retrieveByType("_no_preview_format")->getValue());
		$convert2png = explode(";",ConfigurationPeer::retrieveByType("_file_convert2png")->getValue());
		$convert2jpeg = explode(";",ConfigurationPeer::retrieveByType("_file_convert2jpeg")->getValue());
		$convert2flv = explode(";",ConfigurationPeer::retrieveByType("_video_convert2flv")->getValue());

		$thumb400 = null;
		$thumb400W = null;

		if(in_array($ext, $documentFormat))
		{
			if(in_array($ext, $convert2jpeg))
			{
				$temp = $path.$name.'.jpeg';
				$mime = "jpeg";

				shell_exec("convert ".escapeshellarg($path.$original.'[0]')." ".escapeshellarg($temp));

				$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

				if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
				{
					$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
					self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
				}

				@unlink($temp);
			}
		}
		elseif(in_array($ext, array('cr2')))
		{
			$temp = $path.$name.'.jpeg';
			$mime = "jpeg";

			shell_exec("dcraw -T -w -c -v ".escapeshellarg($path.$original)." > ".escapeshellarg($path.$name.".tiff"));
			shell_exec("convert ".escapeshellarg($path.$name.'.tiff')." ".escapeshellarg($temp));

			@unlink($path.$name.'.tiff');

			$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
			}

			@unlink($temp);
		}
		elseif(in_array($ext, $convert2png))
		{
			$temp = $path.$name.'.png';
			$mime = "png";

			shell_exec("convert ".escapeshellarg($path.$original)." -layers flatten ".escapeshellarg($temp));

			$thumb400 = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				$thumb400W = self::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
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

			$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
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

			$thumb400 = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				$thumb400W = self::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
			}

			@unlink($temp);
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

			if ($exec == 'CMYK')
			{
				$temp = $path.$name.'_RGB.'.$ext;
				shell_exec("cp ".escapeshellarg($path.$original)." ".escapeshellarg($temp));

				shell_exec("convert ".escapeshellarg($temp)." -colorspace sRGB -modulate 110 ".escapeshellarg($temp));

				$origN = $temp;
				$doDel = true;
			}
			else
			{
				$origN = $path.$original;
				$doDel = false;
			}

			$thumb400 = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_400");

			if(sfContext::getInstance()->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK))
			{
				$thumb400W = self::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_400_w");
				self::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
			}

			if($doDel)
				@unlink($origN);
		}

		$file->setThumb400($thumb400);
		$file->setThumb400W($thumb400W);

		$file->save();
	}
}
?>