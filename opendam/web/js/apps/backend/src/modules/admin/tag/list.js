(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-tag-list-page");
		var $table = $root.find("#tag-list");
		var $deleteAllBtn = $root.find("button[data-action=delete-all]");
		var notification = window.services.notification;
		var selectedTagsId = [];
		var $replaceBtns = $root.find("button[data-action=replace]");
		
		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this tag?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});
		
		/**
		 * Pour faciliter le cochage, on coche la case au clique sur la colonne.
		 */
		$table.find("tbody td:first-child").on("click", function() {
			$(this).find("input").trigger("click");
		});
		
		$table.find("tbody td:first-child input").on("click", function(event) {
			event.stopPropagation();
		})
		.on("change", function() {
			var $this = $(this);
			var tagId = $this.val();
			
			if ($this.is(":checked")) {// add to selectedTagsId
				selectedTagsId.push(tagId);
			}
			else {// remove to selectedTagsId
				selectedTagsId = _.without(selectedTagsId, tagId);
			}
			
			if (selectedTagsId.length) {
				$deleteAllBtn.show();
				$replaceBtns.show();
			}
			else {
				$deleteAllBtn.hide();
				$replaceBtns.hide();
			}
		});

		$deleteAllBtn.on("click", function() {
			if (confirm(__("Are you sure want to replace those tags ?"))) {
				notification.loading();
				
				$.ajax(Routing.generate("admin_tag_all_delete"), {
					data: {
						ids: selectedTagsId
					}
				})
				.done(function() {
					document.location.reload();
				})
				.fail(function() {
					notification.error();
				});
			}
		});
		
		$replaceBtns.on("click", function() {
			var $this = $(this);
			var tagId = $this.attr("data-tag-id");
			var params = {"csrfToken": $this.attr("data-csrf-token"), "tag": tagId, "oldTag": selectedTagsId};
			
			var url = Routing.generate("admin_tag_replace_by", params);
			
			if (confirm(__("Are you sure want to replace those tags to") + ' "'+$this.attr("data-name")+'"'))
			document.location.href=url;
		});
	});
})(jQuery);