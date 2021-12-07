<?php 

namespace model\notification;

class Sms {

    private \DB $mydb;

    function __construct() {
        global $framework;
        $config = $framework->get('config');

        $this->mydb = new \DB(
            $config->get('db_notification_type'),
            $config->get('db_notification_hostname'),
            $config->get('db_notification_username'),
            $config->get('db_notification_password'),
            $config->get('db_notification_database'),
            $config->get('db_notification_port')
        );
    }

    public function addToQueue(string $message, string $phone) {
        $this->mydb->command("INSERT INTO `sms` SET `message` = :message, `phone` = :phone, is_processed = '0', queued_on = NOW()", array(
            ':message' => $message,
            ':phone' => $phone
        ));
    }

    public function getFromQueue(int $limit) {
        $results = $this->mydb->query("SELECT * FROM `sms` WHERE is_processed = '0' LIMIT " . (int)$limit)->rows;

        foreach($results as $result) {
            $this->mydb->command("UPDATE `sms` SET is_processed = '1', sent_on = NOW() WHERE `id` = :id", array(
                ':id' => $result['id']
            ));
        }

        return $results;
    }
}

?>
