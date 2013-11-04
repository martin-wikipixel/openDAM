<?php foreach($continents as $continent) : ?>
	<?php $countries = CountryPeer::retrieveByContinentId($continent->getId()); ?>
	<?php $idCountry = ""; ?>

	<div class='continent'>
		<input type='checkbox' name='continent_<?php echo $continent->getId(); ?>' id='continent_<?php echo $continent->getId(); ?>' class='left check_continent' style="margin-right: 5px;" />
		<label style="width: auto;" for="continent_<?php echo $continent->getId(); ?>"><?php echo $continent->getTitle(); ?> [<a href='javascript: void(0);' class='show_countries'><?php echo __("Show all countries"); ?></a>]</label>

		<div class="countries">
			<?php foreach($countries as $country) : ?>
				<div>
					<input type='checkbox' name='country_<?php echo $country->getId(); ?>' id='country_<?php echo $country->getId(); ?>' rel='<?php echo $country->getTitle(); ?>' value='<?php echo $country->getId(); ?>' class='left check_country' style="margin-right: 5px;" />
					<label style="width: auto;" for="country_<?php echo $country->getId(); ?>"><?php echo $country->getTitle(); ?></label>
				</div>
				<?php $idCountry .= $country->getId().";"; ?>
			<?php endforeach; ?>
			<input type="hidden" title="<?php echo $continent->getId(); ?>" class="continent" value="<?php echo $continent->getTitle(); ?>" rel="<?php echo $idCountry; ?>" />
		</div>
	</div>
<?php endforeach; ?>
<script>
	jQuery(document).ready(function() {
		jQuery(".show_countries").bind("click", function() {
			var countries = jQuery(this).parent().parent().find(".countries");

			if(jQuery(countries).is(":visible"))
			{
				jQuery(countries).slideUp();
				jQuery(this).html("<?php echo __("Show all countries"); ?>")
			}
			else
			{
				jQuery(countries).slideDown();
				jQuery(this).html("<?php echo __("Hide all countries"); ?>")
			}
		});

		jQuery(".check_continent").bind("click", function() {
			if(jQuery(this).is(":checked") == true)
				jQuery(this).parent().find(".countries input").attr("checked", true);
			else
				jQuery(this).parent().find(".countries input").attr("checked", false);
		});

		jQuery(".check_country").bind("click", function() {
			if(jQuery(this).is(":checked") == false)
				jQuery(this).parent().parent().parent().find(".check_continent").attr("checked", false);
		});
	});
</script>