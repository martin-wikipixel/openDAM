<?php if(ThesaurusPeer::hasThesaurus($sf_user->getCustomerId())) : ?>
	<div class="rub text">
		<div class="label-right no-margin">
			<?php echo __("Suggested tags (thesaurus)")?>
		</div>
	</div>

	<br clear="all" />
	<br clear="all" />

	<ul id="tree_thesaurus" style="padding: 5px; border: 1px solid white; min-height: 100px; max-height: 300px; overflow: auto;"></ul>

	<script>
		var clicked = false;

		jQuery(document).ready(function() {
			jQuery("#tree_thesaurus").treeview({
				url: "<?php echo url_for("thesaurus/tree"); ?>",
				ajax: {
					data: {
						"file_id": function() {
							return <?php echo $file->getId(); ?>
						},
						"culture": function() {
							return "<?php echo $sf_user->getCulture(); ?>";
						}
					},
					type: "post"
				}
			});


				jQuery("a.addThesaurus").live("click", function() {
					if(!clicked)
					{
						clicked = true;
						var current = jQuery(this).parent().parent();
						var id = jQuery(this).parent().parent().attr('id');

						if(jQuery("#file_tags").children('a:last').length > 0)
							var to = jQuery("#file_tags").children('a:last');
						else
							var to = jQuery("#file_tags");

						var toX = to.offset().left + to.width();
						var toY = to.offset().top;

						var new_object = jQuery(this).parent().clone().prependTo(current).css({'position' : 'absolute'});

						var fromX = jQuery(new_object).offset().left;
						var fromY = jQuery(new_object).offset().top;

						var gotoX = toX - fromX;
						var gotoY = toY - fromY;

						jQuery(new_object)
							.animate({opacity: 0.4}, 100)
							.animate({opacity: 0.2, marginLeft: gotoX, marginTop: gotoY}, 1200, function() {
								jQuery(this).remove();
							});

						current.children("span").addClass("waiting");

						jQuery.post(
							"/tag/addThesaurus",
							{ id: id, file_id: <?php echo $file->getId(); ?> },
							function(data) {
								current.children("span").removeClass("waiting");
								jQuery('#file_tags').html(data);
								clicked = false;
							}
						);
					}
				});

		});
	</script>
<?php endif; ?>