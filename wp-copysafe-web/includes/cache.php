<?php

class WPCSW_Cache {

	private $data;

	public function set($key, $value)
	{
		$this->data[$key] = $value;
	}

	public function get($key, $default = false)
	{
		if(isset($this->data[$key]))
		{
			return $this->data[$key];
		}

		return $default;
	}

	public function exists($key)
	{
		return isset($this->data[$key]);
	}

	public function delete($key)
	{
		if($this->exists($key))
		{
			unset($this->data[$key]);
		}
	}
}