<?php 

namespace model\common\infrastructure;

use model\common\application\IFileLocator;
use model\common\domain\model\FileId;

class FileLocator implements IFileLocator {

	private string $dir_root;
	
	function __construct(string $dir_root) {
		$this->dir_root = $dir_root;
	}

	public function locate(FileId $id, string $subpath, ?string $extension): string {
		$full_dir = $this->dir_root . $subpath;

		// if subpath is provided and does not end 
		// with a forward slash, add a forward slash
		if($subpath && substr($subpath, -1) != '/')
			$full_dir .= '/';

		if(false == file_exists($full_dir))
			mkdir($full_dir, 0755, true);
			
		$path = $full_dir . $id->getId();

		if($extension)
			$path .= '.' . $extension;

		return $path;
	}
}

?>