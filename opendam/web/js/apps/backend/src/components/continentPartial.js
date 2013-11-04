(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#continents-body");

		/*____________________________________________________________________________________________________________*/
		$root.find("a[data-action=show-all]").on("click", function() {
			var $this = $(this);
			var $countries = $this.closest(".continent").find(".countries");
			
			if ($countries.is(":visible")) {
				$countries.slideUp();
				$this.html(__("Show all countries"));
			}
			else {
				$countries.slideDown();
				$this.html(__("Hide all countries"));
			}
		});

		/*____________________________________________________________________________________________________________*/
		$root.find("input[data-action=check-continent]").on("change", function() {
			var $this = $(this);
			var $countries = $this.closest(".continent").find("input[data-action=check-country]");
			
			$countries.prop("checked", $this.is(":checked"));
		});

		/*____________________________________________________________________________________________________________*/
		$root.find("input[data-action=check-country]").on("change", function() {
			var $this = $(this);
			var $continentCheckbox = $this.closest(".continent").find("[data-action=check-continent]");
			
			$continentCheckbox.prop("checked", false);
		});
		
		function Country(id, name) {
			this.id = id;
			this.name = name;
		}
		
		window.widgets = {};
		window.widgets.continent = {
			getSelectedContries: function() {
				var countries = [];
				
				$root.find(".countries input:checked").each(function() {
					var $input = $(this);
					
					countries.push(new Country($input.val(), $input.attr("data-country-name")));
				})
				
				return countries;
			}
		};
	});
})(jQuery);