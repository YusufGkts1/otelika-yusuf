<?php 

namespace model\common;

class QueryObject {
    private array $order_by;
    private $limit;
    private $offset;
    private array /* QueryFilter[] */ $filters;
    private bool $and;  // whether filters should be ANDed or ORed

    function __construct($order_by=array(), $limit=25, $offset=0, $filters=array(), $and=false) {
        $this->order_by = $order_by;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->filters = $filters;
        $this->and = $and;
    }

    public function disablePagination() {
        $this->limit = 99999999;
        $this->offset = 0;
    }

    public function clearFilters() {
        $this->filters = array();
    }

    public function removeFilter($field) {
        foreach($this->filters as $k => $f) {
            if($f->field() == $field) {
                unset($this->filters[$k]);
                return;
            }
        }
    }

    public function orderBy() {
        return $this->order_by;
    }

    /**
     * setOrderBy
     *
     * @param  mixed $order_by EXAMPLE: ['0' => ['field1' => ASC], '1' => ['field2' => DESC]]
     * @return void
     */
    public function setOrderBy(array $order_by) {
        $this->order_by = $order_by;
    }

    public function limit() {
        return $this->limit;
    }

    public function setLimit(int $limit) {
        $this->limit = $limit;
    }

    public function offset() {
        return $this->offset;
    }

    public function setOffset(int $offset) {
        $this->offset = $offset;
    }

    /**
     * @return QueryFilter[]
     */
    public function filters() {
        return $this->filters;
    }

    public function setFilters(array $filters) {
        $this->filters = $filters;
    }

    public function addFilter(QueryFilter $filter) {
        $this->filters[] = $filter;
    }

    public function andFilters() : bool {
        return $this->and;
    }
}
?>