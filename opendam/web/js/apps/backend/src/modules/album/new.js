(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#groups");
		var $addGroup = $root.find("#add-group"); 
		var $groupName = $addGroup.find("#group-name");
		var notification = services.notification;

		$addGroup.find("[data-role=submit]").on("click", function() {
			if($.trim($groupName.val()).length <= 0) {
				notification.error(__("Group name is required."));
			}
			else {
				$.ajax(Routing.generate("group_new", {"name": $groupName.val()}))
				.done(function(data){
					var $lastChild = $("body > div:first-child");
					var errorCode = parseInt(data.errorCode, 10);

					if (errorCode <= 0) {
						$lastChild.remove();

						notification.success(__("Album has been added."));

						unbindGroups();
						$addGroup.replaceWith(data.album);
						bindGroups();

						$root.masonry("reload");
					}
					else {
						switch (errorCode) {
							case 2:
								notification.error(__("Group name is required."));
							break;

							case 3:
								notification.error(__("Groupe name already exists."));
							break;

							default:
								notification.error();
							break;
						}
					}
				})
				.fail(function() {
					notification.error();
				});
			}
		});

		$addGroup.find("[data-role=cancel]").on("click", function() {
			var $lastChild = $("body > div:first-child");

			$root.append($lastChild.show());
			$root.masonry("remove", $addGroup);

			$root.masonry("reload");
		});
	});
})(jQuery);