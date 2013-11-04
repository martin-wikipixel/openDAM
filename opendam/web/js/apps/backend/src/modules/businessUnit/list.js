(function($) {
	"use strict";

	function displayItems(itemSelector)
	{
		$(itemSelector).each(function() {
			var $this = $(this);
			var $image = $this.find("img");
			var $thumbnail = $this.find(".thumbnail");

			var width = $this.width();
			var infoHeight = $this.find(".info").get(0).offsetHeight + 1;
			var availableHeight = width - infoHeight;
			var ratio = 1;
			var margin = 0;
			var left = 0;
			var css = "";

			if ($image.length > 0) {
				var originalWidth = $image.attr("data-width");
				var originalHeight = $image.attr("data-height");

				if (originalHeight <= availableHeight) {
					$image.css({
						"padding-top":		Math.round((availableHeight - 1 - originalHeight) / 2) + "px",
						"padding-bottom":	Math.round((availableHeight - 1 - originalHeight) / 2) + "px"
					});
	
					$thumbnail.css("height", availableHeight + "px");
				}
				else {
					if (originalWidth > width) {
						ratio = width / originalWidth;
					}

					if (originalHeight > availableHeight) {
						ratio = availableHeight / originalHeight;
					}

					var tWidth = Math.round(originalWidth * ratio);
					var tHeight = Math.round(originalHeight * ratio);

					if (tWidth > width) {
						margin = (tWidth - width) / 2;
						left = tWidth - (width + margin);

						css = {
								"clip":			"rect(auto, " + (width + margin) + "px, auto, " + left + "px)",
								"width":		tWidth + "px",
								"height":		tHeight + "px",
								"left":			(left * -1) + "px",
								"max-width":	"none"
						};
					}
					else if(tHeight > availableHeight)
					{
						margin = ((tHeight - availableHeight) / 2);
						css = {
								"clip": "rect(" + margin + "px, auto, " + (availableHeight + margin) + "px, auto)",
								"width": tWidth + "px",
								"height": tHeight + "px", "top": (margin * -1) + "px",
								"max-width": "none"
						};
					}
					else
					{
						var paddingLeft = ((width - tWidth) / 2);
						var paddingTop = ((availableHeight - tHeight) / 2);
	
						css = {
								"width": tWidth + "px",
								"height": tHeight + "px",
								"padding-top": paddingTop + "px",
								"padding-left": paddingLeft + "px",
								"max-width": "none"
						};
					}
	
					$thumbnail.css("height", availableHeight + "px");
					$image.addClass("cropp").css(css);
				}
			}
			else {
				$thumbnail.css("height", availableHeight + "px");
			}
		});
	}

	$(document).ready(function() {
		var $root = $("#business-unit-list");
		var $wall = $root.find("#wall");
		var $nextPage = $root.find(".next-page");
		var notification = services.notification;
		var gutterWidth = 10;
		var minWidth = 270;
		var itemSelector = ".business-unit";

		$wall.masonry({
			itemSelector: itemSelector,
			gutterWidth: gutterWidth,
			isAnimated: true,
			columnWidth: function(containerWidth) {
				var itemNbr = (containerWidth / minWidth | 0);
				var itemWidth = (((containerWidth - (itemNbr - 1) * gutterWidth) / itemNbr) | 0);

				if (containerWidth < minWidth) {
					itemWidth = containerWidth;
				}

				$(itemSelector).width(itemWidth);

				displayItems(itemSelector);

				return itemWidth;
			}
		});

		$nextPage.on("click", function() {
			var $this = $(this);
			var target = $this.attr("href");

			$.ajax(target)
			.done(function(data){
				var $dataReturn = $(data);
				var $items = $dataReturn.find(itemSelector);
				var $page = $dataReturn.find(".next-page")

				$wall.append($items);
				$wall.masonry("reload");

				if ($page.length > 0) {
					$this.attr("href", $page.attr("href"));
				}
				else {
					$this.fadeOut(400);
				}
			})
			.fail(function() {
				notification.error();
			});

			return false;
		});
	});
})(jQuery);