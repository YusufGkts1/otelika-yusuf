<?php

namespace model\system\log;

use \model\common\IOperationReporter;

class OperatorTypeNameProvider {
    private array $map = array(
        '1' => 'operation',
        '2' => 'personnel',
        '9' => 'not_found'
    );

    private array $map_reverse = array(
        'operation' => OperatorType::Operation,
        'personnel' => OperatorType::Personnel,
        'not_found' => OperatorType::NotFound
    );

    public function fromType(int $type) {
        return $this->map[$type];
    }

    public function fromName(string $name) {
        foreach($this->map_reverse as $key => $value) {
            if($key == $name)
                return $value;
        }
    }
}

class Operation {
    private ?int $id;
    private ?Node $source;
    private Node $result;
    private string $operation;
    private Node $operator;
    private ?int $module_id;
    private \DateTime $operation_date;

    function __construct(?int $id, ?Node $source, Node $result, string $operation, Node $operator, ?int $module_id, \DateTime $operation_date) {
        $this->id = $id;
        $this->source = $source;
        $this->result = $result;
        $this->operation = $operation;
        $this->operator = $operator;
        $this->module_id = $module_id;
        $this->operation_date = $operation_date;
    }

    public function id() : ?int {
        return $this->id;
    }

    public function source() : ?Node {
        return $this->source;
    }

    public function result() : Node {
        return $this->result;
    }

    public function operation() : string {
        return $this->operation;
    }

    public function operator() : Node {
        return $this->operator;
    }

    public function moduleId() : ?int {
        return $this->module_id;
    }

    public function operationDate() : \DateTime {
        return $this->operation_date;
    }
}

class Node {
    private ?int $node_id;
    private string $type;
    private string $id;
    private array $data;
    private int $version;

    function __construct(?int $node_id, string $type, string $id, array $data, int $version) {
        $this->node_id = $node_id;
        $this->type = $type;
        $this->id = $id;
        $this->data = $data;
        $this->version = $version;
    }

    public function nodeId() : ?int {
        return $this->node_id;
    }

    public function type() : string {
        return $this->type;
    }

    public function id() : string {
        return $this->id;
    }

    public function data() : array {
        return $this->data;
    }

    public function version() : int {
        return $this->version;
    }
}

class LoggingService implements IOperationReporter {

    private $session;

    function __construct() {
        global $framework;

        $this->session = $framework->get('session');

        $config = $framework->get('config');

        $this->db = new \DB(
            $config->get('db_system_type'),
            $config->get('db_system_hostname'),
            $config->get('db_system_username'),
            $config->get('db_system_password'),
            $config->get('db_system_database'),
            $config->get('db_system_port')
        );
    }

    /* api */

    public function getLastNOperators(int $module_id, int $limit) {
        $results = $this->db->query("SELECT operator, MAX(`id`) FROM `log_operation` WHERE module_id = :module_id GROUP BY operator ORDER BY MAX(id) DESC LIMIT " . (int)$limit, array(
            ':module_id' => $module_id
        ))->rows;

        $offset = $limit;

        $ret_arr = array();

        while($results != null && count($ret_arr) < $limit) {
            foreach($results as $result) {
                $node = $this->findNode($result['operator']);
                $node = $this->findMostUpToDateNode($node->type(), $node->id());

                if(false == key_exists($node->nodeId(), $ret_arr) && $node->type() == 'personnel') {
                    $ret_arr[$node->nodeId()] = array(
                        'id' => $node->id(), 
                        'attributes' => [
                            'firstname' => $node->data()['firstname'],
                            'lastname' => $node->data()['lastname'],
                            'image_id' => $node->data()['image_id'] ?? ''
                        ]
                    );
                }
            }

            $results = $this->db->query("SELECT operator, MAX(`id`) FROM `log_operation` WHERE module_id = :module_id GROUP BY operator ORDER BY MAX(id) DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset, array(
                ':module_id' => $module_id
            ))->rows;

            $offset += $limit;
        }

        $ret_arr_unindexed = array();

        foreach($ret_arr as $ret_val)
            $ret_arr_unindexed[] = $ret_val;

