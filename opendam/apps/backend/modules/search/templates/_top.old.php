<div id="search">
	<div class="search-wrap">	
		<?php echo form_tag("search/search", array("id"=>"top_search_form", "name"=>"top_search_form", "method" => "get"))?>
			<?php echo input_hidden_tag('first_call', 1) ?>
			<?php echo input_tag('top_keyword', $sf_params->get("top_keyword") ? $sf_params->get("top_keyword") : __("Search"), array("class"=>"no-background", "class"=>"key", "onfocus"=>"onSearchFocus(this)", "onblur"=>"onSearchBlur(this)", "onkeyup"=>"onKeyUpTopSearch(this)", "id"=>"top_keyword"))?>

			<a class="zoom" id="zoom-btn" href="javascript: void(0);" onclick="jQuery('#top_search_form').submit();"></a>

			<div id="searchDropdown" style="display:none;"><div></div></div>
		</form>
		<span id="hidden_text" style="display: none;">
			<div style='width: 400px; text-align: justify;'>
				<u><?php echo __("Use of search engine:"); ?></u><br /><br />
				<strong><?php echo __("Exact search:"); ?></strong> <?php echo __("\"word\""); ?><br />
				<strong><?php echo __("Exclude word:"); ?></strong> <?php echo __("NOT word"); ?><br />
				<strong><?php echo __("Search word or another word:"); ?></strong> <?php echo __("word1 OR word2"); ?><br />
				<strong><?php echo __("Search two words (default):"); ?></strong> <?php echo __("word1 AND word2"); ?><br />
				<strong><?php echo __("Approximate search (before the word):"); ?></strong> <?php echo __("*word"); ?><br />
				<strong><?php echo __("Approximate search (after the word):"); ?></strong> <?php echo __("word*"); ?><br />
				<strong><?php echo __("Search for items with no keyword:"); ?></strong> <?php echo __("keyword:none"); ?>
			</div>
		</span>
	</div>
</div>


<script type="text/javascript">
	function onSearchFocus(obj)
	{
		if(obj.value.toLowerCase() == "<?php echo __("search")?>")
			obj.value = "";

		jQuery("#zoom-btn").addClass("active");
	}

	function onSearchBlur(obj)
	{
		if(obj.value == "")
			obj.value = "<?php echo __("Search")?>";

		jQuery("#zoom-btn").removeClass("active");
	}
  
	function onKeyUpTopSearch(obj)
	{
		jQuery("#searchDropdown").show();
		jQuery("select").css({ visibility: "hidden" });

		jQuery.post(
			'<?php echo url_for("search/searchTop"); ?>',
			{ 'top_keyword': obj.value },
			function (data) {
				jQuery("#searchDropdown").html(data);
			}
		);
	}
  
	function doSubmitTopSearch(i)
	{
		jQuery('#top_keyword').val(jQuery('#top_match_'+i).val());
		jQuery('#top_search_form').submit();
	}

	var xOffset = 10;
	var yOffset = 20;
	var search_help = 0;

	function display(e)
	{
		var tooltip = jQuery("<p id='tooltip'></p>");
		jQuery("body").append(tooltip);
		tooltip.append(jQuery("#hidden_text").html());
		jQuery("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");
	}

	function unDisplay()
	{
		jQuery("#tooltip").remove();
	}

	jQuery(document).ready( function() {
		jQuery("body").bind("click", function(e){
			jQuery("#searchDropdown").hide();
			jQuery("select").css({ visibility: "visible" });
		});

		jQuery("#zoom-btn").hover(function(e) {
			search_help = setTimeout(function() { display(e); }, 2000);
		}, function(){
			clearTimeout(search_help);
			unDisplay();
		});

		jQuery("#zoom-btn").mousemove(function(e) {
			jQuery("#tooltip")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(e.pageX + yOffset) + "px");
		});
	});
</script>