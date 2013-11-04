<?php
	$folder = FolderPeer::retrieveByPk($file->getFolderId());
	$lat = $sf_params->get("lat") ? $sf_params->get("lat") : ($file->getLat() ? $file->getLat() : '');
	$lng = $sf_params->get("lng") ? $sf_params->get("lng") : ($file->getLng() ? $file->getLng() : '');
?>

<div id="searchResults-popup">
	<div class="inner">
		<label for="location" style="width: 240px;">&nbsp;</label>
		<?php echo input_tag('address', $sf_params->get('address') ? $sf_params->get('address') : __("Address, City, Region"), array("style"=>"float: left; font-size: 11px; color: gray; width:320px;", "onblur"=>"onBlur(this);", "onfocus"=>"onFocus(this);", "id"=>"address")) ?>
		<input class="search_btn" type="button" value="" onclick="searchLocation()" style="margin-top:9px; float: left; margin-left: 5px; margin-right: 5px;"/>
		<span id="map_indicator" style="display: none;"><?php echo image_tag('icons/loader/small-yellow-circle.gif', array('align'=>'absmiddle'))?></span>

		<br clear="all">

		<label class="single_map_label">
			- <?php echo __("Zoom in: Double click on the map."); ?><br />
			- <?php echo __("Zoom out: Double right click on the map."); ?><br />
			- <?php echo __("Place a marker: Single click on the map."); ?><br />
			- <?php echo __("Remove the marker: Double click on the marker. Only one marker can be made times.")?>
		</label>
		<div id="map_canvas" align="center" class="single_map_canvas"></div>
		<input name="lat" id="lat" type="hidden" value="<?php echo $lat; ?>">
		<input name="lng" id="lng" type="hidden" value="<?php echo $lng; ?>">

		<br clear="all">

		<div class="right">
			<a class="button btnBS" id="save-gps" href="javascript: void(0);"><span><?php echo __("Save"); ?></span></a>
		</div>
	</div>
</div>

<script language="javascript" type="text/javascript">
	var id;

	jQuery(document).ready(function() {
		jQuery("#address").bind("keydown", function(event) {
			var code = (event.keyCode ? event.keyCode : event.which);

			if(code == 13)
				searchLocation();
		});

		jQuery("#save-gps").bind("click", function() {
			jQuery.post(
				"<?php echo url_for("file/updateGps"); ?>",
				{ file_id: "<?php echo $file->getId(); ?>", lat: jQuery("#lat").val(), lng: jQuery("#lng").val() },
				function(data) {
					parent.location.href=parent.location.href;
					window.parent.closeFacebox();
				}
			);
		});

		id = setTimeout(loadMap, 500);
		// jQuery(window).bind("resize", function() {
			// clearTimeout(id);
		// });
	});

	function loadMap()
	{
		jQuery.getScript("/js/leaflet/key.js");
		jQuery.getScript("/js/leaflet/leaflet.core.min.js", function(data, textStatus, jqxhr) {
			jQuery.getScript("/js/leaflet/leaflet.min.js", function(data, textStatus, jqxhr) {
				initializeL('map_canvas', '<?php echo $sf_params->get("address") ? $sf_params->get("address") : (empty($lat) && empty($lng) ? $sf_user->getInstance()->getCountry()->getTitle() : ""); ?>', '<?php echo $lat; ?>', '<?php echo $lng; ?>', 'add');
			});
		});
	}

	function onFocus(obj)
	{
		if(obj.value == "<?php echo __("Address, City, Region")?>")
			obj.value = "";
	}

	function onBlur(obj)
	{
		if(obj.value == "")
			obj.value = "<?php echo __("Address, City, Region")?>";
	}

	function searchLocation()
	{
		searchLocationL(jQuery('#address').val().replace(/ /g,"-"));
	}
</script>