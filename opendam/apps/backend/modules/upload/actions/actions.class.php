<?php

/**
 * ajax actions.
 *
 * @package    jurj
 * @subpackage ajax
 * @author     Ariunbayar, Others
 * @version    SVN: $Id: actions.class.php 2692 2006-11-15 21:03:55Z fabien $
 */
class uploadActions extends sfActions
{
	/*________________________________________________________________________________________________________________*/
	public function preExecute()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("I18N");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeObserveGroupId()
	{
	if($this->getRequest()->isXmlHttpRequest())
	{
		$this->forward404Unless($group = GroupePeer::retrieveByPK($this->getRequestParameter("group_id")));
		$this->folders = FolderPeer::getUploadFoldersPath($group->getId(), $this->getUser()->getId());

		$this->setLayout(false);
		return sfView::SUCCESS;
	}

	$this->redirect404();
	}

	/*________________________________________________________________________________________________________________*/
	# STEP3 OPTION
	public function executeOption()
	{
		$folderId = $this->getRequestParameter("folder");

		$folder = FolderPeer::retrieveByPK($folderId);

		$this->forward404Unless($folder);

		$this->forward404Unless($albumRole = $this->getUser()->getRole($folder->getGroupeId()));
		$this->forward404If($albumRole > RolePeer::__CONTRIB);

		$this->forward404Unless($folderRole = $this->getUser()->getRole($folder->getGroupeId(), $folder->getId()));

		$fileTmps = FileTmpPeer::retrieveByUserIdFolderId($this->getUser()->getId(), $folder->getId());

		// no uploaded file
		if(!sizeof($fileTmps)){
			$this->getUser()->setFlash("warning", __("Please upload 1 file at least."), true);
			$this->forward('upload', 'uploadify');
		}

		$this->redirect("file/editSelected?folder_id=".$folder->getId()."&navigation=upload&first_call1=1");
	}

	/*________________________________________________________________________________________________________________*/
	public function executeUploadify(sfWebRequest $request)
	{
		$folderId = $request->getParameter("folder_id");

		$folder = FolderPeer::retrieveByPK($folderId);
		$this->forward404Unless($folder);

		$album = $folder->getGroupe();

		$roleAlbum = $this->getUser()->getRole($album->getId());
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__CONTRIB);

		$roleFolder = $this->getUser()->getRole($album->getId(), $folder->getId());
		$this->forward404Unless($roleFolder);

		$key = new UniqueKey();
		$key->setUserId($this->getUser()->getId());
		$key->setCreatedAt(time());
		$key->setExpiredAt(time());
		$key->setIp(@$_SERVER['REMOTE_ADDR']);
		$key->setUri(@$_SERVER['REQUEST_URI']);
		$key->setReferer(@$_SERVER['REFERER']);
		$key->save();

		$this->albums = GroupePeer::getUploadGroups($this->getUser()->getId());
		$this->folders = FolderPeer::getUploadFoldersPath($album->getId(), $this->getUser()->getId());
		$this->folder = $folder;
		$this->key = $key;

		FileTmpPeer::deleteByFolderId($folder->getId());

		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRedirect()
	{
		sfContext::getInstance()->getConfiguration()->loadHelpers("Url");
	
		if($this->getRequestParameter("link") != "")
			$this->uri = url_for("folder/publicShow?link=".$this->getRequestParameter("link"));
		else
			$this->uri = url_for("folder/show?id=".$this->getRequestParameter("folder_id"));
	
		return sfView::SUCCESS;
	}

	/*________________________________________________________________________________________________________________*/
	public function executeRedirectToUpload()
	{
		if (!$this->getRequestParameter("folder_id"))
			$this->redirect404();

		$this->getResponse()->setSlot('title', __("Upload files"));
		$this->folder_id = $this->getRequestParameter("folder_id");

		return sfView::SUCCESS;
	}
	
	public function executeIdentifyFile(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$key = $request->getParameter("keyId");

		$this->restoreSession($key);

		$uniqueId = base64_encode(uniqid($this->getUser()->getId()));
		$target = sys_get_temp_dir()."/";

		do {
			$uniqueId = base64_encode(uniqid($this->getUser()->getId()));
		}
		while(file_exists($target.$uniqueId));

		file_put_contents($target.$uniqueId, "");

		echo $uniqueId;

		return sfView::NONE;
	}

	public function executeUploadFile(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$name = $request->getParameter("name");
		$index = $request->getParameter("index");
		$key = $request->getParameter("keyId");
		$file = $request->getFiles("file");
		$target = sys_get_temp_dir()."/".$name."-".$index;

		$this->restoreSession($key);

		$this->forward404Unless($name);

		$this->forward404Unless($request->hasParameter("index"));
		$this->forward404Unless(preg_match("/^[0-9]+$/", $index));

		$this->forward404Unless($file);
		$this->forward404If($file["error"] != 0);

		$this->forward404Unless(preg_match("/^[0-9]+$/", $index));

		move_uploaded_file($file['tmp_name'], $target);

		sleep(1);

		return sfView::NONE;
	}

