<?php 

namespace uow;

interface UOWAware {

	public function begin();

	public function commit();

	public function rollback();

	/**
	 * @return null|string null if not objections, objection otherwise
	 */
	public function objection() : ?string;
}

?>