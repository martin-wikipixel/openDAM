<?php $term = $sf_params->get("top_keyword"); ?>
<?php $types = $sf_params->get("types") ? $sf_params->get("types")->getRawValue() : Array("albums", "folders", "pictures", "videos", "audios", "documents"); ?>
<form class="navbar-search pull-left" action="<?php echo url_for("search/search"); ?>" method="get" id="top_search_form" name="top_search_form">
	<input type="hidden" name="first_call" id="first_call" value="1" />
	<div class="input-append">
		<input type="text" name="top_keyword" id="top_keyword" class="search-query<?php echo !empty($term) ? " focused" : ""; ?>" placeholder="<?php echo __("Search"); ?>" value="<?php echo $term; ?>" />
		<div class="btn-actions"<?php echo !empty($term) ? " style='display: inline-block;'" : ""; ?>>
			<div class="btn-group">
				<button type="button" class="btn dropdown-toggle">
					<i class="icon-caret-down"></i>
				</button>
				<ul class="dropdown-menu" id="search_type">
					<li>
						<a href="javascript: void(0);" <?php echo in_array("albums", $types) ? "class='selected'" : ""; ?> data-type="albums">
							<div class="content"><i class="icon-book" data-type="albums"></i> <?php echo __("Groups"); ?></div>
							<div class="selected-search-type"><i class="<?php echo in_array("albums", $types) ? "icon-ok" : "icon-check-empty"; ?>"></i></div>
						</a>
					</li>
					<li>
						<a href="javascript: void(0);" <?php echo in_array("folders", $types) ? "class='selected'" : ""; ?> data-type="folders">
							<div class="content"><i class="icon-folder-close" data-type="folders"></i> <?php echo __("Folders"); ?></div>
							<div class="selected-search-type"><i class="<?php echo in_array("folders", $types) ? "icon-ok" : "icon-check-empty"; ?>"></i></div>
						</a>
					</li>
					<li>
						<a href="javascript: void(0);" <?php echo in_array("pictures", $types) ? "class='selected'" : ""; ?> data-type="pictures">
							<div class="content"><i class="icon-picture" data-type="pictures"></i> <?php echo __("Pictures"); ?></div>
							<div class="selected-search-type"><i class="<?php echo in_array("pictures", $types) ? "icon-ok" : "icon-check-empty"; ?>"></i></div>
						</a>
					</li>
					<li>
						<a href="javascript: void(0);" <?php echo in_array("videos", $types) ? "class='selected'" : ""; ?> data-type="videos">
							<div class="content"><i class="icon-play-circle" data-type="videos"></i> <?php echo __("Videos"); ?></div>
							<div class="selected-search-type"><i class="<?php echo in_array("videos", $types) ? "icon-ok" : "icon-check-empty"; ?>"></i></div>
						</a>
					</li>
					<li>
						<a href="javascript: void(0);" <?php echo in_array("audios", $types) ? "class='selected'" : ""; ?> data-type="audios">
							<div class="content"><i class="icon-music" data-type="audios"></i> <?php echo __("Audios"); ?></div>
							<div class="selected-search-type"><i class="<?php echo in_array("audios", $types) ? "icon-ok" : "icon-check-empty"; ?>"></i></div>
						</a>
					</li>
					<li>
						<a href="javascript: void(0);" <?php echo in_array("documents", $types) ? "class='selected'" : ""; ?> data-type="documents">
							<div class="content"><i class="icon-file-alt" data-type="documents"></i> <?php echo __("Documents"); ?></div>
							<div class="selected-search-type"><i class="<?php echo in_array("documents", $types) ? "icon-ok" : "icon-check-empty"; ?>"></i></div>
						</a>
					</li>
				</ul>
			</div>
			<button class="btn" type="submit" id="submit_search"><i class="icon-search"></i></button>
		</div>
	</div>
	<?php foreach($types as $type) : ?>
		<input type="hidden" name="types[]" class="type-form" value="<?php echo $type; ?>" />
	<?php endforeach; ?>
</form>
<div id="hidden_text" style="display: none;">
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
</div>

<script type="text/javascript">
(function() {
	"use strict";

	var xOffset = 10;
	var yOffset = 20;
	var search_help = 0;
	
	function display(e) {
		var tooltip = $("<p id='tooltip'></p>");
		
		$("body").append(tooltip);
		tooltip.append($("#hidden_text").html());
		
		$("#tooltip")
			.css("top",(e.pageY - xOffset) + "px")
			.css("left",(e.pageX + yOffset) + "px")
			.fadeIn("fast");
	}

	function unDisplay() {
		$("#tooltip").remove();
	}

	$(document).ready( function() {
		var $root = $("#top_search_form");
		var $topKeyword = $("#top_keyword");
		var $submitSearch = $("#submit_search");
		
		$root.find(".btn-actions .dropdown-toggle").on("click", function(event) {
			event.stopPropagation();
		});

		$("#search_type a").bind("click", function(event) {
			event.stopPropagation();

			var selected = "";
			var $this = $(this);
			
			$(".type-form").remove();

			if ($this.hasClass("selected")) {
				$this.find(".selected-search-type i").addClass("icon-check-empty").removeClass("icon-ok");
				$this.removeClass("selected");
			}
			else {
				$this.find(".selected-search-type i").addClass("icon-ok").removeClass("icon-check-empty");
				$this.addClass("selected");
			}

			$("#search_type a.selected .content i").each(function() {
				selected += "<i class='" + $(this).attr("class") + "'></i>";
				$root.append("<input type='hidden' class='type-form' name='types[]' value='" + $(this).attr("data-type") + "' />");
			});
		});

		$topKeyword.on("focus", function() {
			$(this).addClass("focused");
			$(".btn-actions").css("display", "inline-block");
		});

		$topKeyword.on("click", function(event) {
			event.stopPropagation();
			
			$(this).addClass("focused");
			$(".btn-actions").css("display", "inline-block");
		});

		<?php if($sf_context->getModuleName() != "search" && $sf_context->getActionName() != "search") : ?>
			$(document).on("click", function() {
				$topKeyword.removeClass("focused");
				$(".btn-actions").css("display", "none");
			});
		<?php endif; ?>

		$root.on("submit", function(event) {
			if ($topKeyword.val() == "") {
				event.preventDefault();
			}
		});

		$submitSearch.hover(function(e) {
			search_help = setTimeout(function() { display(e); }, 2000);
		}, 
		function(){
			clearTimeout(search_help);
			unDisplay();
		});

		$submitSearch.mousemove(function(e) {
			$("#tooltip")
				.css("top",(e.pageY - xOffset) + "px")
				.css("left",(e.pageX + yOffset) + "px");
		});
	});
})();
</script>