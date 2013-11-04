<?php 
	$selectedSupportsId = $selectedSupportsId->getRawValue();
?>

<?php foreach ($supports as $support): ?>
	<div class="support">
		<label class="checkbox">
			<?php echo $support->getTitle(); ?>
			<input type="checkbox" name="supports[]" 
				<?php echo in_array($support->getId(), $selectedSupportsId) ? "checked" : ""?>
				value="<?php echo $support->getId(); ?>">
		</label>
	</div>
<?php endforeach; ?>