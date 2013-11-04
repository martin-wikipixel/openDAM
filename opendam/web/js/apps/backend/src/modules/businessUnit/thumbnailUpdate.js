(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#business-unit-thumbnail-update-page");
		var $uploadForm = $root.find("#upload_form");

		$root.find(".trash-ico").on("click", function() {
			var $this = $(this);
			$this.closest(".upload-target-container").html("");
		});

		$root.find("input[type=file]").change(function () {
			$(this).closest(".controls").find("button[data-action=upload-file]").trigger("click");
		});

		/*___________________________________________________________________________________________________________*/
		$root.find("button[data-action=upload-file]").on("click", function(event) {
			event.preventDefault();

			var $this = $(this);
			var $controlsContainer = $this.closest(".controls");
			var $inputFile = $controlsContainer.find("input[type=file]");
			var $uploadContainer = $controlsContainer.find(".upload-target-container");

			// cast en string (car si undefined jquery.attr ne fct pas)
			var previousAction = $uploadForm.attr("action")? $uploadForm.attr("action") : "";
			var targetUrl = $this.attr("data-target-url");

			$("<iframe name='iframe_upload_thumb' id='iframe_upload_thumb' src='javascript: return false;'></iframe>")
				.load(function() {
					var html = $(this).contents().find("body").html();

					// restauration du formulaire
					$uploadForm.attr("action", previousAction);
					$uploadForm.removeAttr("target");

					// update du container
					$uploadContainer.html(html);
					$inputFile.val("");

					$(this).remove();
				})
				.appendTo("body");

			$uploadForm.attr("target", "iframe_upload_thumb");
			$uploadForm.attr("action", targetUrl);

			$uploadForm.submit();
		});
	});
})(jQuery);