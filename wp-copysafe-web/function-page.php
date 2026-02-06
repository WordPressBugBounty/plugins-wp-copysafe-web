<?php

if( ! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

// ============================================================================================================================
# "List" Page
function wpcsw_admin_page_list()
{
	$msg = '';
	$table = '';
	$files = wpcsw_get_uploaded_files();

	if (!empty($_POST))
	{
		$wpcsw_options = get_option('wpcsw_settings');

		$wp_upload_dir = wp_upload_dir();
		$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
		if (!empty($wpcsw_options['settings']['upload_path'])) {
			$target_dir = $wp_upload_dir_path . '/' . $wpcsw_options['settings']['upload_path'];
		} else {
			$target_dir = $wp_upload_dir_path;
		}

		// Check if image file is a actual image or fake image
		if (isset($_POST["copysafe-web-class-submit"]))
		{
			$wpcopysafeweb_wpnonce = isset($_POST['wpcopysafeweb_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['wpcopysafeweb_wpnonce'])) : '';

			if (wp_verify_nonce($wpcopysafeweb_wpnonce, 'wpcopysafeweb_settings'))
			{
				$file_name = isset($_FILES["copysafe-web-class"]["name"]) ? sanitize_text_field(wp_unslash($_FILES["copysafe-web-class"]["name"])) : '';

				$target_file = $target_dir . basename($file_name);
				$uploadOk = 1;
				$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
				
				// Allow only .class file formats
				if ($file_name == "")
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Please upload file to continue.', 'wp-copysafe-web')) . '</strong></p></div>';
					$uploadOk = 0;
				}
				else if ($imageFileType != "class")
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, only .class files are allowed.', 'wp-copysafe-web')) . '</strong></p></div>';
					$uploadOk = 0;
				}
				// Check if $uploadOk is set to 0 by an error
				else if ($uploadOk == 0)
				{
					$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, your file was not uploaded.', 'wp-copysafe-web')) . '</strong></p></div>';
					// if everything is ok, try to upload file
				}
				else
				{
					$file_data_keys = [
						'name',
						'tmp_name',
						'size',
						'error',
						'type',
					];
					$file_data = [];
					foreach($file_data_keys as $file_data_key)
					{
						$file_data[$file_data_key] =
							isset($_FILES['copysafe-web-class'][$file_data_key]) ?
								sanitize_text_field($_FILES['copysafe-web-class'][$file_data_key]) : '';
					}

					//Register path override
					add_filter('upload_dir', 'wpcsw_upload_dir');

					//Move file
					$movefile = wp_handle_upload($file_data, [
						'test_form' => false,
						'test_type' => false,
						'mimes' => [
							'class' => 'application/octet-stream'
						],
					]);

					//Remove path override
					remove_filter('upload_dir', 'wpcsw_upload_dir');

					if ($movefile && ! isset($movefile['error']))
					{
						$base_url = get_site_url();
						$msg .= '<div class="updated"><p><strong>' . 'The file ' . esc_html(basename($file_name)) . ' has been uploaded. Click <a href="' . esc_attr($base_url) . '/wp-admin/admin.php?page=wpcsw_list">here</a> to update below list.' . '</strong></p></div>';
					}
					else
					{
						$msg .= '<div class="error"><p><strong>' . esc_html(__('Sorry, there was an error uploading your file. Check write permissions on the upload folder.', 'wp-copysafe-web')) . '</strong></p></div>';
					}
				}
			} //nonce
		}
	}

	if (!empty($files))
	{
		foreach ($files as $file)
		{
			$bare_url = 'admin.php?page=wpcsw_list&cswfilename=' . $file["filename"] . '&action=cswdel';

			$complete_url = wp_nonce_url($bare_url, 'cswdel', 'cswdel_nonce');

			$link = "<div class='row-actions'>
					<span><a href='" . esc_attr($complete_url) . "' title=''>Delete</a></span>
				</div>";
			// prepare table row
			$table .= "<tr><td></td><td>" . esc_html($file["filename"]) . " " . $link . "</td><td>" . esc_html($file["filesize"]) . "</td><td>" . esc_html($file["filedate"]) . "</td></tr>";
		}
	}

	if (!$table) {
		$table .= '<tr><td colspan="3">' . esc_html(__('No file uploaded yet.', 'wp-copysafe-web')) . '</td></tr>';
	}

	$wpcsw_options = get_option('wpcsw_settings');
	if ($wpcsw_options["settings"]) {
		extract($wpcsw_options["settings"], EXTR_OVERWRITE);
	}

	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);
	$upload_dir = $wp_upload_dir_path . '/' . $upload_path;

	$display_upload_form = !is_dir($upload_dir) ? FALSE : TRUE;

	if (!$display_upload_form) {
		$msg = '<div class="updated"><p><strong>' .
			esc_html(__('Upload directory doesn\'t exist. Please configure upload directory to upload class files.', 'wp-copysafe-web')) . '</strong></p></div>';
	}
  ?>
    <div class="wrap">
        <div class="icon32" id="icon-file"><br/></div>
        <?php echo wp_kses($msg, wpcsw_instance()->settings->kses_allowed_options()); ?>
        <h2>List Class Files</h2>
        <?php if ($display_upload_form): ?>
            <form action="" method="post" enctype="multipart/form-data">
                <?php echo wp_kses(wp_nonce_field('wpcopysafeweb_settings', 'wpcopysafeweb_wpnonce'), wpcsw_instance()->settings->kses_allowed_options()); ?>
                <input type="file" name="copysafe-web-class" value=""/>
                <input type="submit" name="copysafe-web-class-submit"
                       value="Upload"/>
            </form>
        <?php endif; ?>
        <!--<div><?php // echo wpcsw_media_buttons('');
        ?></div>-->
        <div id="col-container" style="width:700px;">
            <div class="col-wrap">
                <h3>Uploaded Class Files</h3>
                <table class="wp-list-table widefat">
                    <thead>
                    <tr>
                        <th width="5px">&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php echo wp_kses($table, wpcsw_instance()->settings->kses_allowed_options()); ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>&nbsp;</th>
                        <th>File</th>
                        <th>Size</th>
                        <th>Date</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="clear"></div>
    </div>
  <?php
}

