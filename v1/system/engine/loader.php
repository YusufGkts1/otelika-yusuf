<?php
final class Loader {
	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;
	}
	
	public function controller($route, $data = array()) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('controller/' . $route . '/before', array(&$route, &$data));
		
		if ($result) {
			return $result;
		}
		
		$action = new Action($route);
		$output = $action->execute($this->registry, array(&$data));
			
		// Trigger the post events
		$result = $this->registry->get('event')->trigger('controller/' . $route . '/after', array(&$route, &$data, &$output));
		
		if (!($output instanceof Exception)) {
			return $output;
		} else {
			return false;
		}
	}
	
	public function model($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		$file  = DIR_APPLICATION . 'model/' . $route . '.php';
		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $route);
		
		if (is_file($file)) {
			include_once($file);
			
			$proxy = new Proxy();

			foreach (get_class_methods($class) as $method) {
				$proxy->{$method} = $this->callback($this->registry, $route . '/' . $method);
			}

			$this->registry->set('model_' . str_replace(array('/', '-', '.'), array('_', '', ''), (string)$route), $proxy);
		} else {
			throw new \Exception('Error: Could not load model ' . $route . '!');
		}
	}
	
	/**
	 * returns module reference if $return 
	 * is set to true; puts module reference 
	 * into registry otherwise
	 *
	 * @param  mixed $route
	 * @param  mixed $return
	 * @return mixed
	 */
	public function module($route, $return = false) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);

		$file  = DIR_APPLICATION . 'model/' . $route . '/Module.php';
		$class = 'Module' . preg_replace('/[^a-zA-Z0-9]/', '', $route);

		if (is_file($file)) {
			include_once($file);
			//echo $class;
			$proxy = new Proxy();

			foreach (get_class_methods($class) as $method) {
				$proxy->{$method} = $this->moduleCallback($this->registry, $route . '/' . $method);
			}

			$to_snake_case = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $route));

			if($return)
				return $proxy;
			else
				$this->registry->set('module_' . str_replace(array('/', '-', '.'), array('_', '', ''), $to_snake_case), $proxy);
		}
		else
			throw new \Exception('Error: Could not load module ' . $route . '!');
	}

	public function view($route, $data = array()) {
		// Sanitize the call
		$route = str_replace('../', '', (string)$route);
		
		// Trigger the pre events
		$result = $this->registry->get('event')->trigger('view/' . $route . '/before', array(&$route, &$data));
		
		if ($result) {
			return $result;
		}
		
		$template = new Template('basic');
		
		foreach ($data as $key => $value) {
			$template->set($key, $value);
		}
		
		$output = $template->render($route . '.tpl');
		
		// Trigger the post e
		$result = $this->registry->get('event')->trigger('view/' . $route . '/after', array(&$route, &$data, &$output));
		
		if ($result) {
			return $result;
		}
		
		return $output;
	}

	public function library($route) {
		// Sanitize the call
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
			
		$file = DIR_SYSTEM . 'library/' . $route . '.php';
		$class = str_replace('/', '\\', $route);

		if (is_file($file)) {
			include_once($file);

			$this->registry->set(basename($route), new $class($this->registry));
		} else {
			throw new \Exception('Error: Could not load library ' . $route . '!');
		}
	}
	
	public function helper($route) {
		$file = DIR_SYSTEM . 'helper/' . str_replace('../', '', (string)$route) . '.php';

		if (is_file($file)) {
			include_once($file);
		} else {
			throw new \Exception('Error: Could not load helper ' . $route . '!');
		}
	}
	
	public function config($route) {
		$this->registry->get('event')->trigger('config/' . $route . '/before', $route);
		
		$this->registry->get('config')->load($route);
		
		$this->registry->get('event')->trigger('config/' . $route . '/after', $route);
	}

	public function language($route) {
		$this->registry->get('event')->trigger('language/' . $route . '/before', $route);
		
		$output = $this->registry->get('language')->load($route);
		
		$this->registry->get('event')->trigger('language/' . $route . '/after', $route);
		
		return $output;
	}

	protected function moduleCallback($registry, $route) {
		return function($args) use($registry, &$route) {
			// Trigger the pre events
			$result = $registry->get('event')->trigger('module/' . $route . '/before', array_merge(array(&$route), $args));

			if($result)
				return $result;

			$file = DIR_APPLICATION . 'model/' .  substr($route, 0, strrpos($route, '/')) . '/Module.php';
			$class = 'Module' . preg_replace('/[^a-zA-Z0-9]/', '', substr($route, 0, strrpos($route, '/')));
			$method = substr($route, strrpos($route, '/') + 1);

			if(is_file($file)) {
				include_once($file);

				$module = new $class($registry);
			}
			else
				throw new \Exception('Error: Could not load module ' . substr($route, 0, strrpos($route, '/')) . '!');

			if(method_exists($module, $method))
				$output = call_user_func_array(array($module, $method), $args);
			else
				throw new \Exception('Error: Could not call module/' . $route . '!');

			// Trigger the post events
			$result = $registry->get('event')->trigger('module/' . $route . '/after', array_merge(array(&$route, &$output), $args));
			
			if($result)
				return $result;

			return $output;
		};
	}

	protected function callback($registry, $route) {
		return function($args) use($registry, &$route) {			
			// Trigger the pre events
			$result = $registry->get('event')->trigger('model/' . $route . '/before', array_merge(array(&$route), $args));

			if ($result) {
				return $result;
			}

			$file = DIR_APPLICATION . 'model/' .  substr($route, 0, strrpos($route, '/')) . '.php';
			$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', substr($route, 0, strrpos($route, '/')));
			$method = substr($route, strrpos($route, '/') + 1);

			if (is_file($file)) {
				include_once($file);

				$model = new $class($registry);
			} else {
				throw new \Exception('Error: Could not load model ' . substr($route, 0, strrpos($route, '/')) . '!');
			}

			if (method_exists($model, $method)) {
				$output = call_user_func_array(array($model, $method), $args);
			} else {
				throw new \Exception('Error: Could not call model/' . $route . '!');
			}

			// Trigger the post events
			$result = $registry->get('event')->trigger('model/' . $route . '/after', array_merge(array(&$route, &$output), $args));
			
			if ($result) {
				return $result;
			}

			return $output;
		};
	}	
}