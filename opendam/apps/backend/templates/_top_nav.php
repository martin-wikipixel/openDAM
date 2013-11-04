<ul class="nav">
					<li class="container-dropdown">
						<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
							<i class="icon-user icon-large"></i> <?php echo $sf_user->getFullname();?> <b class="icon-caret-down"></b>
						</a>

						<ul class="dropdown-menu" role="menu">
							<li><a href="<?php echo path("account"); ?>"><i class="icon-info-sign"></i> <?php echo __("Edit my profile"); ?></a></li>

							<?php if ($sf_user->haveAccessModule(ModulePeer::__MOD_REINIT_PASSWORD)): ?>
								<li><a href="<?php echo path("account_password"); ?>"><i class="icon-key"></i> <?php echo __("Change my password"); ?></a></li>
							<?php endif; ?>

							<li class="divider"></li>

							<li><a href="<?php echo url_for('@logout'); ?>"><i class="icon-off"></i> <?php echo __("Logout");?></a></li>
						</ul>
					</li>
				</ul>

				<?php if ($sf_user->isAdmin()) : ?>
					<ul class="nav">
						<li class="container-dropdown">
							<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
								<i class="icon-cog icon-large"></i> <?php echo __("Management"); ?> <b class="icon-caret-down"></b>
							</a>

							<ul class="dropdown-menu" role="menu">
								<li>
									<a href="<?php echo path("admin_user_list"); ?>">
										<i class="icon-user"></i> <?php echo __("Manage users"); ?>
									</a>
								</li>

								<li>
									<a href="<?php echo url_for("admin_group_list"); ?>">
										<i class="icon-group"></i> <?php echo __("group.title"); ?>
									</a>
								</li>

								<li>
									<a href="<?php echo url_for("admin_album_list"); ?>">
										<i class="icon-book"></i> <?php echo __("Albums"); ?>
									</a>
								</li>

								<li class="divider"></li>

								<li class="dropdown-submenu">
									<a tabindex="-1" href="#">
										<i class="icon-cogs"></i> <?php echo __("Advanced settings"); ?>
									</a>
									<ul class="dropdown-menu">
										<li>
											<a href="<?php echo path("admin_file_duplicate_list")?>">
												<i class="icon-copy"></i> <?php echo __("Find duplicates"); ?>
											</a>
										</li>
		
										<li>
											<a href="<?php echo path("admin_module_list"); ?>">
												<i class="icon-beaker"></i> <?php echo __("Services"); ?>
											</a>
										</li>

										<li>
											<a href="<?php echo path("admin_preset_list"); ?>">
												<i class="icon-legal"></i> <?php echo __("Manage presets"); ?>
											</a>
										</li>

										<li>
											<a href="<?php echo path("admin_tag_list"); ?>">
												<i class="icon-tags"></i> <?php echo __("Keyword management"); ?>
											</a>
										</li>

										<?php if ($sf_user->haveAccessModule(ModulePeer::__MOD_THESAURUS)):?>
											<li>
												<a href="<?php echo path("admin_thesaurus_list"); ?>">
													<i class="icon-font"></i> <?php echo __("Manage thesaurus"); ?>
												</a>
											</li>
										<?php endif; ?>
									</ul>
								</li>
							</ul>
						</li>
					</ul>

					<ul class="nav">
						<li class="container-dropdown">
							<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
								<i class="icon-bar-chart icon-large"></i> <?php echo __("Reports"); ?> <b class="icon-caret-down"></b>
							</a>

							<ul class="dropdown-menu" role="menu">
								<li>
									<a href="<?php echo path("admin_log_list"); ?>">
										<i class="icon-eye-open"></i> <?php echo __("Events log"); ?>
									</a>
								</li>

								<li>
									<a href="<?php echo path("admin_usage_tracking"); ?>">
										<i class="icon-bar-chart"></i> <?php echo __("Consumer log"); ?>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				<?php endif; ?>
				
				<ul class="nav pull-right">
					<li>
						<?php 
							$link_upload = get_slot("link_upload");
						?>

						<?php if (!empty($link_upload)):?>
							<a href="<?php echo url_for($link_upload); ?>" data-toogle="modal-iframe" class="btn-header long">
								<i class="icon-upload"></i> <?php echo __("UPLOAD"); ?>
							</a>
						<?php endif?>
					</li>
				</ul>