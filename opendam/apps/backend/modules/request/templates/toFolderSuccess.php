<div id="searchResults-popup">
	<div class="inner">
		<?php if(!sizeof($requests)):?>
			<div class="text"><?php echo __("There are no new access requests.")?></div>
		<?php else:?>
			<form class="form">
				<?php foreach ($requests as $request):?>
					<div id="request_<?php echo $request->getId()?>">
						<?php 
						if($request->getIsRequest()){
							include_partial("request/request", array("request"=>$request));
						}else{
							include_partial("request/message", array("request"=>$request));
						}?>
					</div>
				<?php endforeach;?>
			</form>
		<?php endif;?>

		<div class="right">
			<a href="#" onclick="window.parent.closeFacebox(); " class="button btnBSG"><span><?php echo __("CLOSE")?></span></a>
		</div>
	</div>
</div>