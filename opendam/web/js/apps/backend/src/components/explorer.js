(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#explorer-side");
		var $treeView = $root.find("#explorer-treeview");
		var $table = $root.find("table");
		var notification = services.notification;
		var treeClicks = 0;

		/*____________________________________________________________________________________________________________*/
		function attachTreeEvents(event)
		{
			var $this = $(event.currentTarget);
			var $icon = $this.find("i");
			var $parent = $this.closest("li");
			var folderId = $this.attr("data-id");
			var albumId = $this.attr("data-album-id");
			var $children = $parent.find("> ul");
			var isFolder = $this.hasClass("folder-tree");
			var data = "";

			treeClicks++;

			if (treeClicks == 1) {
				setTimeout(function() {
					if(treeClicks == 1) {
						window.location.href = $this.attr("data-href");
					}
					else {
						if ($children.length > 0) {
							if ($children.is(":visible")) {
								$children.slideUp(400, function() {
									$children.remove();
								});

								if (isFolder) {
									$icon.addClass("icon-folder-close").removeClass("icon-folder-open");
								}
							}
							else {
								$children.slideDown(400);

								if (isFolder) {
									$icon.addClass("icon-folder-open").removeClass("icon-folder-close");
								}
							}
						}
						else {
							if (isFolder) {
								data = {"folderId": folderId};
							}

							$.ajax(Routing.generate("explorer_show_album", {"albumId": albumId}), {
								data: data
							})
							.done(function(data){
								if (data.length > 0) {
									$children = $("<ul style='display: none;'></ul>");
									$children.append(data);

									$parent.append($children);

									$children.slideDown(400);

									$icon.addClass("icon-folder-open").removeClass("icon-folder-close");

									$children.find(".folder-tree").on("click", function(event) {
										attachTreeEvents(event);
									});
								}
							})
							.fail(function() {
								notification.error();
							});
						}
					}

					treeClicks = 0;
				}, 300);
			}
		}

		/*____________________________________________________________________________________________________________*/
		$root.find("#toggle-treeview a").on("click", function(event) {
			if ($treeView.text().length > 0) {
				$treeView.css("width", "0px").addClass("collapse");
				$treeView.text("");
			}
			else {
				$.ajax(Routing.generate("explorer_show"))
				.done(function(data){
					$treeView.html(data);
					$treeView.css("width", "370px").removeClass("collapse");

					$treeView.find(".album-tree").on("click", function(event) {
						attachTreeEvents(event);
					});
				})
				.fail(function() {
					notification.error();
				});
			}
		});
	});
})(jQuery);