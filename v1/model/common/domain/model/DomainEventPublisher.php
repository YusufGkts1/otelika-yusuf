<?php 

namespace model\common\domain\model;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use \model\system\log\Operator;

class DomainEventPublisher {
    
    static ?DomainEventPublisher $publisher = null;

    private $db;
    private $config;
    private ?Operator $operator;

    public static function instance() : DomainEventPublisher {
        if(self::$publisher == null)
            self::$publisher = new DomainEventPublisher();

        return self::$publisher;
    }

    function __construct() {
        global $framework;
        $this->config = $framework->get('config');
        $this->operator = $framework->get('session')->get('operator');

        $this->db = new \DB(
            $this->config->get('db_event_type'),
            $this->config->get('db_event_hostname'),
            $this->config->get('db_event_username'),
            $this->config->get('db_event_password'),
            $this->config->get('db_event_database'),
            $this->config->get('db_event_port')
        );
    }

    public function publish(DomainEvent $event) {
        $subscriptions = $this->db->query("SELECT * FROM subscription WHERE `type` = :type", array(
            ':type' => str_replace('\\', '/', get_class($event))
        ))->rows;

        foreach($subscriptions as $subscription) {
            $this->db->insert('event', array(
                'type' => get_class($event),
                'action_module' => $subscription['action_module'],
                'action_service' => $subscription['action_service'],
                'action_method' => $subscription['action_method'],
                'data' => json_encode($event->data()),
                'version' => $event->eventVersion(),
                'trigger_type' => $this->operator ? $this->operator->type() : null,
                'trigger_id' => $this->operator ? $this->operator->id() : null,
                'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s')
            ));
        }

        /* trigger events */

        // $client = new Client(['base_uri' => 'localhost/v1/event/event']);

        // $headers = [
        //     'Authorization' => 'Event ' . $this->config->get('event_key')
        // ];

        // $request = new Request('POST', '', $headers);
        
        // try {
        //     $promise = $client->sendAsync($request)->then(function($response) {
        //         // echo PHP_EOL . "RESPONSE" . PHP_EOL;
        //         // var_dump($response->getBody()->getContents());
        //     });

        //     $promise->wait();
        // }
        // catch (\Exception $e) {
        //     // TODO: Burasi doldurulacak
        //     // do nothing...
        // }
    }
}
?>