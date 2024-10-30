jQuery(document).ready(function () {
	jQuery(".plugin-title .row-actions > span").each(function () {
		var _this = jQuery(this);

		_this.addClass("more").attr("title", _this.text().replace("|", ""));
	});
	
	jQuery(".wp-list-table .row-actions > span > a").each(function () {
		_this = jQuery(this);
		
		// First we check if element has aria-label 
		if(typeof _this.attr("aria-label") != "undefined")
		{
			jQuery(this).addClass("more").attr("title", "");
			jQuery(this).parent().attr("title", "");
			return;
		}
		
		if(typeof _this.parent().attr("title") != "undefined")
			_this.attr("aria-label", _this.parent().attr("title"));
	
		
		console.log(_this.attr("title"));
		// Here we chack if the elements has title attr
		if(typeof _this.attr("title") != "undefined")
			_this.attr("aria-label", _this.attr("title"));
		// then we check if parent has attr
		else if(typeof _this.parent().attr("title") != "undefined")
			_this.attr("aria-label", _this.parent().attr("title"));
		
		jQuery(this).addClass("more").attr("title", "");
		jQuery(this).parent().attr("title", "");
		
	});

	// Toggle sidebar submenu
	jQuery(".wp-not-current-submenu.wp-has-submenu > a").click(function () {
		_this = jQuery(this);
		var indedx = _this.index();

		jQuery("#adminmenuwrap .wp-has-submenu:not(.wp-has-current-submenu)").not(this).siblings(".wp-submenu").slideUp();

		jQuery(this).siblings(".wp-submenu").each(function (i, e) {
			if (jQuery(e).css("display") == "none")
				jQuery(e).css("height", "auto").stop(true, true).slideDown();
			else
				jQuery(e).stop(true, true).slideUp();
		});

		// Disable links
		return false;
	});

	// Handle sidebar show in desktop mode
	jQuery("#wp-admin-bar-menu-toggle, .toggle-sidemenu").click(function () {
		jQuery("#collapse-button").trigger("click");
		jQuery("#adminmenuwrap").on("transitionend", function () {
			if (jQuery("body").hasClass("folded"))
				jQuery(this).css("width", "249px");
			else
				jQuery(this).css("width", "250px");
		});

	});

	var media_uploader = null;
	jQuery(".kalimah_admin_upload_actions .kalimah_admin_delete_action").click(function () {
		jQuery(this).parent().siblings(".kalimah_admin_image_wrapper").children("img").attr("src", "");
		jQuery(this).parent().siblings(".image_url").val("");
	});

	jQuery(".kalimah_admin_upload_actions .kalimah_admin_upload_action").click(function () {
		var parent = jQuery(this).parent();

		media_uploader = wp.media({
				frame : "post",
				state : "insert",
				multiple : false,
				library : {
					type : "image"
				}
			});

		media_uploader.on("insert", function () {
			var json = media_uploader.state().get("selection").first().toJSON();

			var image_url = json.url;
			var image_caption = json.caption;
			var image_title = json.title;

			parent.siblings(".image_url").val(image_url);
			parent.siblings(".kalimah_admin_image_wrapper").children("img").attr("src", image_url);
		});

		media_uploader.open();
	});

	// On load hide theme colors
	var theme = jQuery(".kalimah_admin_theme").val();
	if (theme == 'flat')
		jQuery("#kalimah-admin-settings table tr:nth-child(3)").hide();
	else
		jQuery("#kalimah-admin-settings table tr:nth-child(2)").hide();

	jQuery(".kalimah_admin_theme").change(function () {
		var theme = jQuery(".kalimah_admin_theme").val();
		if (theme == 'flat') {
			jQuery("#kalimah-admin-settings table tr:nth-child(3)").hide();
			jQuery("#kalimah-admin-settings table tr:nth-child(2)").show();
		} else {
			jQuery("#kalimah-admin-settings table tr:nth-child(2)").hide();
			jQuery("#kalimah-admin-settings table tr:nth-child(3)").show();
		}
	});
});
