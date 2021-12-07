<?php 

namespace model\common;

interface IOperationReporter {
    
    public function getLastNOperators(int $module_id, int $limit);

    public function addOperation(string $operation, string $type, string $id, array $data, ?int $module_id = null);
}

?>