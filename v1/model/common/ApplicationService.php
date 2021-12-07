<?php 

namespace model\common;

use model\common\domain\model\DomainEvent;
use model\common\domain\model\DomainEventPublisher;

abstract class ApplicationService {

	private array $exceptions = array();
	private array $domain_events = array();

	public function addException(\Exception $exception) {
		$this->exceptions[] = $exception;
	}

	public function addDomainEvent(DomainEvent $domain_event) {
		$this->domain_events[] = $domain_event;
	}

	public function process(Entity $entity, IPersistenceProvider $persistence_provider) {
		$exceptions = array_merge($this->exceptions, $entity->exceptions());

		if(count($exceptions) > 0)
			throw new ExceptionCollection($exceptions);

		$retval = $persistence_provider->save($entity);

		$events = array_merge($this->domain_events, $entity->domainEvents());

		foreach($events as $event)
			DomainEventPublisher::instance()->publish($event);

		return $retval;
	}
}
