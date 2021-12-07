<?php 

namespace model\common\infrastructure;

use \model\common\domain\model\IModuleRepository;
use \model\common\domain\model\ModuleId;
use \model\common\domain\model\Module;

class ModuleRepository implements IModuleRepository {
    function __construct(){ 
        global $framework;
        $config = $framework->get('config');

        $this->db = new \DB(
            $config->get('db_common_type'),
            $config->get('db_common_hostname'),
            $config->get('db_common_username'),
            $config->get('db_common_password'),
            $config->get('db_common_database'),
            $config->get('db_common_port')
        );
    }

    public function findById(ModuleId $id) : ?Module {
        $result = $this->db->query("SELECT * FROM module WHERE id = :id", array(
            ':id' => $id->getId()
        ));

        if(null == $result->row)
            return null;

        return $this->moduleFromDBO($result->row);
    }

    private function moduleFromDBO(array $dbo) : Module {
        return new Module(
            new ModuleId($dbo['id']),
            $dbo['name']
        );
    }
}
?>