<?php

namespace model\notification;

class Mail {

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

    public function addToQueue(string $subject, string $message, string $recipient) {
        $this->mydb->command("INSERT INTO `mail` SET `subject` = :subject, `message` = :message, recipient = :recipient, is_processed = '0', queued_on = NOW()", array(
            ':subject' => $subject,
            ':message' => $message,
            ':recipient' => $recipient
        ));
    }

    public function getFromQueue(int $limit) {
        $results = $this->mydb->query("SELECT * FROM `mail` WHERE is_processed = '0' LIMIT " . (int)$limit)->rows;

        foreach($results as $result) {
            $this->mydb->command("UPDATE `mail` SET is_processed = '1', sent_on = NOW() WHERE `id` = :id", array(
                ':id' => $result['id']
            ));
        }

        return $results;
    }
}

?>
