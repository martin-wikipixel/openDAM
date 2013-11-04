<?php  include_partial("group/navigationManage", array("selected"=>"step1", "group"=>$group)); ?>
<?php include_partial("group/subMenubarInformations", array("selected" => "tags", "group" => $group)); ?>

<div id="searchResults-popup">
	<div class="inner">
		<form name="tags_form" id="tags_form" action="<?php echo url_for("group/tags?id=".$group->getId()); ?>" method="post">
			<input type="hidden" name="tags_input" id="tags_input" />
			<label for="tag_key" style="width: 150px;"><?php echo __("Group tags")?> :</label>
			<input type="text" onblur="onTagBlur(this);" onfocus="onTagFocus(this);" value="<?php echo __("Add tag ..."); ?>" id="tag_title" name="tag_title" class="nc left" style="width: 364px;" />
			<a href='#' id='addTagRemote' class="left" style="margin-left: 5px; margin-top: 10px;"><?php echo image_tag("icons/add4Bis.gif", array("align"=>"absmiddle")); ?></a>

			<br clear="all" />

			<div id="file_tags" class="left optionsButton" style="width: 630px; margin-left: 158px;">
				<?php
					$fileTags = $sf_params->get("tags_input") ? $sf_params->get("tags_input") : null;
					if(!$fileTags)
						$fileTags = FileTagPeer::retrieveByFileIdType(1, $group->getId());
				?>
				<?php include_partial("tag/selectedTagsGroups", array("groups_tags" => $fileTags));?>
			</div>

			<br clear="all">

			<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_THESAURUS)) : ?>
				<label style="width: 140px;"><?php echo __("Suggested tags (thesaurus)")?></label>
				<ul class="thesaurus_tree" id="tree_thesaurus" style="padding: 5px; border: 1px solid #E6E6E6; min-height: 100px; max-height: 300px; overflow: auto; margin-top: 9px; width: 388px;"></ul>
	 
				<br clear="all">
			<?php endif; ?>

			<div class="right">
				<a href="#" onclick="window.parent.closeFacebox();" class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>

					<a href="#" onclick="jQuery('#tags_form').submit();" class="button btnBS"><span><?php echo __("SAVE CHANGES")?></span></a>

			</div>
		</form>
	</div>
</div>
<script>
function onTagBlur(object)
{
	if(jQuery(object).val() == "")
	{
		jQuery(object).val("<?php echo __("Add tag ..."); ?>");
		jQuery(object).addClass("nc");
	}
}

function onTagFocus(object)
{
	if(jQuery(object).val() == "<?php echo __("Add tag ..."); ?>")
	{
		jQuery(object).val("");
		jQuery(object).removeClass("nc");
	}
}

function deleteTag(object)
{
	var title = jQuery(object).find("span").html();
	var tags = jQuery("#tags_input").val().split("|");
	var tmp = "";

	for(var i = 0; i < tags.length; i++)
	{
		if(tags[i] != title && tags[i] != "")
			tmp += tags[i] + "|";
	}

	jQuery("#tags_input").val(tmp);
	jQuery(object).remove();
}

jQuery(document).ready(function() {
	jQuery('#addTagRemote').bind("click", function() {
		if(jQuery.trim(jQuery('#tag_title').val()).length > 0 && jQuery('#tag_title').val() != "<?php echo __("Add tag ...")?>")
		{
			jQuery("#tags_input").val(jQuery("#tags_input").val() + jQuery('#tag_title').val() + "|");

			var a = jQuery("<a href='#' onclick='deleteTag(this);'><span>" + jQuery('#tag_title').val() + "</span><em id=''></em></a>");
			jQuery('#file_tags .list_tags').append(a);
			jQuery('#tag_title').val("<?php echo __("Add tag ..."); ?>");
			jQuery('#tag_title').addClass("nc");
		}
	});

	jQuery("#tag_title").bind("keydown", function(event) {
		var code = (event.keyCode ? event.keyCode : event.which);

		if(code == 13)
		{
			jQuery('#addTagRemote').trigger("click");
			jQuery("#tag_title").blur();
		}
	});

	jQuery("#tag_title").autocomplete({
		source: '<?php echo url_for("tag/fetchTags"); ?>',
		minLength: 3
	});

	<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_THESAURUS)) : ?>
		jQuery(".thesaurus_tree").treeview({
			url: "<?php echo url_for("thesaurus/tree"); ?>",
			ajax: {
				data: {
					"culture": function() {
						return "<?php echo $sf_user->getCulture(); ?>";
					}
				},
				type: "post"
			}
		});

		jQuery("#tree_thesaurus a.addThesaurus").live("click", function() {
			var current = jQuery(this).parent().parent();
			var new_object = jQuery(this).parent().clone().prependTo(current).css({'position' : 'absolute'});
	
			if(jQuery("#file_tags .list_tags").children('a:last').length > 0)
				var to = jQuery("#file_tags .list_tags").children('a:last');
			else
				var to = jQuery("#file_tags");
	
			var toX = to.offset().left + to.width();
			var toY = to.offset().top;
	
			var fromX = jQuery(new_object).offset().left;
			var fromY = jQuery(new_object).offset().top;
	
			var gotoX = toX - fromX;
			var gotoY = toY - fromY;
	
			jQuery(new_object)
				.animate({opacity: 0.4}, 100)
				.animate({opacity: 0.2, marginLeft: gotoX, marginTop: gotoY}, 1200, function() {
					jQuery(this).remove();
	
					jQuery("#tags_input").val(jQuery("#tags_input").val() + jQuery(current).find("span:first").text() + "|");
	
					var a = jQuery("<a href='#' onclick='deleteTag(this);'><span>" + jQuery(current).find("span:first").text() + "</span><em id=''></em></a>");
					jQuery('#file_tags .list_tags').append(a);
				});
		});
	<?php endif; ?>
})
</script>