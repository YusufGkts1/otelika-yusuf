<?php

namespace model\common\infrastructure;

use model\common\domain\model\exception\UnclaimedFileNotFoundException;
use model\common\domain\model\IFileDescriptor;
use model\common\domain\model\IUnclaimedFileService;

class UnclaimedFileService implements IUnclaimedFileService {

	function __construct(
		private string $UNCLAIMED_FILE_DIR
	) {}

	public function exists(string $id) : bool {
		$files = $this->getFilesMatchingId($id);

		if(count($files) == 0)
			return false;
		else
			return true;
	}

	public function claim(string $id, string $path) : IFileDescriptor {
		if(!$this->exists($id))
			throw new UnclaimedFileNotFoundException($id);

		$files = $this->getFilesMatchingId($id);

		$split = explode('__', $files[0]);

		$name = $split[1];
		$extension = substr($this->name, strrpos($this->name, '.') + 1);

		return new FileDescriptor(
			$path,
			$name,
			$extension
		);
	}

	private function getFilesMatchingId(string $id) {
		return glob($this->UNCLAIMED_FILE_DIR . '*' . $id . "*");
	}
}

?>