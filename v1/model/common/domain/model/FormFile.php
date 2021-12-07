<?php 

namespace model\common\domain\model;

class FormFile {
	
	private string $name;
	private string $type;
	private string $tmp_name;
	private string $error;
	private string $size;

	function __construct(string $name, string $type, string $tmp_name, string $error, string $size) {
		$this->name = $name;
		$this->type = $type;
		$this->tmp_name = $tmp_name;
		$this->error = $error;
		$this->size = $size;
	}

	/* getter */

	public function name() : string {
		return $this->name;
	}

	public function type() : string {
		return $this->type;
	}

	public function tmpName() : string {
		return $this->tmp_name;
	}

	public function error() : string {
		return $this->error;
	}

	public function size() : string {
		return $this->size;
	}
}

?>