(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-custom-show-page");
		var $customForm = $root.find("#custom_form");
		
		$root.find(".trash-ico").on("click", function() {
			var $this = $(this);
			$this.closest(".upload-target-container").html("");
		});

		$root.find("input[type=file][data-action=upload-file]").change(function (event) {
			event.preventDefault();
			
			uploadSelectedFile(event.target);
		});

		/*____________________________________________________________________________________________________________*/
		function uploadSelectedFile(targetFile) {
			var $this = $(targetFile);
			
			var $controlsContainer = $this.closest(".controls");
			var $inputFile = $controlsContainer.find("input[type=file]");
			var $uploadContainer = $controlsContainer.find(".upload-target-container");

			// cast en string (car si undefined jquery.attr ne fct pas)
			var previousAction = $customForm.attr("action")? $customForm.attr("action") : "";
			
			var targetUrl = $this.attr("data-iframe-href");
			var $uploadIframe = $root.find("iframe[name="+$this.attr("data-iframe-name")+"]");
			
			$uploadIframe.load(function() {
				var html = $(this).contents().find("body").html();

				// restauration du formulaire
				$customForm.attr("action", previousAction);
				$customForm.removeAttr("target");

				// update du container
				$uploadContainer.html(html);
				$inputFile.val("");
			});

			$customForm.attr("target", $uploadIframe.attr("name"));
			$customForm.attr("action", targetUrl);

			$customForm.submit();
		}
	});
})(jQuery);