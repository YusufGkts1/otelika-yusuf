<?php 

namespace DB;

class QueryObject {
    private array $order_by;
    private $limit;
    private $offset;
    private array $filters;
    private array $includes;
    private array $entity_includes;
    private $and_filters;

	/**
	 * @param QueryFilter[] $filters
     * @param QueryInclude[] $includes
     * @param array $entity_includes specifies which entities must be included in a simpler manner. ignored by DB adaptors, used by queryservice. ex: ['personnel.department', 'personnel.image']
     * @param bool $and_filters filters will be ORed by default, use this param to alter this behaviour
	 */
    function __construct($order_by=array(), $limit=25, $offset=0, $filters=array(), $includes=array(), $entity_includes=array(), $and_filters=false) {
        $this->order_by = $order_by;
        $this->limit = $limit;
        $this->offset = $offset;
		$this->filters = $filters;
        $this->includes = $includes;
        $this->entity_includes = $entity_includes;
        $this->and_filters = $and_filters;
    }

    public function disablePagination() {
        $this->limit = -1;
    }

    public function paginationIsEnabled() {
        return $this->limit != -1;
    }

    public function orderBy() {
        return $this->order_by;
    }

    public function clearFilters() {
        $this->filters = array();
    }

    public function andFilters() {
        return $this->and_filters;
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
        if($this->paginationIsEnabled() == false)
            throw new \Exception('Pagination is disabled');
        
        $this->limit = $limit;
    }

    public function offset() {
        return $this->offset;
    }

    public function setOffset(int $offset) {
        if($this->paginationIsEnabled() == false)
            throw new \Exception('Pagination is disabled');

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

    /**
     * @return QueryInclude[]
     */
    public function includes() {
        return $this->includes;
    }

    public function entityIncludes() {
        return $this->entity_includes;
    }

    /**
     * @param QueryInclude[] $includes
     */
    public function setIncludes(array $includes) {
        $this->includes = $includes;
    }
}
?>