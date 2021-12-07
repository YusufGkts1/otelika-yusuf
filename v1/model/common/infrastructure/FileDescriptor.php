<?php

namespace model\common\infrastructure;

use model\common\domain\model\IFileDescriptor;

class FileDescriptor implements IFileDescriptor {

	function __construct(
		private string $path,
		private string $name,
		private string $extension
	) {}

	public function path() : string {
		return $this->path;
	}

	public function name() : string {
		return $this->name;
	}

	public function extension(): string {
		return $this->extension;
	}
}


?>