<?php 

namespace model\common\domain\model\exception;

class UnclaimedFileNotFoundException extends \NotFoundException {
    protected $code = 808;

	function __construct($id) {
		parent::__construct("Unclaimed file with id '" . $id . "' is not found");
	}
}

?>
