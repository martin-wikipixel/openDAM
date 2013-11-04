<div id="admin-preset-new-edit-page" class="span12">
	<?php
		$links = array(
			array("link" => path("@admin_homepage"), "text" => "<i class='icon-cog icon-large'></i>"." ".__('Management')),
			array("link" => path("@admin_preset_list"), "text" => __("Presets")),
			array("link" => path("@admin_preset_edit", array("id" => $preset->getId())), "text" => __("Edit")),
		);

		draw_breadcrumb($links);
		
		$limitationChecks = $limitationChecks->getRawValue();
		$limitationValues = $limitationValues->getRawValue();
	?>

	<form class="form-horizontal" method="post">
		<?php echo $form["_csrf_token"]->render(); ?>

		<div class="control-group">
			<label class="control-label required" for="data_name"><?php echo __("Name")?></label>
			<div class="controls">
				<?php echo $form["name"]->render(); ?>
				<?php echo $form["name"]->renderError();?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label required" for="data_licence"><?php echo __("Licence")?></label>
			<div class="controls">
				<?php echo $form["licence"]->render();?>
				<?php echo $form["licence"]->renderError();?>
				
				<div id="creative-commons-container" class="<?php echo $currentLicence == LicencePeer::__CREATIVE_COMMONS ? "" : "hide"; ?>">
					<div id="creative-commons-preview">
						<img src="<?php echo $currentCreativeCommons ? image_path($currentCreativeCommons->getImagePath()) 
							: image_path("creative_commons/cc.jpg"); ?>" alt="<?php echo $currentCreativeCommons ? $currentCreativeCommons->getTitle(): ""?>"/>
				
						<a href="javascript: void(0);" class="edit-limitation"></a>
					</div>
					
					<select name="creative-commons" class="hide">
						<?php foreach ($creativeCommonsList as $el): ?>
							<option value="<?php echo $el->getId(); ?>" <?php echo $currentCreativeCommons 
								&& $el->getId() == $currentCreativeCommons->getId() ? "selected" : ""; ?>>
								<?php echo $el->getTitle(); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>

		<div id="copyrights">
			<div class="control-group">
				<label class="control-label required" for="data_use"><?php echo __("Use")?></label>
				<div class="controls">
					<?php echo $form["use"]->render(); ?>
					<?php echo $form["use"]->renderError();?>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label required" for="data_distribution"><?php echo __("Distribution")?></label>
				<div class="controls">
					<?php echo $form["distribution"]->render(); ?>
					<?php echo $form["distribution"]->renderError();?>

					<div id="limitation-container" class="<?php echo $currentUsageDistribution == UsageDistributionPeer::__AUTH ? "" : "hide"; ?>">
						<h4><?php echo __("Distribution restrictions"); ?></h4>
						<ul class="unstyled">
							<?php 
								$internalUsageChecked = in_array(UsageLimitationPeer::__INTERNAL, $limitationChecks);
							?>
							<?php foreach ($limitations as $limitation): ?>
								<?php 
									$isChecked = in_array($limitation->getId(), $limitationChecks);
									$hasValue = isset($limitationValues[$limitation->getId()]);
								?>
								<li data-type="<?php echo $limitation->getUsageTypeId()?>">
									<label class="checkbox">
										<input type="checkbox" 
											name="limitationChecks[]" 
											value="<?php echo $limitation->getId(); ?>"
											data-action="check-limitation"
											<?php echo $isChecked ? "checked" : ""?>
											<?php echo ($internalUsageChecked && $limitation->getId() != UsageLimitationPeer::__INTERNAL ? "disabled": "")?>
											>
										<?php echo $limitation->getTitle(); ?>
									</label>
									
									<div>
										<?php 
											switch ($limitation->getUsageTypeId()) {
												case UsageTypePeer::__TYPE_GEO: 
													?>
													<a href="#continent-modal" role="button" class="btn btn-primary <?php echo $isChecked ? "" : "hide"?>" data-toggle="modal">
														<?php echo __("Open");?>
													</a>
													<?php 
													break;
												
												case UsageTypePeer::__TYPE_SUPPORT:
														?>
													<a href="#support-modal" role="button" class="btn btn-primary <?php echo $isChecked ? "" : "hide"?>" data-toggle="modal">
														<?php echo __("Open");?>
													</a>
													<?php 
													break;
	
												case UsageTypePeer::__TYPE_BOOLEAN:
													break;
													
												default:
													?>
													<input type="text" <?php echo $isChecked ? "" : "disabled"?>
														name="limitationValues[<?php echo $limitation->getId()?>]"
														data-type="<?php echo $limitation->getUsageTypeId();?>"
														value="<?php echo $hasValue ? $limitationValues[$limitation->getId()] : ""?>"
														>
													<?php 
											}
										?>
									</div>
								</li>
							<?php endforeach;?>
						</ul>
					</div>
				</div>
			</div>
		</div><!-- end copyright -->

		<div class="form-actions">
			<button class="btn btn-primary"><?php echo __("Save");?></button>
		</div>

		<!-- continent modal -->
		<div id="continent-modal" class="modal fade hide">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3><?php echo __("Select continent / country")?></h3>
			</div>
			
			<div class="modal-body">
				<?php include_partial("right/continent", array("selectedCountriesId" => $selectedCountriesId));?>
			</div>
			
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn"><?php echo __("Close")?></button>
			</div>
		</div>
		
		<!-- support modal -->
		<div id="support-modal" class="modal fade hide">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3><?php echo __("Select support");?></h3>
			</div>
			
			<div class="modal-body">
				<?php include_partial("right/support", array("selectedSupportsId" => $selectedSupportsId));?>
			</div>
			
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" class="btn"><?php echo __("Close")?></button>
			</div>
		</div>
	</form>
</div>
