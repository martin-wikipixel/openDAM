<?php
  $tags_array = array();
  foreach ($tags as $tag){
    $tags_array[] = "<a href='#selected_tags' onclick='appendToSelectedTags(\"".$tag."\", ".$tag->getId().")'>".$tag."</a>";
  }
  echo join(", ", $tags_array);
?>