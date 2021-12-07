<?php

use uow\UOWAware;

/**
 * Test
 * 		Iki kez begin-sonuc yapilabilmeli
 * 		Zaten begin yapildiysa tekrar begin yapilamamali
 * 		Eger commit yapilmadiysa default olarak rollback yapilmali
 * 		Eger commit yapildiysa destructor icerisindeki rollback calismamali
 * 		commit yapildiginda tum uowaware nesnelerde commit yapilmali
 * 		rollback yapildiginda tum uowaware nesnelerde rollback yapilmali
 * 		zaten begin yapildiysa yeni bir uowaware nesne eklendiginde bu nesnede otomatik begin yapilmali
 * 		eger begin yapilmadiysa uowaware nesnelerin commit veya rollback methodlari cagrilmamali
 * 		bu testlerin hepsi 2 kez arka arkaya calistirildiginda gecmeli
 */
class UOW {
	/**
	 * Unit Of Work
	 */
	 
	/**
	 * @var UOWAware[] $uow
	 */
	private array $uow;
	private bool $committed;
	private bool $began;
	private bool $rollback_on_objection;
	private ?Logger $logger;

	function __construct(bool $rollback_on_objection = true, ?Logger $logger = null) {
		$this->committed = false;
		$this->began = false;
		$this->uow = array();
		$this->rollback_on_objection = $rollback_on_objection;
		$this->logger = $logger;
	}

	public function add(UOWAware $obj) {
		if(true == $this->began) {
			$obj->begin();
		}

		$this->uow[] = $obj;
	}

	public function begin() {
		if(true == $this->began)
			return;

		foreach($this->uow as $uow)
			$uow->begin();

		$this->began = true;
		$this->committed = false;
	}

	public function commit() {
		if(false == $this->began)
			return;

		foreach($this->uow as $uow) {
			$objection = $uow->objection();

			if(null !== $objection) {
				if($this->logger)
					$this->logger->log('UOWAware class had an objection to commit: ' . $objection);

				if($this->rollback_on_objection) {
					$this->rollback();

					throw new InternalError('An error occurred while processing the transaction (UOW)');
				}
			}
		}

		foreach($this->uow as $uow) {
			$uow->commit();
		}

		$this->committed = true;
		$this->began = false;
	}

	public function rollback() {
		if(false == $this->began)
			return;

		foreach($this->uow as $uow)
			$uow->rollback();

		$this->began = false;
	}

	public function inProgress() : bool {
		return $this->began;
	}

	function __destruct() {
		if(false == $this->committed)
			$this->rollback();
    }
}

?>