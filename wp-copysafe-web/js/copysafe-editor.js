var WPCSW_EDITOR = null;
(function($) {

	WPCSW_EDITOR = {

		sendEditorTimeout: null,

		init: function() {
			let that = this;

			$('#wpcsw_link').on('click', function(e) {
				that.showPopup();
			});

			$(document).on("click", ".wpcsw-sendtoeditor" , function(e) {
				e.preventDefault();

				if(that.sendEditorTimeout) {
					clearTimeout(that.sendEditorTimeout);
				}

				let obj = this;
				that.sendEditorTimeout = setTimeout(function() {
					$(obj).attr("disabled", true);
					let nname = $(obj).attr('data-alt');
					that.sendToEditor(nname);
				}, 100);
			});
		},

		showPopup: function() {
			const that = this;
			setTimeout(function() {
				that.appyPopupBaseStyle();
				var TB_WIDTH = WPCSW_EDITOR_DATA.popup_width;
				$("#TB_window").animate({
					marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
					width: TB_WIDTH + 'px',
				});
			}, 10);
		},

		appyPopupBaseStyle: function() {
			$("#TB_window").addClass('wpcsw-window');
			$("#TB_ajaxContent").css({
				"width": "100%",
				"height": "100%",
				'box-sizing': 'border-box',
				'padding-top': '15px',
			});
		},

		sendToEditor: function(filename, callback) {
			const that = this;
			
			const ajaxData = {
				action: 'wpcsw_ajaxprocess',
				fucname: 'get_parameters',
				filename: filename,
				post_id: that.getPostId(),
			};

			$.post(ajaxurl, ajaxData, function (param) {
				const shortcode = "[copysafe name='" + filename + "'" + param + "]";
				send_to_editor(shortcode);

				if(typeof callback == 'function') {
					callback(param);
				}
			});
		},

		getPostId: function() {
			return WPCSW_EDITOR_DATA.ID;
		},
	};

	$(document).ready(function() {
		WPCSW_EDITOR.init();
	});
})(jQuery);