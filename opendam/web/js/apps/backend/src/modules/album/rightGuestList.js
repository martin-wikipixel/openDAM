(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#album-right-guest-list-page");
		var $permissionsTable = $root.find("#permissions-table");
		var notification = services.notification;

		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this user?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});

		$permissionsTable.find("tbody [data-action=notify-user]").on("click", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var guestId = $this.attr("data-guest-id");

			notification.loading();

			$.ajax(Routing.generate("album_right_guest_notify", {"album": albumId, "guest": guestId}))
			.done(function(response){
				if (response) {
					var parsedResponse = JSON.parse(response);

					if (parsedResponse.state) {
						$this.closest("tr").find("td.state").html(parsedResponse.state);
					}
				}

				notification.success(__("Invitation returned successfully."));
			})
			.fail(function() {
				notification.error();
			});
		});

		$permissionsTable.find("tbody input[type=radio]").on("change", function() {
			var $this = $(this);
			var albumId = $this.attr("data-album-id");
			var guestId = $this.attr("data-guest-id");
			var roleId = $this.val();

			notification.loading();

			$.ajax(Routing.generate("album_right_guest_update", {"album": albumId, "guest": guestId}), {
				data: {"role": roleId}
			})
			.done(function(){
				notification.success(__("The album's rights were been updated."));
			})
			.fail(function() {
				notification.error();
			});
		});

		$permissionsTable.find("tbody .edit-expiration").on("click", function() {
			var editExpiration = $("<div></div>");
			var self = $(this);
			var buttonsOpts = {};

			$("body").append(editExpiration);

			buttonsOpts[__("Save")] = function() {
				var dialogBox = $(this);
				var error = false;
				var type, expiration = "";

				if ($("#expiration_unlimited").prop("checked") == false && $("#expiration_limited").prop("checked") == false) {
					error = true;
					$("#error_invite").append(__("Expiration of access right is required.") + "<br />");
				}
				else if (!error) {
					$("#error_invite").html("");
				}

				if ($("#expiration_limited").prop("checked") ==  true && $("#expiration_date").val() == "") {
					error = true;
					$("#error_invite").append(__("Select end access date."));
				}
				else if (!error) {
					$("#error_invite").html("");
				}

				if (!error) {
					if($("#expiration_unlimited").prop("checked") == true) {
						type = "unlimited";
						expiration = "";
					}
					else {
						type = "limited";
						expiration = $("#expiration_date").val();
					}

					$.ajax(Routing.generate("group_right_user_expiration_update", {"id": self.attr("data-id") }), {
						data: {"type": type, "expiration": expiration}
					})
					.done(function(){
						dialogBox.dialog("close");
						window.location.reload();
					})
					.fail(function() {
						notification.error();
					});
				}
			};

			editExpiration.dialog({
				title: __("Edit expiration"),
				modal: true,
				resizable: false,
				draggable: false,
				show: 'fade',
				hide: 'fade',
				width: 550,
				height: 350,
				buttons: buttonsOpts,
				open: function(event, ui) {
					var dialogBox = $(this);

					dialogBox.html("");

					$.ajax(Routing.generate("group_right_user_expiration_list", {"id": self.attr("data-id") }))
					.done(function(data){
						dialogBox.fadeOut(400, function() {
							dialogBox.html(data).show();
							dialogBox.find(".dialog").fadeIn();
						});
					})
					.fail(function() {
						notification.error();
					});
				},
				close: function(event, ui) {
					editExpiration.remove();
				}
			});
		});
	});
})(jQuery);