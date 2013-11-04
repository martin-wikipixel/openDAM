<?php
$folder_ids = $sf_data->getRaw("folder_ids");
$file_ids = $sf_data->getRaw("file_ids");
$selected_tag_ids = $sf_params->get("selected_tag_ids") ? $sf_params->get("selected_tag_ids")->getRawValue() : array();

$nb_tags_array = TagPeer::getCountHomeTags();
$max = sizeof($nb_tags_array) ? (max($nb_tags_array) ? max($nb_tags_array) : 20) : 20;
?>
<?php
  // get tags
  if(sizeof($folder_ids) || sizeof($file_ids)){
    $folder_tags = sizeof($folder_ids) ? TagPeer::getTagsIn($sf_params->get("tag_title"), 2, $folder_ids) : array();
    $file_tags = sizeof($file_ids) ? TagPeer::getTagsIn($sf_params->get("tag_title"), 3, $file_ids) : array();
    $tags = array_merge($folder_tags, $file_tags);
    $tags = array_unique($tags);
    
  }else{
    $tags = TagPeer::getTagsIn($sf_params->get("tag_title"));
  }
?>

<!--show tags-->
<?php 
$tags_array = array();
$tags_size = array();
$i = 1;
$sizeof = sizeof($tags);
$limit = $sf_user->getModuleValue(ModulePeer::__MOD_TAG_HOME);

foreach ($tags as $tag)
{
	if(array_key_exists($tag->getId(), $nb_tags_array))
		$a = $nb_tags_array[$tag->getId()];
	else
		$a = 1;

	$display = in_array($tag->getId(), $selected_tag_ids) ? "none" : "";
	$class = ceil((20*$a)/$max);
	$tags_array[ucfirst( replaceAccentedCharacters($tag->getTitle())) ] = $tag->getId()."|".$display."|".$class."|".$tag->getTitle();
	$tags_size[ucfirst(replaceAccentedCharacters($tag->getTitle()))] = $class;
}

if($limit > 0)
{
	arsort($tags_size);
	$tags_size = array_slice($tags_size, 0, $limit);
	$tags_array = array_intersect_key($tags_array, $tags_size);
}

ksort($tags_array);

foreach($tags_array as $key => $value) : ?>
	<?php $tab = explode("|", $value); ?>
	<a style='display: <?php echo $tab[1]; ?>;' id='cloud_tag_id_<?php echo $tab[0]; ?>' href='javascript: appendToSelectedTags("<?php echo $tab[0]; ?>", <?php echo urlencode(json_encode($tab[3])); ?>);' class='tag<?php echo $tab[2]; ?>'><?php echo $tab[3]; ?></a>
	<?php if($i < count($tags_array)) : ?>
		<span style='color:#BDE1F2; display:<?php echo $tab[1]; ?>;'> - </span>
	<?php endif; $i++; ?>
<?php endforeach; ?>

<br clear="all">