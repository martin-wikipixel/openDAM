<script type="text/javascript">
// <![CDATA[

<?php
foreach ($folders as $folder)  {
  // href
  $href = url_for("folder/show?id=".$folder->getId());
  
  // info
	if (!$folder->getThumbnail()) {
		$relative = image_path("no-access-file-200x200.png");
	}
	else {
		$relative = path("@folder_thumbnail", array("id" => $folder->getId()));
	}
  
  $name = strlen($folder->getName()) > 20 ? myTools::utf8_substr($folder->getName(), 0, 20)."..." : $folder->getName();
  $description = myTools::utf8_substr(myTools::longword_break_old($folder->getDescription(), 20), 0, 120);
  $info = "
  <a href=".$href." style=\'text-decoration:none;\'>
    <img src=".$relative." style=\'float:left;\'/>
    <div style=\'float:left;margin-left:10px;width:120px;border:1px solid #fff;\'><div style=\'color:#000;font-weight:bold;\'>".$name."</div>".$description."</div>
  <br clear=\'all\'></a>";
  
  $info = preg_replace('/(\n|\r)/', '', $info);
  // $info = htmlspecialchars($info, ENT_QUOTES);
      
  echo "createShowMarkerL('".$folder->getLat()."', '".$folder->getLng()."','".$info."');";
} 
?>

// ]]>
</script>