	public function executeMergeFile(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$name = $request->getParameter("name");
		$originalName = $request->getParameter("originalName");
		$index = $request->getParameter("index");
		$key = $request->getParameter("keyId");
		$folderId = $request->getParameter("folderId");
		$target = sys_get_temp_dir()."/".$name;

		$this->restoreSession($key);

		$this->forward404Unless($name);

		$this->forward404Unless($originalName);

		$this->forward404Unless($request->hasParameter("index"));
		$this->forward404Unless(preg_match("/^[0-9]+$/", $index));

		$folder = FolderPeer::retrieveByPK($folderId);
		$this->forward404Unless($folder);

		$roleAlbum = $this->getUser()->getRole($folder->getGroupeId());
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__CONTRIB);

		$roleFolder = $this->getUser()->getRole($folder->getGroupeId(), $folder->getId());
		$this->forward404Unless($roleFolder);

		$dst = fopen($target, "wb");

		for ($i = 0; $i < $index; $i++) {
			$slice = $target.'-'.$i;
			$src = fopen($slice, "rb");

			stream_copy_to_stream($src, $dst);

			fclose($src);
			unlink($slice);
		}

		fclose($dst);

		$fileUp = array(
				"name"		=> $originalName,
				"tmp_name"	=> $target
		);

		if (!$folder->getDiskId()) {
			$folder->setDiskId($this->getUser()->getDisk()->getId());
			$folder->save();
		}

		$file = myTools::addFile($fileUp, $folder, null, true, null);

