<div id="admin-usage-tracking-page" class="span12">
	<?php
		draw_breadcrumb(array(
				array("link" => path("@admin_homepage"), "text" => "<i class='icon-bar-chart icon-large'></i>"." ".__('Reports')),
				array("link" => path("@admin_usage_tracking"), "text" => __("Consumer log")),
		));

		$upload_traffic = $upload_traffic->getRawValue();
		$monthTab = Array(
				"01" => __("January"),
				"02" => __("February"),
				"03" => __("March"),
				"04" => __("April"),
				"05" => __("May"),
				"06" => __("June"),
				"07" => __("July"),
				"08" => __("August"),
				"09" => __("September"),
				"10" => __("October"),
				"11" => __("November"),
				"12" => __("December"),
		);
	?>

		<h2><?php echo __("Usage report"); ?></h2>
		<div class="row-fluid section">
			<div class="span6">
				<h4><?php echo __("General"); ?></h4>
	
				<label>
					<?php echo __("Number of users")?> : <?php echo $total_users?> / <?php echo $max_user; ?>
				</label>
	
				<label>
					<?php echo __("Number of main folders")?> : <?php echo $total_main_folders; ?> / <?php echo $max_main_folder; ?>
				</label>
	
				<label>
					<?php echo __("Number of folders")?> : <?php echo $total_folders; ?>
				</label>
	
				<label>
					<?php echo __("Number of files")?>: <?php echo $total_picture; ?> / <?php echo $max_files; ?>
				</label>
			</div>
		
			<div class="span6">
				<h4><?php echo __("Storage"); ?></h4>
	
				<label>
					<?php echo __("Used disk space")?> : <?php echo MyTools::getSize($total_size); ?> / <?php echo is_numeric($max_disk) ? MyTools::getSize($max_disk * 1024 * 1024) : $max_disk; ?>
				</label>
			</div>
		</div>

		<h2><?php echo __("History"); ?></h2>
		<div class="row-fluid section">
			<div class="span12">
				<form class="form-inline" method="get">
					<label for="year_month"><?php echo __("Select date"); ?> :</label>
						
					<select name="year_month">
						<?php 
							for($i=$start_year; $i <= date("Y", time()); $i++): ?>
								<?php for($j=$start_month; $j <= 12; $j++):
									$j = $j < 10 ? '0'.intval($j) : intval($j);
									echo date('Y-m')." -- ".strval($i.'-'.$j)."<br />";
									if(date('Y-m') < strval($i.'-'.$j))
										continue; ?>
									<option value="<?php echo $i.'-'.$j; ?>" <?php echo ($year.'-'.$month == strval($i.'-'.$j) ? "selected" : '') ?>><?php echo $monthTab[$j]." ".$i; ?></option>
						<?php	endfor;
								$start_month = 1; ?>
								<option value="<?php echo $i.'-all'; ?>" <?php echo ($year.'-'.$month == strval($i.'-all') ? "selected" : '') ?>><?php echo __("All")." ".$i; ?></option>
						<?php endfor; ?>
					</select>
					
					<button class="btn"><i class="icon-search"></i></button>
				</form>
			</div>
		</div>
		
		
		<div class="row-fluid section">	
			<div class="span6">
				<h4><?php echo __("Traffic"); ?></h4>
				<label>
					<?php echo __("Upload traffic")?> : <?php echo MyTools::getSize($upload_traffic["total"]); ?>, 
					<?php echo $upload_traffic["nb"]; ?> <?php echo __("files"); ?>
				</label>
	
				<label>
					<?php echo __("Download traffic")?> : <?php echo MyTools::getSize($download_traffic["total"]); ?>, 
					<?php echo $download_traffic["nb"]; ?> <?php echo __("files"); ?>
				</label>
			</div>
				
			<div class="span6">
				<h4><?php echo __("Views"); ?></h4>
				<label>
					<?php echo __("Number of views")?> : <?php echo $view_global; ?>
				</label>
	
				<label>
					<?php echo __("Number of views (unique)")?> : <?php echo $view_unique?>
				</label>
			</div>
		</div>
		
		<div class="row-fluid">
			<?php include_partial("admin/logGroup", array("groups" => $groups, "year" => $year, "month" => $month, "type" => "html")); ?>
		</div>

		<div class="pull-right">
			<a class="btn" target="blank" href="<?php echo path("admin_usage_tracking_export", array("year" => $year, "month" => $month));?>">
				<?php echo __("Simplified export (*.csv)"); ?>
			</a>
			<a class="btn" target="blank" href="<?php echo path("admin_usage_tracking_export_user", array("year" => $year, "month" => $month));?>">
				<?php echo __("Complete export (*.csv)"); ?>
			</a>
		</div>

</div>