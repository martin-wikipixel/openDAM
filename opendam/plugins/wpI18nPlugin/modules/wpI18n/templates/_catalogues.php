(function(module) {
	"use strict";
	var i18n = i18n || {};
	
	i18n.locale = <?php echo json_encode($locale);?>;
	i18n.catalogues = {};
	
	<?php 
		$traductions = $traductions->getRawValue();
	?>
	<?php foreach ($traductions as $catalogueName => $sentences): ?>
		i18n.catalogues[<?php echo json_encode($catalogueName)?>] = {};
	
		<?php foreach ($sentences as $key => $sentence):?>
			i18n.catalogues[<?php echo json_encode($catalogueName)?>][<?php echo json_encode($key)?>] = <?php echo json_encode($sentence[0])?>;
		<?php endforeach;?>
	<?php endforeach;?>

	i18n.trans = function(key, args, catalogue) {
		if (!key) {
			throw new Error("Missing key parameter");
		}
		
		if (!args) {
			args = [];
		}
		
		if (!catalogue) {
			catalogue = "messages";
		}
		
		if (!i18n.catalogues.hasOwnProperty(catalogue)) {
			throw new Error("unknow catalogue: " + catalogue);
		}
		
		var catalogue = i18n.catalogues[catalogue];
		var message = catalogue[key] || key;
		
		for (var name in args) {
			if (args.hasOwnProperty(name)) {
				message = message.replace(name, args[name]);
			}
		}

		return message;
	};

	module.i18n = i18n;
	module.__ = function() {
		return i18n.trans.apply(this, arguments);
	};
})(window);
