<?php
  $tags = TagPeer::retrieveByPKs($sf_params->get('selected_tag_ids') ? $sf_params->get('selected_tag_ids')->getRawValue() : array());
  $tags_array = array();
  foreach ($tags as $tag){
    $tags_array[] = '<a href="javascript:doSubmitFoldersFilesForm();" id="selected_tag_id_'.$tag->getId().'" class="r"><input type="hidden" value="'.$tag->getId().'" name="selected_tag_ids[]" /> <span>'.$tag.'</span><em onclick="removeFromSelectedTags('.$tag->getId().');"></em></a>';
  }
  echo join(" ", $tags_array);
?>