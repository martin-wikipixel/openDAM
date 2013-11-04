<?php 
	$continents = ContinentPeer::getContinents();
	$selectedCountriesId = $selectedCountriesId->getRawValue();
?>

<div id="continents-body">
	<?php foreach ($continents as $continent) : ?>
		<?php 
			$countries = CountryPeer::retrieveByContinentId($continent->getId()); 
			$countriesId = FormUtils::getIds($countries);
			
			// si tous les pays d'un continent sont coché, il faut cocher le continent
			$selectedAll = true;
			
			for ($i = 0, $length = count($countriesId); $selectedAll && $i < $length; $i++) {
				$selectedAll = in_array($countriesId[$i], $selectedCountriesId) ? true : false;
			}
		?>
	
		<div class="continent">
			<label class="checkbox">
				<input type="checkbox" name="continents[]" 
					value="<?php echo $continent->getId()?>"
					data-action="check-continent"
					data-continent-name="<?php echo $continent->getTitle()?>"
					<?php echo $selectedAll ? "checked": ""?>
					>
				<?php echo $continent->getTitle(); ?> 
				[<a href="javascript: void(0);" role="button" data-action="show-all"><?php echo __("Show all countries"); ?></a>]
			</label>
	
			<ul class="countries">
				<?php foreach ($countries as $country): ?>
					<li>
						<label class="checkbox">
							<input  type="checkbox" name="countries[]" 
								value="<?php echo $country->getId()?>"
								data-action="check-country"
								data-country-name="<?php echo $country->getTitle()?>"
								<?php echo in_array($country->getId(), $selectedCountriesId) ? "checked": ""?>
								>
							<?php echo $country->getTitle(); ?>
						</label>
					</li>
				<?php endforeach;?>
			</ul>
		</div>
	<?php endforeach; ?>
</div>
