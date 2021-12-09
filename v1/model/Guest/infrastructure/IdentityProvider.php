<?php

namespace model\Guest\infrastructure;

use \model\Guest\application\IIdentityProvider;

class IdentityProvider implements IIdentityProvider {

	private ?string $id;

	function __construct(?string $id) {
		$this->id = $id;
	}

	public function identity() : ?string {
		return $this->id;
	}
}

?>