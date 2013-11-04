(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#uploadify-page");
		var $albums = $root.find("#albums");
		var $folders = $root.find("#folders");
		var $upload = $root.find("#content-upload");
		var $dropZone = $root.find("#drop-files");
		var $selectFiles = $root.find("#select-files");
		var $uplaodList = $upload.find("ul");
		var $btnUpload = $root.find("#upload-button");
		var folderId = $upload.attr("data-folder-id");
		var keyId = $upload.attr("data-key-id");
		var notification = services.notification;

		var dataArray = [];
		var uploadArray = [];
		var countFiles = 1;
		const BYTES_PER_CHUNK = 1024 * 1024;
		var slices;
		var slices2;

		$btnUpload.on("click", startUpload);

		$selectFiles.on("change", function() {
			var files = $(this).prop("files");

			addFiles(files);
		});

		$.event.props.push("dataTransfer");

		$dropZone.on("drop", function(e) {
			var files = e.dataTransfer.files;

			addFiles(files);
		});

		$(document).on("dragenter", function() {
			$dropZone.addClass("dragged");
			return false;
		});

		$(document).on("drop", function() {
			$dropZone.removeClass("dragged");
			return false;
		});

		$albums.on("change", function() {
			var $this = $(this);
			var albumId = $this.val();

			$.ajax(Routing.generate("upload_get_folders", {"album": albumId}))
			.done(function(data) {
				var foldersData = data;

				$folders.text("");

				$folders.append("<option value='' selected>" + __("Select") + "</option>");

				for (var count = 0; count < foldersData.length; count++) {
					$folders.append("<option value='" + foldersData[count].id + "'>" + foldersData[count].label + "</option>");
				}
			})
			.fail(function() {
				notification.error();
			});
		});

		$folders.on("change", function() {
			var $this = $(this);
			var folderId = $this.val();

			if (folderId) {
				window.location.href = Routing.generate("upload_show", {"folder_id": folderId});
			}
		});

		function disableUpload()
		{
			$selectFiles.closest(".btn").addClass("disabled");

			$selectFiles.on("click", function() {
				return false;
			});

			$selectFiles.off("change");

			$btnUpload.off("click");
		}

		function startUpload()
		{
			var $this = $(this);

			if (dataArray.length > 0) {
				disableUpload();

				$this.html(__("Files transfer") + " (<span id='current-file'>0</span> / " + dataArray.length + ")...");
				$upload.find("li button").replaceWith("<i class='icon-large icon-time' title='0%'></i>");

				sendData(dataArray[0]);
			}
		}

		function addFiles(files)
		{
			$.each(files, function(index, file) {
				if (!files[index].type.match('image.*')) {
					var image = "/images/no-access-file-200x200.png";

					displayFile(files[index], image);
				}
				else {
					var fileReader = new FileReader();
					fileReader.onload = (function(file) {
						return function(e) { 
							var image = this.result;
	
							displayFile(files[index], image);
						}; 
						
					})(files[index]);
	
					fileReader.readAsDataURL(file);
				}
			});
		}

		function displayFile(file, image)
		{
			dataArray.push({id: countFiles, name: file.name, value: file});

			var $newFile = $("<li id='" + countFiles + "'></li>");

			$newFile.append("<div class='img'><img src='" + image + "' /></div>");
			$newFile.append("<div class='info'><div class='title'>" + file.name + "</div><button class='btn btn-danger' data-id='" + countFiles + "'><i class='icon-trash'></i></button></div>");

			$newFile.find("button").on("click", function() {
				var $this = $(this);
				var $element = $this.closest("li");
				var id = $this.attr("data-id");

				var result = $.grep(dataArray, function(e) {
					return e.id == id;
				});

				if (result.length == 1) {
					dataArray.splice($.inArray(result[0], dataArray), 1);
				}

				if (dataArray.length == 0) {
					$btnUpload.prop("disabled", true);
				}

				$element.remove();
			});

			$uplaodList.append($newFile);

			var $img = $newFile.find("img");
			var $imgContainer = $newFile.find(".img");

			$img.on("load", function() {
				if ($img.height() < $imgContainer.height()) {
					var paddingTop = ($imgContainer.height() - $img.height()) / 2;

					$img.css("padding-top", paddingTop + "px");
				}
			});
			

			if (dataArray.length > 0 && $btnUpload.prop("disabled")) {
				$btnUpload.prop("disabled", false);
			}

			countFiles++;
		}

		function sendData(object)
		{
			var file = object.value;
			var id = object.id;
			var start =0, end = 0, index = 0;

			var currentFile = parseInt($btnUpload.find("#current-file").text(), 10);

			$btnUpload.find("#current-file").text(currentFile + 1);

			$upload.find("#" + id + " .icon-time").removeClass("icon-time").addClass("icon-spinner icon-spin");

			$.ajax(Routing.generate("upload_file_identify"), {
				data: {"keyId": keyId}
			})
			.done(function(data) {
				var name = data;

				slices = Math.ceil(file.size / BYTES_PER_CHUNK);
				slices2 = slices;

				while (start < file.size) {
					end = start + BYTES_PER_CHUNK;

					if (end > file.size) {
						end = file.size;
					}

					uploadArray.push({name: name, html: $upload.find("#" + id + " .icon-spinner"), file: file, index: index, start: start, end: end});

					start = end;
					index++;
				}

				if (uploadArray.length > 0) {
					uploadFile(uploadArray[0]);
				}
			})
			.fail(function() {
				notification.error();
			});
		}

		function uploadFile(object)
		{
			var chunk, fd;

			var file = object.file;
			var index = object.index;
			var start = object.start;
			var end = object.end;
			var html = object.html;
			var name = object.name;

			if (file.webkitSlice) {
				chunk = file.webkitSlice(start, end);
			}
			else if (file.mozSlice) {
				chunk = file.mozSlice(start, end);
			}

			fd = new FormData();
			fd.append("file", chunk);
			fd.append("name", name);
			fd.append("index", index);
			fd.append("keyId", keyId);

			$.ajax({
				type: "POST",
				url: Routing.generate("upload_file"),
				data: fd,
				processData: false,
				contentType: false
			})
			.done(function(data) {
				slices--;

				var count = slices2 - slices;
				var percent = Math.round((count / slices2) * 100 * 100) / 100;

				html.attr("title", percent + "%");

				if (slices == 0) {
					mergeFile(html, file, name);
				}

				uploadArray.splice(0, 1);

				if (uploadArray.length > 0) {
					uploadFile(uploadArray[0]);
				}
			})
			.fail(function() {
				notification.error();
			});
		}

		function mergeFile(html, file, name)
		{
			$.ajax({
				type: "POST",
				url: Routing.generate("upload_file_merge"), 
				data: {"name": name, "originalName": file.name, "index": slices2, "keyId": keyId, "folderId": folderId}
			})
			.done(function() {
				html.removeClass("icon-spinner icon-spin").addClass("icon-ok-sign");

				dataArray.splice(0, 1);

				if (dataArray.length > 0) {
					sendData(dataArray[0]);
				}
				else {
					$btnUpload.html("<i class='icon-arrow-right'></i> " + __("Next step"));
					$btnUpload.on("click", function() {
						$(".modal-footer [data-action=close]", window.parent.document).trigger("click");
						window.parent.lauchFaceboxIframe(Routing.generate("upload_file_edit", {"folder": folderId}));
					});
				}
			})
			.fail(function() {
				notification.error();
			});
		}
	});
})(jQuery);