jQuery(document).ready(function () {
	// Login page - Move input text to placeholder
	jQuery("#user_login, #user_pass").each(function () {
		var _this = jQuery(this);

		_this.attr("placeholder", _this.parent().text());

	});

});
