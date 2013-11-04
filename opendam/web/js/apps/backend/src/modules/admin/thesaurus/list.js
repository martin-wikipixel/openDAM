(function($) {
	"use strict";

	$(document).ready(function() {
		var $root = $("#admin-thesaurus-list-page");
		
		if (!$root.length) {
			return;
		}

		var notification = window.services.notification;
		
		var $culture = $root.find("#culture");
		var $tree = $root.find("#tag-tree");
		var $addTagForm = $root.find("form[name=add-tag-form]");
		var $inputName = $addTagForm.find("input[name=name]");
		var $suggeredTags = $root.find("#suggered-tags");
		
		$root.find("select[name=culture]").on("change", function() {
			$(this).closest("form").submit();
		});

		$tree.jstree({ 
			"plugins" : [ "themes","json_data","dnd","crrm","ui" ],
			"themes" : {
				"theme" : "classic",
				"url": "/css/jquery.jstree.css",
				"icons": false
			},
			"json_data" : { 
				"ajax" : {
					"url" : Routing.generate("admin_thesaurus_tree"),
					"data" : function (n) { 
						return { 
							"culture" : $culture.val(),
							"id" : n.attr ? n.attr("id").replace("node_","") : "" 
						}; 
					}
				}
			}
		})
		.on("select_node.jstree", function (event, data) {
			$tree.jstree("toggle_node", "#" + data.rslt.obj.attr("id"));
		})
		.on("create.jstree", function (e, data) {
			$.ajax(Routing.generate("admin_thesaurus_add"), {
				type: "POST",
				async : false,
				data: { "culture": $culture.val(),"title" : data.rslt.name }, 
			})
			.done(function(r) {
				$inputName.val("");
				$(data.rslt.obj).attr("id", "node_" + r.id);
			})
			.fail(function() {
				notification.error();
				$.jstree.rollback(data.rlbk);
			});
		})
		.on("remove.jstree", function (e, data) {
			data.rslt.obj.each(function () {
				notification.loading();
				
				$.ajax(Routing.generate("admin_thesaurus_delete"), {
					type: "POST",
					async : false,
					data : { "id" : this.id.replace("node_","") }
				})
				.done(function(r) {
					notification.success(__("The tag has been deleted."));
					data.inst.refresh();
				})
				.fail(function() {
					notification.error();
				});
			});
		})
		.on("rename.jstree", function (e, data) {
			var id = data.rslt.obj.attr("id").replace("node_","");

			notification.loading();
			
			$.ajax(Routing.generate("admin_thesaurus_update", {"id" : id}), {
				type: "POST",
				async : false,
				data : { 
					"field" : "title", 
					"value" : data.rslt.new_name
				}
			})
			.done(function() {
				notification.success(__("The tag has been updated."));
			})
			.fail(function(xhr) {
				notification.error();
				$.jstree.rollback(data.rlbk);
			});
		})
		.on("move_node.jstree", function (e, data) {
			data.rslt.o.each(function (i) {
				notification.loading();

				$.ajax(Routing.generate("admin_thesaurus_move"), {
					async : false,
					type: "POST",
					data : { 
						"from" : $(this).attr("id").replace("node_",""), 
						"to" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_","")
					}
				})
				.done(function(r) {
					$(data.rslt.oc).attr("id", "node_" + r.id);
					
					if (data.rslt.cy && $(data.rslt.oc).children("UL").length) {
						data.inst.refresh(data.inst._get_parent(data.rslt.oc));
					}
					
					notification.success(__("The tag has been updated."));
				})
				.fail(function() {
					notification.error();
					$.jstree.rollback(data.rlbk);
				});
			});
		});

		/**
		 * Events pour les icônes
		 */
		$tree.on("mouseenter", "a", function() {
			var $this = $(this);
			
			if (!$this.parent().find("a.edit").length) {
				$tree.find("a.edit").remove();
				$tree.find("a.remove").remove();
				
				$this.append("<a href='javascript: void(0);' class='edit'></a><a href='javascript: void(0);' class='remove'></a>");
			}
		});

		$tree.on("click", "a.edit", function() {
			var $li = $(this).closest("li");
			
			$tree.jstree("rename", "#" + $li.attr("id"));
		});

		$tree.on("click", "a.remove", function() {
			var $li = $(this).closest("li");
			
			if (confirm(__("Are you sur to want to delete this tag / this categories?"))) {
				$tree.jstree("remove", "#" + $li.attr("id"));
			}
		});
		
		/*____________________________________________________________________________________________________________*/
		/**
		 * Formulaire add tag
		 */
		$inputName.autocomplete({
			source: Routing.generate("tag_fetchLexicon"),
			minLength: 2,
			change: function(event, ui) {
				if (ui.item) {
					$inputName.val(ui.item.id);
				}
			},
			select: function(event, ui) { 
				if (ui.item) {
					$inputName.val(ui.item.id);
				}
			}
		});
		
		$addTagForm.on("submit", function(event) {
			// prevent submit
			event.preventDefault();
			
			var name = $.trim($inputName.val());

			if (!name) {
				notification.error(__("A name is required."));
				return;
			}
			
			$tree.jstree("create", -1, "last", name, false, true); 
		});
		
		/*____________________________________________________________________________________________________________*/
		/**
		 * Tag suggéré
		 */
		$suggeredTags.on("click", "> button", function() {
			var $this = $(this);
			var title = $this.text();

			$tree.jstree("create", -1, "last", title, false, true);

			$this.fadeOut();
		});

		/*____________________________________________________________________________________________________________*/
		$root.find("a[data-action=refresh-suggered-tags]").on("click", function(){
			notification.loading();
			
			$.ajax(Routing.generate("admin_thesaurus_random_tags"), {})
				.done(function(data) {
					$suggeredTags.html(data);
					notification.clear();
				})
				.fail(function(){
					notification.error();
				});
		});
		
		$root.find(".icon-question-sign").tooltip();
	});
})(jQuery);
