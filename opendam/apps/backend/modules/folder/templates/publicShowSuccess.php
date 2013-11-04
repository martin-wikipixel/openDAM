<?php
	$folders = $folders->getRawValue();
	$files = $files->getRawValue();
	$breadcrumb = $breadcrumb->getRawValue();
?>

<div class="container">
	<div class="row">
		<div class="span5 title-folder">
			<ul class="breadcrumb">
				<?php
					$count = 1;
					if(count($breadcrumb) > 2)
					{
						// Inner
						echo "<li class='dropdown'>
								<a class='dropdown-toggle' id='breadcrumb' role='button' data-toggle='dropdown' href='javascript: void(0);'>...</a>
								<ul id='breadcrumb-dropdown' class='dropdown-menu'>";

								$breadcrumb = array_reverse($breadcrumb);
								foreach($breadcrumb as $folderB)
								{
									if($count > 2)
										echo "<li><a href='".url_for("folder/publicShow?link=".$permalink->getLink()."&folder_id=".$folderB->getId())."'><i class='icon-folder-close'></i> ".$folderB."</a></li>";

									$count++;
								}
								$breadcrumb = array_reverse($breadcrumb);

						echo "	</ul><span class='divider'>/</span>
							</li>";

						// Last
						echo "<li><a href='".url_for("folder/publicShow?link=".$permalink->getLink()."&folder_id=".$breadcrumb[count($breadcrumb) - 2]->getId())."'>".$breadcrumb[count($breadcrumb) - 2]."</a><span class='divider'>/</span></li>";
						echo "<li class='active'>".$breadcrumb[count($breadcrumb) - 1]." <span class='content'>".$files->getNbResults()." <i class='icon-picture'></i> ".count($folders)." <i class='icon-folder-close'></i></span></li>";
					}
					else
					{
						foreach($breadcrumb as $folderB)
						{
							if($count == count($breadcrumb))
								echo "<li class='active'>".$folderB."<span class='content'>".$files->getNbResults()." <i class='icon-picture'></i> ".count($folders)." <i class='icon-folder-close'></i></span></li>";
							else
								echo "<li><a href='".url_for("folder/publicShow?link=".$permalink->getLink()."&folder_id=".$folderB->getId())."'>".$folderB."</a><span class='divider'>/</span></li>";

							$count++;
						}
					}
				?>
			</ul>
		</div>
		<div class="span7 title-actions">
			<ul class="inline">
				<?php if($permalink->getAllowComments()) : ?>
					<li><a href="javascript: void(0);" class="toogle-comments"><?php echo __("Comments"); ?> <i class="icon-comment"></i></a></li>
				<?php endif; ?>
				<?php if($files->getNbResults()) : ?>
					<li><a href="javascript: void(0);" class="toogle-slideshow"><?php echo __("Slideshow"); ?> <i class="icon-play-circle"></a></i></li>
				<?php endif; ?>
			</ul>
		</div>
	</div>
</div>

<?php if(!empty($folders)) : ?>
	<div class="folders">
		<div class="container">
			<div id="folders">
				<?php foreach($folders as $folderIn) : ?>
					<div class="folder item-folder item">
						<a class="contain" href="<?php echo url_for("folder/publicShow?link=".$permalink->getLink()."&folder_id=".$folderIn->getId()); ?>">
							<div class="thumbnail">
								<?php
									if (!$folderIn->getThumbnail()) {
										$relative = image_path("no-access-file-200x200.png");
										$absolute = sfConfig::get('app_path_images_dir')."/no-access-file-200x200.png";
									}
									else {
										$absolute = $folderIn->getRealPathname();
										$relative = path("@folder_thumbnail", array("id" => $folderIn->getId(), "link" => $permalink->getLink()));
									}

									$size = getimagesize($absolute);
								?>
								<img src="<?php echo $relative; ?>" />
							</div>
							<div class="info">
								<div class="title">
									<i class="icon-folder-close"></i> <?php echo $folderIn->getName(); ?>
								</div>
								<div class="clearfix"></div>
								<span class="contain-inside">
									<?php echo $folderIn->getNumberOfFolders()." ".__("subfolders"); ?> | <?php echo $folderIn->getNumberOfFiles()." ".__("files"); ?>
								</span>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="files">
	<div class="container">
		<div id="files">
			<?php foreach ($files->getResults() as $fileIn): ?>
				<?php include_partial("folder/publicFile", Array("fileIn" => $fileIn, "permalink" => $permalink)); ?>
			<?php endforeach; ?>

			<?php if($files->getNbResults() == 0 && empty($folders)) : ?>
				<div class="row">
					<div class="span12">
						<?php echo __("No file found."); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php if($files->getLastPage() > 1) : ?>
	<div id="nav">
		<a href="<?php echo url_for("folder/publicShow?link=".$permalink->getLink()."&folder_id=".$folder->getId()."&page=".$files->getNextPage()); ?>"><?php echo $files->getNextPage(); ?></a>
	</div>
