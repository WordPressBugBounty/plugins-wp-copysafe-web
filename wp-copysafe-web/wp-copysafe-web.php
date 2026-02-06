<?php

/*
Plugin Name: CopySafe Web Protection
Plugin URI: https://artistscope.com/copysafe_web_protection_wordpress_plugin.asp
Description: Add copy protection from PrintScreen and screen capture. Copysafe Web uses encrypted images and domain lock to apply copy protection for all media displayed on the web page. Click here for the <a href="https://youtu.be/zG6EJGGsw8k" target="_blank">Usage Video</a> and the <a href="https://artistscope.com/docs/CopySafeWeb_WordPress_Installation.pdf" target="_blank">Setup Guide</a>.
Author: ArtistScope
Text Domain: wp-copysafe-web
Version: 5.2
License: GPLv2
Author URI: https://artistscope.com/

	Copyright 2025 ArtistScope Pty Limited


	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// ================================================================================ //
//                                                                                  //
//  WARNING : DONT CHANGE ANYTHING BELOW IF YOU DONT KNOW WHAT YOU ARE DOING        //
//                                                                                  //
// ================================================================================ //
# set script max execution time to 5mins

if( ! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

define('WPCSW_ASSET_VERSION', 1.105);
define('WPCSW_MIN_BROWSER_VERSION', 35);
define('WPCSW_DOWNLOAD_URL', 'https://artisbrowser.com/download/');
define('WPCSW_DIR', __DIR__);

require_once __DIR__ . "/function.php";
require_once __DIR__ . "/function-page.php";

class WPCSW_Main {

	protected static $instance = null;

	public
		$settings,
		$cache,
		$data,
		$frontend,
		$backend,
		$backend_media,
		$shortcode,
		$gutenberg,
		$elementor;

	protected function __construct()
	{
		require_once WPCSW_DIR . '/includes/settings.php';
		require_once WPCSW_DIR . '/includes/cache.php';
		require_once WPCSW_DIR . '/includes/data.php';
		require_once WPCSW_DIR . '/includes/frontend.php';
		require_once WPCSW_DIR . '/includes/backend.php';
		require_once WPCSW_DIR . '/includes/backend-media.php';
		require_once WPCSW_DIR . '/includes/shortcode.php';
		require_once WPCSW_DIR . '/includes/gutenberg/gutenberg.php';
		require_once WPCSW_DIR . '/includes/elementor/elementor.php';

		$this->settings = new WPCSW_Settings;
		$this->cache = new WPCSW_Cache;
		$this->data = new WPCSW_Data;
		$this->frontend = new WPCSW_Frontend;
		$this->backend = new WPCSW_Backend;
		$this->backend_media = new WPCSW_Backend_Media;
		$this->shortcode = new WPCSW_Shortcode;
		$this->gutenberg = new WPCSW_Gutenberg;
		$this->elementor = new WPCSW_Elementor;
	}

	public static function instance()
	{
		if(self::$instance == null)
		{
			$class_name = __CLASS__;
			self::$instance = new $class_name();
		}

		return self::$instance;
	}

	private function __clone() { }

	public function __wakeup() {
		throw new Exception('Cannot unserialize a singleton.');
	}
}

function wpcsw_instance() {
	return WPCSW_Main::instance();
}

wpcsw_instance();

function wpcsw_enable_extended_upload($mime_types = []) {
	// You can add as many MIME types as you want.
	$mime_types['class'] = 'application/octet-stream';
	// If you want to forbid specific file types which are otherwise allowed,
	// specify them here.  You can add as many as possible.
	return $mime_types;
}

//This filter is added to add the support for upload of .class file
add_filter('upload_mimes', 'wpcsw_enable_extended_upload');

// ============================================================================================================================
# register WordPress menus
function wpcsw_admin_menus() {
	add_menu_page('CopySafe Web', 'CopySafe Web', 'publish_posts', 'wpcsw_list');
	add_submenu_page('wpcsw_list', 'CopySafe Web List Files', 'List Files', 'publish_posts', 'wpcsw_list', 'wpcsw_admin_page_list');
	add_submenu_page('wpcsw_list', 'CopySafe Web Settings', 'Settings', 'publish_posts', 'wpcsw_settings', 'wpcsw_admin_page_settings');
}

// ============================================================================================================================
# delete short code
function wpcsw_delete_shortcode() {
	// get all posts
	$posts_array = get_posts();
	foreach ($posts_array as $post) {
		// delete short code
		$post->post_content = wpcsw_deactivate_shortcode($post->post_content);
		// update post
		wp_update_post($post);
	}
}

// ============================================================================================================================
# deactivate short code
function wpcsw_deactivate_shortcode($content) {
	// delete short code
	$content = preg_replace('/\[copysafe name="[^"]+"\]\[\/copysafe\]/s', '', $content);
	return $content;
}

// ============================================================================================================================
# search short code in post content and get post ids
function wpcsw_search_shortcode($file_name) {
	// get all posts
	$posts = get_posts();
	$IDs = FALSE;
	foreach ($posts as $post) {
		$file_name = preg_quote($file_name, '\\');
		preg_match('/\[copysafe name="' . $file_name . '"\]\[\/copysafe\]/s', $post->post_content, $matches);
		if (is_array($matches) && isset($matches[1])) {
			$IDs[] = $post->ID;
		}
	}
	return $IDs;
}

// ============================================================================================================================
# delete file options
function wpcsw_delete_file_options($file_name) {
	$file_name = trim($file_name);
	$wpcsw_options = get_option('wpcsw_settings');
	foreach ($wpcsw_options["classsetting"] as $k => $arr) {
		if ($wpcsw_options["classsetting"][$k][$file_name]) {
			unset($wpcsw_options["classsetting"][$k][$file_name]);
			if (!count($wpcsw_options["classsetting"][$k])) {
				unset($wpcsw_options["classsetting"][$k]);
			}
		}
	}
	update_option('wpcsw_settings', $wpcsw_options);
}

// ============================================================================================================================
# install media buttons
function wpcsw_media_buttons($context) {
	$url = wpcsw_instance()->backend->getPopupUrl();
	echo wp_kses(
		"<a href='" . esc_attr($url) . "' class='thickbox' id='wpcsw_link' data-body='no-overflow' title='CopySafe Web'><img src='" . esc_attr(plugin_dir_url(__FILE__)) . "/images/copysafebutton.png'></a>",
		wpcsw_instance()->settings->kses_allowed_options()
	);
}

// ============================================================================================================================

// ============================================================================================================================
# admin page scripts
function wpcsw_admin_load_js() {
	// load jquery suggest plugin
	wp_enqueue_script('suggest');
}

function wpcsw_is_admin_postpage() {
	$script_name = explode("/", isset($_SERVER["SCRIPT_NAME"]) ? sanitize_text_field(wp_unslash($_SERVER["SCRIPT_NAME"])) : '');
	$ppage = end($script_name);
	if ($ppage == "post-new.php" || $ppage == "post.php") {
		return TRUE;
	}
}

// ============================================================================================================================
# setup plugin
function wpcsw_setup()
{
	//----add codding----
	$options = get_option("wpcsw_settings");
	define('WPCSW_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__))); //use for include files to other files
	define('WPCSW_PLUGIN_URL', plugins_url('/', __FILE__));

	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
	$upload_path = $wp_upload_dir_path . '/' . $options["settings"]["upload_path"];
	define('WPCSW_UPLOAD_PATH', $upload_path); //use for include files to other files

	$wp_upload_dir_url = str_replace("\\", "/", $wp_upload_dir['baseurl']);
	$upload_url = $wp_upload_dir_url . '/' . $options["settings"]["upload_path"];
	define('WPCSW_UPLOAD_URL', $upload_url);

	add_action('wp_ajax_wpcsw_ajaxprocess', 'wpcsw_ajaxprocess');

	//Sanitize the GET input variables
	$pagename = !empty($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
	$cswfilename = !empty($_GET['cswfilename']) ? sanitize_file_name(wp_unslash($_GET['cswfilename'])) : '';
	$action = !empty($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';
	$cswdel_nonce = !empty($_GET['cswdel_nonce']) ? sanitize_key(wp_unslash($_GET['cswdel_nonce'])) : '';

	if ($pagename == 'wpcsw_list' && $cswfilename && $action == 'cswdel')
	{
		//check that nonce is valid and user is administrator
		if (current_user_can('administrator') && wp_verify_nonce($cswdel_nonce, 'cswdel')) {
			wpcsw_delete_file_options($cswfilename);
			if (file_exists(WPCSW_UPLOAD_PATH . $cswfilename)) {
				wp_delete_file(WPCSW_UPLOAD_PATH . $cswfilename);
			}
			wp_safe_redirect('admin.php?page=wpcsw_list');
		}
		else {
			wp_nonce_ays('');
		}
	}

	if (isset($_GET['wpcsw-popup']) && $_GET["wpcsw-popup"] == "copysafe") {
		require_once(WPCSW_PLUGIN_PATH . "popup_load.php");
		exit();
	}

	// if user logged in
	if (is_user_logged_in()) {
		// install admin menu
		add_action('admin_menu', 'wpcsw_admin_menus');

		// check user capability
		if (current_user_can('edit_posts')) {
			// load admin JS
			add_action('admin_print_scripts', 'wpcsw_admin_load_js');
			// load media button
			add_action('media_buttons', 'wpcsw_media_buttons');
		}
	}
}

// ============================================================================================================================
# runs when plugin activated
function wpcsw_activate() {
	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

	//if this is first activation, setup plugin options
	if( ! get_option('wpcsw_settings')) {
		// set plugin folder
		$upload_dir = 'copysafe-web/';
		$upload_path = $wp_upload_dir_path . '/' . $upload_dir;

		// set default options
		$wpcsw_options['settings'] = [
			'upload_path' => $upload_dir,
			'mode' => "demo",
		];

		update_option('wpcsw_settings', $wpcsw_options);

		if (!is_dir($upload_path)) {
			wp_mkdir_p($upload_path);
		}
		// create upload directory if it is not exist
	}
}

// ============================================================================================================================
# runs when plugin deactivated
function wpcsw_deactivate() {
	// remove text editor short code
	remove_shortcode('copysafe');
}

// ============================================================================================================================
# runs when plugin deleted.
function wpcsw_uninstall() {
	global $wp_filesystem;

	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();

	// delete all uploaded files
	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

	$default_upload_dir = $wp_upload_dir_path . '/copysafe-web/';
	if (is_dir($default_upload_dir)) {
		$dir = scandir($default_upload_dir);
		foreach ($dir as $file) {
			if ($file != '.' || $file != '..') {
				wp_delete_file($default_upload_dir . $file);
			}
		}
		$wp_filesystem->rmdir($default_upload_dir);
	}

	// delete upload directory
	$options = get_option("wpcsw_settings");

	if ($options["settings"]["upload_path"]) {
		$upload_path = $wp_upload_dir_path . '/' . $options["settings"]["upload_path"];
		if (is_dir($upload_path)) {
			$dir = scandir($upload_path);
			foreach ($dir as $file) {
				if ($file != '.' || $file != '..') {
					wp_delete_file($upload_path . '/' . $file);
				}
			}
			// delete upload directory
			$wp_filesystem->rmdir($upload_path);
		}
	}

	// delete plugin options
	delete_option('wpcsw_settings');

	// unregister short code
	remove_shortcode('copysafe');

	// delete short code from post content
	wpcsw_delete_shortcode();
}

function wpcsw_includecss_js_to_footer(){
	if (!wpcsw_is_admin_postpage())
		return;
	
	?>
	<script>
	if( jQuery("#wpcsw_link").length > 0 ){
		if( jQuery("#wpcsw_link").data("body") == "no-overflow" ){
			jQuery("body").addClass("wps-no-overflow");
			
		}
	}
	</script>
	<?php
}

add_action('admin_footer', 'wpcsw_includecss_js_to_footer');

function wpcsw_ajax_action() {
	$response = [];

	$nonce = isset($_POST['_ajax_nonce']) ? sanitize_text_field(wp_unslash($_POST['_ajax_nonce'])) : '';
	if ( ! wp_verify_nonce($nonce, 'wpcsw_upload_nonce')) {
		wp_send_json_error();
	}

	if (current_user_can('upload_files')) {
		add_filter('upload_dir', 'wpcsw_upload_dir');

		// handle file upload
		$id = media_handle_upload(
			'async-upload',
			0,
			[
				'test_form' => TRUE,
				'action' => 'wpcsw-plugin-upload-action',
			]
		);

		// send the file' url as response
		if (is_wp_error($id)) {
			$response['status'] = 'error22';
			$response['error'] = $id->get_error_messages();
		}
		else {
			$response['status'] = 'success';

			$src = wp_get_attachment_image_src($id, 'thumbnail');
			$response['attachment'] = [];
			$response['attachment']['id'] = $id;
			$response['attachment']['src'] = $src[0];
		}

		remove_filter('upload_dir', 'wpcsw_upload_dir');
	}

	wp_send_json($response);
}

add_action('wp_ajax_wpcsw-plugin-upload-action', 'wpcsw_ajax_action');


// ============================================================================================================================
# register plugin hooks
register_activation_hook(__FILE__, 'wpcsw_activate'); // run when activated
register_deactivation_hook(__FILE__, 'wpcsw_deactivate'); // run when deactivated
register_uninstall_hook(__FILE__, 'wpcsw_uninstall'); // run when uninstalled

add_action('init', 'wpcsw_setup');