		return sfView::NONE;
	}

	public function executeMergeFileForReplacing(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		function regexAccentsPath($chaine)
		{
			$accent = array('à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ð','ò','ó','ô','õ','ö','ù','ú','û','ü','ý','ý','ÿ',' ','"',"'");
			$inter = array('a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','o','o','o','o','o','o','u','u','u','u','y','y','y','_','_','_');

			$temp = str_replace($accent,$inter,$chaine);

			return preg_replace('/[^a-zA-Z0-9.-]/', "_", $temp);
		}
	
		$name = $request->getParameter("name");
		$originalName = $request->getParameter("originalName");
		$index = $request->getParameter("index");
		$key = $request->getParameter("keyId");
		$fileId = $request->getParameter("fileId");
		$target = sys_get_temp_dir()."/".$name;

		$this->restoreSession($key);

		$this->forward404Unless($name);

		$this->forward404Unless($originalName);

		$this->forward404Unless($request->hasParameter("index"));
		$this->forward404Unless(preg_match("/^[0-9]+$/", $index));
	
		$file = FilePeer::retrieveByPK($fileId);

		$this->forward404Unless($file);

		$folder = $file->getFolder();
		$album = $file->getGroupe();

		$roleAlbum = $this->getUser()->getRole($album->getId());
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__CONTRIB);

		$roleFolder = $this->getUser()->getRole($album->getId(), $folder->getId());
		$this->forward404Unless($roleFolder);

		$dst = fopen($target, "wb");

		for ($i = 0; $i < $index; $i++) {
			$slice = $target.'-'.$i;
			$src = fopen($slice, "rb");

			stream_copy_to_stream($src, $dst);

			fclose($src);
			unlink($slice);
		}

		fclose($dst);

		$fileUp = array(
				"name"		=> $originalName,
				"tmp_name"	=> $target
		);

		$filename = regexAccentsPath($fileUp["name"]);
		$path = $file->getPath().'/';
		$ext = strtolower(myTools::getFileExtension($filename));
		$name =myTools::getFileNameFile($file->getOriginal());
		$mime = ($ext == "jpg") ? "jpeg" : $ext;
		$mime = strtolower($mime);
		$original = $name.'.'.$ext;

		$exts = unserialize(base64_decode($this->getUser()->getModuleValue(ModulePeer::__MOD_TYPE_ALLOWED)));

		if (in_array($ext, $exts)) {
			@unlink($path.$file->getWeb());
			@unlink($path.$file->getThumb200());
			@unlink($path.$file->getThumb100());
			@unlink($path.$file->getThumbMob());
			@unlink($path.$file->getThumbMobW());
			@unlink($path.$file->getThumbTab());
			@unlink($path.$file->getThumbTabW());
			@unlink($path.$file->getThumb400());
			@unlink($path.$file->getThumb400W());
			@unlink($path.$file->getOriginal());

			@rename($fileUp['tmp_name'], $path.$original);

			$videoFormat = explode(";",ConfigurationPeer::retrieveByType("video_format_allowed")->getValue());
			$audioFormat = explode(";",ConfigurationPeer::retrieveByType("audio_format_allowed")->getValue());
			$documentFormat = explode(";",ConfigurationPeer::retrieveByType("document_format_allowed")->getValue());
			$previewType = explode(";",ConfigurationPeer::retrieveByType("_no_preview_format")->getValue());
			$convert2png = explode(";",ConfigurationPeer::retrieveByType("_file_convert2png")->getValue());
			$convert2jpeg = explode(";",ConfigurationPeer::retrieveByType("_file_convert2jpeg")->getValue());
			$convert2flv = explode(";",ConfigurationPeer::retrieveByType("_video_convert2flv")->getValue());

			$thumbMob = null;
			$thumbTab = null;
			$thumbMobW = null;
			$thumbTabW = null;
			$thumb400 = null;
			$thumb400W = null;
		
			if (in_array($ext, $previewType)) {
				$web = $name."_thumb.png";
				$thumb200 = $name."_thumb200.png";
				$thumb100 = $name."_thumb100.png";

				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$web);
				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$thumb200);
				@copy(file_exists(sfConfig::get('app_path_upload_dir').'/'.$ext.'.png') ? sfConfig::get('app_path_upload_dir').'/'.$ext.'.png' : sfConfig::get('app_path_upload_dir').'/other.png', $path.$thumb100);
				$mime = "png";

				if ($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($path.$web,$file->getCustomerId());
				}
			}
			else if (in_array($ext, array('cr2'))) {
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

				if ($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($path.$web,$file->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
				}

				@unlink($temp);
			}
			else if (in_array($ext, $convert2png)) {
				$temp = $path.$name.'.png';
				$mime = "png";

				shell_exec("convert ".escapeshellarg($path.$original)." -layers flatten ".escapeshellarg($temp));

				$web = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb");
				$thumb100 = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_100");
				$thumb200 = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_200");
				$thumbMob = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_mob");
				$thumbTab = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_tab");
				$thumb400 = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400");

				if ($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($path.$web,$file->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "png", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
				}

				@unlink($temp);
			}
			else if (in_array($ext, $convert2jpeg)) {
				$temp = $path.$name.'.jpeg';
				$mime = "jpeg";

				if (in_array(strtolower($ext), Array('eps', 'tif', 'tiff'))) {
					shell_exec("convert -density 300 ".escapeshellarg($path.$original)." -resize 5000x5000 ".escapeshellarg($temp));
				}
				else {
					shell_exec("convert ".escapeshellarg($path.$original.'[0]')." ".escapeshellarg($temp));
				}

				$web = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb");
				$thumb100 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_100");
				$thumb200 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_200");
				$thumbMob = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob");
				$thumbTab = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab");
				$thumb400 = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400");

				if ($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($path.$web,$file->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
				}

				@unlink($temp);
			}
			else if (in_array($ext, $convert2flv)) {
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

				if ($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($path.$web,$file->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, "jpeg", $temp, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
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
			else {
				$exif = @exif_read_data($path.$original);
				$orientation = @$exif['Orientation'];

				switch ($orientation) {
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

				if ($this->getUser()->haveAccessModule(ModulePeer::__MOD_WATERMARK)) {
					imageTools::writeWatermark($path.$web,$file->getCustomerId());

					$thumbMobW = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_mob_w");
					imageTools::writeWatermarkThumb($path.$thumbMobW,$file->getCustomerId());

					$thumbTabW = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_tab_w");
					imageTools::writeWatermarkThumb($path.$thumbTabW,$file->getCustomerId());

					$thumb400W = imageTools::createThumbnail($name, $ext, $origN, $path, $mime, "thumb_400_w");
					imageTools::writeWatermarkThumb($path.$thumb400W,$file->getCustomerId());
				}
			}

			$sizes = getimagesize($path.$original);

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
			$file->setWidth($sizes[0]);
			$file->setHeight($sizes[1]);
			$file->setSize(filesize($path.$original));
			$file->setExtention($ext);
			$file->save();
		}

		$this->getUser()->setFlash("success", "The file was successfully replaced.", true);

		return sfView::NONE;
	}

	protected function restoreSession($keyId)
	{
		if (!$this->getUser()->isAuthenticated()) {
			$this->forward404Unless($key = UniqueKeyPeer::retrieveByPk($keyId));
			$this->forward404Unless($user = UserPeer::retrieveByPK($key->getUserId()));

			$this->getContext()->getUser()->signIn($user);

			if ($key->getSessionParams()) {
				$params = unserialize(base64_decode($key->getSessionParams()));
			
				foreach ($params as $name => $value) {
					$this->getUser()->setAttribute($name, $value);
				}
			}
		}
	}

	public function executeGetFolders(sfWebRequest $request)
	{
		if (!$request->isXmlHttpRequest()) {
			$this->forward404();
		}

		$response = $this->getResponse();
		$response->setContentType("application/json");

		$albumId = $request->getParameter("album");

		$album = GroupePeer::retrieveByPK($albumId);

		$this->forward404Unless($album);

		$roleAlbum = $this->getUser()->getRole($album->getId());
		$this->forward404If(!$roleAlbum || $roleAlbum > RolePeer::__CONTRIB);

		$folders = FolderPeer::getUploadFoldersPath($album->getId(), $this->getUser()->getId());

		$json = array();

		foreach ($folders as $id => $label) {
			array_push($json, array("id" => $id, "label" => $label));
		}

		echo json_encode($json);

		return sfView::NONE;
	}
}