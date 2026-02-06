<?php

class WPCSW_Data {

	public function getWatermarkPositions()
	{
		$options = [
			'Center' => __('Center', 'wp-copysafe-web'),
			'Bottom' => __('Bottom', 'wp-copysafe-web'),
			'Top' => __('Top', 'wp-copysafe-web'),
			'TopLeft' => __('Top Left', 'wp-copysafe-web'),
			'TopRight' => __('Top Right', 'wp-copysafe-web'),
			'BottomLeft' => __('Bottom Left', 'wp-copysafe-web'),
			'BottomRight' => __('Bottom Right', 'wp-copysafe-web'),
			'Rotating' => __('Rotating', 'wp-copysafe-web'),
			'Blinking' => __('Blinking', 'wp-copysafe-web'),
			'Randomg' => __('Random', 'wp-copysafe-web'),
		];

		return $options;
	}

	public function getWatermarkColors()
	{
		$options = [
			'#FFFFFF' => __('White', 'wp-copysafe-web'),
			'#FF3333' => __('Red', 'wp-copysafe-web'),
			'#FFFF00' => __('Yellow', 'wp-copysafe-web'),
			'#00FF00' => __('Green', 'wp-copysafe-web'),
			'#00FFFF' => __('Blue', 'wp-copysafe-web'),
		];

		return $options;
	}

	public function getWatermarkShades()
	{
		$options = [
			'#999999' => __('Grey', 'wp-copysafe-web'),
			'#FFFFFF' => __('White', 'wp-copysafe-web'),
			'#000000' => __('Black', 'wp-copysafe-web'),
			'#FF3333' => __('Red', 'wp-copysafe-web'),
			'#FFFF00' => __('Yellow', 'wp-copysafe-web'),
			'#00FF00' => __('Green', 'wp-copysafe-web'),
			'#00FFCC' => __('Blue', 'wp-copysafe-web'),
		];

		return $options;
	}

	public function getWatermarkTextSizes()
	{
		$options = [
			'10px',
			'15px',
			'20px',
			'25px',
			'30px',
			'35px',
			'40px',
		];

		return $options;
	}

	public function getWatermarkOpacities()
	{
		$options = [
			'1' => __('100% (opaque)', 'wp-copysafe-web'),
			'.9' => __('90%', 'wp-copysafe-web'),
			'.8' => __('80%', 'wp-copysafe-web'),
			'.7' => __('70%', 'wp-copysafe-web'),
			'.6' => __('60%', 'wp-copysafe-web'),
			'.5' => __('50%', 'wp-copysafe-web'),
			'.4' => __('40%', 'wp-copysafe-web'),
			'.3' => __('30%', 'wp-copysafe-web'),
			'.2' => __('20%', 'wp-copysafe-web'),
			'.1' => __('10%', 'wp-copysafe-web'),
		];

		return $options;
	}

	public function getWatermarkPosition($position)
	{
		$default = 'Top';
		$positions = $this->getWatermarkPositions();

		return isset($positions[$position]) ? $position : $default;
	}

	public function getWatermarkColor($color)
	{
		$default = '#00FFFF';
		$colors = $this->getWatermarkColors();

		return isset($colors[$color]) ? $color : $default;
	}

	public function getWatermarkShade($color)
	{
		$default = '#FFFFFF';
		$shades = $this->getWatermarkShades();

		return isset($shades[$color]) ? $color : $default;
	}

	public function getWatermarkTextSize($size)
	{
		$default = '10px';
		$sizes = $this->getWatermarkTextSizes();

		return in_array($size, $sizes) ? $size : $default;
	}

	public function getWatermarkOpacity($opacity)
	{
		$default = 1;
		$opacities = $this->getWatermarkOpacities();

		return isset($opacities[$opacity]) ? $opacities[$opacity] : $default;
	}
}