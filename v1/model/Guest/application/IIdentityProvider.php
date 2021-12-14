<?php 

namespace model\Guest\application;

interface IIdentityProvider {

	public function identity() : ?string;
}
?>