(function() {
	"use strict";

	var IframeModal = function (element, options) {
		var defaultOptions = {
				closeBtn: {
				'class': 'btn',
				'text': __("Close")
			}
		};

		this.options = $.extend({}, defaultOptions, options);
		this.$element = $(element);
		this.$modal = null;

		this._create();
	}
	
	IframeModal.prototype = {
		constructor: IframeModal,

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

			this._bindEvents();
		},
		_renderHtml: function() {
			var title = this.options.title;
			var closeBtn = this.options.closeBtn;

			var html = '' +
			'<div class="modal large hide fade" tabindex="-1" role="dialog">';
						
			if (title) {
				html += '' +
				'	<div class="modal-header">' +
				'		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
				'		<h4>' + title + '</h4>' +
				'	</div>';
			}
			
			html += '' +
			'	<div class="modal-body">' +
					'<div class="loading-iframe text-center"><i class="icon-spinner icon-spin icon-large"></i> ' + __("Loading...") + '</div>' +
					'<iframe frameborder="0" src="' + this.$element.attr("href") + '"></iframe>' +
			'	</div>' +
			'	<div class="modal-footer">' +
			'		<button class="' + closeBtn["class"] + '" data-action="close" aria-hidden="true">' + closeBtn["text"] + '</button>' +
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
			self.$modal.find(".modal-footer button[data-action=close]").on("click", function() {
				var event = jQuery.Event("close");
				
				self.$element.trigger(event);
		
				if (event.isDefaultPrevented()) {
					return;
				}

				self.$modal.modal("hide");
			});

			self.$modal.find("iframe").load(function() {
				var $iframe = $(this);

				self.$modal.find(".loading-iframe").fadeOut(400, function() {
					$iframe.fadeIn(400);
				});
			});
		},
		// public methods
		//========================
		show: function() {
			this._createModal();
			this.$modal.modal("show");
		},
		hide: function() {
			if (this.$modal) {
				this.$modal.modal("hide");
			}
		},
		destroy: function() {
			this.hide();
			this._unbindEvents();
		}
	};
	
	//jquery plugins
	$.fn.IframeModal = function (options) {
		return this.each(function () {
			var $this = $(this);
			var instance = $this.data('iframeModal');

			if (!instance) {
				instance = new IframeModal(this, options);
				$this.data('iframeModal', instance);
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

	$.fn.IframeModal.Constructor = IframeModal;

	$(document).ready(function() {
		// html 5 data api
		$('[data-toogle=modal-iframe]').on('click', function (event) {
			var $this = $(this);
			var title = $this.attr("data-title");

			$this.IframeModal({
				title: title
			});

			$this.IframeModal("show");

			return false;
		});
	});
})();
