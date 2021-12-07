<?php
/**
 * @package		Kant Framework
 * @author		Emirhan Yumak
 * @copyright	Copyright (c) 2016 - 2020, Kant Yazılım A.Ş. (https://kant.ist/)
 * @license		https://opensource.org/licenses/mit
 * @link		https://kant.ist
*/

/**
* Session class
*/
class Session {
	private array $data = array();

	public function get(string $key) {
		return $this->data[$key];
	}

	public function isset(string $key) {
		return isset($this->data[$key]);
	}

	public function set(string $key, $value) {
		$this->data[$key] = $value;
	}

	public function has(string $key) {
		return array_key_exists($key, $this->data);
	}
}