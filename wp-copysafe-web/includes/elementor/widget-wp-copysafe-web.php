<?php defined('ABSPATH') or exit;

class WPCSW_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() {
		return 'wpcsw_widget';
	}

	public function get_title() {
		return esc_html__('Copysafe Web Protection', 'wp-copysafe-web');
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return ['basic'];
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$name = empty($settings['wpcsw_name']) ? '' : $settings['wpcsw_name'];
		$width = empty($settings['wpcsw_width']) ? '' : $settings['wpcsw_width'];
		$height = empty($settings['wpcsw_height']) ? '' : $settings['wpcsw_height'];
		$border = empty($settings['wpcsw_border']) ? '' : $settings['wpcsw_border'];
		$border_color = empty($settings['wpcsw_border_color']) ? '' : $settings['wpcsw_border_color'];
		$text_color = empty($settings['wpcsw_text_color']) ? '' : $settings['wpcsw_text_color'];
		$loading_message = empty($settings['wpcsw_loading_message']) ? '' : $settings['wpcsw_loading_message'];
		$target = empty($settings['wpcsw_target']) ? '' : $settings['wpcsw_target'];
		$hyperlink = empty($settings['wpcsw_hyperlink']) ? '' : $settings['wpcsw_hyperlink'];

		if(\Elementor\Plugin::$instance->editor->is_edit_mode())
		{
			?>
		<p><strong><?php echo esc_html__('Copysafe Web Protection', 'wp-copysafe-web'); ?></strong></p>
		<p>
		<?php echo esc_html('Name:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($name); ?></span><br />
		<?php echo esc_html('Width:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($width); ?></span><br />
		<?php echo esc_html('Height:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($height); ?></span><br />
		<?php echo esc_html('Border size:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($border); ?></span><br />
		<?php echo esc_html('Border color:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($border_color); ?></span><br />
		<?php echo esc_html('Text color:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($text_color); ?></span><br />
		<?php echo esc_html('Loading message:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($loading_message); ?></span><br />
		<?php echo esc_html('Target frame:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($target); ?></span><br />
		<?php echo esc_html('Hyperlink:', 'wp-copysafe-web'); ?> <span><?php echo esc_html($hyperlink); ?></span><br />
			<?php
		}
		else
		{
			if( ! empty($name))
			{
				?>
				[copysafe
					name="<?php echo esc_attr($name); ?>"
					width="<?php echo esc_attr($width); ?>"
					height="<?php echo esc_attr($height); ?>"
					border="<?php echo esc_attr($border); ?>"
					border_color="<?php echo esc_attr($border_color); ?>"
					text_color="<?php echo esc_attr($text_color); ?>"
					loading_message="<?php echo esc_attr($loading_message); ?>"
					hyperlink="<?php echo esc_attr($hyperlink); ?>"
					target="<?php echo esc_attr($target); ?>"]
				<?php
			}
		}
	}

	protected function content_template()
	{
		?>
		<p><strong><?php echo esc_html__('Copysafe Web Protection', 'wp-copysafe-web'); ?></strong></p>
		<p>
		<?php echo esc_html__('Name:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_name }}</span><br />
		<?php echo esc_html__('Width:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_width }}</span><br />
		<?php echo esc_html__('Height:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_height }}</span><br />
		<?php echo esc_html__('Border size:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_border }}</span><br />
		<?php echo esc_html__('Border color:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_border_color }}</span><br />
		<?php echo esc_html__('Text color:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_text_color }}</span><br />
		<?php echo esc_html__('Loading message:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_loading_message }}</span><br />
		<?php echo esc_html__('Target frame:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_target }}</span><br />
		<?php echo esc_html__('Hyperlink:', 'wp-copysafe-web'); ?> <span>{{ settings.wpcsw_hyperlink }}</span><br />
		<?php
	}

	protected function register_controls()
	{
		$this->start_controls_section(
			'selection_title',
			[
				'label' => esc_html__('Copysafe Web Protection', 'wp-copysafe-web'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'wpcsw_action',
			[
				'label' => esc_html__('File selection', 'wp-copysafe-web'),
				'text' => esc_html__('Select File', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::BUTTON,
				'event' => 'copysafe-web:editor:modal',
			]
		);

		$this->add_control(
			'wpcsw_name',
			[
				'label' => esc_html__('File ID', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => esc_html__('File ID', 'wp-copysafe-web'),
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_width',
			[
				'label' => esc_html__('Width', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_height',
			[
				'label' => esc_html__('Height', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_border',
			[
				'label' => esc_html__('Border size', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '0',
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_border_color',
			[
				'label' => esc_html__('Border color', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '000000',
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_text_color',
			[
				'label' => esc_html__('Text color', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'FFFFFF',
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_loading_message',
			[
				'label' => esc_html__('Loading message', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_target',
			[
				'label' => esc_html__('Target frame', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '_top',
				'ai' => false,
			]
		);

		$this->add_control(
			'wpcsw_hyperlink',
			[
				'label' => esc_html__('Hyperlink', 'wp-copysafe-web'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'ai' => false,
			]
		);

		$this->end_controls_section();
	}
}