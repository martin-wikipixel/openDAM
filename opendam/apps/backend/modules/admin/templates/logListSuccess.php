<div id="admin_log-list-page" class="span12">
	<?php 
		draw_breadcrumb(array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-bar-chart icon-large'></i>"." ".__('Reports')),
			array("link" => path("@admin_log_list"), "text" => __("Events log")),
		));
	?>
	<?php $options = getLogTypes(); ?>
	<div class="search-block clearfix">
		<div class="filters pull-left">
			<form class="form-inline">
				<?php params_to_input_hidden(merge_query_params(null, array("type", "page")));?>
				
				<label><?php echo __("Show")?></label>
				<select name="type">
					<?php foreach($options as $key => $value) : ?>
						<option value="<?php echo $key; ?>" <?php echo $key == $currentType? "selected" : ""; ?>><?php echo $value; ?></option>
					<?php endforeach; ?>
				</select>

				<button class="btn"><i class="icon-search"></i></button>
			</form>
		</div>
	</div>

	<table class="table">
		<thead>
			<tr>
				<th class="span3"><?php echo __("Action");?></th>
				<th class="span2"><?php echo __("Date");?></th>
				<th class="span7"><?php echo __("Message");?></th>
			</tr>
		</thead>
		<tbody>
			<?php if(count($logs->getResults()) == 0):?>
				<tr>
					<td colspan="3"><?php echo __("No log.")?></td>
				</tr>
			<?php else:?>
				<?php foreach ($logs as $log):?>
					<tr>
						<td><?php echo returnLogTypes($log->getLogType()) ?></td>
						<td>
							<?php echo my_format_date_time($log->getCreatedAt())?>
						</td>
						<td>
							<?php echo strip_tags(html_entity_decode($log->getContent(), ENT_QUOTES), "<b><i>"); ?>
						</td>
					</tr>
				<?php endforeach;?>
			<?php endif;?>
		</tbody>
	</table>
	
	<?php echo pagination($logs, "@admin_log_list");?>
</div>