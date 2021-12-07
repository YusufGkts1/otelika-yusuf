<?php 

namespace model\Guest;

use \model\auth\exception\PasswordLengthException;

// TODO: Bu dosya icerisinde LoggingService ile ilgili islemler AuthEventProcessor icerisine tasinmali ve async hale getirilmeli. 
// Loglama isleminin basarisiz olabilecegi on gorulerek, bu islemin basarisiz olmasi durumunda bir davranis tanimlanmali. 
use \model\common\IOperationReporter;

class Login {
    private $db;
    private $reporter;
     
    function __construct(?IOPerationReporter $reporter = null) {
        global $framework;
        $config = $framework->get('config');

        $this->reporter = $reporter;

        $this->db = new \DB(
            $config->get('db_auth_type'),
            $config->get('db_auth_hostname'),
            $config->get('db_auth_username'),
            $config->get('db_auth_password'),
            $config->get('db_auth_database'),
            $config->get('db_auth_port')
        );
    }

    public function validLoginWithEmail(string $email) : array {
        $result = $this->db->query("SELECT * FROM `login` WHERE email = :email AND is_active = '1'", array(
            ':email' => $email
        ));

        if(null == $result)
            return false;

        return $result->row;
    }

    public function loginIsValid(string $guest_phone_or_tc_or_passport_no) : ?int {
        $result = $this->db->query("SELECT * FROM `login` WHERE guest_phone_or_tc_or_passport_no = :guest_phone_or_tc_or_passport_no", array(
            ':guest_phone_or_tc_or_passport_no' => $guest_phone_or_tc_or_passport_no
        ))->row;

        if(null == $result)
            return false;

        if(false == $result['is_active'])
            return false;

        if(null != $result)
            return $result['guest_id'];
    }

    public function addLogin(int $personnel_id, string $email, string $password, bool $is_active) {
        if(strlen($password) < 6 || strlen($password) > 32)
            throw new PasswordLengthException();

        $salt = token(9);

        $this->db->command("INSERT INTO `login` SET personnel_id = :personnel_id, email = :email, `password` = :password, `salt` = :salt, is_active = :is_active, date_added = NOW(), last_modification = NOW()", array(
            ':personnel_id' => $personnel_id,
            ':email' => $email,
            ':password' => sha1($salt . sha1($salt . sha1($password))),
            ':salt' => $salt,
            ':is_active' => $is_active
        ));

        $this->report('login_added', $this->db->getLastId());
    }

    public function updateLogin(int $personnel_id, ?string $email, ?string $password) {
        if(null !== $password && (strlen($password) < 6 || strlen($password) > 32))
            throw new PasswordLengthException();

        $salt = token(9);

        if($email) {
            $this->db->command("UPDATE `login` SET email = :email, last_modification = NOW() WHERE personnel_id = :personnel_id", array(
                ':personnel_id' => $personnel_id,
                ':email' => $email
            ));    
        }

        if($password) {
            $this->db->command("UPDATE `login` SET `password` = :password, `salt` = :salt, last_modification = NOW() WHERE personnel_id = :personnel_id", array(
                ':personnel_id' => $personnel_id,
                ':password' => sha1($salt . sha1($salt . sha1($password))),
                ':salt' => $salt
            ));
        }
        
        /* report */

        $login = $this->db->query("SELECT * FROM `login` WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ))->row;

        $this->report(
            'login_updated',
            $login['id']
        );
    }

    public function deactivateLogin(int $personnel_id) {
        $this->db->command("UPDATE `login` SET is_active = '0' WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ));

        /* report */

        $login = $this->db->query("SELECT * FROM `login` WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ))->row;

        $this->report('login_deactivated', $login['id']);
    }

    public function activateLogin(int $personnel_id) {
        $this->db->command("UPDATE `login` SET is_active = '1' WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ));

        /* report */

        $login = $this->db->query("SELECT * FROM `login` WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ))->row;

        $this->report('login_activated', $login['id']);
    }

    public function deleteLogin(int $personnel_id) {
        /* report */

        $login = $this->db->query("SELECT * FROM `login` WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ))->row;

        $this->report('login_removed', $login['id']);
        
        $this->db->command("INSERT INTO `login_bin` SELECT *, NOW() as removal_date FROM `login` WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ));

        $this->db->command("DELETE FROM `login` WHERE personnel_id = :personnel_id", array(
            ':personnel_id' => $personnel_id
        ));
    }

    private function report($operation, $id) {
        try {
            if(null != $this->reporter) {
                $this->reporter->addOperation(
                    $operation,
                    'login',
                    $id,
                    $this->db->query("SELECT id, personnel_id, email, is_active, date_added, last_modification FROM `login` WHERE `id` = :id", array(
                        ':id' => $id
                    ))->row,
                    null
                );
            }
        }
        catch(\Exception $e) {
            
        }
    }
}
?>
