(function(module) {
	"use strict";

	function Quotation () {
	}

	Quotation.STATE_WAIT = 1;
	Quotation.STATE_VALIDATE = 2;
	Quotation.STATE_DELETE = 3;
	Quotation.STATE_CANCEL = 4;
	Quotation.STATE_REFUSAL_BANK = 5;

	Quotation.TYPE_NEW = 1;
	Quotation.TYPE_UPGRADE = 2;
	Quotation.TYPE_RENEW = 3;
	Quotation.TYPE_RENEW_UPGRADE = 4;
	Quotation.TYPE_OTHER = 5;

	module.Quotation = Quotation;

})(window.model = (window.model || {}));