<?php $host  = $_SERVER['SERVER_NAME'];?>
<div style="padding-left: 10px;">
	<br clear="all"/>
	<h3><?php echo __('Direct access permalink')?></h3>
	<i><?php echo __('This permalink is unique. You can delete it at any time from the section "Permalinks" in "My Account"')?></i>
	<br clear="all"/>
	<br clear="all"/>
	<table style="width: 100%;" cellpadding="5px;" class='permalink-table'>
		<tr>
			<td>
				<?php echo __("Permalink"); ?> :
				<b>
					<a href="https://<?php echo $host.'/p/'.$permalink->getLink();?>" target="_blank">https://<?php echo $host.'/p/'.$permalink->getLink();?></a>
				</b>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo __("Permalink"); ?> (<?php echo __("integrated version"); ?>) :
				<b>
					<input type="text" id="permalink_integrated" value="<img src='https://<?php echo $host.'/p/'.$permalink->getLink();?>' />" />
				</b>
			</td>
		</tr>
		<?php if($sf_user->haveAccessModule(ModulePeer::__MOD_QR_CODE)): ?>
			<?php switch($permalink->getObjectType())
			{
				case 1:
					$file = FilePeer::retrieveByPk($permalink->getObjectId()); ?>
					<tr>
						<td>
							<?php echo __("Permalink (QR code)"); ?> :
							<img src='<?php echo "/".$file->getPath(false)."/".$permalink->getQrcode().".png"; ?>' style='vertical-align: -65px;' />
						</td>
					</tr>
				<?php break;
			} ?>
		<?php endif; ?>
	</table>
</div>

<a href="#" onclick="parent.location.href=parent.location.href;window.parent.closeFacebox();" class="button btnBS"><span><?php echo __("OK")?></span></a>

<script>
jQuery("#permalink_integrated").click(function() {
	jQuery(this).focus();
	jQuery(this).select();
});
</script>