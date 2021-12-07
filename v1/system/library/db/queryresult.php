<?php 

namespace DB;

class QueryResult {

	private ?array $data;
	private ?QueryMeta $meta;

	function __construct(?array $data, ?QueryMeta $meta) {
		$this->data = $data;
		$this->meta = $meta;
	}

	/**
	 * @return array return an array of arrays if this was a 'fetch' operation, a single array if this was a 'get' operation
	 */
	public function data() : ?array {
		return $this->data;
	}

	public function meta() : ?QueryMeta {
		return $this->meta;
	}

	public function isEmpty() : bool {
		return $this->data == null;
	}
}

?>