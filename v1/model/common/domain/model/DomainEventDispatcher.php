<?php 

namespace model\common\domain\model;

use \model\system\log\Operator;
use \model\system\log\OperatorType;

class DomainEventDispatcher {
    static ?DomainEventDispatcher $publisher = null;

    private \DB $db;
    private \Loader $load;
    private \Session $session;
    private \UOW $uow;

    function __construct(\DB $connection, \Loader $loader, \Session $session, \UOW $uow) {
        $this->db = $connection;
        $this->load = $loader;
        $this->session = $session;
        $this->uow = $uow;
    }

    public function dispatchEvents(int $count) {
        $events = $this->db->query("SELECT * FROM `event` WHERE is_processed != '1' ORDER BY occurred_on ASC LIMIT " . (int)$count)->rows;

        foreach($events as $event) {
            try {
                $this->uow->begin();

                if(null == $event['trigger_type'] || null == $event['trigger_id'])
                    $operator = null;
                else
                    $operator = new Operator(
                        $event['trigger_type'],
                        $event['trigger_id']
                    );

                $this->session->set('operator', $operator);

                $module = $this->load->module($event['action_module'], true);
                
                $service = $module->service($event['action_service']);

                $method = $event['action_method'];

                $service->$method(json_decode($event['data']));
            }
            catch (\Throwable $e){
                $this->db->command("
                    UPDATE `event` SET has_failed = '1', error = :error WHERE id = :id
                ", array(
                    ':error' => $e->getMessage() . ' | ' . $e->getTraceAsString(),
                    ':id' => $event['id']
                ));

                // $this->db->update('event', array(
                //     'has_failed' => 1,
                //     'error' => $e->getMessage()
                // ), array(
                //     'id' => $event['id']
                // ));
            }

            $this->db->command("
                UPDATE `event` SET is_processed = '1' WHERE id = :id
            ", array(
                ':id' => $event['id']
            ));

            // $this->db->update('event', array(
            //     'is_processed' => 1
            // ), array(
            //     'id' => $event['id']
            // ));

            $this->uow->commit();
        }
    }

    private function getSubscribersForEvent($type) : array {
        /**
         * Veritabaninda icerisinde ters slash bulunan satirlar aranamiyor, o yuzden forward slash
         * kaydedilir ve araniyorken backwards slash ler forward slash lere donusturulur.
         */

        $subscribers = $this->db->query("SELECT * FROM subscription WHERE `type` = :type", array(
            ':type' => str_replace('\\', '/', $type)
        ))->rows;
        
        return $subscribers;
    }
}
?>