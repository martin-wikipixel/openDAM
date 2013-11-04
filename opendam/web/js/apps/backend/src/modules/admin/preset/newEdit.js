(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-preset-new-edit-page");

		var CREATIVE_COMMONS = 4;
		
		var USAGE_AUTH = 1;
		var USAGE_LIMITED = 2;
		var USAGE_UNAUTH = 3;

		var TYPE_NUM = 1;
		var TYPE_TEXT = 2;
		var TYPE_DATE = 3;
		var TYPE_GEO = 4;
		var TYPE_SUPPORT = 5;
		var TYPE_BOOLEAN = 6;

		var LIMITATION_INTERNE = 6;
		
		$.datepicker.setDefaults(i18n.locale);

		// gestion des licences
		//_______________________________________________________________________________
		var $licenceSelect = $root.find("#data_licence");
		var $creativeCommonContainer = $root.find("#creative-commons-container");
		var $creativeCommonContainerImg = $root.find("#creative-commons-preview");
		var $creativeEditLink = $creativeCommonContainerImg.find(".edit-limitation");
		var $creativeCommonsSelect = $root.find("select[name=creative-commons]");

		$licenceSelect.on("change", function() {
			var $this = $(this);
			
			if ($this.val() == CREATIVE_COMMONS) {
				$creativeCommonContainer.show();
			}
			else {
				$creativeCommonContainer.hide();
			}
		});

		$creativeEditLink.on("click", function() {
			$creativeCommonContainerImg.hide();
			$creativeCommonsSelect.show();
			$creativeCommonsSelect.focus();
		});
		
		$creativeCommonsSelect.on("change", function() {
			var $this = $(this);
			
			$.ajax(Routing.generate("admin_get_creative_commons"), {
				data: {value: $this.val()}
			})
			.done(function(data) {
				$creativeCommonContainerImg.find("img").attr("src", data.img);
				$creativeCommonsSelect.hide();
				$creativeCommonContainerImg.show();
			});
		});

		// Gestion des restrictions de diffusion
		//__________________________________________________________________________
		var $distributionSelect = $("#data_distribution");
		var $limitationContainer = $("#limitation-container");
		
		$distributionSelect.on("change", function() {
			var $this = $(this);
			
			if ($this.val() == USAGE_AUTH) {
				$limitationContainer.show();
			}
			else {
				$limitationContainer.hide();
			}
		});

		/*____________________________________________________________________________________________________________*/
		// coche une checkbox
		$limitationContainer.find("input[data-action=check-limitation]").on("change", function() {
			var $this = $(this);
			var $li = $this.closest("li");
			var type = parseInt($li.attr("data-type"), 10);// obligatoire car switch ne compare pas les entrier et string !!

			switch (type) {
				case TYPE_GEO:
				case TYPE_SUPPORT:
					var $button = $li.find("a[data-toggle=modal]");

					if ($this.is(":checked")) {
						$button.show();
					}
					else {
						$button.hide();
					}
				break;

				default:
					var $input = $li.find("input[type=text]");

					if ($this.is(":checked")) {
						$input.removeAttr("disabled");
						$input.focus();
					}
					else {
						$input.attr("disabled", "disabled");
						$input.val("");
					}
			}
		});

		/*____________________________________________________________________________________________________________*/
		// cas particulier de : Usage interne uniquement 
		$limitationContainer.find("input[value="+LIMITATION_INTERNE+"]").on("change", function(event) {
			var $this = $(this);
			var checkboxs = $limitationContainer.find("input[data-action=check-limitation]");
			var inputs = $limitationContainer.find("input[type=text]");

			if ($this.is(":checked")) {
				// désactive toutes les autres limitations
				checkboxs.each(function(i, val) {
					var $checkbox = $(val);
					
					if ($checkbox.val() == LIMITATION_INTERNE) {// ne pas désactiver le bouton courant
						return;
					}
					
					if ($checkbox.is(":checked")) {
						$checkbox.trigger("click");
					}
					
					$checkbox.attr("disabled", "disabled");
				});
				
				inputs.each(function(i, val) {
					var $input = $(val);

					$input.val("");
					$input.attr("disabled", "disabled");
				});
			}
			else {
				// réactive toutes les autres limitations
				checkboxs.each(function(i, val) {
					$(val).removeAttr("disabled");
				});
			}

			event.stopPropagation();
		});
		
		/*____________________________________________________________________________________________________________*/
		// cas des champs entier
		$limitationContainer.find("input[data-type="+TYPE_NUM+"]").keydown(function(event) {
			var $this = $(this);
			
			// Allow: backspace, delete, tab and escape
			if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || 
				 // Allow: Ctrl+A
				(event.keyCode == 65 && event.ctrlKey === true) || 
				 // Allow: home, end, left, right
				(event.keyCode >= 35 && event.keyCode <= 39)) {
					 // let it happen, don't do anything
					 return;
			}
			else {
				// Ensure that it is a number and stop the keypress
				if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
					event.preventDefault(); 
				}
				else if(event.keyCode >= 48 && event.keyCode <= 57) {
					var val = $this.val();

					switch(event.keyCode) {
						case 48: $this.val(val + "0"); break;
						case 49: $this.val(val + "1"); break;
						case 50: $this.val(val + "2"); break;
						case 51: $this.val(val + "3"); break;
						case 52: $this.val(val + "4"); break;
						case 53: $this.val(val + "5"); break;
						case 54: $this.val(val + "6"); break;
						case 55: $this.val(val + "7"); break;
						case 56: $this.val(val + "8"); break;
						case 57: $this.val(val + "9"); break;
					}

					event.preventDefault();
				}
			}
		});

		/*____________________________________________________________________________________________________________*/
		// cas des champs entier
		$limitationContainer.find("input[data-type="+TYPE_DATE+"]").datepicker({
			showOn: "focus",
			currentText: __("Now"),
			closeText: __("Save"),
			buttonText: "",
			dateFormat: "dd/mm/yy",
			firstDay: 1,
			gotoCurrent: true,
			minDate: 0,
		});
		
		/*____________________________________________________________________________________________________________*/
		// cas des champs geographique
		$limitationContainer.find("input[data-type="+TYPE_GEO+"]").on("focus", function() {
			$("#continent-modal").modal({});
		});
	});
})(jQuery);