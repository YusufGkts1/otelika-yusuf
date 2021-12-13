<?php

use model\Guest\application\OrderManagementService;

use model\Guest\domain\model\IGuestRepository;
use model\Guest\domain\model\IModuleRepository;
use model\Guest\domain\model\IOrderRepository;
use model\Guest\domain\model\IProductRepostitory;
use model\Guest\infrastructure\OrderRepository;
use model\Guest\infrastructure\ModuleRepository;


class ModuleGuest extends Module {

	private array $map;

	protected function initialize() : void {
		$this->map = array(
            'OrderManagementService' => function() {
				return new OrderManagementService(
					$this->orders(),
                    $this->guests(),
                    $this->modules(),
                    $this->products()
				);
			},
        );
		

    }

    private function orders() : IOrderRepository {
		return new OrderRepository(
			$this->dbConnection()
		);
	}

    private function guests() : IGuestRepository {
		return new GuestRepository(
			$this->dbConnection()
		);
	}

    private function modules() : IModuleRepository {
		return new ModuleRepository(
			$this->dbConnection()
		);
	}

    private function products() : IProductRepostitory {
		return new ProductRepository(
			$this->dbConnection()
		);
	}

	private function dbConnection() : \DB {
		return new \DB(
			$this->config->get('db_otelika_type'),
            $this->config->get('db_otelika_hostname'),
            $this->config->get('db_otelika_username'),
            $this->config->get('db_otelika_password'),
            $this->config->get('db_otelika_database'),
            $this->config->get('db_otelika_port'),
			$this->uow
		);
	}


	protected function serviceProvider(string $identifier) : object {
        return $this->map[$identifier]();
    }

}

?>