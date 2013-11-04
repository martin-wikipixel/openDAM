<script>
(function() {
	"use strict";
	
	$(document).ready(function() {
		<?php if ($sf_user->hasFlash("success")): ?>
			var message = <?php echo json_encode($sf_data->getRaw("sf_user")->getFlash("success"));?>;
			services.notification.success(message);
		<?php elseif($sf_user->hasFlash("info")): ?>
			var message = <?php echo json_encode($sf_data->getRaw("sf_user")->getFlash("info"));?>;
			services.notification.info(message);
		<?php elseif ($sf_user->hasFlash("warning")): ?>
			var message = <?php echo json_encode($sf_data->getRaw("sf_user")->getFlash("warning"));?>;
			services.notification.warning(message);
		<?php elseif ($sf_user->hasFlash("error")): ?>
			var message = <?php echo json_encode($sf_data->getRaw("sf_user")->getFlash("error"));?>;
			services.notification.error(message);
		<?php endif; ?>
	});	
})();
</script>