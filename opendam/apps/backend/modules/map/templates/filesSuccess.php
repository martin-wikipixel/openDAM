<script type="text/javascript">
<?php
	$count = 0;
	$multiLocs = array();
	$offSet = .0002;
	$curVal = 0;
	$magVal = 0;
	$stepVal = 0;
	$numSteps = 0;
	$dirVal = 0;
	$posVal = 0;

	// $array = Array();

	foreach ($files as $file) 
	{
		$count++;
		$newmarker = array();

		$newmarker['latitude'] = $file->getLat();
		$newmarker['longitude'] = $file->getLng();

		$multiLocs[$newmarker['latitude']][$newmarker['longitude']] += 1;

		if($multiLocs[$newmarker['latitude']][$newmarker['longitude']] > 1)
		{
			$curVal = $multiLocs[$newmarker['latitude']][$newmarker['longitude']] - 1;
			$magVal = (int) ((sqrt($curVal)+1)/2);
			$stepVal = $curVal - pow(2*$magVal-1,2);
			$numSteps = 2*$magVal;
			$dirVal = (int) ($stepVal / $numSteps);
			$posVal = $stepVal % $numSteps;

			switch ($dirVal)
			{
				//Top to bottom
				//       x
				// x x x |
				// x x x |
				// x x x |
				case 0:
					$newmarker['longitude'] += ($magVal*$offSet);
					$newmarker['latitude'] += $offSet*($magVal - $posVal);
				break;

				//Right to left
				//       x
				// x x x x
				// x x x x
				// x x x x
				// - - - x
				case 1:
					$newmarker['latitude'] -= ($magVal*$offSet);
					$newmarker['longitude'] += $offSet*($magVal - $posVal);
				break;

				//Bottom to top
				//         x
				// | x x x x
				// | x x x x
				// | x x x x
				// x x x x x
				case 2:
					$newmarker['longitude'] -= ($magVal*$offSet);
					$newmarker['latitude'] += $offSet*($posVal - $magVal);
				break;

				//Left to right
				// x - - - x
				// x x x x x
				// x x x x x
				// x x x x x
				// x x x x x
				case 3:
					$newmarker['latitude'] += ($magVal*$offSet);
					$newmarker['longitude'] += $offSet*($posVal - $magVal);
				break;
			}
		}

		$href = url_for("file/showFromMap?id=".$file->getId()."&folder_id=".$file->getFolderId());

		$src = path("@file_thumbnail", array("id" => $file->getId(), "format" => "100"));

		$name = $file;
		$name = strlen($name) > 20 ? myTools::utf8_substr($name, 0, 17)."..." : $name;
		$description = myTools::utf8_substr(myTools::longword_break_old($file->getDescription(), 17), 0, 120);
	  
		$info = "
			<a href=".$href." style=\'text-decoration:none; display:block;\'>
				<img src=".$src." style=\'float:left;\'/>
				<div style=\'float:left;margin-left:2px;width:100px;border:1px solid #fff;\'><div style=\'color:#000;font-weight:bold;\'>".$name."</div>".$description."</div>
				<br clear=\'all\'>
			</a>";

		$info = preg_replace('/(\n|\r)/', '', $info);
		// $info = htmlspecialchars($info, ENT_QUOTES);

		echo "createShowMarkerL('".$newmarker['latitude']."', '".$newmarker['longitude']."','".$info."');";
	} 
?>
</script>