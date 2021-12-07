<?php 

namespace model\common;

interface IPersistenceProvider {

	public function save(Entity $entity);
}

?>