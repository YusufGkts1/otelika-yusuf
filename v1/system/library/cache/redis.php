<?php
namespace Cache;
class Redis {
	private $expire;
	private $cache;

	private $prefix;

	public function __construct($expire, $hostname, $port, $prefix) {
		$this->expire = $expire;

		$this->cache = new \Redis();
		$this->cache->pconnect($hostname, $port);

		$this->prefix = $prefix;
	}

	public function get($key) {
		$data = $this->cache->get($this->prefix . $key);

		if(null == $data)
			return null;

		return json_decode($data, true);
	}

	public function set($key, $value) {
		$status = $this->cache->set($this->prefix . $key, json_encode($value));

		if ($status) {
			$this->cache->expire($this->prefix . $key, $this->expire);
		}

		return $status;
	}

	public function delete($key) {
		$this->cache->delete($this->prefix . $key);
	}
}