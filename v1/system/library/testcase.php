<?php 

use PHPUnit\Framework\TestCase as PUnitTestCase;

use \model\system\log\Operator;
use \model\system\log\OperatorType;

class TestCase extends PUnitTestCase {
	protected $registry;

	public function __construct($registry) {
		$this->registry = $registry;

		parent::__construct();

		$this->setUpOnce();
	}

	public function __get($key) {
		return $this->registry->get($key);
	}

	public function __set($key, $value) {
		$this->registry->set($key, $value);
	}

	protected function setOperator(int $id, int $type) {
		$this->session->set('operator', new Operator(
			$type, $id
		));
	}

	protected function setUpOnce() {
		// can be overriden by subclass
	}

	/**
	 * Accessing a private/protected property to check if it
	 * has the correct value is against the pricinciples of
	 * unit testing. Sould only be used for edge cases.
	 */
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
}

?>