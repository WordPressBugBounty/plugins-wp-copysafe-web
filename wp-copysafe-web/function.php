<?php defined('ABSPATH') or exit;

function wpcsw_ajaxprocess()
{
	if( ! current_user_can('manage_options')) {
		wp_send_json_error();
	}

	$nonce = isset($_POST['_nonce']) ? sanitize_text_field(wp_unslash($_POST['_nonce'])) : '';
	$function_name = isset($_POST["fucname"]) ? sanitize_text_field(wp_unslash($_POST["fucname"])) : '';

	if($function_name == "file_upload")
	{
		if ( ! wp_verify_nonce($nonce, 'wpcsw_upload_nonce')) {
			wp_send_json_error();
		}

		$msg = wpcsw_file_upload($_POST);
		$upload_list = wpcsw_get_uploadfile_list();
		$data = [
			"message" => $msg,
			"list" => $upload_list,
		];
		echo wp_json_encode($data);
	}
	elseif ($function_name == "file_search")
	{
		$data = wpcsw_file_search($_POST);
		echo wp_kses($data, wpcsw_instance()->settings->kses_allowed_options());
	}
	else if ($function_name == "setting_save")
	{
		if ( ! wp_verify_nonce($nonce, 'wpcsw_settings_save_nonce')) {
			wp_send_json_error();
		}

		$data = wpcsw_setting_save($_POST);
		echo wp_kses($data, wpcsw_instance()->settings->kses_allowed_options());
	}
	else if ($function_name == "get_parameters")
	{
		$data_type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : '';
		$data = wpcsw_get_parameters($_POST, $data_type);

		if($data_type == 'json')
		{
			wp_send_json($data);
		}
		else
		{
			echo wp_kses($data, wpcsw_instance()->settings->kses_allowed_options());
		}
	}

	exit;
}

function wpcsw_get_parameters($params, $type = 'string')
{
	$default_settings = [];
	$postid           = (int)$params["post_id"];
	$filename         = trim(sanitize_text_field($params["filename"]));
	$settings         = wpcsw_get_first_class_settings();

	$options = get_option("wpcsw_settings");
	if ($options["classsetting"][$postid][$filename])
	{
		$settings = wp_parse_args($options["classsetting"][$postid][$filename], $default_settings);
	}

	extract($settings);

	$width = sanitize_text_field($width);
	$height = sanitize_text_field($height);
	$border = sanitize_text_field($border);
	$border_color = sanitize_text_field($border_color);
	$text_color = sanitize_text_field($text_color);
	$loading_message = sanitize_text_field($loading_message);
	$hyperlink = sanitize_text_field($hyperlink);
	$target = sanitize_text_field($target);

	if($type == 'json')
	{
		$params = [
			'width' => $width,
			'height' => $height,
			'border' => $border,
			'border_color' => $border_color,
			'text_color' => $text_color,
			'loading_message' => $loading_message,
			'hyperlink' => $hyperlink,
			'target' => $target,
		];
	}
	else
	{
		$params =
			" width='" . esc_attr( $width ) . "'" .
			" height='" . esc_attr( $height ) . "'" .
			" border='" . esc_attr( $border ) . "'" .
			" border_color='" . esc_attr( $border_color ) . "'" .
			" text_color='" . esc_attr( $text_color ) . "'" .
			" loading_message='" . esc_attr( $loading_message ) . "'" .
			" hyperlink='" . esc_attr( $hyperlink ) . "'" .
			" target='" . esc_attr( $target ) . "'";
	}

	return $params;
}

function wpcsw_get_first_class_settings() {
	$settings = [
		'border' => 0,
		'border_color' => '000000',
		'text_color' => 'FFFFFF',
		'loading_message' => 'Image loading...',
		'hyperlink' => '',
		'target' => "_top",
	];
	return $settings;
}

