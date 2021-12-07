<?php 

namespace DB;

class QueryMeta implements \JsonSerializable {
    private int $current_page;
    private int $total_pages;
    private int $total_count;

    function __construct(int $current_page, int $total_pages, int $total_count) {
        $this->current_page = $current_page;
        $this->total_pages = $total_pages;
        $this->total_count = $total_count;
    }

    public function currentPage() : int {
        return $this->current_page;
    }

    public function totalPages() : int {
        return $this->total_pages;
    }

    public function totalCount() : int {
        return $this->total_count;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}

?>