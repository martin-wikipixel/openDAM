<div id="admin-thesaurus-list-page" class="span12">
	<?php
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_thesaurus_list"), "text" => __("Thesaurus")),
		));
	?>

	<div class="row">
		<div class="span6">
			<p>
				<?php
					$text = "Thesaurus allows you to organize your tags into categories.";
					$text .= " You can add tags into fields by moving them inside.";

					echo __($text);
				?>
			</p>
			
			<form class="form-horizontal" method="get">
				<div class="control-group">
					<label for="culture"><?php echo __("Culture"); ?></label>
					<select name="culture" id="culture">
						<?php foreach($cultures as $culture) : ?>
							<option value="<?php echo $culture->getCode(); ?>" <?php echo $culture->getCode() == $culture_ ? "selected" : ""; ?>>
								<?php echo $culture->getTitle(); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</form>
			
			<div id="tag-tree"></div>

			<form name="add-tag-form" class="form-inline">
				<input type="text" name="name" placeholder="<?php echo __("Write a word.");?>">
				<button class="btn btn-primary">
					<i class="icon-plus-sign"></i>
					<?php echo __("Add"); ?>
				</button>
			</form>
			
		</div>
		
		<div class="span6">
			<h5>
				<?php echo __("Suggested tags"); ?> 
				<i data-toggle="tooltip" class="icon-question-sign" title="<?php echo __("These keywords come from your Wikipixel's account and are not included in your thesaurus."); ?>"></i>
				&nbsp;<a data-action="refresh-suggered-tags" href="javascript:void(0)"><i class="icon-refresh"></i></a>
			</h5>
			<div id="suggered-tags">
				<?php include_component("admin", "thesaurusRandomTags"); ?>
			</div>
		</div>
	</div>
</div>
