<?php 

namespace model\common\domain\model;

use model\common\IComparable;

class File implements IComparable{
	private FileId $id;
	private ?string $prefix;
	private string $base64;
	private string $name;
	private \DateTime $date_added;

	function __construct(FileId $id, string $base64, string $name, ?\DateTime $date_added) {
		$this->id = $id;
		$this->setName($name);
		$this->setBase64($base64);
		$this->setDateAdded($date_added);
	}

	public function id() : FileId {
		return $this->id;
	}

	public function name() : string {
		return $this->name;
	}

	private function setName(string $name) {
		$this->name = $name;
	}

	public function prefix() : ?string {
		return $this->prefix;
	}

	public function base64() : string {
		return $this->base64;
	}

	private function setBase64(string $base64) {
		$this->base64 = $base64;
		
		if(false === strpos($base64, ',')) {
			$this->base64 = $base64;
			$this->prefix = null;
		}
		else {
			$arr = explode(',', $base64);
			$this->prefix = reset($arr) . ',';
			$this->base64 = end($arr);
		}
	}
	
	public function dateAdded() : \DateTime {
		return $this->date_added;
	}

	private function setDateAdded(?\DateTime $date_added) {
		if(null === $date_added)
			$this->date_added = new \DateTime('now');
		else
			$this->date_added = $date_added;
	}

	public function mimeType() : string {
		$data = base64_decode($this->base64);
		$finfo = finfo_open();

		return finfo_buffer($finfo, $data, FILEINFO_MIME_TYPE);
	}

	public function extension() : ?string {
		if(false === strpos($this->name(), '.'))
			return null;
		else {
			$arr = explode('.', $this->name());
			return end($arr);
		}
	}

	public function rename(string $name) {
		$this->setName($name);
	}

	public function equals($obj): bool {
		if(null == $obj)
			return false;

		return $this->id()->equals($obj->id());
	}
}

?>