<?php endif; ?>

<div id="download-modal" class="modal hide fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="icon-remove"></i></button>
		<h3><?php echo __("Download folder"); ?></h3>
	</div>
	<div class="modal-body">
		<p>
			<?php echo __("Number of files:"); ?> <?php echo $files->getNbResults(); ?><br />
			<?php echo __("Size:"); ?> <?php echo myTools::getSize($filesSize); ?>
		</p>
	</div>
	<div class="modal-footer">
		<iframe name="download_frame" id="download_frame" class="hide"></iframe>
		<a id="download_folder" href="javascript: void(0);" class="btn-header"><span><?php echo __("Download folder"); ?></span></a>
	</div>
</div>
<script>
	var defaultContainer = "";

	jQuery(document).ready(function() {
		jQuery('a[rel*=facebox]').bind("click", function(){
			jQuery.facebox({ iframe: this.href });
			return false;
		});

		jQuery('a[rel*=faceframe]').bind("click", function() {
			jQuery.facebox.settings.minHeight = 670;
			jQuery.facebox({ iframe: this.href });
			return false;
		});

		jQuery(".item-file .download").tooltip();

		initMasonry();

		<?php if(!empty($folders)) : ?>
			defaultContainer = jQuery("#folders");
		<?php else: ?>
			defaultContainer = jQuery("#files");
		<?php endif; ?>

		var oldDefaultContainerWidth = 0;

		/*** COMMMENTS ***/
		jQuery(".toogle-comments").bind("click", function() {
			if(jQuery(".comments-container").is(":visible"))
			{
				var limit = jQuery(".comments-container").width() + jQuery(window).width();

				jQuery(".comments-container").animate({
					left: limit
				}, 800, function() {
					jQuery(".comments-container").remove();
					defaultContainer.css("width", oldDefaultContainerWidth + "px");

					setTimeout(function() {
						defaultContainer.masonry("reload");
					}, 200);
				});
			}
			else
			{
				jQuery.post(
					"<?php echo url_for("folder/loadComments"); ?>",
					{ permalink: "<?php echo $permalink->getLink(); ?>" },
					function(data) {
						var defaultContainerWidth = defaultContainer.width();
						oldDefaultContainerWidth = defaultContainerWidth;

						if(defaultContainer.find(".masonry-brick").length > 0)
							var defaultWidth = defaultContainer.find(".masonry-brick").width();
						else
						{
							var temp = jQuery("<div class='span3'></div>");
							jQuery("body").append(temp);
							var defaultWidth = temp.width();
						}

						var comment = jQuery("<div class='comments-container' style='width: " + defaultWidth + "px;'>" + data + "</div>");

						comment.css("left", comment.width() + jQuery(window).width() + "px");
						defaultContainer.parent().prepend(comment);

						var newWidth = (defaultContainerWidth - (comment.width() + 15));
						defaultContainer.css("width", newWidth + "px");

						setTimeout(function() {
							defaultContainer.masonry("reload");

							var limit = defaultContainer.offset().left + newWidth + 15;

							jQuery(".comments-container").animate({
								left: limit
							}, 800, function() {
								jQuery(".comments-container").addClass("pull-right").css("left", 0).css("position", "relative");
							});
						}, 200);
					}
				);
			}
		});
		/*************/

		/*** SLIDESHOW ***/
		jQuery(".toogle-slideshow").bind("click", function() {
			var temp = jQuery("<div class='span12'></div>");
			jQuery("body").append(temp);
			var width = temp.width();
			temp.remove();
			var height = jQuery(window).height();

			jQuery.post(
				"<?php echo url_for("folder/slideshowPublic"); ?>",
				{ folder_id: "<?php echo $folder->getId(); ?>", permalink: "<?php echo $permalink->getLink(); ?>", height: height, width: width },
				function(data) {
					jQuery("body").addClass("no-scroll");
					jQuery("body").append("<div class='overlay'></div>");
					jQuery(".overlay").fadeIn(400, function() {
						jQuery("body").append("<div id='slideshow'>" + data + "</div>");
						jQuery("#slideshow").fadeIn(400);
					});
				}
			);
		});
		/*************/

		/*** DOWNLOAD FOLDER ***/
		jQuery("#download_folder").bind("click", function() {
			if(!jQuery("#download_folder").hasClass("active"))
			{
				jQuery("#download_folder").find("span").fadeOut(400, function() {
					jQuery("#download_folder").find("span").html("<p><i class='icon-spinner icon-spin'></i> <?php echo __("Preparing to download..."); ?></p>");
					jQuery("#download_folder").find("span").fadeIn(400);
					jQuery("#download_folder").addClass("active");

					jQuery("#download_frame").ready(function() {
						jQuery("#download_folder").find("span").fadeOut(400, function() {
							jQuery("#download_folder").find("span").html("<?php echo __("Download folder"); ?>");
							jQuery("#download_folder").find("span").fadeIn(400);
							jQuery("#download_folder").removeClass("active");
						});
					});

					jQuery("#download_frame").attr("src", "<?php echo url_for("download/downloadFolder?folder_id=".$folder->getId()."&permalink_id=".$permalink->getLink()."&definition=".($permalink->getFormatHd() ? "original" : "web")); ?>");
				});
			}
		});
		/*************/
	});

	function initMasonry()
	{
		/*** INIT FILES ***/
		displayFilesBackground();
		var containerFiles = jQuery("#files");

		var gutterWidthFiles = 15;
		var minWidtFiles = 270;

		containerFiles.imagesLoaded( function() {
			containerFiles.masonry({
				itemSelector : ".item-file",
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

		<?php if($files->getLastPage() > 1) : ?>
			containerFiles.infinitescroll({
				loading: {
					finishedMsg: "<?php echo __("All files are loaded."); ?>",
					img: "<?php echo image_path("icons/loader/big-gray.gif"); ?>",
					msgText : '<i class="icon-spinner icon-spin"></i> <?php echo __("Loading next files ..."); ?>'
				},
				navSelector  : "#nav",
				nextSelector : "#nav a",
				itemSelector : ".item-file"
			},
			function(newElements) {
				var newElems = jQuery(newElements).css({ opacity: 0 });

				newElems.imagesLoaded(function() {
					newElems.animate({ opacity: 1 });
					containerFiles.masonry("appended", newElems, true); 
				});
			});
		<?php endif; ?>
		/*************/

		<?php if(!empty($folders)) : ?>
			/*** INIT FOLDERS ***/
			displayFoldersBackground();
			var containerFolders = jQuery("#folders");

			var gutterWidthFolders = 15;
			var minWidthFolders = 270;

			containerFolders.imagesLoaded( function() {
				containerFolders.masonry({
					itemSelector : '.item-folder',
					gutterWidth: gutterWidthFolders,
					isAnimated: true,
					columnWidth: function(containerWidth) {
						var itemNbr = (containerWidth / minWidthFolders | 0);

						var itemWidth = (((containerWidth - (itemNbr - 1) * gutterWidthFolders) / itemNbr) | 0);

						if (containerWidth < minWidthFolders)
							itemWidth = containerWidth;

						jQuery(".item-folder").width(itemWidth);

						displayFolders();

						return itemWidth;
					}
				});
			});
		<?php endif; ?>
		/*************/
	}

	function displayFoldersBackground()
	{
		var backgroundLeft = jQuery(".folders").find(".container").offset().left + jQuery(".folders").find(".container").width() + 30;
		var backgroundTop = 30;

		jQuery(".folders").css("background-position", backgroundLeft + "px " + backgroundTop + "px");
	}

	function displayFilesBackground()
	{
		var backgroundLeft = jQuery(".files").find(".container").offset().left + jQuery(".files").find(".container").width() + 30;
		var backgroundTop = 30;

		jQuery(".files").css("background-position", backgroundLeft + "px " + backgroundTop + "px");
	}

	function displayFolders()
	{
		jQuery(".item-folder").each(function() {
			var width = jQuery(this).width();
			var infoHeight = jQuery(this).find(".info").get(0).offsetHeight + 1;
			var imgHeight = jQuery(this).find("img").height();
			var availableHeight = width - infoHeight;

			jQuery(this).find("img").css("padding-top", ((availableHeight - imgHeight) / 2) + "px");
			jQuery(this).find("img").css("padding-bottom", ((availableHeight - imgHeight) / 2) + "px");
		});
	}
</script>