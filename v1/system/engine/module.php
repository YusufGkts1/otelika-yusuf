<?php

/**
 * Module
 * 
 * Use this class to advertise a `Module` to the framework.
 * Modules have the ability to provide only a selected set
 * of services, authorize requests and encapsulate a domain
 * capability.
 * 
 * You can load modules using the `loader`.
 */
abstract class Module {
	protected $registry;

	public final function __construct($registry) {
        $this->registry = $registry;
        
        $this->initialize();
	}

	public final function __get($key) {
		return $this->registry->get($key);
	}

	public final function __set($key, $value) {
		$this->registry->set($key, $value);
    }

    public final function service(string $identifier) : object {
        return $this->serviceProvider($identifier);
    }

    // override this function for initialization operations
    protected function initialize() : void { }

    // provide requested service object using this method
    abstract protected function serviceProvider(string $identifier) : object;
}