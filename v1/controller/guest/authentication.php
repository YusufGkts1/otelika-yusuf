<?php

use model\Guest\Session;

use \model\system\log\OperatorType;

class ControllerGuestAuthentication extends Controller {

public function index() {
	if(in_array($this->request->get['_route_'], $this->config->get('insecure_endpoints')))  // Bu kontrol assagidakinden cok daha hizli. Assagidaki kontrol bu kontrolu kapsamasina ragmen optimizasyon icin once bu kontrol yapildi.
		return;
	else {

		$split = explode('/', $this->request->get['_route_']);

		$is_grocer_endpoint = false;

		while(count($split) > 0) {
			if(in_array(implode('/', $split), $this->config->get('insecure_endpoints')))
				return;

			if(in_array(implode('/', $split), $this->config->get('guest_endpoints')))
				$is_grocer_endpoint = true;

			array_pop($split);
		}

		if(!$is_grocer_endpoint)
			return;	
	}

	$bearer_token = $this->getKey('Bearer');

	if($bearer_token) {
		$session = $this->sessionService();

		$is_authenticated = $session->authenticate($bearer_token, $this->request->server['REMOTE_ADDR']);

		if(false == $is_authenticated)

			return $this->load->controller('http/error/401', 'authentication failed');

		$this->session->set('operator', new Operator(
			OperatorType::Grocer, $session->getGrocerId($bearer_token)
		));
		}
		else
			return $this->load->controller('http/error/401', 'authentication token is not found');
    }

	private function sessionService() : Session {
		$this->load->module('Sdm');

		return $this->module_sdm->service('Session');
	}

    private function getAuthorizationHeader(){
		$headers = null;
		if (isset($this->request->server['Authorization'])) {
			$headers = trim($this->request->server['Authorization']);
		}
		else if (isset($this->request->server['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
			$headers = trim($this->request->server['HTTP_AUTHORIZATION']);
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			//print_r($requestHeaders);
			if (isset($requestHeaders['Authorization'])) {
				$headers = trim($requestHeaders['Authorization']);
			}
		}
		return $headers;
	}

	private function getKey(string $type) {
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/' . $type . '\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}
?>