<?php $options = IptcPeer::getDistinctTitle(); ?>
<?php $selected = $sf_data->getRaw("selected"); ?>

<br clear="all" />
<label><?php echo __("Field"); ?></label>
<select name='iptc_field_<?php echo $index; ?>' id='iptc_field_<?php echo $index; ?>' rel='<?php echo $index; ?>' class='iptc_field' style='float: left; width: 206px;'>
	<?php foreach($options as $key => $value) : ?>
		<option value='<?php echo $key; ?>' <?php echo is_array($selected) && $selected[0] == $key ? "selected" : ""; ?>><?php echo $value; ?></option>
	<?php endforeach; ?>
</select>

<br clear="all" />

<label><?php echo __("Search value"); ?></label>
<input type='text' name='value_iptc_field_<?php echo $index; ?>' id='value_iptc_field_<?php echo $index; ?>' style='float: left; width: 200px;' value='<?php echo is_array($selected) ? $selected[1] : ""; ?>' />

<br clear="all" />

<div class='sep_field' /></div>