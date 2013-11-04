<?php define("__DIR__", dirname(__FILE__)); ?>

<!DOCTYPE html>
<html>
	<head>
		<?php include_http_metas() ?>
		<?php include_metas() ?>
		<?php my_include_title("Wikipixel")?>
		
		<link rel="shortcut icon" href="<?php echo image_path("favicon.ico"); ?>" />

		<?php require_once __DIR__."/assets/layout/_stylesheets.php";?>
		<?php include_stylesheets()?>

		<?php require_once __DIR__."/assets/layout/_javascripts.php";?>
		<?php include_javascripts()?>

		<script type="text/javascript" src="<?php echo url_for("@wpI18n_calalogues");?>"></script>
		<script type="text/javascript">
			var configPath = "<?php echo url_for('@homepage', true); ?>";
			<?php
				$sizes = "var facebox_sizes = new Array(";

				foreach(sfConfig::get("app_facebox_iframe_sizes") as $size)
				{
					$temp = "new Array(";
					foreach(sfConfig::get("app_facebox_".$size."_pages") as $url)
						$temp .= "'".$url."',";

					$temp = substr($temp, 0, -1).")";

					$sizes .= "new faceboxSizes(".$size.",".$temp."),";
				}

				$sizes = substr($sizes, 0, -1).");";

				echo $sizes;
			?>
		</script>
	</head>
	<?php
		if($sf_user->isAuthenticated())
		{
			$basket = get_component("selection", "myBasket");
			$bodyClass = get_slot("body-class");
		}
		else
		{
			$basket = null;
			$bodyClass = null;
		}
	?>
	<body <?php echo !empty($bodyClass) ? "class='".$bodyClass."'" : ""; ?>>
		<?php if($sf_user->isAuthenticated() && $sf_user->haveAccessModule(ModulePeer::__MOD_EXPLORER)): ?>
			<?php $callback = get_slot('callback_explorer'); ?>
			<?php $params = get_slot('params_explorer'); ?>
			<div id="explorer-side">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td>
							<div id="explorer-treeview" class="collapse"></div>
						</td>
						<td>
							<div id="toggle-treeview">
								<a href="javascript: void(0);">
									<img src="<?php echo image_path(__("explorer_en.gif")); ?>" />
								</a>
							</div>
						</td>
					</tr>
				</table>
			</div>
		<?php endif; ?>

		<div id="notifications-container" class="notifications center"></div>
		<?php include_partial("global/navigation", array()); ?>
		
		<?php if(!empty($basket)) : ?>
			<?php echo $basket; ?>
		<?php endif; ?>

		<?php $bread_crumbs = get_slot('bread_crumbs'); ?>
		<?php include_partial("global/breadCrumb", array("bread_crumbs"=>(isset($bread_crumbs) && !empty($bread_crumbs) ? $bread_crumbs : array())));?>
		<?php echo $sf_data->getRaw('sf_content'); ?>

		<div class="clearfix"></div>
		<div class="container">
			<div class="row">
				<div class="span12" id="footer">
					<div id="footer-wrap">
						<span class="footerLinkLeft"><a href="<?php echo url_for('public/setCulture?culture=fr'); ?>">Français</a> - <a href="<?php echo url_for('public/setCulture?culture=en'); ?>">English</a></span>

						<span class="footerLinkRight">
							<?php echo __("Provided by")?> <a href='<?php echo __("http://www.wikipixel.com/"); ?>' target='_blank'>wikipixel</a>
							&nbsp;
						</span>
					</div>
				</div>
			</div>
		</div>

		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('a[rel*=facebox]').bind("click", function(){
					jQuery.facebox({ iframe: this.href });
					return false;
				});

				jQuery('a[rel*=faceframe]').bind("click", function(){
					jQuery.facebox.settings.minHeight = 670;
					jQuery.facebox({ iframe: this.href });
					return false;
				});
			});
		</script>
		
		<script src="/js/bootstrap-dropdown.js" type="text/javascript"></script>
	</body>
</html>
