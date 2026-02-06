<?php

class WPCSW_Frontend {

	public function __construct()
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_action('wp_head', [$this, 'display_meta']);
	}

	public function enqueue_scripts()
	{
		wp_register_style('wpcsw-watermark', WPCSW_PLUGIN_URL . 'css/copysafe-watermark.css', [], WPCSW_ASSET_VERSION);
		
		//wp_register_script('wpcsw-watermark', WPCSW_PLUGIN_URL . 'js/copysafe-watermark.js', ['jquery'], WPCSW_ASSET_VERSION, ['in_footer' => false]);
		wp_register_script('wpcsw-shortcut', WPCSW_PLUGIN_URL . 'js/copysafe-shortcut.js', [], WPCSW_ASSET_VERSION, ['in_footer' => false]);

		$active_settings = wpcsw_instance()->cache->get('active_settings');
		if( ! empty($active_settings))
		{
			
			wp_enqueue_style('wpcsw-watermark');
			
			//wp_enqueue_script('wpcsw-watermark');
			wp_enqueue_script('wpcsw-shortcut');
		}
	}

	public function display_meta()
	{
		$active_settings = wpcsw_instance()->cache->get('active_settings');

		if( ! empty($active_settings))
		{
			$allow_remote = 'false';
			$allow_mac = 'false';
			$allow_android = 'false';
			$allow_ios = 'false';
			$allow_linux = 'false';

			if($active_settings['allow_remote']) {
				$allow_remote = 'true';
			}

			if($active_settings['allow_mac']) {
				$allow_mac = 'true';
			}

			if($active_settings['allow_android']) {
				$allow_android = 'true';
			}

			if($active_settings['allow_ios']) {
				$allow_ios = 'true';
			}

			if($active_settings['allow_linux']) {
				$allow_linux = 'true';
			}
	?>
<meta name="artis-allowkeys" value="true" />
<meta name="artis-allowprint" value="true" />
<meta name="artis-allowremote" value="<?php echo esc_attr($allow_remote); ?>" />
<meta name="artis-allowmac" value="<?php echo esc_attr($allow_mac); ?>" />
<meta name="artis-allowandroid" value="<?php echo esc_attr($allow_android); ?>" />
<meta name="artis-allowios" value="<?php echo esc_attr($allow_ios); ?>" />
<meta name="artis-allowlinux" value="<?php echo esc_attr($allow_linux); ?>" />
		<?php
		}
	}
}
