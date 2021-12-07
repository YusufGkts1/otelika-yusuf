<?php 

class ControllerStartupFilter extends Controller {
    public function index() {
        if('OPTIONS' == $this->request->server['REQUEST_METHOD'])
            return new Action('startup/cors');

        if(false == isset($this->request->get['_route_']))
            return $this->load->controller('http/error/403', 'You are Not Authorized');
    }
}

?>