<?php 

namespace model\common\domain\model;

class FormData {

	/** @var string $data raw JSON string */
	private string $data;
	/** @var FormField[] $fields */
	private array $fields = array();
	/** @var FormFile[] $files */
	private ?array $files;

	function __construct(string $data, ?array $files) {
		$this->setData($data);
		$this->setFiles($files);
	}

	/* setter */

	private function setData(string $data) {
		try {
			$fields = json_decode($data);
		}
		catch(\Exception $e) {
			throw new InvalidFormDataException();
		}
		
		if(null != $fields)
			foreach($fields as $field)
				$this->fields[] = new FormField($field);

		$this->data = $data;
	}

	private function setFiles(?array $files) {
		$files = $files ?? array();

		if(null == $files)
			$this->files = array();
		else
			$this->files = $files;

		$required_file_fields = array_filter($this->fileFields(), function(FormField $field) {
			return $field->isRequired();
		});

		if(count($required_file_fields) != count($this->files))
			throw new InvalidFileCountException();

		$names_taken = array();

		/** @var FormFile $file */
		foreach($files as $file) {
			if(in_array($file->name(), $names_taken))
				throw new DuplicateFileNameException();

			$names_taken[] = $file->name();
		}
	}

	/* getter */

	public function data() : string {
		return $this->data;
	}

	/* domain */

	/**
	 * @return FormField[]
	 */
	public function fileFields() : array {
		$fields = array();

		foreach($this->fields as $field) {
			if($field->isFileField())
				$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * @return FormField[]
	 */
	public function fields() : array {
		return $this->fields;
	}

	/**
	 * @return FormFile[]
	 */
	public function files() : array {
		return $this->files;
	}
}

?>