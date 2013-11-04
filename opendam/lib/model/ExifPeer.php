<?php

/**
 * Subclass for performing query and update operations on the 'exif' table.
 *
 * 
 *
 * @package lib.model
 */ 
class ExifPeer extends BaseExifPeer
{
  public static function setExif($exif_data, $file_id, $pre="")
  {
	if($exif_data !== false)
	{
		foreach ($exif_data as $key => $section)
		{
			if(is_array($section)) {
				self::setExif($section, $file_id, $pre.$key.".");
			} else {
				if($pre.$key == "DateTimeOriginal")
				{
					$pattern = "/^\d{4}-\d{2}-\d{2}\s\d{2}\:\d{2}\:\d{2}$/"; /* YYYY-MM-DD HH:MM:SS */
					$pattern2 = "/^\d{4}\:\d{2}\:\d{2}\s\d{2}\:\d{2}\:\d{2}$/"; /* YYYY:MM:DD HH:MM:SS */

					if(preg_match($pattern, $section))
					{
						$temp = explode(" ", $section);
						$date = explode("-", $temp[0]);

						$section = $date[2]."/".$date[1]."/".$date[0]." ".$temp[1];
					}

					if(preg_match($pattern2, $section))
					{
						$temp = explode(" ", $section);
						$date = explode(":", $temp[0]);

						$section = $date[2]."/".$date[1]."/".$date[0]." ".$temp[1];
					}
				}

				$exif = new Exif();
				$exif->setTitle($pre.$key);
				$exif->setValue($section);
				$exif->setFileId($file_id);
				$exif->setCreatedAt(time());
				$exif->save();
			}
		}
	}
  }

  public static function getAllTags($file_id)
  {
	$exif = new Criteria();
	$exif->add(self::FILE_ID, $file_id);
	$exif->addAscendingOrderByColumn(self::TITLE);
	$exifs = self::doSelect($exif);
	return $exifs;
  }

  public static function getTag($tag_name, $file_id)
  {
	$exif = new Criteria();
	$exif->add(self::FILE_ID, $file_id);
	$exif->add(self::TITLE, $tag_name);
	return self::doSelectOne($exif);
  }

  public static function getDistinctTitle()
  {
	$c = new Criteria();
	$c->clearSelectColumns();
	$c->addSelectColumn(self::TITLE);
	$c->setDistinct();
	$c->addAscendingOrderByColumn(self::TITLE);

	$stm = self::doSelectStmt($c);
	$result = $stm->fetchAll();

	$titles_array = array();
	foreach($result as $title)
		$titles_array[$title["TITLE"]] = $title["TITLE"];

	return $titles_array;
  }

  public static function search($engine, $criteria)
  {
	$ids = array();

	$engine->setMode(SPH_MATCH_EXTENDED);
	$engine->setIndex("exifs");
	$ids = $engine->search($criteria);

	return empty($ids) ? Array() : $ids;
  }

  public static function writeExif($exif, $path)
  {
	/*$cmd = "cat ".escapeshellarg($path);
	$tmp = $path.".".time();

	foreach($exif as $tag => $string)
	{
		switch(strtolower($tag))
		{
			case "artist": $cmd = "exiftool -artist=".escapeshellarg($string)." -overwrite_original ".escapeshellarg($path); break;
			case "author": $cmd = "exiftool -author=".escapeshellarg($string)." -overwrite_original ".escapeshellarg($path); break;
			case "title": $cmd = "exiftool -title=".escapeshellarg($string)." -overwrite_original ".escapeshellarg($path); break;
			case "keywords":
			{
				$keywords = explode(";", $string);

				if(count($keywords) > 1)
					;
				else
				{
					$temp = explode(',', $string);

					if(count($temp) > count($keywords))
						$keywords = $temp;
				}

				if(is_array($keywords))
				{
					$cmd .= " | exiftool";
					foreach($keywords as $substring)
						$cmd .= " -".strtolower($tag)."+=".escapeshellarg($substring);
				}
				else
					$cmd .= " | exiftool -".strtolower($tag)."+=".escapeshellarg($string);

				$cmd .= " -> ".escapeshellarg($tmp);
			}
			break;
		}

		shell_exec($cmd);
		@rename($tmp, $path);
	}*/
  }
}
