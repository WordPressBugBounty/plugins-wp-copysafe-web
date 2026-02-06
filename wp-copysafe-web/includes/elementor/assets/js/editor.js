var WPCSW_ELEMENTOR_EDITOR = null;
(function($) {
	WPCSW_ELEMENTOR_EDITOR = {

		active_panel: null,

		init: function() {
			const that = this;

			WPCSW_EDITOR.sendToEditor = that.sendToEditor;

			if(typeof elementor != 'undefined')
			{
				elementor.hooks.addAction('panel/open_editor/widget/wpcsw_widget', function(panel, model, view) {
					that.active_panel = panel;

					const elementFileSelection = panel.$el.find('.elementor-button[data-event="copysafe-web:editor:modal"]');

					if(elementFileSelection.length) {
						elementFileSelection.on('click', that.showPopup);
					}
				});
			}
		},

		showPopup: function() {
			const that = WPCSW_EDITOR;
			tb_show(WPCSW_EDITOR_DATA.popup_title, WPCSW_EDITOR_DATA.popup_url);
			setTimeout(function() {
				that.appyPopupBaseStyle();
				var TB_WIDTH = WPCSW_EDITOR_DATA.popup_width;
				$("#TB_window").animate({
					marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
					width: TB_WIDTH + 'px',
				});
			}, 10);
		},

		sendToEditor: function(filename, callback) {
			const that = WPCSW_ELEMENTOR_EDITOR;

			const ajaxData = {
				action: 'wpcsw_ajaxprocess',
				fucname: 'get_parameters',
				type: 'json',
				filename: filename,
				post_id: WPCSW_EDITOR.getPostId(),
			};

			$.post(ajaxurl, ajaxData, function (response) {
				const {border, height, width, border_color, text_color, loading_message, hyperlink, target} = response;

				that.active_panel.$el.find('.elementor-control-wpcsw_name input').val(filename).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_width input').val(width).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_height input').val(height).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_border input').val(border).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_border_color input').val(border_color).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_text_color input').val(text_color).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_loading_message input').val(loading_message).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_hyperlink input').val(hyperlink).trigger('input');
				that.active_panel.$el.find('.elementor-control-wpcsw_target input').val(target).trigger('input');

				tb_remove();

				if(typeof callback == 'function') {
					callback(param);
				}
			});
		},
	};

	$(document).ready(function() {
		WPCSW_ELEMENTOR_EDITOR.init();
	});
})(jQuery);