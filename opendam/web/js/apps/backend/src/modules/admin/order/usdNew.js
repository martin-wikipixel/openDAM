(function($) {
	"use strict";

	function disableForm()
	{
		var $root = $("#admin-order-usd-new-page");
		var $billings = $root.find("input[name=billing]");
		var $validOrder = $root.find(".valid-order button");

		$billings.off("click");
		$billings.prop("disabled", true);
		$validOrder.prop("disabled", true);
	}

	function getSliderPrice(disk, user)
	{
		var $root = $("#admin-order-usd-new-page");
		var notification = services.notification;
		var $price = $root.find("#price div span");
		var $monthlyPrice = $root.find("#monthly-payment").closest("label").find("span:first");
		var $yearlyPrice = $root.find("#yearly-payment").closest("label").find("span:first");
		var $selectedPayment = $root.find("input[name=payment]:checked");

		$.ajax(Routing.generate("admin_order_get_price"), {
			data: {"disk": disk, "user": user}
		})
		 .done(function(data){
			 $price.text(data.monthly);
			 $monthlyPrice.text(data.monthly);
			 $yearlyPrice.text(data.yearly);

			 $selectedPayment.trigger("click");
		})
		.fail(function() {
			notification.error();
		});
	}

	$(document).ready(function() {
		var $root = $("#admin-order-usd-new-page");
		var $diskSlider = $root.find("#slider-disk");
		var $userSlider = $root.find("#slider-user");
		var $totalPrice = $root.find("#total span");
		var notification = services.notification;

		$diskSlider.slider({
			min: parseInt($diskSlider.attr("data-bound-min"), 10),
			max: parseInt($diskSlider.attr("data-bound-max"), 10),
			value: parseInt($diskSlider.val(), 10),
			step: 10,
			tooltip: "hide",
			selection: "none"
		});

		var $diskLabel = $("#disk-pricing .slider-handle:not(.hide)");

		$diskSlider.slider().on("slide", function(event) {
			$diskLabel.text(event.value);
		});

		$diskSlider.slider().on("slideStop", function(event) {
			var user = $("#slider-user").slider("getValue").val();
			var disk = event.value;

			getSliderPrice(disk, user);
		});

		$diskLabel.text($diskSlider.val());

		$userSlider.slider({
			min: parseInt($userSlider.attr("data-bound-min"), 10),
			max: parseInt($userSlider.attr("data-bound-max"), 10),
			value: parseInt($userSlider.val(), 10),
			step: 1,
			tooltip: "hide",
			selection: "none"
		});

		var $userLabel = $("#user-pricing .slider-handle:not(.hide)");

		$userSlider.slider().on("slide", function(event) {
			$userLabel.text(event.value);
		});

		$userSlider.slider().on("slideStop", function(event) {
			var user = event.value;
			var disk = $("#slider-disk").slider("getValue").val();

			getSliderPrice(disk, user);
		});

		$userLabel.text($userSlider.val());

		$root.find("input[name=payment]").on("click", function() {
			var $this = $(this);
			var $price = $this.closest("label").find("span:first");

			if ($this.prop("checked") ==  true) {
				$totalPrice.text($price.text());
			}
		});

		$root.find(".valid-order button").on("click", function() {
			var $this = $(this);
			var orderType = $this.attr("data-order-type");
			var disk = $("#slider-disk").slider("getValue").val();
			var user = $("#slider-user").slider("getValue").val();
			var billing = $("input[name=billing]:checked").val();
			var $certif = $(".AuthorizeNetSeal").clone();

			notification.loading("<i class='icon-refresh icon-spin'></i> " + __("Payment module loading..."));

			$.ajax(Routing.generate("admin_order_create"), {
				data: {"user": user, "disk": disk, "type": orderType, "payment": billing}
			})
			.done(function(data) {
				if (data.errorCode > 0) {
					notification.error();
				}
				else {
					notification.clear();

					disableForm();

					$root.append(data.paymentBox);

					$root.find("#payment_area").prepend($certif.removeClass("hide"));
				}
			})
			.fail(function() {
				notification.error();
			});
		});
	});
})(jQuery);