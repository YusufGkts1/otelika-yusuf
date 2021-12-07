<?php 

namespace DB;

class QueryInclude {

	private string $table_left;
	private string $table_right;
	private string $field_left;
	private string $field_right;
	private ?QueryInclude $include;

	/**
	 * @param string $table_left MUST be entity name if this object is going to be used by a queryservice, table name otherwise
	 * @param string $table_right MUST be entity name if this object is going to be used by a queryservice, table name otherwise
	 * @param string|null $field_left MUST be null if this object is going to be used by a queryservice, string otherwise
	 * @param string|null $field_right MUST be null if this object is going to be used by a queryservice, string otherwise
	 * @param QueryInclude $include an inclusion can contain one other inclusion
	 */
	function __construct(string $table_left, string $table_right, string $field_left, string $field_right, ?QueryInclude $include=null) {
		$this->table_left = $table_left;
		$this->table_right = $table_right;
		$this->field_left = $field_left;
		$this->field_right = $field_right;
		$this->include = $include;
	}

	public function hasSubInclude() : bool {
		return $this->include != null;
	}

	public function tableLeft() : string {
		return $this->table_left;
	}

	public function tableRight() : string {
		return $this->table_right;
	}

	public function fieldLeft() : string {
		return $this->field_left;
	}

	public function fieldRight() : string {
		return $this->field_right;
	}

	public function include() : ?QueryInclude {
		return $this->include;
	}

	public function equals(QueryInclude $include) {
		$equals = $this->tableLeft() == $include->tableLeft() &&
					$this->tableRight() == $include->tableRight() &&
					$this->fieldLeft() == $include->fieldLeft() &&
					$this->fieldRight() == $include->fieldRight();

		return $equals;
	}

	public function setInclude(QueryInclude $include) {
		$this->include = $include;
	}

	public function prettyPrint() : string {
		$rows = $this->_prettyPrint();
		
		return implode(PHP_EOL, $rows);
	}

	protected function _prettyPrint($indent = 0) : array {
		$rows = [];
		$rows[] = str_repeat(' ', $indent) . 'TABLE left: ' . $this->table_left;
		$rows[] = str_repeat(' ', $indent) . 'TABLE right: ' . $this->table_right;
		$rows[] = str_repeat(' ', $indent) . 'FIELD left: ' . $this->field_left;
		$rows[] = str_repeat(' ', $indent) . 'FIELD right: ' . $this->field_right;
		
		if($this->include) {
			$inner_rows = $this->include->_prettyPrint($indent + 6);

			$rows = array_merge($rows, $inner_rows);
		}

		return $rows;
	}
}

?>