<?php include_partial("group/navigationManage", array("selected"=>"step1", "group"=>$group)); ?>
<?php include_partial("group/subMenubarInformations", array("selected" => "manage_tags", "group" => $group)); ?>

<div id="searchResults-popup">
	<div class="inner">
		<form name='tags_form' id='tags_form' action='<?php echo url_for("group/manageTags?id=".$group->getId()); ?>' method='post'>
			<table style="width: 100%;">
				<tr>
					<td class="caption" style="width: 50%; padding: 3px;" colspan="3">
						<span class="text left" style="padding: 3px;"><?php echo __('Sort tags by')?></span>
						<?php sortBy($sf_user->getAttribute("manage_tag_sort"), "tags_form", url_for("group/manageTags?id=".$group->getId()));?>
					</td>
					<td class="caption" colspan="4" style="width: 40%; text-align: right;"><?php include_partial("search/form", array("keyword"=>$sf_user->getAttribute("manage_tag_keyword")));?></td>
				</tr>
				<tr>
					<td class="caption" colspan="7" width="90%"><?php echo __('Tags starting by')." ";?> :
						<a style="<?php echo (!$l)?'color: black':''?>" href="<?php echo url_for("group/manageTags?id=".$group->getId().($sf_user->getAttribute("manage_tag_keyword") ? "&keyword=".$sf_user->getAttribute("manage_tag_keyword") : "")); ?>"><?php echo __('ALL')?></a> 
						<?php foreach ($letters as $letter): ?>
							<a style="<?php echo ($l == $letter)?'color: black':''?>" href="<?php echo url_for("group/manageTags?id=".$group->getId().($sf_user->getAttribute("manage_tag_keyword") ? "&keyword=".$sf_user->getAttribute("manage_tag_keyword") : "")."&l=".$letter); ?>"><?php echo replaceAccentedCharacters( $letter)?></a>
						<?php endforeach; ?>

						<div class="right">

								<a href="javascript: void(0);" class="but_admin" id="delete_selected_tags" style="margin-right: 5px;"><span><?php echo __("Delete selected tags"); ?></span></a>

						</div>
					</td>
				</tr>

				<?php if(!$tag_pager->getNbResults()):?>
					<tr>
						<td class="no-border"  colspan="7"><div class="info"><?php echo __("No tag found.")?></div></td>
					</tr>
				<?php else:?>
					<tr>
						<td class="no-border" colspan="7">
							<div class="text">
								<?php echo __("To replace one or more tags from an existing tag:"); ?><br /><br />
								<?php echo __("Check the tags to be replaced,"); ?><br />
								<?php echo __('Click on "Replace" button next to the tag to replace the other.'); ?>
							</div>
						</td>
					</tr>
				<?php endif;?>

				<?php foreach ($tag_pager->getResults() as $tag): ?>
					<tr class="admin_tab_border_bottom">
						<td class="no-border text"><input type='checkbox' name='checkbox[]' id='checkbox_<?php echo $tag->getId(); ?>' value='<?php echo $tag->getId(); ?>' /></td>
						<td class="no-border text"><label for="checkbox_<?php echo $tag->getId(); ?>" class="no_css"><?php echo $tag; ?></label></td>
						<td class="no-border text"><a href='<?php echo url_for('tag/attachGroup?group_id='.$group->getId().'&id='.$tag->getId()); ?>' class='but_admin'><span style='white-space:nowrap;'><?php echo __("Link to folders / files"); ?></span></a></td>
						<td class="no-border text" width="10%">

								<a href='<?php echo url_for('tag/updateGroup?group_id='.$group->getId().'&id='.$tag->getId()); ?>' class='but_admin'><span><?php echo __("Rename"); ?></span></a>

						</td>
						<td class="no-border text" width="10%">

								<a href="javascript: void(0);" class="but_admin replace_tag" rel="<?php echo addslashes($tag); ?>" id="<?php echo $tag->getId(); ?>"><span><?php echo __("Replace"); ?></span></a>

						</td>
						<td class="no-border text" width="10%">

								<a href='<?php echo url_for('tag/deleteGroup?group_id='.$group->getId().'&id='.$tag->getId()); ?>' class='but_admin' onclick='return confirm("<?php echo __('Are you sure want to delete this tag?'); ?>");'><span><?php echo __("Remove"); ?></span></a>

						</td>
						<td class="no-border text" width="10%"></td>
					</tr>
				<?php endforeach;?>
			</table>

			<br clear="all"/>

			<div class="text"><?php echo pager_navigation($tag_pager, url_for("group/manageTags?id=".$group->getId()))?></div>
			<br clear="all"/>

			<div class="right">
				<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".replace_tag").bind("click", function() {
		if(confirm("<?php echo __("Are you sure want to replace those tags to"); ?> \"" + jQuery(this).attr("rel") + "\" ?"))
		{
			jQuery("#tags_form").attr("action", "/tag/replaceGroup?group_id=<?php echo $group->getId(); ?>&id=" + jQuery(this).attr("id"));
			jQuery("#tags_form").submit();
		}
	});

	jQuery("#delete_selected_tags").bind("click", function() {
		if(confirm("<?php echo __("Are you sure you want to delete these tags?"); ?>"))
		{
			jQuery("#tags_form").attr("action", "/tag/deleteAllGroup?group_id=<?php echo $group->getId(); ?>");
			jQuery("#tags_form").submit();
		}
	});
});
</script>