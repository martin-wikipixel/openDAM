(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-group-user-list-page");
		var groupId = $root.attr("data-group-id")
		var $inputAutocomplete = $root.find("#user-add-form input[name=user]");
		var notification = window.services.notification;
		
		$inputAutocomplete.attr("autocomplete", "off");
		
		$root.find("[data-action=delete]").ConfirmModal({
			title: __("Delete confirmation"),
			message: __("Are you sure want to delete this user?"),
			confirmBtn: {
				"class": "btn btn-danger",
				"text": '<i class="icon-trash"></i> '+__("Delete")
			}
		});
		
		/**
		 * @see https://gist.github.com/tacone/4534615
		 */
		function serializeForBootstrap(user) {
			
			var text = "";
			
			if (user.firstname || user.lastname) {
				text += user.firstname + " " + user.lastname + " - ";
			}
			
			text += user.email;
	
			return {
				// attributes
				id: user.id,
				firstname: user.firstname,
				lastname: user.lastname,
				email: user.email,
				
				text: text,

				// these functions allows Bootstrap typehead to use this item in places where it was expecting a string
				toString: function() {
					return JSON.stringify(this);
				},
				toLowerCase: function() {
					return this.text.toLowerCase();
				},
				indexOf: function(string) {
					return String.prototype.indexOf.apply(this.text, arguments);
				},
				replace: function(string) {
					return String.prototype.replace.apply(this.text, arguments);
				}
			};
		}

		$inputAutocomplete.typeahead({
			minLength: 3,
			// le trie est faite par api
			sorter: function(items) {
				return items;
			},
			// tous les résultats renvoyés par api match la requête
			matcher: function() {
				return true;
			},
			source: function(query, process) {
				$.ajax(Routing.generate("admin_group_user_autocomplete", {"group" : groupId}), {
					data: {keyword: query} 
				})
				.done(function(items) {
					var data = [];
					
					for (var i = 0; i < items.length; i++) {
						data.push(serializeForBootstrap(items[i]));
					}

					process(data);
				});
			},
			updater: function(item) {
				var item = JSON.parse(item);
				
				$inputAutocomplete.val(item.id);
				notification.loading();

				$inputAutocomplete.closest("form").submit();
				
				return item.email;
			}
		});
	});
})(jQuery);