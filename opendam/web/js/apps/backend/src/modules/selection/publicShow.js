(function($) {
	"use strict";

	/**
	* Ready
	*/
	$(document).ready(function() {
		var $root = $("#public-selection-show-page");
		var $body = $("body");
		var $commentContainer = $(".comments-container");
		var $downloadFrame = $("#download_frame");
		var $downloadBasket = $("#download_basket");
		var selectionCode = $root.attr("data-selection-code");
		var $filesContainer = $('#files');

		/**
		* Init masonry.
		*/
		function initMasonry() {
			var gutterWidthFiles = 15;
			var minWidtFiles = 270;

			var maxPage = $root.attr("data-max-page");
			
			$filesContainer.imagesLoaded( function() {
				$filesContainer.masonry({
					itemSelector : '.item-file',
					gutterWidth: gutterWidthFiles,
					isAnimated: true,
					columnWidth: function(containerWidth) {
						var itemNbr = (containerWidth / minWidtFiles | 0);

						var itemWidth = (((containerWidth - (itemNbr - 1) * gutterWidthFiles) / itemNbr) | 0);

						if (containerWidth < minWidtFiles)
							itemWidth = containerWidth;

						jQuery(".item-file").width(itemWidth);

						return itemWidth;
					}
				});
			});

			if (maxPage > 1) {
				$filesContainer.infinitescroll({
					loading: {
						finishedMsg: __("All files are loaded."),
						img: "/images/icons/loader/big-gray.gif",
						msgText : '<i class="icon-spinner icon-spin"></i> '+__("Loading next files ...")
					},
					navSelector  : '#nav',
					nextSelector : '#nav a',
					itemSelector : '.item-file'
				},
				function(newElements) {
					var newElems = $(newElements).css({ opacity: 0 });

					newElems.imagesLoaded(function() {
						newElems.animate({ opacity: 1 });
						$filesContainer.masonry('appended', newElems, true); 
						$(".item-file .download").tooltip();
					});
				});
			}
		}
		
		initMasonry();

		/*** COMMMENTS ***/
		$(".toogle-comments").on("click", function() {
			if ($commentContainer.is(":visible")) {
				var limit = $commentContainer.width() + $(window).width();

				$commentContainer.animate({
					left: limit
				}, 
				800, function() {
					$commentContainer.remove();
					$filesContainer.css("width", "auto").masonry("reload");
				});
			}
			else {
				$.get(
					Routing.generate("public_selection_comment_list", {"code": selectionCode}),
					function(data) {
						var defaultWidth = $filesContainer.find(".masonry-brick").width();
						$commentContainer = $("<div class='comments-container' style='width: " + defaultWidth + "px;'>" + data + "</div>");

						$commentContainer.css("left", $commentContainer.width() + $(window).width() + "px");

						$filesContainer.parent().prepend($commentContainer);
						$filesContainer.css("width", $filesContainer.width() - ($commentContainer.width() + 15) + "px").masonry("reload");

						var limit = $filesContainer.offset().left + $filesContainer.width() + 15;

						$commentContainer.animate({
							left: limit
						}, 800, function() {
							$commentContainer.addClass("pull-right").css("left", 0).css("position", "relative");
						});
					}
				);
			}
		});
		/*************/

		/*** DOWNLOAD BASKET ***/
		$downloadBasket.on("click", function() {
			if (!$downloadBasket.hasClass("active")) {
				$downloadBasket.find("span").fadeOut(400, function() {
					$downloadBasket.find("span").html("<p><i class='icon-spinner icon-spin'></i> "
							+__("Preparing to download...")+"</p>");
					
					$downloadBasket.find("span").fadeIn(400);
					$downloadBasket.addClass("active");

					$downloadFrame.ready(function() {
						$downloadBasket.find("span").fadeOut(400, function() {
							$downloadBasket.find("span").html(__("Download basket"));
							$downloadBasket.find("span").fadeIn(400);
							$downloadBasket.removeClass("active");
						});
					});

					$downloadFrame.attr("src", Routing.generate("public_selection_donwload", {"code": selectionCode}));
				});
			}
		});
		/*************/

		/*** SLIDESHOW ***/
		$(".toogle-slideshow").on("click", function() {
			var temp = $("<div class='span12'></div>");
			$body.append(temp);
			var width = temp.width();
			temp.remove();
			var height = $(window).height();

			$.get(
				Routing.generate("public_selection_slideshow", {"code" : selectionCode}),
				{ height: height, width: width },
				function(data) {
					$body.addClass("no-scroll");
					$body.append("<div class='overlay'></div>");
					$(".overlay").fadeIn(400, function() {
						$body.append("<div id='slideshow'>" + data + "</div>");
						$("#slideshow").fadeIn(400);
					});
				}
			);
		});
		/*************/
	});
})(jQuery);