// ============================================================================================================================
# "Settings" page
function wpcsw_admin_page_settings()
{
	$msg = '';
	$wp_upload_dir = wp_upload_dir();
	$wp_upload_dir_path = str_replace("\\", "/", $wp_upload_dir['basedir']);

	$watermarked = '';
	$wtmtextsize = '';
	$wtmtextcolour = '';
	$wtmshadecolour = '';
	$wtmtextposition = '';
	$wtmtextopacity = '';

	$allow_mac = 'yes';
	$allow_ios = 'yes';
	$allow_linux = 'yes';
	$allow_android = 'yes';
	$allow_remote = 'yes';

	$version_mac = '';
	$version_ios = '';
	$version_linux = '';
	$version_android = '';
	$version_windows = '';

	$wtm_text_size_options = wpcsw_instance()->data->getWatermarkTextSizes();
	$wtm_text_colour_options = wpcsw_instance()->data->getWatermarkColors();
	$wtm_shade_colour_options = wpcsw_instance()->data->getWatermarkShades();
	$wtm_text_position_options = wpcsw_instance()->data->getWatermarkPositions();
	$wtm_text_opacity_options = wpcsw_instance()->data->getWatermarkOpacities();

	if (!empty($_POST))
	{
		if(isset($_POST['wpcopysafeweb_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['wpcopysafeweb_wpnonce'])), 'wpcopysafeweb_settings'))
		{
			$wpcsw_options = get_option('wpcsw_settings');
			extract($_POST, EXTR_OVERWRITE);

			if( ! isset($_POST['allow_mac'])) {
				$allow_mac = 'no';
			}
			
			if( ! isset($_POST['allow_ios'])) {
				$allow_ios = 'no';
			}
			
			if( ! isset($_POST['allow_linux'])) {
				$allow_linux = 'no';
			}
			
			if( ! isset($_POST['allow_android'])) {
				$allow_android = 'no';
			}
		
			if( ! $upload_path) {
				$upload_path = 'copysafe-web/';
			}
			else
			{
				$upload_path = sanitize_text_field($upload_path);
			}

			$upload_path = str_replace("\\", "/", stripcslashes($upload_path));
			if (substr($upload_path, -1) != "/") {
				$upload_path .= "/";
			}

			$watermarked = '';

			$wpcsw_options['settings'] = [
				'upload_path' => $upload_path,
				'mode' => sanitize_text_field($mode),
				'watermarked' => empty($watermarked) ? '' : 'checked',
				'wtmtextsize' => sanitize_text_field($wtmtextsize),
				'wtmtextcolour' => sanitize_text_field($wtmtextcolour),
				'wtmshadecolour' => sanitize_text_field($wtmshadecolour),
				'wtmtextposition' => sanitize_text_field($wtmtextposition),
				'wtmtextopacity' => sanitize_text_field($wtmtextopacity),
				'allow_windows' => 'yes',
				'allow_mac' => $allow_mac == 'yes' ? 'yes' : 'no',
				'allow_ios' => $allow_ios == 'yes' ? 'yes' : 'no',
				'allow_linux' => $allow_linux == 'yes' ? 'yes' : 'no',
				'allow_android' => $allow_android == 'yes' ? 'yes' : 'no',
				'allow_remote' => $allow_remote == 'yes' ? 'yes' : 'no',
				'version_windows' => sanitize_text_field($version_windows),
				'version_mac' => sanitize_text_field($version_mac),
				'version_ios' => sanitize_text_field($version_ios),
				'version_linux' => sanitize_text_field($version_linux),
				'version_android' => sanitize_text_field($version_android),
			];

			$max_upload_size = wp_max_upload_size();
			if ( ! $max_upload_size ) {
				$max_upload_size = 0;
			}

			$wpcsw_options['settings']['max_size'] = esc_html(size_format($max_upload_size));

			$upload_path = $wp_upload_dir_path . '/' . $upload_path;
			if (!is_dir($upload_path)) {
				wp_mkdir_p($upload_path);
			}

			update_option('wpcsw_settings', $wpcsw_options);
			$msg = '<div class="updated"><p><strong>' . __('Settings Saved', 'wp-copysafe-web') . '</strong></p></div>';
		} //nounce
	}

	$wpcsw_options = get_option('wpcsw_settings');
	if ($wpcsw_options["settings"])
	{
		extract($wpcsw_options["settings"], EXTR_OVERWRITE);
	}

	$upload_dir = $wp_upload_dir_path . '/' . $upload_path;

	if (!is_dir($upload_dir)) {
		$msg = '<div class="updated"><p><strong>' . __('Upload directory doesn\'t exist.', 'wp-copysafe-web') . '</strong></p></div>';
	}

	$select =
		'<option value="licensed">Active</option>
		<option value="debug">Debug Mode</option>
		<option value="demo">Placeholder</option>';
	$select = str_replace('value="' . $mode . '"', 'value="' . $mode . '" selected', $select);
	?>
    <style type="text/css">#wpcsw_page_setting img { cursor: pointer; }</style>
    <div class="wrap">
        <div class="icon32" id="icon-settings"><br/></div>
        <?php echo wp_kses($msg, wpcsw_instance()->settings->kses_allowed_options()); ?>
        <h2> Default Settings</h2>

        <div class="card">
        <h3><?php echo esc_html__('CopySafe Web - Setup Guide', 'wp-copysafe-web'); ?></h3>
        <a href="https://youtu.be/zG6EJGGsw8k" target="_blank" class="button"><?php echo esc_html__('Usage Video', 'wp-copysafe-web'); ?></a>
        <a href="https://artistscope.com/docs/CopySafeWeb_WordPress_Installation.pdf" target="_blank" class="button"><?php echo esc_html__('Instruction PDF', 'wp-copysafe-web'); ?></a>
        </div>

        <form action="" method="post">
            <?php echo wp_kses(wp_nonce_field('wpcopysafeweb_settings', 'wpcopysafeweb_wpnonce'), wpcsw_instance()->settings->kses_allowed_options()); ?>
            <table cellpadding='1' cellspacing='0' border='0' id='wpcsw_page_setting'>
                <tbody>
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr class="copysafe-section-title">
                    <td colspan="5"><h2 class="title"><?php esc_html_e('Default settings applied to all protected pages:', 'wp-copysafe-web'); ?></h2></td>
                </tr>
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr>
                    <td width="50">&nbsp;</td>
                    <td width="30"><img src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png' alt='Path to the upload folder for Web.'></td>
                    <td><?php esc_html_e('Upload Folder:', 'wp-copysafe-web'); ?></td>
                    <td><input value="<?php echo esc_attr($upload_path); ?>"
                                            name="upload_path"
                                            class="regular-text code"
                                            type="text"><br />
                        <?php esc_html_e("Only specify the folder name. It will be located in site's upload directory,", 'wp-copysafe-web'); ?> <?php echo esc_attr($wp_upload_dir_path); ?>.
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src='<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png' alt='Set the mode to use. Use Licensed if you have licensed images. Otherwise set for Demo or Debug mode.'></td>
                    <td><?php esc_html_e('Mode:', 'wp-copysafe-web'); ?></td>
                    <td><select name="mode"><?php echo wp_kses($select, wpcsw_instance()->settings->kses_allowed_options()); ?></select></td>
                </tr>
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr class="copysafe-section-title">
                    <td colspan="5"><h2 class="title"><?php esc_html_e('Operating system allowed', 'wp-copysafe-web'); ?></h2></td>
                </tr>
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="<?php esc_attr_e('Allow visitors using Windows OS to access this page.', 'wp-copysafe-web'); ?>" /></td>
                    <td><?php esc_html_e('Allow Windows:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <div>
                            <input type="checkbox" checked disabled />
                            <input type="text" size="8"
                                name="version_windows"
                                placeholder="<?php echo esc_attr(WPCSW_MIN_BROWSER_VERSION); ?>"
                                value="<?php echo esc_attr($version_windows ? $version_windows : WPCSW_MIN_BROWSER_VERSION); ?>" />
                            <span><?php esc_html_e('Min. Version', 'wp-copysafe-web'); ?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="<?php esc_attr_e('Allow visitors using Mac OS to access this page.', 'wp-copysafe-web'); ?>" /></td>
                    <td><?php esc_html_e('Allow Mac OSX:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <div>
                            <input name="allow_mac" type="checkbox" value="yes"<?php echo $allow_mac == 'yes' ? ' checked' : ''; ?> />
                            <input type="text" size="8"
                                name="version_mac"
                                placeholder="<?php echo esc_attr(WPCSW_MIN_BROWSER_VERSION); ?>"
                                value="<?php echo esc_attr($version_mac ? $version_mac : WPCSW_MIN_BROWSER_VERSION); ?>" />
                            <span><?php esc_html_e('Min. Version', 'wp-copysafe-web'); ?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="<?php esc_attr_e('Allow visitors using Android to access this page.', 'wp-copysafe-web'); ?>" /></td>
                    <td><?php esc_html_e('Allow Android:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <div>
                            <input name="allow_android" type="checkbox" value="yes"<?php echo $allow_android == 'yes' ? ' checked' : ''; ?> />
                            <input type="text" size="8"
                                name="version_android"
                                placeholder="<?php echo esc_attr(WPCSW_MIN_BROWSER_VERSION); ?>"
                                value="<?php echo esc_attr($version_android ? $version_android : WPCSW_MIN_BROWSER_VERSION); ?>" />
                            <span><?php esc_html_e('Min. Version', 'wp-copysafe-web'); ?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="<?php esc_attr_e('Allow visitors using iOS to access this page.', 'wp-copysafe-web'); ?>" /></td>
                    <td><?php esc_html_e('Allow iOS:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <div>
                            <input name="allow_ios" type="checkbox" value="yes"<?php echo $allow_ios == 'yes' ? ' checked' : ''; ?> />
                            <input type="text" size="8"
                                name="version_ios"
                                placeholder="<?php echo esc_attr(WPCSW_MIN_BROWSER_VERSION); ?>"
                                value="<?php echo esc_attr($version_ios ? $version_ios : WPCSW_MIN_BROWSER_VERSION); ?>" />
                            <span><?php esc_html_e('Min. Version', 'wp-copysafe-web'); ?></span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="<?php esc_attr_e('Allow visitors using Linux OS to access this page.', 'wp-copysafe-web'); ?>" /></td>
                    <td><?php esc_html_e('Allow Linux:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <div>
                            <input name="allow_linux" type="checkbox" value="yes"<?php echo $allow_linux == 'yes' ? ' checked' : ''; ?> />
                            <input type="text" size="8"
                                name="version_linux"
                                placeholder="<?php echo esc_attr(WPCSW_MIN_BROWSER_VERSION); ?>"
                                value="<?php echo esc_attr($version_linux ? $version_linux : WPCSW_MIN_BROWSER_VERSION); ?>" />
                            <span><?php esc_html_e('Min. Version', 'wp-copysafe-web'); ?></span>
                        </div>
                    </td>
                </tr>
                <tr><td colspan="5"><hr /></td></tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="<?php esc_attr_e('Prevent viewing by remote or virtual computers when the class image loads.', 'wp-copysafe-web'); ?>" /></td>
                    <td><?php esc_html_e('Allow Remote:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <select name="allow_remote">
                            <option value="yes"><?php esc_html_e('Yes', 'wp-copysafe-web'); ?></option>
                            <option value="no"<?php echo $allow_remote == 'no' ? ' selected' : ''; ?>><?php esc_html_e('No', 'wp-copysafe-web'); ?></option>
                        </select>
                    </td>
                </tr>
                <?php
                /*
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr class="copysafe-section-title">
                    <td colspan="5"><h2 class="title"><?php esc_attr_e('Watermark Style Settings', 'wp-copysafe-web'); ?></h2></td>
                </tr>
                <tr><td colspan="5">&nbsp;</td></tr>
                <tr>
                    <td width="50">&nbsp;</td>
                    <td width="30">
                        <img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="Allow watermarking?">
                    </td>
                    <td><?php esc_attr_e('Enabled', 'wp-copysafe-web'); ?></td>
                    <td><input name="watermarked" type="checkbox"<?php echo esc_attr($watermarked); ?> /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="Text Size (in pixels)"></td>
                    <td><?php esc_html_e('Watermark Text Size (in pixels):', 'wp-copysafe-web'); ?></td>
                    <td>
                        <select name="wtmtextsize">
                            <?php foreach($wtm_text_size_options as $value) : ?>
                            <option value="<?php echo esc_attr($value); ?>"<?php echo $value == $wtmtextsize ? ' selected' : ''; ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="Watermark Text Color"></td>
                    <td><?php esc_html_e('Text Color:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <select name="wtmtextcolour">
                            <?php foreach($wtm_text_colour_options as $key => $value) : ?>
                            <option value="<?php echo esc_attr($key); ?>"<?php echo $key == $wtmtextcolour ? ' selected' : ''; ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="CSS code for Shade color for watermark."></td>
                    <td><?php esc_html_e('Shade Color:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <select name="wtmshadecolour">
                            <?php foreach($wtm_shade_colour_options as $key => $value) : ?>
                            <option value="<?php echo esc_attr($key); ?>"<?php echo $key == $wtmshadecolour ? ' selected' : ''; ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="Watermark Text Position"></td>
                    <td><?php esc_html_e('Text Position:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <select name="wtmtextposition">
                            <?php foreach($wtm_text_position_options as $key => $value) : ?>
                            <option value="<?php echo esc_attr($key); ?>"<?php echo $key == $wtmtextposition ? ' selected' : ''; ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td><img src="<?php echo esc_attr(WPCSW_PLUGIN_URL); ?>images/help-24-30.png" alt="Watermark Text Opacity"></td>
                    <td><?php esc_html_e('Opacity:', 'wp-copysafe-web'); ?></td>
                    <td>
                        <select name="wtmtextopacity">
                            <?php foreach($wtm_text_opacity_options as $key => $value) : ?>
                            <option value="<?php echo esc_attr($key); ?>"<?php echo $key == $wtmtextopacity ? ' selected' : ''; ?>><?php echo esc_html($value); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                */
                ?>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" value="<?php esc_attr_e('Save Settings', 'wp-copysafe-web'); ?>" class="button-primary" id="submit" name="submit">
            </p>
        </form>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <script type='text/javascript'>
      jQuery(document).ready(function () {
        jQuery("#wpcsw_page_setting img").click(function () {
          alert(jQuery(this).attr("alt"));
        });
      });
    </script>
  <?php
}