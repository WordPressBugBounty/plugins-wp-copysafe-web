<?php

class WPCSW_Gutenberg {

	public function __construct()
	{
		add_action('init', [$this, 'add_block']);
	}

	public function add_block()
	{
		register_block_type(__DIR__ . '/build');
	}
}