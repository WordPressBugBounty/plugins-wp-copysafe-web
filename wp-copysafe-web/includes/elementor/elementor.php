<?php

class WPCSW_Elementor {

	public function __construct()
	{
		add_action('elementor/widgets/register', [$this, 'register_widget']);
		add_action('elementor/editor/before_enqueue_styles', [$this, 'register_style']);
	}

	public function register_widget($widgets_manager)
	{
		require_once __DIR__ . '/widget-wp-copysafe-web.php';

		$widgets_manager->register(new WPCSW_Elementor_Widget());
	}

	public function register_style()
	{
		wp_register_script('wpcsw-elementor-editor', WPCSW_PLUGIN_URL . 'includes/elementor/assets/js/editor.js', ['wp-copysafeweb-editor'], WPCSW_ASSET_VERSION, true);
		wp_enqueue_script('wpcsw-elementor-editor');

		wp_register_style('wpcsw-elementor-editor', WPCSW_PLUGIN_URL . 'includes/elementor/assets/css/editor.css', [], WPCSW_ASSET_VERSION);
		wp_enqueue_style('wpcsw-elementor-editor');
	}
}