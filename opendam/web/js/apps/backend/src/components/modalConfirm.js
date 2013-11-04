(function() {
	"use strict";

	/**
	 * Plugin jquery pour gérer des popups de confirmation avec le style bootstrap
	 * 
	 *  Exemple :
		$("[data-action=delete]").ConfirmModal({
			title: __("Confirmation de la supression"),
			message: __("Are you sure want to delete this user?"),
			confirmBtn: {
				'class': 'btn btn-danger',
				'text': '<i class="icon-trash"></i> '+__("Delete")
			}
		});
		
		Events:
		show: show the modal
		hide: hide the modal
		destroy: destroy the modal (hide + unbind event)
		
	 * 
	 * 	html5 data api
	 *	<a data-toogle="modal-confirm" data-title="Confirmation" data-message="Are you sure want to delete this user?" class="btn" href="/toto">
	 *	  <i class="icon-trash"></i> Remove
	 *  </a>
	 */
	var ConfirmModal = function (element, options) {
		var defaultOptions = {
			cancelBtn: {
				'class': 'btn',
				'text': __("Cancel")
			},
			confirmBtn: {
				'class': 'btn btn-primary',
				'text': __("OK")
			}
		};

		this.options = $.extend({}, defaultOptions, options);
		this.$element = $(element);
		this.$modal = null;

		this._create();
	}
	
	ConfirmModal.prototype = {
		constructor: ConfirmModal,

		// private methods
		//========================
		_events: {
			click: function(event) {
				this._createModal();
				this.$modal.modal("show");
				
				event.preventDefault();
				event.stopPropagation();
			}
		},
		_bindEvents: function() {
			this.$element.on("click", $.proxy(this._events.click, this));
		},
		_unbindEvents: function() {
			this.$element.off("click");
		},
		_create: function() {
			var self = this;
			
			if (!this.$element.length) {
				throw new Error("Element is null");
			}
			
			// check required options
			if (!this.options.message) {
				throw new Error("missing message parameter");
			}

			this._bindEvents();
		},
		_renderHtml: function() {
			
			var message = this.options.message;
			var title = this.options.title;
			var confirmBtn = this.options.confirmBtn;
			var cancelBtn = this.options.cancelBtn;

			var html = '' +
			'<div class="modal hide" tabindex="-1" role="dialog">';
						
			if (title) {
				html += '' +
				'	<div class="modal-header">' +
				'		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
				'		<h4>' + title + '</h4>' +
				'	</div>';
			}
			
			html += '' +
			'	<div class="modal-body">' +
					message +
			'	</div>' +
			'	<div class="modal-footer">' +
			'		<button class="' + cancelBtn["class"] + '" data-action="cancel" aria-hidden="true">' + cancelBtn["text"] + '</button>' +
			'		<button class="' + confirmBtn["class"] + '" data-action="confirm">' + confirmBtn["text"] + '</button>' +
			'	</div>' +
			'</div>';

			return html;
		},
		_createModal: function() {
			if (this.$modal) {
				return;
			}

			var self = this;
			var message = self.options.message;
			self.$modal = $(self._renderHtml());

			// cancel
			self.$modal.find(".modal-footer button[data-action=cancel]").on("click", function() {
				var event = jQuery.Event("cancel");
				
				self.$element.trigger(event);
		
				if (event.isDefaultPrevented()) {
					return;
				}

				self.$modal.modal("hide");
			});

			// confirm
			self.$modal.find(".modal-footer button[data-action=confirm]").on("click", function() {
				var href = self.$element.attr("href");
				var event = jQuery.Event("confirm");
			
				self.$element.trigger(event);
		
				if (event.isDefaultPrevented()) {
					return;
				}
				
				// on détache évènement click de la modal pour déclencher l'évènement original
				self._unbindEvents();
				self.$element.trigger("click");
				self._bindEvents();
		
				self.$modal.modal("hide");
				
				// il n'existe pas d'event pour déclencher une redirection d'un lien (<a></a>)
				if (href) {
					document.location.href = href;
				}
			});
		},
		// public methods
		//========================
		show: function() {
			this._createModal();
			this.$modal.modal("show");
		},
		hide: function() {
			this.$modal.modal("hide");
		},
		destroy: function() {
			this.hide();
			this._unbindEvents();
		}
	};
	
	//jquery plugins
	$.fn.ConfirmModal = function (options) {
		return this.each(function () {
			var $this = $(this);
			var instance = $this.data('confirmModal');

			if (!instance) {
				instance = new ConfirmModal(this, options);
				$this.data('confirmModal', instance);
			}
			
			// call a public method ex: $(el).plugin("fonction")
			if (typeof options == 'string') {
				if (typeof instance[options] !== 'function') {
					$.error('Function "' + options + '" doesn\'t exists.');
					return;
				}
				
				if (options.substr(0, 1) == '_') {
					$.error('Method "' + options + '" is private.');
					return;
				}
	
				// call function
				return instance[options].apply(instance, Array.prototype.slice.call(arguments, 1));
			}
		});
	};

	$.fn.ConfirmModal.Constructor = ConfirmModal;

	$(document).ready(function() {
		// html 5 data api
		$('[data-toogle=modal-confirm]').on('click', function (event) {
			var $this = $(this);
			
			var title = $this.attr("data-title");
			var message = $this.attr("data-message");
			var href = $this.attr("href");

			$this.ConfirmModal({
				title: title,
				message: message,
			});

			$this.ConfirmModal("show");

			return false;
		});
	});
})();
