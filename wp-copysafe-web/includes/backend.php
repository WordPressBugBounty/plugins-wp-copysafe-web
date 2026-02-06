<?php

class WPCSW_Backend {

	public function __construct()
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueue_scripts']);
	}

	public function enqueue_scripts()
	{
		wp_register_style('jquery-ui-1.9', WPCSW_PLUGIN_URL . 'css/jquery-ui.css', [], WPCSW_ASSET_VERSION);
		wp_register_style('wpcsw-style', WPCSW_PLUGIN_URL . 'css/wp-copysafe-web.css', [], WPCSW_ASSET_VERSION);
		wp_enqueue_style('wpcsw-style');

		wp_register_script('wp-copysafeweb-editor', WPCSW_PLUGIN_URL . 'js/copysafe-editor.js', [
				'jquery',
			], WPCSW_ASSET_VERSION,
			['in_footer' => true]
		);

		$screen = get_current_screen();
		
		if( ! empty($screen->base) && $screen->base == 'post')
		{
			global $post;

			add_thickbox();
			
			$popup_url = $this->getPopupUrl();

			$uploader_options = [
				'runtimes' => 'html5,silverlight,flash,html4',
				'browse_button' => 'wpcsw-plugin-uploader-button',
				'container' => 'wpcsw-plugin-uploader',
				'drop_element' => 'wpcsw-plugin-uploader',
				'file_data_name' => 'async-upload',
				'multiple_queues' => TRUE,
				'max_file_size' => wp_max_upload_size() . 'b',
				'url' => admin_url('admin-ajax.php'),
				'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
				'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
				'filters' => [
					[
					'title' => __('Allowed Files', 'wp-copysafe-web'),
					'extensions' => '*',
					],
				],
				'multipart' => TRUE,
				'urlstream_upload' => TRUE,
				'multi_selection' => TRUE,
				'multipart_params' => [
					'_ajax_nonce' => '',
					'action' => 'wpcsw-plugin-upload-action',
				],
			];

			wp_localize_script('wp-copysafeweb-editor', 'WPCSW_EDITOR_DATA', [
				'popup_title' => __('CopySafe Web', 'wp-copysafe-web'),
				'popup_url' => $popup_url,
				'popup_width' => wpcsw_instance()->settings->TB_WIDTH,
				'uploader_options' => $uploader_options,
				'ID' => empty($post->ID) ? 0 : $post->ID,
			]);

			wp_enqueue_style('jquery-ui-1.9');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-progressbar');

			wp_enqueue_script('wp-copysafeweb-editor');
		}
	}

	public function getPopupUrl()
	{
		global $post_ID;

		$token = wp_create_nonce('wpcsw_token');
		$params = [
			'wpcsw-popup' => 'copysafe',
			'wpcsw_token' => $token,
			'post_id' => $post_ID,
		];
		return admin_url('?' . http_build_query($params));
	}
}