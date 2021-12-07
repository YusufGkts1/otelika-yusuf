<?php

namespace DB;

class QueryFilter {

    private $operators = [
        'cont' => 'LIKE',
        'eq' => '=',
        'ne' => '!=',
        'gt' => '>',
        'ge' => '>=',
        'lt' => '<',
        'le' => '<='
    ];

    private string $field;
    private string $operator;
    private string $value;

    function __construct(string $field, string $operator, string $value) {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function field() : string {
        return $this->field;
    }

    public function operator() : string {
        return $this->operators[$this->operator];
    }

    public function value(bool $add_symbols=true) : string {
        if($this->operator == 'cont' && $add_symbols)
            return '%' . $this->value . '%';
        else
            return $this->value;
    }
}

?>