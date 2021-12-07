<?php 

namespace model\common\infrastructure;

use \model\common\domain\model\ModuleId;
use \model\common\domain\model\SubmoduleId;
use \model\common\domain\model\Submodule;
use \model\common\domain\model\ISubmoduleRepository;

class SubmoduleRepository implements ISubmoduleRepository {
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

    public function findById(SubmoduleId $id) : ?Submodule {
        $result = $this->db->query("SELECT * FROM submodule WHERE id = :id", array(
            ':id' => $id->getId()
        ));

        if(null == $result->row)
            return null;

        return $this->submoduleFromDBO($result->row);
    }
    
    public function exists(SubmoduleId $id) : bool {
        $result = $this->db->query("SELECT COUNT(*) as total FROM `submodule` WHERE id = :id", array(
            ':id' => $id->getId()
        ));

        return $result->row['total'] > 0;
    }

    private function submoduleFromDBO(array $dbo) : Submodule {
        return new Submodule(
            new SubmoduleId($dbo['id']),
            new ModuleId($dbo['module_id']),
            $dbo['name']
        );
    }
}
?>