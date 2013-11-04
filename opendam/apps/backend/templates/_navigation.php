<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a href="<?php echo url_for("@homepage"); ?>" class="brand scroller">
				<img alt="logo" src="<?php echo image_path("bootstrap/layout/logo-wikipixel.png"); ?>" />
			</a>
			
			<?php if($sf_user->isAuthenticated()):?>
				<?php include_partial("search/top", array());?>
				<?php include_partial("global/top_nav")?>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php include_partial("global/messages", array());?>
<script>
(function($) {
	"use strict";

	$(document).ready(function() {
		$(".dropdown-toggle").parent()
			.mouseover(function(){
				var $this = $(this);
				var $menu = $this.find("> .dropdown-menu");

				var windowWidth = $(window).width();

				var topLeftX = $this.position().left;
				var menuWidth = $menu.outerWidth();//outerWidth plutôt que width pour prendre en compte les margins, border...
				
				var overflowDelta = (topLeftX + menuWidth) - windowWidth;

				if (overflowDelta < 0) {
					$menu.css("left", 0);
					$menu.css("right", "inherit");
				}
				else {
					$menu.css("left", "inherit");
					$menu.css("right", 0);
				}
				
				$(this).find(".dropdown-menu:first").show();
			})
			.mouseout(function(event) {
				$(this).find(".dropdown-menu:first").hide();
		});
		
		$(".slidedown-toogle").on("click", function() {
			if ($(this).parent().find(".slidedown-menu").height() > 0) {
				$(this).parent().find(".slidedown-menu").css({"height": "0px"});
				$(this).find("i").addClass("icon-caret-right").removeClass("icon-caret-down");
			}
			else {
				var height = $(this).parent().find(".slidedown-menu").children().outerHeight() * 
					$(this).parent().find(".slidedown-menu").children().length;

				$(this).parent().find(".slidedown-menu").css({"height": height + "px"});
				$(this).find("i").addClass("icon-caret-down").removeClass("icon-caret-right");
			}
		});
	});
})(jQuery);
</script>
