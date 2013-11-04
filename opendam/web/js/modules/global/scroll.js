var lastScrollTop = 0;
var gotoNavbarInverse = true;

jQuery(document).ready(function() {
	jQuery(window).scroll(function(event){
		var st = jQuery(this).scrollTop();

		if (st >= lastScrollTop)
		{
			if(!jQuery(".navbar-inverse").attr("data-height"))
				jQuery(".navbar-inverse").attr("data-height", jQuery(".navbar-inverse").outerHeight());

			if (jQuery(".top-under").length) {
				if(!jQuery(".top-under").attr("data-top"))
					jQuery(".top-under").attr("data-top", jQuery(".top-under").css("top").replace(/[^-\d\.]/g, ''));
			}
			
			if(parseInt(st, 10) >= 0 && parseInt(st, 10) <= parseInt(jQuery(".navbar-inverse").attr("data-height"), 10))
			{
				jQuery(".navbar-inverse").css({
					"position": "absolute"
				});

				jQuery(".top-under").css("top", jQuery(".top-under").attr("data-top") - parseInt(st, 10) + "px");
			}
			else if(st >= (parseInt(jQuery(".navbar-inverse").offset().top, 10) + parseInt(jQuery(".navbar-inverse").attr("data-height"), 10)))
			{
				jQuery(".navbar-inverse").css({
					"position": "absolute",
					"top": (parseInt(st, 10) - parseInt(jQuery(".navbar-inverse").attr("data-height"), 10)) + "px"
				});

				jQuery(".top-under").css("top", "0px");

				gotoNavbarInverse = true;
			}
			else
			{
				if((lastScrollTop + jQuery(window).height()) == jQuery(document).height())
				{
					jQuery(".navbar-inverse").css({
						"position": "absolute",
						"top": (parseInt(st, 10) - parseInt(jQuery(".navbar-inverse").attr("data-height"), 10)) + "px"
					});
				}
				else
				{
					if(gotoNavbarInverse)
					{
						jQuery(".navbar-inverse").css({
							"position": "absolute",
							"top": parseInt(st) + "px"
						});

						gotoNavbarInverse = false;
					}
				}

				jQuery(".top-under").css("top", (parseInt(jQuery(".top-under").attr("data-top"), 10) - (parseInt(st, 10) - parseInt(jQuery(".navbar-inverse").offset().top, 10))) + "px");
			}

			if(jQuery(".selected-actions").is(":visible"))
			{
				if(jQuery(".actions-container").hasClass("navbar-fixed-top") && parseInt(jQuery(".actions-container").css("top").replace(/[^-\d\.]/g, ''), 10) > 0)
				{
					if(jQuery(".top-under").is(":visible"))
						jQuery(".actions-container").css("top", (parseInt(jQuery(".top-under").css("top").replace(/[^-\d\.]/g, '')) + parseInt(jQuery(".top-under").outerHeight(), 10)) + "px");
					else
						jQuery(".actions-container").css("top", parseInt(jQuery(".top-under").css("top").replace(/[^-\d\.]/g, '')) + "px");
				}
			}
		}
		else
		{
			if(st <= parseInt(jQuery(".navbar-inverse").offset().top, 10))
			{
				jQuery(".navbar-inverse").css({
					"position": "fixed",
					"top": "0px"
				});

				jQuery(".top-under").css("top", parseInt(jQuery(".top-under").attr("data-top"), 10) + "px");
				gotoNavbarInverse = true;
			}
			else
			{
				jQuery(".navbar-inverse").css({
					"position": "absolute"
				});

				jQuery(".top-under").css("top", (parseInt(jQuery(".top-under").attr("data-top"), 10) - (parseInt(st, 10) - parseInt(jQuery(".navbar-inverse").offset().top, 10))) + "px");
			}

			if(jQuery(".selected-actions").is(":visible"))
			{
				if(jQuery(".actions-container").hasClass("navbar-fixed-top"))
				{
					var cssTop = parseInt(jQuery(".navbar-inverse").attr("data-height"), 10);

					if(jQuery(".top-under").is(":visible"))
						cssTop += parseInt(jQuery(".top-under").outerHeight(), 10);

					if(st <= parseInt(jQuery(".navbar-inverse").offset().top, 10))
						jQuery(".actions-container").css("top", cssTop + "px");
					else
					{
						if(jQuery(".top-under").is(":visible"))
							jQuery(".actions-container").css("top", (parseInt(jQuery(".top-under").css("top").replace(/[^-\d\.]/g, '')) + parseInt(jQuery(".top-under").outerHeight(), 10)) + "px");
						else
							jQuery(".actions-container").css("top", parseInt(jQuery(".top-under").css("top").replace(/[^-\d\.]/g, ''), 10) + "px");
					}
				}
			}
		}

		if(st > 0 && (st + jQuery(window).height()) <= jQuery(document).height())
			lastScrollTop = st;
	});
});