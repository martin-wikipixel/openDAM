<?php if($sf_user->isAuthenticated()):?>
	<?php
		$bread_crumbs = $sf_data->getRaw("bread_crumbs");
		$actions = get_slot("actions");
		$selectedActions = get_slot("selectedActions");

		$string = str_replace(array("\t","\r\n","\n","\0","\v"," "),'', $actions);
		$actionsLength = mb_strlen($string, "UTF-8");
	?>

	<div class="container">
		<div class="row">
			<div class="<?php echo array_key_exists("parameters", $bread_crumbs) ? "span9" : "span12"; ?>">
				<ul class="breadcrumb">
					<?php if(isset($bread_crumbs) && !empty($bread_crumbs)) : ?>
						<?php
							if(array_key_exists("bread", $bread_crumbs)) {
								$browse = $bread_crumbs["bread"];
							}
							else {
								$browse = $bread_crumbs;
							}

							$count = 1;
							if(in_array($sf_context->getModuleName()."/".$sf_context->getActionName(), array("public/home", "group/shared", "group/private", "file/recent", "group/list", "favorite/list", "selection/list")))
							{
								foreach($browse as $bread)
								{
									echo "<li class='home-bread".(array_key_exists("selected", $bread) && $bread["selected"] == true ? " active" : "")."'><a href='".$bread["link"]."'>".$bread["label"]."</a></li>";
								}
							}
							elseif(($sf_context->getModuleName() == "favorite" && $sf_context->getActionName() == "list") || ($sf_context->getModuleName() == "search" && $sf_context->getActionName() == "search") || ($sf_context->getModuleName() == "order" && $sf_context->getActionName() == "subscribe"))
							{
								foreach($browse as $bread)
								{
									if($count == count($browse))
										echo "<li class='active'><a href='".$bread["link"]."'>".$bread["label"]."</a></li>";
									else
										echo "<li><a href='".$bread["link"]."'>".$bread["label"]."</a><span class='divider'>/</span></li>";

									$count++;
								}
							}
							elseif(count($browse) > 2)
							{
								echo "<li class='container-dropdown'><a class='dropdown-toggle' href='javascript: void(0);'><i class='icon-folder-close'></i></a><ul class='dropdown-menu' role='menu'>";

								$temp = array();

								foreach($browse as $bread)
								{
									if($count < (count($browse) - 1)) {
										if(in_array($bread["link"], array(url_for("@homepage"), url_for("group/private"), url_for("group/shared")))) {
											$icon = "<i class='icon-book'></i>";
										}
										else {
											$icon = "<i class='icon-folder-close-alt'></i>";
										}

										$temp[] = "<li><a href='".$bread["link"]."'>".$icon." ".$bread["label"]."</a></li>";
									}

									$count++;
								}

								krsort($temp);

								echo implode("", $temp);
								echo "</ul></li><li><a href='".$browse[count($browse) - 2]["link"]."'>".$browse[count($browse) - 2]["label"]."</a><span class='divider'>/</span></li>";

								if($sf_context->getModuleName() == "file" && $sf_context->getActionName() == "show")
									echo "<li class='active'><a href='".$browse[count($browse) - 1]["link"]."'>".$browse[count($browse) - 1]["label"]."</a></li>";
								else
									echo "<li class='active'><a href='".$browse[count($browse) - 1]["link"]."'>".$browse[count($browse) - 1]["label"]."<span class='divider'>/</span></a></li>";
							}
							else
							{
								foreach($browse as $bread)
								{
									if($count == count($browse))
										echo "<li class='active'><a href='".$bread["link"]."'>".$bread["label"]."<span class='divider'>/</span></a></li>";
									else
										echo "<li><a href='".$bread["link"]."'>".$bread["label"]."</a><span class='divider'>/</span></li>";

									$count++;
								}
							}
						?>
					<?php else: ?>
						<li class="active"><a href="<?php echo url_for("@homepage"); ?>"><?php echo __("Home"); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<?php if(array_key_exists("parameters", $bread_crumbs)) : ?>
				<div class="span3"><?php echo $bread_crumbs["parameters"]; ?></div>
			<?php endif; ?>
		</div>
	</div>

	<?php if((isset($actions) && !empty($actions) && $actionsLength > 0) || (isset($selectedActions) && !empty($selectedActions))) : ?>
		<div class="navbar navbar-static-top actions-container <?php echo $actionsLength == 0 ? "hide" : ""; ?>">
			<div class="navbar-inner">
				<div class="container">
					<div class="row">
						<div class="span12 actions-wrapper">
							<?php if(isset($selectedActions) && !empty($selectedActions)) : ?>
								<ul class="inline list-actions selected-actions hide">
									<?php echo $selectedActions; ?>
								</ul>
							<?php endif; ?>
							<?php if($actionsLength > 0) : ?>
								<ul class="inline list-actions <?php echo isset($selectedActions) && !empty($selectedActions) ? "default-actions" : ""; ?>">
									<?php echo $actions; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>