<?php 

namespace model\common\domain\model;

use MyCLabs\Enum\Enum;

class FormFieldType extends enum {
	const Other = 1;
	const Input = 2;
	const File = 3;
	const Checkboxes = 4;
	const MultipleChoices = 5;
	const Textarea = 6;
	const Matrix = 7;
	const ChooseFromList = 8;
}

?>