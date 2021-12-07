<?php 

namespace model\common\application;

use model\common\domain\model\FileId;

interface IFileLocator {
	public function locate(FileId $id, string $subpath, ?string $extension) : string;
}

?>