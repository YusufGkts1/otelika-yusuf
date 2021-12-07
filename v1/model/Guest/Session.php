<?php

namespace model\Guest;

class Session {
    private $db;
    private $config;

    function __construct() {
        global $framework;
        $config = $framework->get('config');
        $this->config = $config;

        $this->db = new \DB(
            $config->get('db_guest_type'),
            $config->get('db_guest_hostname'),
            $config->get('db_guest_username'),
            $config->get('db_guest_password'),
            $config->get('db_guest_database'),
            $config->get('db_guest_port')
        );
    }

    private function getSession(string $token) {
        $session = $this->db->query("SELECT * FROM `auth_session` WHERE token = :token", array(
            ':token' => $token
        ));

        return $session->row;
    }

    public function startSession(string $guest_id, string $ip) : string {
        $this->endSession($guest_id);

        $token = token(64);

        $this->db->command("INSERT INTO `auth_session` SET guest_id = :guest_id, token = :token, ip = :ip, expires_in = :expires_in, last_operation = CURRENT_TIMESTAMP()", array(
            ':guest_id' => $guest_id,
            ':token' => $token,
            ':ip' => $ip,
            ':expires_in' => $this->config->get('session_duration'),
        ));

        return $token;
    }

    public function endSession(string $grocer_id) {

        $this->db->command("DELETE FROM `auth_session` WHERE grocer_id = :grocer_id", array(
            ':grocer_id' => $grocer_id
        ));
    }

    public function authenticate(string $token, string $ip) {

        if(null == $token)
            return false;

        $session = $this->getSession($token);

        if(null == $session)
            return false;

        if($session['ip'] != $ip)
            return false;

        if((strtotime($session['last_operation']) + $session['expires_in']) < time())
            return false;

        $this->resetSessionDuration($token);

        return true;
    }

    public function getGuestId(string $token) : string {
        $result = $this->db->query("SELECT * FROM auth_session WHERE token = :token", array(
            ':token' => $token
        ));
        return $result->row['guest_id'];
    }

    private function resetSessionDuration(string $token) {
        $this->db->command("UPDATE `auth_session` SET last_operation = CURRENT_TIMESTAMP() WHERE `token` = :token", array(
            ':token' => $token
        ));
    }
}
?>
