<?php 

namespace model\common;

abstract class Repository implements IPersistenceProvider {

	protected function getProperty($obj, string $prop_path) {
		if(null == $obj)
			return null;

		$split = explode('.', $prop_path);

		$prop = new \ReflectionProperty($obj, $split[0]);

		$prop->setAccessible(true);

		$value = $prop->getValue($obj);

		if(count($split) > 1) {
			if(is_array($value)) {
				$ret = [];

				foreach($value as $v) {
					$ret[] = $this->getProperty($v, implode('.', array_slice($split, 1)));
				}

				return $ret;
			}
			else
				return $this->getProperty($value, implode('.', array_slice($split, 1)));
		}
		else
			return $value;
	}

	// TODO: Should work recursively
	protected function toArray($obj) {
		if(null == $obj)
			return null;

		$props = (new \ReflectionObject($obj))->getProperties();

		$arr = [];

		foreach($props as $prop) {
			$prop->setAccessible(true);

			if($prop->isInitialized($obj))
				$arr[$prop->getName()] = $prop->getValue($obj);
			else
				$arr[$prop->getName()] = null;
		}

		return $arr;
	}

	// TODO: Should work recursively

	/**
	 * Constructur arguments of the object to be created must be of primitive types
	 */
	protected function fromArray(array $params, string $class) {
		return new $class(...$params);
	}
}

?>