<?php

class WPCSW_Shortcode {

	public function __construct()
	{
		add_shortcode('copysafe', [$this, 'shortcode']);
	}

	public function shortcode($atts)
	{
		if(is_admin() || (did_action( 'elementor/loaded' ) && \Elementor\Plugin::$instance->preview->is_preview_mode())) {
			return '<p>Shortcode is disabled on admin preview.</p>';
		}

		if(defined( 'REST_REQUEST' ) && REST_REQUEST) {
			return '<p>Not available via api.</p>';
		}
		
		global $post;

		wpcsw_check_artis_browser_version();

		$postid = $post->ID;
		$filename = $atts["name"];
		$current_user = wp_get_current_user();

		if( ! file_exists(WPCSW_UPLOAD_PATH . $filename)) {
			return "<div style='padding:5px 10px;background-color:#fffbcc'><strong>File(" . esc_html($filename) . ") don't exist</strong></div>";
		}

		$settings = wpcsw_get_first_class_settings();

		// get plugin options
		$wpcsw_options = get_option('wpcsw_settings');
		if ($wpcsw_options["settings"]) {
			$settings = wp_parse_args($wpcsw_options["settings"], $settings);
		}

		if ($wpcsw_options["classsetting"][$postid][$filename]) {
			$settings = wp_parse_args($wpcsw_options["classsetting"][$postid][$filename], $settings);
		}

		$name = '';
		$width = '';
		$height = '';
		$text_color = '';
		$border_color = '';
		$border = '';
		$hyperlink = '';
		$target = '';

		$watermarked = '';
		$wtmtextsize = '';
		$wtmtextcolour = '';
		$wtmshadecolour = '';
		$wtmtextposition = '';
		$wtmtextopacity = '';

		$allow_mac = '';
		$allow_ios = '';
		$allow_linux = '';
		$allow_android = '';
		$allow_remote = '';

		$version_windows = WPCSW_MIN_BROWSER_VERSION;
		$version_mac = WPCSW_MIN_BROWSER_VERSION;
		$version_ios = WPCSW_MIN_BROWSER_VERSION;
		$version_linux = WPCSW_MIN_BROWSER_VERSION;
		$version_android = WPCSW_MIN_BROWSER_VERSION;
		$version_artisbrowser = wpcsw_get_artistbrowser_version();

		if((int)$current_user->ID > 0) {
			$username = $current_user->user_login;

			if($current_user->user_firstname)
				$username=$current_user->user_firstname;

			$userString = $current_user->ID . ' ' . $username . ' ' . gmdate('Y-m-d');
		} else {
			$userString = wpcsw_get_ip() . ' ' . gmdate('Y-m-d');
		}

		$settings = wp_parse_args($atts, $settings);
		extract($settings);

		/**
		* Disable watermark
		*/
		$watermarked = '';

		$plugin_url = WPCSW_PLUGIN_URL;
		$upload_url = WPCSW_UPLOAD_URL;
		$script_tag = 'script';

		wpcsw_instance()->cache->set('active_settings', [
			'allow_remote' => $allow_remote == 'no' ? false : true,
			'allow_mac' => $allow_mac == 'no' ? false : true,
			'allow_android' => $allow_android == 'no' ? false : true,
			'allow_ios' => $allow_ios == 'no' ? false : true,
			'allow_linux' => $allow_linux == 'no' ? false : true,
		]);

		ob_start();
		if( ! defined('WPCSW_SCRIPT_LOADED')) {
		?>
		<script type="text/javascript">
			// hide JavaScript from non-JavaScript browsers
			var wpcsw_debugging = <?php echo esc_js($mode == 'debug' ? 'true' : 'false'); ?>;
			var wpcsw_download_url = '<?php echo esc_js(WPCSW_DOWNLOAD_URL); ?>';

			var wpcsw_allow_mac = <?php echo $allow_mac == 'no' ? 'false' : 'true'; ?>;
			var wpcsw_allow_ios = <?php echo $allow_ios == 'no' ? 'false' : 'true'; ?>;
			var wpcsw_allow_linux = <?php echo $allow_linux == 'no' ? 'false' : 'true'; ?>;
			var wpcsw_allow_android = <?php echo $allow_android == 'no' ? 'false' : 'true'; ?>;

			var wpcsw_version_windows = '<?php echo esc_js(trim($version_windows)); ?>';
			var wpcsw_version_mac = '<?php echo esc_js(trim($version_mac)); ?>';
			var wpcsw_version_ios = '<?php echo esc_js(trim($version_ios)); ?>';
			var wpcsw_version_linux = '<?php echo esc_js(trim($version_linux)); ?>';
			var wpcsw_version_android = '<?php echo esc_js(trim($version_android)); ?>';
			var wpcsw_version_artisbrowser = '<?php echo esc_js($version_artisbrowser); ?>';
		</script>
		<<?php echo esc_html($script_tag); ?> src="<?php echo esc_attr(WPCSW_PLUGIN_URL . 'js/wp-copysafe-web.js?v=' . urlencode(WPCSW_ASSET_VERSION)); ?>"></<?php echo esc_html($script_tag); ?>>
		<?php
			define('WPCSW_SCRIPT_LOADED', true);
		}

		$content_id = 'wpcsw-' . uniqid();
		$params = [
			'name' => $name,
			'file_url' => $upload_url . $name,
			'width' => $width,
			'height' => $height,
			'text_color' => $text_color,
			'border_color' => $border_color,
			'border' => $border,
			'loading_message' => $loading_message,
			'hyperlink' => $hyperlink,
			'target' => $target,
		];
		?>
		<div class="wpcsw-wrapper" id="<?php echo esc_attr($content_id); ?>">
			<script type="text/javascript">
				//hide JavaScript from non-JavaScript browsers
				<?php if(in_array($mode, ['licensed', 'debug'])) { ?>
				var params = <?php echo wp_json_encode($params); ?>;
				insertCopysafeWeb(params);
				<?php } else { ?>
				document.writeln("<img src='<?php echo esc_js($plugin_url); ?>images/image_placeholder.jpg' border='0' alt='Demo mode'>");
				<?php } ?>
				<?php if($watermarked) { ?>
				setTimeout(function() {
					wpcsw_watermark.add('#<?php echo esc_js($content_id); ?>', {
						'watermark' : true,
						'watermark_text' : '<?php echo esc_js($userString); ?>',
						'watermark_type' : '<?php echo esc_js(wpcsw_instance()->data->getWatermarkPosition($wtmtextposition)); ?>',
						'watermark_color' : '<?php echo esc_js(wpcsw_instance()->data->getWatermarkColor($wtmtextcolour)); ?>',
						'watermark_shade_color' : '<?php echo esc_js(wpcsw_instance()->data->getWatermarkShade($wtmshadecolour)); ?>',
						'watermark_font_size' : '<?php echo esc_js(wpcsw_instance()->data->getWatermarkTextSize($wtmtextsize)); ?>',
						'watermark_font_size_fullscreen' : '<?php echo esc_js(wpcsw_instance()->data->getWatermarkTextSize($wtmtextsize)); ?>',
						'watermark_opacity' : '<?php echo esc_js(wpcsw_instance()->data->getWatermarkOpacity($wtmtextopacity)); ?>'
					});
				}, 500);
				<?php } ?>
			</script>
		</div>
		<?php
		$output = ob_get_clean();

		return $output;
	}
}
