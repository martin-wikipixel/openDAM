<p style="margin:10px;">
<?php
	switch($type) :
		case "folder": echo __("There is no geo tags for this folder."); break;
		case "group": echo __("There is no geo tags for this main folder."); break;
		default: echo __("There is no geo tags for the files chosen."); break;
	endswitch;
?>
</p>