<div id="uploadify-page">
	<div class="header-page">
		<h2>
			<?php echo __("Import files into \"%folder%\"", array("%folder%" => $folder->getName())); ?>
		</h2>

		<form class="form-horizontal">
			<div class="control-group">
				<label class="control-label" for="albums"><?php echo __("Select group"); ?></label>
				<select name="albums" id="albums">
					<?php foreach($albums as $album) : ?>
						<option value="<?php echo $album->getId(); ?>" <?php echo $folder->getGroupeId() == $album->getId() ? "selected" : ""; ?>>
							<?php echo $album->getName(); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="control-group">
				<label class="control-label" for="folders"><?php echo __("Select folder"); ?></label>
				<select name="folders" id="folders">
					<option value=""><?php echo __("Select"); ?></option>
					<?php foreach ($folders as $folderId => $folderLabel) : ?>
						<option value="<?php echo $folderId; ?>" <?php echo $folder->getId() == $folderId ? "selected" : ""; ?>>
							<?php echo $folderLabel; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</form>
		<div id="contols-buttons">
			<span class="btn btn-primary btn-select-files">
				<i class="icon-plus"></i> <?php echo __("Add files"); ?>
				<input type="file" multiple="multiple" id="select-files" />
			</span>
		
			<button class="btn btn-primary" disabled="disabled" id="upload-button">
				<i class="icon-upload-alt"></i> <?php echo __("Start upload"); ?>
			</button>
		</div>
	</div>

	<div id="drop-files" ondragover="return false">
		<div class="drop-text">
			<div>
				<?php echo __("Drop your files here"); ?>
			</div>
		</div>

		<div class="clearfix"></div>

		<div id="content-upload" data-folder-id="<?php echo $folder->getId(); ?>"
				data-key-id="<?php echo $key->getId(); ?>">
			<ul class="inline"></ul>
		</div>
	</div>
</div>