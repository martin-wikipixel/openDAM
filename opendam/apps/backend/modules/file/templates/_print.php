<div style="float:left;">
	<?php if($file->existsThumbWeb()):?>
		<img src="<?php echo path("@file_thumbnail", array("id" => $file->getId(), "format" => "web")); ?>" style="max-width:720px; width:expression(this.width > 720 ? '720px': true);"/>
	<?php endif;?>
</div>

<br clear="all">
<br clear="all">

<!--start right sidebar-->
<div style="float:left; width:240px; color:#000; font-size:11px; text-align:left; font-family:Verdana;">
	<!--info right start-->
	<div style="margin-bottom:10px;">
		<div style="font-weight:bold; font-size:12px;"><?php echo myTools::longword_break_old(strtoupper($file), 22)?></div>
    
		<br clear="all">
  
		<div style="margin-bottom:10px;">
			<?php echo $file->getDescription() ? myTools::longword_break_old($file->getDescription(), 27) : ""?>
		</div>

		<?php if(!sizeof($file->getTags())): ?>
			<div style="margin-bottom:10px;">
				<b><?php echo __("Tags")?> : </b>
				<?php echo join(", ", $file->getTags()->getRawValue());?>
			</div>
		<?php endif; ?>
	</div>

	<!--detail right start-->
	<div style="margin-bottom:10px;">
		<div><h4 style="margin:0 0 10px; padding:0 0 0px 5px; color:#000; font-weight:bold; border-bottom:1px solid #333; font-size:11px;"><?php echo __("Details")?></h4></div>
		<div>
			<b><?php echo __("Dimentions")?> : </b>
			<?php $size = getimagesize($file->getPathname()); ?>
			<?php echo $size[0]." x ".$size[1]; ?>

			<br clear="all" style="margin-bottom:5px;">  

			<b><?php echo __("Format")?> : </b> <?php echo strtoupper($file->getExtention());?>

			<br clear="all" style="margin-bottom:5px;">

			<b><?php echo __("Size")?> : </b> <?php echo MyTools::getSize($file->getSize()); ?>

			<br clear="all" style="margin-bottom:5px;">

			<b><?php echo __("Date")?> : </b> <?php echo date("d/m/Y", strtotime($file->getCreatedAt()))?>

			<br clear="all" style="margin-bottom:5px;">

			<?php $user_file = UserPeer::retrieveByPKNoCustomer($file->getUserId()); ?>
			<b><?php echo __("Uploaded by")?> : </b> <?php echo $user_file->getFullname(); ?>

			<br clear="all" style="margin-bottom:5px;">

			<?php $fields = FieldPeer::retrieveByGroupId($file->getGroupeId()); ?>

			<?php if(!empty($fields)) : ?>
				<?php foreach($fields as $field) : ?>
					<?php $content = FieldContentPeer::retrieveByFieldIdAndObjectIdAndObjectType($field->getId(), $file->getId(), FieldContentPeer::__FILE); ?>

					<?php if($field->getType() == FieldPeer::__TYPE_BOOLEAN || ($field->getType() != FieldPeer::__TYPE_BOOLEAN && $content && $content->getValue())) : ?>
						<b><?php echo $field->getName(); ?> : </b>&nbsp;

						<?php
							switch($field->getType())
							{
								case FieldPeer::__TYPE_BOOLEAN:
									echo $content ? ($content->getValue() ? __("Yes") : __("No")) : __("No");
								break;

								default:
									echo $content->getValue();
								break;
							}
						?>

						<br clear="all" style="margin-bottom:5px;">
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>

	<?php if($file->hasCopyright()) : ?>
		<div style="margin-bottom:10px;">
			<div><h4 style="margin:0 0 10px; padding:0 0 0px 5px; color:#000; font-weight:bold; border-bottom:1px solid #333; font-size:11px;"><?php echo __("Usage rights")?></h4></div>
			<div>
				<div style="margin-bottom:10px;">
					<?php if($exif = ExifPeer::getTag("Author", $file->getId()))
						$author = myTools::longword_break_old($exif->getValue(), 22);
					elseif($iptc = IptcPeer::getTag("Writer/Editor", $file->getId()))
						$author = myTools::longword_break_old($iptc->getValue(), 22);
					else
						$author = "";

					if(!preg_match('/[a-zA-Z0-9]/', $author))
						$author = "";

					if(!empty($author)) : ?>
						<b><?php echo __("Author")?> : </b> <?php echo $author; ?>
						<br clear="all" style="margin-bottom:5px;">
					<?php endif; ?>

					<?php $source = $file->getSource() ? $file->getSource() : ""; ?>
					<?php if(!empty($source)) : ?>
						<b><?php echo __("Source")?> : </b> <?php echo $source; ?>
						<br clear="all" style="margin-bottom:5px;">
					<?php endif; ?>

					<?php $licence = $file->getLicenceId() ? $file->getLicence() : ""; ?>
					<?php if(!empty($licence)) : ?>
						<b><?php echo __("Licence")?> : </b> <?php echo $licence; ?>
						<br clear="all" style="margin-bottom:5px;">
					<?php endif; ?>

					<?php if($file->getLicenceId() == LicencePeer::__CREATIVE_COMMONS) : ?>
						<?php $creative_commons = $file->getCreativeCommonsId() ? image_path($file->getCreativeCommons()->getImagePath()) : image_path("creative_commons/cc.jpg"); ?>

						<img src="<?php echo $creative_commons; ?>" />
					<?php else: ?>
						<?php $use = $file->getUsageUseId() ? $file->getUsageUse()->getTitle() : ""; ?>
						<?php if(!empty($use)) : ?>
							<b><?php echo __("Use")?> : </b> <?php echo $use; ?>
							<br clear="all" style="margin-bottom:5px;">
						<?php endif; ?>

						<?php $commercial = $file->getUsageCommercialId() ? $file->getUsageCommercial()->getTitle() : ""; ?>
						<?php if(!empty($commercial)) : ?>
							<b><?php echo __("Commercial")?> : </b> <?php echo $commercial; ?>
							<br clear="all" style="margin-bottom:5px;">
						<?php endif; ?>

						<?php $distribution = $file->getUsageDistributionId() ?  $file->getUsageDistribution()->getTitle() : ""; ?>
						<?php if(!empty($distribution)) : ?>
							<b><?php echo __("Distribution")?> : </b> <?php echo $distribution; ?>
							<br clear="all" style="margin-bottom:5px;">
						<?php endif; ?>

						<?php $constraint = $file->getUsageConstraintId() ?  $file->getUsageConstraint()->getTitle() : ""; ?>
						<?php if(!empty($constraint)) : ?>
							<b><?php echo __("Constraint")?> : </b> <?php echo $constraint; ?>
							<br clear="all" style="margin-bottom:5px;">
						<?php endif; ?>

						<?php $limitations = UsageLimitationPeer::getLimitations(); ?>

						<ul>
							<?php foreach($limitations as $limitation) : ?>
								<?php $file_right = FileRightPeer::retrieveByTypeAndLimitation($file->getId(), 3, $limitation->getId()); ?>
								<?php if($file_right) : ?>
									<?php $value = $file_right->getValue(); ?>
									<li style="line-height: 17px;">
										<b><?php echo $limitation->getTitle(); ?> : </b><br />
										<?php switch($limitation->getUsageTypeId()) :
											case UsageTypePeer::__TYPE_TEXT:
											case UsageTypePeer::__TYPE_NUM:
											case UsageTypePeer::__TYPE_DATE:
												echo $value;
											break;

											case UsageTypePeer::__TYPE_GEO:
												$value_text = "";
												if(!empty($value))
												{
													$countries = explode(";", $value);

													foreach($countries as $country_id)
													{
														if(!empty($country_id))
														{
															$country = CountryPeer::retrieveByPk($country_id);
															$value_text .= $country->getTitle().", ";
														}
													}

													$value_text = substr($value_text, 0, -2);
												}

												echo $value_text;
											break;

											case UsageTypePeer::__TYPE_SUPPORT:
												$value_text = "";
												if(!empty($value))
												{
													$supports = explode(";", $value);

													foreach($supports as $support_id)
													{
														if(!empty($support_id))
														{
															$support = UsageSupportPeer::retrieveByPk($support_id);
															if($support)
																$value_text .= $support->getTitle().", ";
														}
													}

													$value_text = substr($value_text, 0, -2);
												}

												echo $value_text;
											break;
										endswitch; ?>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>