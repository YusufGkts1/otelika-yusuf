<?php 

namespace model\common;

use model\common\domain\model\DomainEvent;

abstract class Entity extends ExceptionStore {

	/** @var DomainEvent[] */
	protected array $events = array();

	private bool $removed = false;

	/**
	 * @return DomainEvent[]
	 */
	public function domainEvents() : array {
		return $this->events;
	}
	
	protected function addDomainEvent(DomainEvent $event) {
		$this->events[] = $event;
	}

	protected final function _remove() {
		$this->removed = true;
	}

	public function isRemoved() {
		return $this->removed;
	}
}

?>