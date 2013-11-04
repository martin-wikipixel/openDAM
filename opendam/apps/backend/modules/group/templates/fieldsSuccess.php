<?php include_partial("group/navigationManage", array("selected"=>"step1", "group"=>$group)); ?>
<?php include_partial("group/subMenubarInformations", array("selected" => "fields", "group" => $group)); ?>

<div id="searchResults-popup">
	<div class="inner">
		<h5><?php echo __("Specific fields for folders and files in this group"); ?></h5>
		<br clear="all" />
		<div id="listFields">
			<?php include_component("group", "listFields", array("group_id" => $group->getId())); ?>
		</div>
		<br clear="all" />
		<br clear="all" />
		<h5><?php echo __("Add new field"); ?></h5>
		<select name="type" id="type" style="width: 196px; float: left;">
			<option value="0"><?php echo __("Choose"); ?></option>
			<?php foreach($types as $key => $value) : ?>
				<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
			<?php endforeach; ?>
		</select>
		<input type="text" name="field" id="field" style="width: 200px; float: left; margin-left: 5px;" />

			<a href="javascript: void(0);" id="add_values" class="but_admin" style="margin-top: 9px; display: none;"><span><?php echo __("Add values"); ?></span></a>


			<a href="javascript: void(0);" id="add_field" class="but_admin" style="margin-top: 9px;"><span><?php echo __("Add"); ?></span></a>

		<div id="loader_add_field" style="float: left; margin-top: 9px; margin-left: 10px; display: none;"><img src="<?php echo image_path("icons/loader/small-yellow-circle.gif"); ?>" /></div>
		<span class="require_field" style="clear: both; float: left; display: none;" id="error_field"></span>
		<input type="hidden" id="select_values" />
		<div id="values"></div>
	</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
	/*jQuery("#type").bind("change", function() {
		if(jQuery(this).val() == <?php echo FieldPeer::__TYPE_SELECT; ?>)
			jQuery("#add_values").fadeIn("slow");
		else
			jQuery("#add_values").fadeOut("slow");
	});*/

	jQuery("#add_field").bind("click", function() {
		if(jQuery("#type").val() > 0)
		{
			if(jQuery("#field").val() != "")
			{
				/*if(jQuery("#type").val() == <?php echo FieldPeer::__TYPE_SELECT; ?> && jQuery("#select_values").val() == "")
					ret = confirm("<?php echo __("Are you sur to want to had this field with no values?"); ?>");
				else*/
					ret = true;

				if(ret)
				{
					jQuery("#error_field").fadeOut('slow', function() {
						jQuery(this).html("");

						jQuery("#loader_add_field").fadeIn("slow", function() {
							jQuery.post(
								"<?php echo url_for("group/addField"); ?>",
								{ group_id: <?php echo $group->getId(); ?>, name: jQuery("#field").val(), type: jQuery("#type").val(), values: jQuery("#select_values").val() },
								function(data) {
									jQuery("#loader_add_field").fadeOut("slow", function() {
										if(data.errorCode > 0)
											jQuery("#error_field").html("<?php echo __("Name of field already exists."); ?>").fadeIn('slow');
										else
										{
											jQuery("#field").val('');
											jQuery("#type").val(0);
											jQuery("#select_values").val('');

											jQuery("#listFields").fadeOut("slow", function() {
												jQuery("#listFields").html(data.message).fadeIn("slow");
											});
										}
									});
								},
								"json"
							)
						});
					});
				}
			}
			else
				jQuery("#error_field").html("<?php echo __("Name of field is required."); ?>").fadeIn('slow');
		}
		else
			jQuery("#error_field").html("<?php echo __("Type of field is required."); ?>").fadeIn('slow');
	});

	jQuery("#add_values").bind("click", function() {
		jQuery("#values").dialog({
			title: "<?php echo __("Add values"); ?>",
			modal: true,
			resizable: false,
			draggable: false,
			show: 'fade',
			hide: 'fade',
			width: 500,
			height: 300,
			/*buttons: {
				"<?php echo __("Validate"); ?>": function() {
					jQuery(this).dialog("close");
				}
			},*/
			open: function(event, ui) {
				var object = jQuery(this);

				jQuery.post(
					"<?php echo url_for("group/loadFieldValue"); ?>",
					{ values: jQuery("#select_values").val() },
					function(data) {
						jQuery(object).fadeOut(200, function() {
							jQuery(object).html(data);
							jQuery(object).show();
							jQuery(object).find(".dialog").fadeIn();
						});
					}
				);
			},
			close: function(event, ui) {
				jQuery(this).html("");
			},
			beforeClose: function(event, ui) {
				var temp = jQuery("#sortable").sortable('toArray');
				var myObject = new Object();

				for(var k = 0; k < temp.length; k++)
					myObject[k] = temp[k];

				jQuery("#select_values").val(jQuery.param(myObject));
			}
		});
	});
});
</script>