<?php

use \model\common\domain\model\DomainEventDispatcher;

use \model\common\domain\model\IModuleRepository;
use \model\common\domain\model\ISubmoduleRepository;

use \model\common\application\SubmoduleService;
use \model\common\infrastructure\SubmoduleRepository;
use \model\common\infrastructure\ModuleRepository;

class ModuleCommon extends Module {

	private array $map;

	protected function initialize() : void {
		$this->map = array (
			'Submodule' => function() {
				return new SubmoduleService(
					$this->submoduleRepository(),
					$this->moduleRepository()
				);
			},
			'DomainEventDispatcher' => function() {
				return new DomainEventDispatcher(
					$this->eventStoreConnection(),
					$this->load,
					$this->session,
					$this->uow
				);
			}
		);
	}

	public function serviceProvider(string $identifier) : object {
		return $this->map[$identifier]();
	}

	private function submoduleRepository() : ISubmoduleRepository {
		return new SubmoduleRepository();
	}

	private function moduleRepository() : IModuleRepository {
		return new ModuleRepository();
	}

	private function eventStoreConnection() : \DB {
		return new \DB(
            $this->config->get('db_event_type'),
            $this->config->get('db_event_hostname'),
            $this->config->get('db_event_username'),
            $this->config->get('db_event_password'),
            $this->config->get('db_event_database'),
            $this->config->get('db_event_port')
        );
	}
}

?>