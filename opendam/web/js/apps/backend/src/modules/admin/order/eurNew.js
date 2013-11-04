(function($) {
	"use strict";

	function disableForm()
	{
		var $root = $("#admin-order-eur-new-page");
		var $buttons = $root.find(".select-product button");
		var $billings = $root.find("input[name=billing]");
		var $storages = $root.find("select.disk-storage");
		var $validOrder = $root.find(".valid-order button");

		$buttons.off("click");
		$billings.off("click");
		$billings.prop("disabled", true);
		$storages.off("change");
		$validOrder.prop("disabled", true);
	}

	function updatePrice(object)
	{
		var $root = $("#admin-order-eur-new-page");
		var $input = object.closest(".order-product").find(".disk-storage");
		var $billing = $root.find("input[name=billing]:checked");
		var $billingMonthly = $root.find("#billing_monthly");
		var $billingYearly = $root.find("#billing_yearly");
		var $labelBillingMonthly = $billingMonthly.parent().find("label[for=billing_monthly]").find("span:first-child");
		var $labelBillingYearly = $billingYearly.parent().find("label[for=billing_yearly]").find("span:first-child");

		if ($input.get(0).tagName == "SELECT") {
			var $price = $input.find("option:selected");
		}
		else {
			var $price = $input;
		}

		$billingMonthly.attr("data-price", $price.attr("data-monthly-credit"));
		$labelBillingMonthly.text($price.attr("data-monthly"));

		$billingYearly.attr("data-price", $price.attr("data-price-year-credit"));
		$labelBillingYearly.text($price.attr("data-price-year"));

		$billing.trigger("click");
	}

	$(document).ready(function() {
		var $root = $("#admin-order-eur-new-page");
		var $validOrder = $root.find(".valid-order button");
		var $alreadyPaidMonthly = $root.find("#alreadyPaidMonthly");
		var $alreadyPaidYearly = $root.find("#alreadyPaidYearly");
		var $total = $root.find("#summary-total");
		var notification = services.notification;

		$root.find("select.disk-storage").on("change", function() {
			var $this = $(this);
			var $currentOption = $("option:selected", this);
			var $billing = $root.find("input[name=billing]:checked");
			var $productBlock = $this.closest(".order-product");
			var $showPrice = $productBlock.find(".display-price");

			if ($billing.val() == "monthly") {
				$showPrice.text($currentOption.attr("data-monthly"));
			}
			else {
				$showPrice.text($currentOption.attr("data-yearly"));
			}

			if ($productBlock.hasClass("selected")) {
				updatePrice($this);
			}
		});

		$root.find(".select-product button").on("click", function() {
			var $this = $(this);
			var $productBlock = $this.closest(".order-product");
			var $products = $root.find(".order-product");

			$products.removeClass("selected");
			$products.find(".select-product button").removeClass("active");

			$productBlock.addClass("selected");
			$productBlock.find(".select-product button").addClass("active");

			if (!$validOrder.is(":visible")) {
				$validOrder.fadeIn(400);
			}

			updatePrice($this);
		});

		$root.find("input[name=billing]").on("click", function() {
			var $this = $(this);
			var price = $this.attr("data-price");
			var $billing = $root.find("input[name=billing]:checked");
			var $products = $root.find(".order-product");

			if ($billing.val() == "monthly") {
				if ($alreadyPaidMonthly.length > 0) {
					$total.text(price + " " + $alreadyPaidMonthly.val());
				}
				else {
					$total.text(price);
				}
			}
			else if ($billing.val() == "yearly") {
				if ($alreadyPaidYearly.length > 0) {
					$total.text(price + " " + $alreadyPaidYearly.val());
				}
				else {
					$total.text(price);
				}
			}

			$products.each(function() {
				var $this = $(this);
				var $showPrice = $this.find(".display-price");
				var $input = $this.find(".disk-storage");

				if ($input.get(0).tagName == "SELECT") {
					var $currentOption = $input.find("option:selected");
				}
				else {
					var $currentOption = $input;
				}

				if ($billing.val() == "monthly") {
					var price = $currentOption.attr("data-monthly");
				}
				else if ($billing.val() == "yearly") {
					var price = $currentOption.attr("data-yearly");
				}

				$showPrice.text(price);
			});
		});

		$validOrder.on("click", function() {
			var $this = $(this);
			var $productSelected = $root.find(".order-product.selected");
			var $productNotSelected = $root.find(".order-product:not(.selected)");
			var $input = $productSelected.find(".disk-storage");
			var pricing = $input.val();
			var billing = $root.find("input[name=billing]:checked").val();
			var orderType = $this.attr("data-order-type");
			var $errorBox = $root.find(".text-error");

			if ($input.get(0).tagName == "SELECT") {
				var disk = $input.find("option:selected").attr("data-space");
			}
			else {
				var disk = $input.attr("data-space");
			}

			if (!pricing || !disk) {
				$errorBox.text(__("Please select an offer."));
			}
			else {
				$errorBox.text("");
				$productNotSelected.addClass("hide-me");

				notification.loading("<i class='icon-refresh icon-spin'></i> " + __("Payment module loading..."));

				$.ajax(Routing.generate("admin_order_create"), {
					data: {"disk": disk, "type": orderType, "payment": billing, "pricing": pricing}
				})
				.done(function(data) {
					if (data.errorCode > 0) {
						notification.error();
					}
					else {
						notification.clear();

						disableForm();
	
						$root.append(data.paymentBox);
					}
				})
				.fail(function() {
					notification.error();
				});
			}
		});
	});
})(jQuery);