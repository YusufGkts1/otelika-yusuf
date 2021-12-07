<?php
class Language {
	private $directory;
	private $data = array();

	public function __construct($directory) {
		$this->directory = $directory;
	}

	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : $key);
	}
	
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function load($filename) {
		$_ = array();

		$file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';

		if (is_file($file))
			require($file);

		$this->data = array_merge($this->data, $_);

		return $this->data;
	}
}