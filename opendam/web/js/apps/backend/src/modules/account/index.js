(function($) {
	"use strict";

	$(document).ready(function() {
		var $countrySelect = $("#data_country");
		var $phoneCode = $("#data_phone_code");
		
		$countrySelect.on("change", function() {
			var phoneCode = $(this).find(":selected").attr("data-phone-code");
			
			$phoneCode.val("+" + phoneCode);
		});

		$countrySelect.trigger("change");
	});
})(jQuery);