<p>
	<strong><?php echo __("Permalink"); ?> :</strong><br />
	<a class="premalink_link" href="<?php echo url($routeName, array("link" => $permalink->getLink()));?>" target="_blank">
	<?php echo url($routeName, array("link" => $permalink->getLink()));?>
	</a>
</p>

<p>
	<strong><?php echo __("Permalink"); ?> (<?php echo __("integrated version"); ?>) :</strong><br />
	<input type="text" readonly data-action=select class="permalink" value="&lt;img src='<?php echo url($routeName, array("link" => $permalink->getLink()));?>' />" />
</p>

<?php if ($sf_user->haveAccessModule(ModulePeer::__MOD_QR_CODE) && file_exists(sfConfig::get("app_path_qrcode_dir")."/".$permalink->getQrcode().".png")): ?>
	<p>
		<strong><?php echo __("Permalink (QR code)"); ?> :</strong><br />
		<img src='<?php echo "/".sfConfig::get("app_path_qrcode_dir_name")."/".$permalink->getQrcode().".png"; ?>' style='vertical-align: -65px;' />
	</p>
<?php endif; ?>