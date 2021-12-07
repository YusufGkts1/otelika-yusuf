<?php
class Action {
	private $route;
	private $method = 'index';
	private $leftovers = array();

	public function __construct($route) {
		$parts = explode('/', preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route));

		// Break apart the route
		while ($parts) {
			$file = DIR_APPLICATION . 'controller/' . implode('/', $parts) . '.php';

			if (is_file($file)) {
				$this->route = implode('/', $parts);		
				
				break;
			} else {
				$part = array_pop($parts);
				array_unshift($this->leftovers, $part);
				$this->method = $part;
			}
		}
	}

	public function execute($registry, array $args = array()) {
		// Stop any magical methods being called
		if (substr($this->method, 0, 2) == '__') {
			return new \Exception('Error: Calls to magic methods are not allowed!');
		}

		$file = DIR_APPLICATION . 'controller/' . $this->route . '.php';		
		$class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $this->route);
		
		// Initialize the class
		if (is_file($file)) {
			include_once($file);
		
			$controller = new $class($registry);
		} else {
			return new Action('error/route');
		}
		
		$reflection = new ReflectionClass($class);
		
		if((false == $reflection->hasMethod($this->method) || $reflection->getMethod($this->method)->isPrivate()) && null != $this->leftovers)
			$this->method = 'index';
		else
			array_shift($this->leftovers);
		
		if($this->leftovers)
			$args = $this->leftovers;
			
		if ($reflection->hasMethod($this->method) && $reflection->getMethod($this->method)->getNumberOfRequiredParameters() <= count($args)) {
			return call_user_func_array(array($controller, $this->method), $args);
		} else {
			return new Action('error/route');
		}
	}
}
