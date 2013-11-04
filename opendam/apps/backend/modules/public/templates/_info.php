<?php $info = MainPeer::retrieveByCustomer($sf_user->getCustomerId());?>

<?php if($info->getIsActive()):?>
	<div class="filterBox">
		<div class="title" style="float:left; width:220px; cursor:pointer;" onclick="toggleContainer('homeinfo_container', 'homeinfo_container_img')">
			<img src='<?php echo image_path("down-arr.gif"); ?>' style='vertical-align: middle;' id='homeinfo_container_img' />
			<h4><?php echo __("Home info")?></h4>
		</div>
		<div id="homeinfo_container">
			<div id="filterByInformation">
				<div class="filterRow" style="float:left;">
					<div class="eotfarea" id="<?php echo $info->getId(); ?>"><?php echo $info->getContent(); ?></div>
				</div>
			</div>
		</div>
	</div>
<?php endif;?>
<?php if($sf_user->isAdmin()) : ?>
	<script>
		function bindBorder(settings, object)
		{
			jQuery(object).css('border-color', '#FFFFFF'); 
			jQuery(object).css('background-color', '#FFFFFF'); 
			jQuery('.eotfarea').bind('mouseover', overTd);
			jQuery('.eotfarea').bind('mouseout', outTd);
			jQuery(object).css('padding', '2px'); 

			return true;
		}

		function unbindBorder(settings, object)
		{
			jQuery('.eotfarea').unbind('mouseover');
			jQuery('.eotfarea').unbind('mouseout');  

			jQuery(object).css('padding', '0px');

			return true;
		}

		function overTd(event)
		{
			jQuery(event.currentTarget).css('border-color', '#E6E6E6'); 
			jQuery(event.currentTarget).css('background-color', '#FAFAFA'); 
		}

		function outTd(event)
		{
			jQuery(event.currentTarget).css('border-color', '#FFFFFF'); 
			jQuery(event.currentTarget).css('background-color', '#FFFFFF'); 
		}

		jQuery(document).ready(function() {
			jQuery(".eotfarea").editable(
				"<?php echo url_for("public/info"); ?>",
				{
					type: 'textarea',
					indicator: '<?php echo __("Saving");?>...',
					placeholder: '',
					cssclass: 'editable-details-file',
					onedit: unbindBorder,
					onreset: bindBorder,
					onblur: "submit",
					width: "100%",
					callback : function(value, settings) {
						jQuery(this).html(value);
						bindBorder(settings, this);
					},
					data: function(value, settings) {
						return strip_tags(value);
					}
				}
			);

			jQuery(".eotfarea").bind("mouseover", overTd);
			jQuery(".eotfarea").bind("mouseout", outTd);
		});
	</script>
<?php endif; ?>