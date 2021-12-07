<?php 

namespace model\common;

class ExceptionStore {
	/** @var \Exception[] $exceptions */
	public array $exceptions = array();

	/**
	 * @return \Exception[]
	 */
	public function exceptions() {
		$exceptions = $this->exceptions;

		$reflect = new \ReflectionObject($this);

		$props = $reflect->getProperties();

		foreach($props as $prop) {
			$prop->setAccessible(true);
			if(!$prop->gettype())
			continue;
			
			$type = $prop->getType()->getName();  // "getName" methodunun altini cizebilir. Dokumante edilmedigi icin ciziyor, gercekten oyle bir method var. Yani hata yoksayilabilir


			if('array' == $type) {
				foreach($prop->getValue($this) as $i) {
					if($i instanceof ExceptionStore)
						$exceptions = array_merge($exceptions, $i->exceptions());
				}
			}
			else {
				if(false == class_exists($type))
					continue;

				if((new \ReflectionClass($type))->isSubclassOf(ExceptionStore::class)) {
					$value = $prop->getValue($this);

					if(null != $value) {
						# TODO: ...
						if(!$value->exceptions())
							$exceptions = array_merge($exceptions, $value->exceptions);
						else
							$exceptions = array_merge($exceptions, $value->exceptions());
					}
				}
			}
		}

		return $exceptions;
	}

	protected function addException(\Exception $exception) {
		$this->exceptions[] = $exception;
	}
}

?>