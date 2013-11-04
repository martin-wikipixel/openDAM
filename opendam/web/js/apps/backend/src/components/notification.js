(function($, module) {
	"use strict";
	
	// http://nijikokun.github.io/bootstrap-notify/
	function Notification($container) {
		if (!$container.length) {
			throw new Error("Container is null");
		}

		this.$container = $container;
	}

	Notification.constructor = Notification;

	Notification.prototype.clear = function() {
		this.$container.empty();
	};
	
	Notification.prototype.close = function() {
		this.clear();
	};
	
	Notification.prototype.loading = function(message) {
		this.clear();
		
		if (!message) {
			message = __("Loading...");
		}
		
		this.$container.notify({
			type: "info", 
			fadeOut: { enabled: false },
			message: { html: message}
		}).show();
	};

	Notification.prototype.info = function(message) {
		this.clear();
		
		var notification = this.$container.notify({
			type: "info", 
			fadeOut: { delay: 2500 },
			message: { html: message }
		});
		
		notification.show();
	};

	Notification.prototype.success = function(message) {
		this.clear();
		
		this.$container.notify({
			type: "success", 
			fadeOut: { delay: 2500 },
			message: { html: message }
		}).show();
	};

	Notification.prototype.warning = function(message) {
		this.clear();
		
		this.$container.notify({
			type: "warning", 
			fadeOut: { delay: 4000 },
			message: { html: message }
		}).show();
	};

	Notification.prototype.error = function(message) {
		this.clear();
		
		if (!message) {
			message = __("An error has occurred.")
		} 
		
		this.$container.notify({
			type: "error", 
			fadeOut: { delay: 4000 },
			message: { html: message }
		}).show();
	};

	module.Notification = Notification;
})(jQuery, window);