        return $ret_arr_unindexed;
    }

    public function getLastNOperations(int $n) {
        $operations = $this->fetchOperations($n);
    }

    public function getNodeLifecycle(int $node_id, int $max_depth) {
        $root_node = $this->findNode($node_id);

        $graph = [
            'node' => $this->generateGraph($root_node, 0, 0, $max_depth)
        ];

        return $graph;
    }
    
    /**
     * generateGraph
     *
     * @param  Node $node
     * @param  int $caller_node_id node'lar gereksiz yere birbirlerini referans gostermesin diye kullanilir.
     * @param  int $depth
     * @param  int $max_depth
     * @return array
     */
    private function generateGraph(Node $node, int $caller_node_id, int $depth, int $max_depth) : array {
        $result = array(
            'node_id' => $node->nodeId(),
            'type' => $node->type(),
            'id' => $node->id(),
            'data' => $node->data(),
            'version' => $node->version()
        );
        
        $prev = null;
        $next = null;

        if($depth < $max_depth) {
            /* previous node */

            $operation_resulting_in_node = $this->findOperationResultingIn($node->nodeId());

            if(null != $operation_resulting_in_node->source() && $operation_resulting_in_node->source()->nodeId() == $caller_node_id)
                $prev = 'loopback';
            else {
                $prev['connection'] = [
                    'operation' => $operation_resulting_in_node->operation(),
                    'operator' => [
                        'node' => [
                            'node_id' => $operation_resulting_in_node->operator()->nodeId(),
                            'type' => $operation_resulting_in_node->operator()->type(),
                            'id' => $operation_resulting_in_node->operator()->id(),
                            'data' => $operation_resulting_in_node->operator()->data(),
                            'version' => $operation_resulting_in_node->operator()->version()
                        ]
                    ]
                ];

                if(null != $operation_resulting_in_node->source()) 
                    $prev['node'] = $this->generateGraph($operation_resulting_in_node->source(), $node->nodeId(), $depth + 1, $max_depth);
                else
                    $prev['node'] = null;
            }

            /* next node */

            $operation_sourced_from_node = $this->findOperationSourcedFrom($node->nodeId());

            if(null != $operation_sourced_from_node) {
                if(null != $operation_sourced_from_node->result() && $operation_sourced_from_node->result()->nodeId() == $caller_node_id)
                    $next = 'loopback';
                else {
                    if(null != $operation_sourced_from_node) {
                        $next['connection'] = [
                            'operation' => $operation_sourced_from_node->operation(),
                            'operator' => [
                                'node' => [
                                    'node_id' => $operation_sourced_from_node->operator()->nodeId(),
                                    'type' => $operation_sourced_from_node->operator()->type(),
                                    'id' => $operation_sourced_from_node->operator()->id(),
                                    'data' => $operation_sourced_from_node->operator()->data(),
                                    'version' => $operation_sourced_from_node->operator()->version()
                                ]
                            ]
                        ];

                        if(null != $operation_sourced_from_node->result())
                            $next['node'] = $this->generateGraph($operation_sourced_from_node->result(), $node->nodeId(), $depth + 1, $max_depth);
                        else
                            $next['node'] = null;
                    }
                }
            }
        }
        else {
            $prev = 'max_depth_reached';
            $next = 'max_depth_reached';
        }

        $result['prev'] = $prev;
        $result['next'] = $next;

        return $result;
    }

    public function addOperation(string $operation, string $type, string $id, array $data, ?int $module_id = null) {
        $operator = $this->session->get('operator');

        $operator_type = $operator ? $operator->type() : OperatorType::NotFound;
        $operator_id = $operator ? $operator->id() : '0';

        $src_node = $this->findMostUpToDateNode($type, $id);
        $operator_node = $this->findMostUpToDateNode((new OperatorTypeNameProvider())->fromType($operator_type), $operator_id);

        // TODO: Bu buyuk ihtimalle sadece `root` kullanicinin operator oldugu eylemlerde calisicak. Daha iyi bir yontem?
        /* Operator bulunamadiysa bilgileri eksik operator ekle */
        if(null == $operator_node) {
            $node_id = $this->saveNode(new Node(
                null,
                (new OperatorTypeNameProvider())->fromType($operator_type),
                $operator_id,
                array(),
                1
            ));

            $operator_node = $this->findMostUpToDateNode(
                (new OperatorTypeNameProvider())->fromType($operator_type),
                $operator_id
            );
        }

        $operation = new Operation(
            null,
            $src_node,
            new Node(
                null,
                $type,
                $id,
                $data,
                null == $src_node ? 1 : $src_node->version() + 1
            ),
            $operation,
            $operator_node,
            $module_id,
            new \DateTime()
        );

        return $this->saveOperation($operation);
    }

    /* Operation */

    private function fetchOperations(int $limit) : array /* Operation[] */ {
        $operations = array();

        $operations_dbo = $this->db->query("SELECT * FROM log_operation ORDER BY operation_date DESC LIMIT " . (int)$limit)->rows;

        foreach($operations_dbo as $operation_dbo) {
            $source = $operation_dbo['source'] ? $this->findNode($operation_dbo['source']) : null;
            $result = $this->findNode($operation_dbo['result']);
            $operator = $this->findNode($operation_dbo['operator']);

            $operations[] = $this->operationFromDBO($operation_dbo);
        }

        return $operations;
    }

    private function findOperationResultingIn(int $node_id) : ?Operation {
        $operation_dbo = $this->db->query("SELECT * FROM log_operation WHERE result = :node_id", array(
            ':node_id' => $node_id
        ))->row;

        if(null == $operation_dbo)
            return null;

        return $this->operationFromDBO($operation_dbo);
    }

    private function findOperationSourcedFrom(int $node_id) : ?Operation {
        $operation_dbo = $this->db->query("SELECT * FROM log_operation WHERE source = :node_id", array(
            ':node_id' => $node_id
        ))->row;

        if(null == $operation_dbo)
            return null;

        return $this->operationFromDBO($operation_dbo);
    }

    private function saveOperation(Operation $operation) : int {
        $source_node_id = $operation->source() ? $operation->source()->nodeId() : null;
        $result_node_id = $this->saveNode($operation->result());
        $operator_node_id = $operation->operator()->nodeId();

        $this->db->command("INSERT INTO log_operation SET source = :source, result = :result, operation = :operation, operator = :operator, module_id = :module_id, operation_date = NOW()", array(
            ':source' => $source_node_id,
            ':result' => $result_node_id,
            ':operation' => $operation->operation(),
            ':operator' => $operator_node_id,
            ':module_id' => $operation->moduleId()
        ));

        return $this->db->getLastId();
    }

    private function operationFromDBO(array $operation_dbo) : Operation {
        return new Operation(
            $operation_dbo['id'],
            $operation_dbo['source'] ? $this->findNode($operation_dbo['source']) : null,
            $this->findNode($operation_dbo['result']),
            $operation_dbo['operation'],
            $this->findNode($operation_dbo['operator']),
            $operation_dbo['module_id'],
            new \DateTime($operation_dbo['operation_date'])
        );
    }

    /* Node */

    private function findNode(int $node_id) : ?Node {
        $node_dbo = $this->db->query("SELECT * FROM log_node WHERE node_id = :node_id", array(
            ':node_id' => $node_id
        ))->row;

        if(null == $node_dbo)
            return null;

        return $this->nodeFromDBO($node_dbo);
    }

    private function findSourceNode(int $node_id) : ?Node {
        $node_dbo = $this->db->query("SELECT * FROM log_node WHERE `node_id` IN (SELECT source FROM log_operation WHERE result = :node_id)", array(
            ':node_id' => $node_id
        ))->row;

        if(null == $node_dbo)
            return null;

        return $this->nodeFromDBO($node_dbo);
    }

    private function findResultNode(int $node_id) : ?Node {
        $node_dbo = $this->db->query("SELECT * FROM log_node WHERE `node_id` IN (SELECT result FROM log_operation WHERE source = :node_id)", array(
            ':node_id' => $node_id
        ))->row;

        if(null == $node_dbo)
            return null;

        return $this->nodeFromDBO($node_dbo);
    }

    private function findMostUpToDateNode(string $type, string $id) : ?Node {
        $node_dbo = $this->db->query("SELECT * FROM log_node WHERE `type` = :type AND `id` = :id ORDER BY version DESC LIMIT 1", array(
            ':type' => $type,
            ':id' => $id
        ))->row;

        if(null == $node_dbo)
            return null;

        return $this->nodeFromDBO($node_dbo);
    }

    private function saveNode(Node $node) : int {
        $this->db->command("INSERT INTO log_node SET `type` = :type, `id` = :id, `data` = :data, `version` = :version", array(
            ':type' => $node->type(),
            ':id' => $node->id(),
            ':data' => json_encode($node->data()),
            ':version' => $node->version()
        ));

        return $this->db->getLastId();
    }

    private function nodeFromDBO(array $node_dbo) : Node {
        return new Node(
            $node_dbo['node_id'],
            $node_dbo['type'],
            $node_dbo['id'],
            (array)json_decode($node_dbo['data']),
            $node_dbo['version']
        );
    }
}

?>