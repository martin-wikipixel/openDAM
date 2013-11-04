(function($) {
	"use strict";

	$(document).ready(function() {
		$("#data_company").bind("blur", function() {
			$("#cname_indicator").fadeIn(200, function() {
				$.post(
					Routing.generate("customer_getCname"),
					{ company: jQuery("#data_company").val() },
					function(data) {
						$("#data_cname").val(data);
						$("#cname_indicator").fadeOut();
					}
				);
			});
		});

		$("#data_country").bind("change", function() {
			$.post(
				Routing.generate("public_getIndicatif"),
				{ id: jQuery(this).val() },
				function(data) {
					if (data.errorCode == 0) {
						$("#data_phone_code").val("+" + data.phoneCode);
					}
				},
				"json"
			);
		});
	});
})(jQuery);
