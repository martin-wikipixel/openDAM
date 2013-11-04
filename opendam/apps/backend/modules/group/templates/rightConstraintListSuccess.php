<?php
	$constraints = $constraints->getRawValue();
	$values = $values->getRawValue();
?>

<div id="right-constraint-list-page">
	<h2><?php echo __("Management of constraints of"); ?> "<?php echo $album->getName(); ?>"</h2>

	<ul class="nav nav-tabs">
		<?php foreach ($constraints as $constraintId => $constraint) : ?>
			<?php if ($constraint["constraints"]) : ?>
				<li <?php echo $constraintId == $type ? "class='active'" : ""; ?>>
					<a href="<?php echo path("@group_right_constraint_list", array("album" => $album->getId(),
							"type" => $constraintId)); ?>">
						<?php echo $constraint["label"]; ?>
					</a> 
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>

	<?php foreach($constraints[$type]["constraints"] as $constraint) : ?>
		<div class="control-group">
			<div class="controls">
				<label class="checkbox">
					<input type="checkbox" value="<?php echo $constraint->getId(); ?>"
							data-album-id="<?php echo $album->getId(); ?>"
							<?php echo in_array($constraint->getId(), $values) ? "checked" : ""; ?> />
					<?php echo $constraint->getTitle(); ?>
				</label>
			</div>
		</div>
	<?php endforeach; ?>
</div>