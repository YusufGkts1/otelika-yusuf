<?php 

namespace model\common;

abstract class ValueObject extends ExceptionStore implements IComparable {

	public function equals($obj) : bool {
		if(null == $obj)
			return false;

		return $this->equalsTo($obj);
	}

	public abstract function equalsTo($obj) : bool;
}

?>