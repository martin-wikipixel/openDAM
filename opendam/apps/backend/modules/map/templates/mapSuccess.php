<?php if($bound)
{
	$sw_latlng_array = $bound["min"];
	$ne_latlng_array = $bound["max"];

	// gmap load() params
	$center_lat = ($sw_latlng_array['lat'] + $ne_latlng_array['lat']) / 2;
	$center_lot = ($sw_latlng_array['long'] + $ne_latlng_array['long']) / 2;

	$sw_lat = ($sw_latlng_array['lat']);
	$sw_lng = ($sw_latlng_array['long']);
	$ne_lat = ($ne_latlng_array['lat']);
	$ne_lng = ($ne_latlng_array['long']);
} ?>
<div id="map" style="position: absolute; width: 100%; height: 600px;"></div>
<script language="javascript" type="text/javascript">
	var id;

	jQuery(document).ready(function() {
		id = setTimeout(loadMap, 500);

		jQuery(window).bind("resize", function() {
			clearTimeout(id);
			id = setTimeout(loadMap, 500);
		});
	});

	function loadMap()
	{
		jQuery.getScript("/js/leaflet/key.js");
		jQuery.getScript("/js/leaflet/leaflet.core.min.js", function(data, textStatus, jqxhr) {
			jQuery.getScript("/js/leaflet/leaflet.min.js", function(data, textStatus, jqxhr) {
				showL('map', '<?php echo $center_lat?>', '<?php echo $center_lot?>', '<?php echo $sw_lat?>', '<?php echo $sw_lng?>', '<?php echo $ne_lat?>', '<?php echo $ne_lng?>', '<?php echo $type?>');
			});
		});
	}
</script>

<br clear="all">

<span id="map_indicator" style="display:none; float:left;"><?php echo image_tag('icons/loader/small-yellow-circle.gif', array('align'=>'absmiddle'))?></span>
<div id="result" style="float:right; color:#737373; font-weight:bold; font-size:11px; margin-right:20px;"></div>