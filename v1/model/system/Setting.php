<?php 

namespace model\system;

// TODO: Bu dosya icerisinde LoggingService ile ilgili islemler async hale getirilmeli. 
// Loglama isleminin basarisiz olabilecegi on gorulerek, bu islemin basarisiz olmasi durumunda bir davranis tanimlanmali. 
use \model\common\IOperationReporter;

class Setting {

    private \DB $db;
    private ?IOperationReporter $reporter;

    function __construct(?IOperationReporter $reporter = null) {
        global $framework;
        $config = $framework->get('config');

        $this->reporter = $reporter;

        $this->db = new \DB(
            $config->get('db_system_type'),
            $config->get('db_system_hostname'),
            $config->get('db_system_username'),
            $config->get('db_system_password'),
            $config->get('db_system_database'),
            $config->get('db_system_port')
        );
    }

    public function getSettings(string $category) {
        $result = $this->db->query("SELECT * FROM `setting` WHERE category = :category", array(
            ':category' => $category
        ));

        return $result->rows;
    }

    public function getSetting($key) {
        if('protocol' == $key)
            return 'SMTP';

        $result = $this->db->query("SELECT * FROM `setting` WHERE `key` = :key", array(
            ':key' => $key
        ));

        return $result->row['value'];
    }

    public function changeSetting($key, $value) {
        $this->db->update('setting', array(
            'value' => $value
        ), array(
            'key' => "'" . $key . "'"
        ));

        $this->report($key);
    }

    public function settingExists($key) {
        $result = $this->db->query("SELECT COUNT(*) as total FROM `setting` WHERE `key` = :key", array(
            ':key' => $key
        ));

        return $result->row['total'] > 0;
    }

    private function report($key) {
        if(null != $this->reporter) {
            $this->reporter->addOperation(
                'setting_changed',
                'setting',
                $key,
                $this->db->query("SELECT * FROM setting WHERE `key` = :key", array(
                    ':key' => $key
                ))->row,
                2
            );
        }
    }
}

?>
