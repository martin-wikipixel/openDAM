<?php if($customer->isExpired() && !$sf_user->isConnectedInto()) : ?>

	<div class="dialog" id="expired_user">
		<?php echo __("Your Wikipixel account expired on %1%.", array("%1%" => $customer->getExpiration("d/m/Y"))); ?><br /><br />
		<?php if ($sf_user->isAdmin()) : ?>
			<?php echo __("To extend your subscription, simply click the \"Renew\" and follow the renewal instructions."); ?><br /><br />
		<?php else : ?>
			<?php echo __("Please contact your administrator."); ?><br /><br />
		<?php endif; ?>
		<?php echo __("For questions, please contact an advisor by email at %1% or by phone %2%.", array("%1%" => "<a href='mailto:".sfConfig::get("app_data_email_support")."' target='_blank'>".sfConfig::get("app_data_email_support")."</a>", "%2%" => "<a href='tel:".sfConfig::get("app_data_phone_tel")."' target='_blank'>".sfConfig::get("app_data_phone_int")."</a>")); ?>
	</div>
	<script>
		jQuery(document).ready(function() {
			jQuery("#expired_user").dialog({
				title: "<span class='first-title'><?php echo __("Your account has expired"); ?></span>",
				resizable: false,
				draggable: false,
				modal: true,
				width: 450,
				height: 250,
				show: 'fade',
				hide: 'fade',
				closeOnEscape: false,
				open: function(event, ui) {
					jQuery(".ui-dialog-titlebar-close").hide();
				},
				buttons: {
					"<?php echo __("Log out"); ?>": function() {
						document.location.href="<?php echo url_for('@logout');?>";
					}
					<?php if($sf_user->isAdmin()) : ?>
						,
						"<?php echo __("Renew"); ?>": function() {
							jQuery(this).dialog("close");
							jQuery.facebox({ iframe: "<?php echo url_for('order/subscribe?no_close=1');?>" });
						}
					<?php endif; ?>
				}
			});
		});
	</script>
<?php endif; ?>