function wpcsw_file_upload($param)
{
	$file_error = $param["error"];
	$file_errors = [
		0 => __("There is no error, the file uploaded with success", 'wp-copysafe-web'),
		1 => __("The uploaded file exceeds the upload_max_filesize directive in php.ini", 'wp-copysafe-web'),
		2 => __("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 'wp-copysafe-web'),
		3 => __("The uploaded file was only partially uploaded", 'wp-copysafe-web'),
		4 => __("No file was uploaded", 'wp-copysafe-web'),
		6 => __("Missing a temporary folder", 'wp-copysafe-web'),
		7 => __("Upload directory is not writable", 'wp-copysafe-web'),
		8 => __("User not logged in", 'wp-copysafe-web'),
	];

	if ($file_error == 0) {
		$msg = '<div class="updated"><p><strong>' . esc_html(__('File Uploaded. You must save "File Details" to insert post', 'wp-copysafe-web')) . '</strong></p></div>';
	}
	else {
		$msg = '<div class="error"><p><strong>' . esc_html(__('Error', 'wp-copysafe-web')) . '!</strong></p><p>' . esc_html($file_errors[$file_error]) . '</p></div>';
	}

	return $msg;
}

function wpcsw_file_search($param)
{
	if(empty($param['search']) || empty($param['post_id']))
	{
		return '';
	}

	$postid = (int)$param['post_id'];
	$search = trim(sanitize_text_field($param["search"]));

	$files = wpcsw_get_uploaded_files();

	$result = FALSE;
	foreach ($files as $file)
	{
		if ($search == trim($file["filename"]))
		{
			$result = TRUE;
		}
	}

	if ( ! $result)
	{
		return "<hr /><h2>No found file</h2>";
	}

	$file_options = wpcsw_get_first_class_settings();

	$wpcsw_options = get_option('wpcsw_settings');
	if ($wpcsw_options["classsetting"][$postid][$search])
	{
		$file_options = $wpcsw_options["classsetting"][$postid][$search];
	}

	extract($file_options, EXTR_OVERWRITE);

	$width = sanitize_text_field($width);
	$height = sanitize_text_field($height);
	$border = sanitize_text_field($border);
	$border_color = sanitize_text_field($border_color);
	$text_color = sanitize_text_field($text_color);
	$loading_message = sanitize_text_field($loading_message);
	$hyperlink = sanitize_text_field($hyperlink);
	$target = sanitize_text_field($target);

	$dimension = wpcsw_get_dimension_from_filename($search);

	if(empty($file_options['width'])) {
		$width = $dimension['width'];
	}

	if(empty($file_options['height'])) {
		$height = $dimension['height'];
	}

	$settings_save_nonce = wp_create_nonce('wpcsw_settings_save_nonce');

    $str = "<hr />
      <div class='icon32' id='icon-file'><br /></div>
        <h2>Page Settings</h2>
        <div>
        <table cellpadding='0' cellspacing='0' border='0' >
            <tbody id='wpcsw_setting_body'> 
            <tr> 
              <td width='40'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Width in pixels. For auto width set 0.' /></td>
              <td class='label'>Custom Width:</td>
              <td width='120'>
                <input name='width' id='wpcsw_width' type='text' value='" . esc_attr($width) . "' size='3' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Height in pixels. For auto height set 0.' /></td>
              <td class='label'>Custom Height:</td>
              <td>
                <input name='height' id='wpcsw_height' type='text' value='" . esc_attr($height) . "' size='3' />
              </td>
            </tr>
            <tr> 
              <td align='left' width='40'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Border thickness in pixels. For no border set 0.' /></td>
              <td class='label'>Border size:</td>
              <td> 
                <input name='border' id='wpcsw_border' type='text' value='" . esc_attr($border) . "' size='3' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Color of the border and image backround area. For example use FFFFFF for white and 000000 is for black... without the # symbol.' /></td>
              <td class='label'>Border color:</td>
              <td> 
                <input name='border_color' id='wpcsw_border_color' type='text' value='" . esc_attr($border_color) . "' size='7' />
              </td>
            </tr>
            <tr> 
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Color of the text message that is displayed in the image area sas the image downloads.' /></td>
              <td class='label'>Text color:</td>
              <td> 
                <input name='text_color' id='wpcsw_text_color' type='text' value='" . esc_attr($text_color) . "' size='7' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Set the message to display as this class image loads.' /></td>
              <td class='label'>Loading message:&nbsp;</td>
              <td> 
                <input name='loading_message' id='wpcsw_loading_message' type='text' value='" . esc_attr($loading_message) . "' />
              </td>
            </tr>
            <tr> 
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Set the target frame for the hyperlink, for example _top' /></td>
              <td class='label'>Target frame:</td>
              <td> 
                <input value='" . esc_attr($target) . "' name='target' id='wpcsw_target' type='text' size='10' />
              </td>
              <td align='left'>&nbsp;</td>
              <td align='left'><img src='" . esc_attr(WPCSW_PLUGIN_URL) . "images/help-24-30.png' border='0' alt='Add a link to another page activated by clciking on the image, or leave blank for no link.' /></td>
              <td class='label'>Hyperlink:</td>
              <td> 
                <input value='" . esc_attr($hyperlink) . "' name='hyperlink' id='wpcsw_hyperlink' type='text' />
              </td>
            </tr>
            </tbody>
          </table>
          <p class='submit'>
            <input type='button' value='Save' class='button-primary' id='wpcsw_setting_save' name='submit' />
            <input type='button' value='Cancel' class='button-primary' id='wpcsw_cancel' />
            <input type='hidden' id='wpcsw_setting_save_nonce' value='" . esc_attr($settings_save_nonce) . "' />
          </p>
      </div>";

	return $str;
}

function wpcsw_setting_save($param)
{
	$postid = (int)$param["post_id"];
	$name   = trim(sanitize_text_field($param["nname"]));
	$data   = (array) json_decode(stripcslashes($param["set_data"]));

	// escape user inputs
	$data = array_map("esc_attr", $data);
	extract($data);

	$border          = (empty($border) ? '0' : esc_attr($border));
	$border_color    = (empty($border_color) ? '' : esc_attr($border_color));
	$text_color      = (empty($text_color) ? '' : esc_attr($text_color));
	$loading_message = (empty($loading_message) ? '' : esc_attr($loading_message));

	$wpcsw_settings = get_option('wpcsw_settings');
	if (!is_array($wpcsw_settings))
	{
		$wpcsw_settings = [];
	}

	$width     = sanitize_text_field($width);
	$height    = sanitize_text_field($height);
	$hyperlink = sanitize_text_field($hyperlink);
	$target    = sanitize_text_field($target);

	$final_data = [
		'border' => $border,
		"width" => $width,
		"height" => $height,
		'border_color' => $border_color,
		'text_color' => $text_color,
		'loading_message' => $loading_message,
		'hyperlink' => $hyperlink,
		'target' => $target,
		'postid' => $postid,
		'name' => $name,
	];

	$wpcsw_settings["classsetting"][$postid][$name] = $final_data;
	update_option('wpcsw_settings', $wpcsw_settings);

	$msg = '<div class="updated fade">
				<strong>' . __('File Options Are Saved', 'wp-copysafe-web') . '</strong><br />
				<div style="margin-top:5px;"><a href="#" alt="' . esc_attr( $name ) . '" class="button-secondary wpcsw-sendtoeditor"><strong>Insert file to editor</strong></a></div>
			</div>';

	return $msg;
}

function wpcsw_get_uploaded_files()
{
	$listdata = [];

	if (!is_dir(WPCSW_UPLOAD_PATH)) {
		return $listdata;
	}

	$file_list = scandir(WPCSW_UPLOAD_PATH);

	foreach ($file_list as $file)
	{
		if ($file == "." || $file == "..") {
			continue;
		}
		$file_path = WPCSW_UPLOAD_PATH . $file;
		if (filetype($file_path) != "file") {
			continue;
		}
		$filename = explode('.', $file);
		$ext = end($filename);
		if ($ext != "class") {
			continue;
		}

		$file_path = WPCSW_UPLOAD_PATH . $file;
		$file_name = $file;
		$file_size = filesize($file_path);
		$file_date = filemtime($file_path);

		if (round($file_size / 1024, 0) > 1) {
			$file_size = round($file_size / 1024, 0);
			$file_size = "$file_size KB";
		}
		else {
			$file_size = "$file_size B";
		}

		$file_date = gmdate("n/j/Y g:h A", $file_date);

		$listdata[] = [
			"filename" => $file_name,
			"filesize" => $file_size,
			"filedate" => $file_date,
		];
	}

	return $listdata;
}

function wpcsw_get_uploadfile_list()
{
	$table = '';
	$files = wpcsw_get_uploaded_files();

	foreach ($files as $file)
	{
		// prepare table row
		$table .=
			"<tr><td></td><td><a href='#' data-alt='" . esc_attr($file["filename"]) . "' class='wpcsw-sendtoeditor row-actionslink'>" . esc_attr($file["filename"]) . "</a></td>".
			"<td width='90px'>" . esc_attr($file["filesize"]) . "</td><td width='180px'>" . esc_attr($file["filedate"]) . "</td></tr>";
	}

	if ( ! $table) {
		$table .= '<tr><td colspan="3">' . __('No file uploaded yet.', 'wp-copysafe-web') . '</td></tr>';
	}

	return $table;
}

function wpcsw_check_artis_browser_version()
{
	$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
	
	if (strpos($user_agent, 'ArtisBrowser') === false)
	{
		$ref_url = get_permalink(get_the_ID());
		?>
		<script>
		document.location = '<?php echo esc_js(WPCSW_DOWNLOAD_URL . "?ref=". urlencode($ref_url)); ?>';
		</script>
		<?php
		exit;
	}
}

function wpcsw_get_artistbrowser_version()
{
	$version = '';
	$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

	if (preg_match('/ArtisBrowser\/([0-9.]+)/', $user_agent, $matches)) {
		$version = $matches[1];
	} else if(preg_match('/ArtisReader\/([0-9.]+)/', $user_agent, $matches)) {
		$version = $matches[1];
	}
	
	return $version;
}

function wpcsw_get_ip()
{
	// populate a local variable to avoid extra function calls.
	// NOTE: use of getenv is not as common as use of $_SERVER.
	//       because of this use of $_SERVER is recommended, but 
	//       for consistency, I'll use getenv below
	$tmp = getenv("HTTP_CLIENT_IP");

	// you DON'T want the HTTP_CLIENT_ID to equal unknown. That said, I don't
	// believe it ever will (same for all below)
	if ( $tmp && !strcasecmp( $tmp, "unknown"))
		return $tmp;

	$tmp = getenv("HTTP_X_FORWARDED_FOR");
	if( $tmp && !strcasecmp( $tmp, "unknown"))
		return $tmp;

	// no sense in testing SERVER after this. 
	// $_SERVER[ 'REMOTE_ADDR' ] == gentenv( 'REMOTE_ADDR' );
	$tmp = getenv("REMOTE_ADDR");
	if($tmp && !strcasecmp($tmp, "unknown"))
		return $tmp;
	
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
	}

	return("unknown");
}

function wpcsw_get_dimension_from_filename($filename)
{
	$width = '';
	$height = '';

	$path_parts = pathinfo($filename);
	
	if( ! empty($path_parts['filename']))
	{
		$file_name_parts = explode('_', $path_parts['filename']);

		if(count($file_name_parts) >= 4)
		{
			$suffix = array_pop($file_name_parts);
			$tmp_height = (int)array_pop($file_name_parts);
			$tmp_width = (int)array_pop($file_name_parts);

			if($suffix == 'C' && $tmp_height > 0 && $tmp_width > 0)
			{
				$width = $tmp_width;
				$height = $tmp_height;
			}
		}
	}

	return [
		'width' => $width,
		'height' => $height,
	];
}

function wpcsw_upload_dir($upload) {
	$upload['subdir'] = '/copysafe-web';
	$upload['path'] = $upload['basedir'] . $upload['subdir'];
	$upload['url'] = $upload['baseurl'] . $upload['subdir'];
	return $upload;
}