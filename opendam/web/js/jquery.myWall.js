(function(window, $, undefined) {
	"use strict";

	$.customWall = function(options, element) {
		this.element = $(element);

		this._create(options);
		this._init();
	};

	$.customWall.settings = {
		maxHeight: 0,
		minWidth: 0,
		itemSelector: "",
		notFoundPath: "",
		gutterWidth: 0,
		showLastLine: false,
		inline: false,
		lazyImg: ""
	};

	$.customWall.prototype = {
		_create: function(options) {
			this.options = $.extend(true, {}, $.customWall.settings, options);

			var instance = this;
			this.containerWidth = instance.element.width();
			this.imageSettings = ["data-src200", "data-src400", "data-srcMob", "data-srcTab"];
			this.lastElements = [];
			this.smallSize = 0;
			this.smallElements = [];

			instance.element.css({
				"position": "relative",
				"width": this.containerWidth + "px"
			});
		},

		_init: function() {
			var images = this._getImages($(this.options.itemSelector));

			this._addItems(images);
		},

		_getImages: function($elements) {
			return $($elements).find("img");
		},

		_displayResults: function(images, width, line, last) {
			if((this.lastElements.length > 0 && line == 1) || this.options.showLastLine == true)
			{
				this.lastElements = [];

				$(images).closest(this.options.itemSelector).imagesLoaded(function() {
					$(this).addClass("show");
				});
			}
			// else if(Math.round(width) < this.containerWidth)
			else if(last)
			{
				$(images).closest(this.options.itemSelector).addClass("hide");
				this.lastElements = images;
			}
			else
			{
				$(images).closest(this.options.itemSelector).imagesLoaded(function() {
					$(this).addClass("show");
				});
			}
		},

		_getBetterSrc: function(image, width, height) {
			var index = this._getBetterSrcIndex(image, width, height);

			if(index == -1)
				return this.options.notFoundPath;
			else
				return $(image).attr(this.imageSettings[index]);
		},

		_getBetterSrcIndex: function(image, width, height) {
			var index = 0;
			var src = "";

			if(width <= 200 && height <= 200)
				index = 0;
			else if(width <= 400 && height <= 400)
				index = 1;
			else if(width <= 1280 && height <= 720)
				index = 2;
			else if(width <= 2560 && height <= 1600)
				index = 3;

			while(index >= 0)
			{
				src = this.imageSettings[index];

				if($(image).attr(src))
					return index;

				index--;
			}

			return index;
		},

		_getRatio: function(images, width) {
			width -= images.length * this.options.gutterWidth;

			var height = 0;

			for(var i = 0; i < images.length; i++)
				height += $(images[i]).attr("data-width") / $(images[i]).attr("data-height");

			return width / height;
		},

		_resizeImages: function(images, height, line, lastElement) {
			var src = "";
			var sum = 0;
			var lastElements = jQuery.makeArray(this.lastElements);
			var allElements = jQuery.makeArray(images);

			for(var i = 0; i < allElements.length; i++)
			{
				var imgWidth = $(allElements[i]).attr("data-width");
				var imgHeight = $(allElements[i]).attr("data-height");
				var widthPic = 0;
				var w = 0;
				var h = 0;

				widthPic = Math.floor((height * imgWidth) / imgHeight);

				if(i == (allElements.length - 1))
				{
					if(lastElement == true && this.options.showLastLine == true)
						;
					else
						widthPic += this.containerWidth - (sum + widthPic + ((allElements.length - 1) * this.options.gutterWidth));
				}

				$(allElements[i]).parent().css({
					"width": widthPic + "px",
					"height": height + "px"
				});

				if(this.options.minWidth > 0)
				{
					if(widthPic < this.options.minWidth)
					{
						this.smallSize += this.options.minWidth - widthPic;
						this.smallElements.push(allElements[i]);
					}
				}

				if(widthPic > imgWidth || height > imgHeight)
				{
					$(allElements[i]).css({
						"width": imgWidth + "px",
						"height": imgHeight + "px",
						"padding-top": "0px"
					});

					w = imgWidth;
					h = imgHeight;

					if(height > imgHeight)
					{
						var padding = (height - imgHeight) / 2;

						$(allElements[i]).css("padding-top", padding + "px");
					}
				}
				else
				{
					$(allElements[i]).css({
						"width": widthPic + "px",
						"height": height + "px",
						"padding-top": "0px"
					});

					w = widthPic;
					h = height;
				}

				src = this._getBetterSrc(allElements[i], w, h);

				if(this.options.inline == true)
				{
					if(line > 0)
					{
						$(allElements[i]).attr("data-src", src);
						// $(allElements[i]).attr("src", this.options.lazyImg || "#");
						$(allElements[i]).attr("src", "#");
					}
					else
						$(allElements[i]).attr("src", src);

					$(allElements[i]).closest(this.options.itemSelector)[0].className = $(allElements[i]).closest(this.options.itemSelector)[0].className.replace(/row-[0-9]/g, '');
					$(allElements[i]).closest(this.options.itemSelector).addClass("row-" + line);
				}
				else
					$(allElements[i]).attr("src", src);

				$(allElements[i]).closest(this.options.itemSelector).removeClass("no-margin-left");
				$(allElements[i]).closest(this.options.itemSelector).removeClass("no-margin-right");

				if(i == 0)
					$(allElements[i]).closest(this.options.itemSelector).addClass("no-margin-left");
				else if(i == (allElements.length - 1))
					$(allElements[i]).closest(this.options.itemSelector).addClass("no-margin-right");

				$(allElements[i]).closest(this.options.itemSelector).css("width", widthPic + "px");
				$(allElements[i]).closest(this.options.itemSelector).attr("data-width", Math.round(widthPic * 1000) / 1000);

				if($(allElements[i]).closest(this.options.itemSelector).hasClass("clicked"))
				{
					$(allElements[i]).closest(this.options.itemSelector).find(".overlay").css({
						"width": $(allElements[i]).closest(this.options.itemSelector).width(),
						"height": $(allElements[i]).closest(this.options.itemSelector).height()
					});
				}

				sum += widthPic;
			}

			return sum + (images.length * this.options.gutterWidth);
		},

		_setSmallElements: function(images, height, line) {
			if(this.smallElements.length < images.length)
			{
				var smallElements = jQuery.makeArray(this.smallElements);
				var allElements = jQuery.makeArray(images);
				var cropp = this.smallSize;
				var sum = 0;
				var widthPic = 0;

				for(var k = 0; k < allElements.length; k++)
				{
					if(jQuery.inArray(allElements[k], smallElements) == -1)
					{
						if(cropp > 0)
						{
							if(($(allElements[k]).closest(this.options.itemSelector).width() - cropp) < this.options.minWidth)
							{
								cropp -= ($(allElements[k]).closest(this.options.itemSelector).width() - this.options.minWidth);
								var newWidth = this.options.minWidth;
							}
							else
							{
								var newWidth = $(allElements[k]).closest(this.options.itemSelector).width() - cropp;
								cropp = 0;
							}
						}
						else
						{
							var tempWidth = parseFloat($(allElements[k]).closest(this.options.itemSelector).attr("data-width"));

							if(Math.round(tempWidth) > tempWidth)
								tempWidth--;

							if(k == (allElements.length - 1))
							{
								console.log((this.containerWidth - (sum + tempWidth + (allElements.length * this.options.gutterWidth))));
							}

							$(allElements[k]).closest(this.options.itemSelector).css("width", tempWidth + "px");
							$(allElements[k]).closest(this.options.itemSelector).attr("data-width", tempWidth);

							var newWidth = 0;
						}
					}
					else
					{
						var newWidth = this.options.minWidth;
					}

					if(newWidth > 0)
					{
						if(Math.round(newWidth) > newWidth)
							newWidth--;

						if(k == (allElements.length - 1))
						{
							console.log((this.containerWidth - (sum + newWidth + (allElements.length * this.options.gutterWidth))));
						}

						var imgWidth = $(allElements[k]).attr("data-width");
						var imgHeight = $(allElements[k]).attr("data-height");
						var newHeight = newWidth * $(allElements[k]).height() / $(allElements[k]).width();
						var paddingTop = ((height - newHeight) / 2);

						$(allElements[k]).parent().css({
							"width": newWidth + "px",
							"height": height + "px"
						});

						if(newWidth > imgWidth || height > imgHeight)
						{
							$(allElements[k]).css({
								"width": imgWidth + "px",
								"height": imgHeight + "px"
							});

							if(height > imgHeight)
							{
								var padding = (height - imgHeight) / 2;

								$(allElements[k]).css("padding-top", padding + "px");
							}
						}
						else
						{
							$(allElements[k]).css({
								"width": newWidth + "px",
								"height": newHeight + "px",
								"padding-top": paddingTop + "px"
							});
						}

						$(allElements[k]).closest(this.options.itemSelector).css("width", newWidth + "px");
						$(allElements[k]).closest(this.options.itemSelector).attr("data-width", Math.round(newWidth * 1000) / 1000);

						widthPic = newWidth;
					}
					else
						widthPic = tempWidth;

					sum += widthPic;
				}
			}

			this.smallElements = [];
			this.smallSize = 0;
		},

		_addItems: function(images) {
			var width = 0;
			var line = 0 ;
			var instance = this;
			var sumHeight = instance.element.height();
			var sumWidth = instance.element.width();

			w: while(images.length > 0)
			{
				for(var i = 1; i < images.length + 1; i++)
				{
					var slice = images.slice(0, i);

					var height = this._getRatio(slice, this.containerWidth);

					if(height < this.options.maxHeight)
					{
						width = this._resizeImages(slice, height, line, false);
						sumHeight += $(slice[0]).closest(this.options.itemSelector).height() + this.options.gutterWidth;

						if(this.options.inline == true)
							sumWidth += width;

						line++;

						if(this.options.minWidth > 0)
						{
							if(this.smallElements.length > 0)
								this._setSmallElements(slice, height, line);
						}

						this._displayResults(slice, width, line, false);

						images = images.slice(i);
						continue w;
					}
				}

				width = this._resizeImages(slice, Math.min(this.options.maxHeight, height), line, true);

				if(this.options.showLastLine == true)
					sumHeight += $(slice[0]).closest(this.options.itemSelector).height() + this.options.gutterWidth;

				if(this.options.inline == true)
					sumWidth += width;

				this._displayResults(slice, width, line, true);

				line++;
				break;
			}

			instance.element.css("height", sumHeight + "px");

			if(this.options.inline == true)
			{
				instance.element.css("width", sumWidth + "px");
				instance.element.css("height", this.options.maxHeight + "px");
			}

			instance.element.attr("data-lines", line);
		},

		addItems: function($content) {
			var images = this._getImages($content);
			var selector = this.options.itemSelector;

			if(this.lastElements.length > 0)
			{
				this.lastElements.each(function(index, element) {
					images.splice(index, 0, element);
					$(element).closest(selector).removeClass("hide");
				});
			}

			if($content.length == 0)
				this.options.showLastLine = true;

			this._addItems(images);
		},

		reload: function() {
			var images = this._getImages($(this.options.itemSelector));
			var instance = this;

			if(this.options.inline == false)
				this.containerWidth = instance.element.width();

			instance.element.css("height", "0px");

			this._addItems(images);
			instance.element.css("opacity", "1");
		},

		setContainerWidth: function(width) {
			this.containerWidth = width;
		},

		setOptions: function(options) {
			var newOptions = $.extend(true, {}, this.options, options);
			this.options = newOptions;
		}
	};

	$.fn.myWall = function(options) {
		if(typeof options === "string")
		{
			var args = Array.prototype.slice.call(arguments, 1);

			if(options == "getLines")
				return this.attr("data-lines");

			this.each(function() {
				var instance = $.data(this, "myWall");

				if(!instance)
				{
					if(window.console)
					{
						window.console.error(
							"cannot call methods on masonry prior to initialization; " +
							"attempted to call method '" + options + "'"
						);
					}

					return;
				}

				if(!$.isFunction(instance[options]) || options.charAt(0) === "_")
				{
					if(window.console)
						window.console.error("no such method '" + options + "' for masonry instance");

					return;
				}

				instance[options].apply(instance, args);
			});
		}
		else
		{
			this.each(function() {
				var instance = $.data(this, "myWall");

				if(instance)
				{
					instance.option(options || {});
					instance._init();
				}
				else
					$.data(this, "myWall", new $.customWall(options, this));
			});
		}

		return this;
	};
})(window, jQuery);