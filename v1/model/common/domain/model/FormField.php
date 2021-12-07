<?php 

namespace model\common\domain\model;

class FormField {

	private object $data;
	private FormFieldType $type;
	private bool $is_required;
	
	function __construct(object $data) {
		$this->parse($data);
	}

	/* setter */

	private function parse(object $data) {
		$this->data = $data;

		/* required */

		if(property_exists($data->config, 'required') && true == filter_var($data->config->required, FILTER_VALIDATE_BOOLEAN))
			$this->is_required = true;
		else
			$this->is_required = false;

		/* type */

		$field_type_map = array(
			'input' => FormFieldType::Input(),
			'file' => FormFieldType::File(),
			'image' => FormFieldType::File(),
			'checkboxes' => FormFieldType::Checkboxes(),
			'mutlipleChoices' => FormFieldType::MultipleChoices(),
			'textarea' => FormFieldType::Textarea(),
			'matrix' => FormFieldType::Matrix(),
			'chooseFromList' => FormFieldType::ChooseFromList()
		);

		if(false == in_array($data->type, array_keys($field_type_map)))
			throw new InvalidFieldTypeException();

		$this->type = new FormFieldType($field_type_map[$data->type]);

		/* validate */

		if($this->isRequired()) {
			switch($this->type) {
				case FormFieldType::Other():
					// pass
					break;
				case FormFieldType::Input():
				case FormFieldType::MultipleChoices():
				case FormFieldType::Textarea():
				case FormFieldType::ChooseFromList():
					if(null == $data->value)
						throw new EmptyRequiredFieldException();

					break;
				case FormFieldType::File():
					if(null == $data->file->name)
						throw new EmptyRequiredFieldException();

					break;
				case FormFieldType::Checkboxes():
					$found = false;

					foreach($data->options as $option) 
						if(filter_var($option->selected, FILTER_VALIDATE_BOOLEAN))
							$found = true;

					if(false == $found)
						throw new EmptyRequiredFieldException();
				
					break;
				case FormFieldType::Matrix():
					foreach($data->config->rows as $row)
						if(null == $row->selected)
							throw new EmptyRequiredFieldException();
							
					break;
			}
		}
	}

	/* domain */

	public function isRequired() : bool {
		return $this->is_required;
	}

	public function isFileField() : bool {
		return $this->type == FormFieldType::File();
	}
}

?>