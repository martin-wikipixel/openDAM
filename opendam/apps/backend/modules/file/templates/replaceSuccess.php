<div id="file-replace-page">
	<div class="header-page">
		<h2>
			<?php echo __("Replace file"); ?>
		</h2>

		<div class="alert alert-info">
			<?php echo __("Please select the replacement file. It will be assigned all the available information (EXIF, IPTC, copyright, etc.)."); ?>
		</div>

		<div id="contols-buttons">
			<span class="btn btn-primary btn-select-files">
				<i class="icon-plus"></i> <?php echo __("Select file"); ?>
				<input type="file" id="select-files" />
			</span>
		
			<button class="btn btn-primary" disabled="disabled" id="upload-button">
				<i class="icon-upload-alt"></i> <?php echo __("Start upload"); ?>
			</button>
		</div>
	</div>

	<div id="drop-files" ondragover="return false">
		<div class="drop-text">
			<div>
				<?php echo __("Drop your file here"); ?>
			</div>
		</div>

		<div class="clearfix"></div>

		<div id="content-upload" data-key-id="<?php echo $key->getId(); ?>"
				data-file-id="<?php echo $file->getId(); ?>">
			<ul class="inline"></ul>
		</div>
	</div>
</div>