<?php

use \model\Sdm\Login;
use \model\Sdm\Session;


class ControllerSdmAuthorize extends Controller{

    private $db;
    /**
     * Yazilacak testler:
     *  Eger personnel is_active degilse authorize olmamali
     *  Eger parametre eksikse hata vermeli
     */
    public function index() {
        $valid_grant_types = array(
            'apikey',
            'ouath2',
            'basic'
        );

        if('POST' != $this->request->server['REQUEST_METHOD'])
            return $this->load->controller('http/error/422', 'request method must be \'POST\'');

        $request = json_decode(file_get_contents("php://input"));

        if(false == isset($request->data))
            return $this->load->controller('http/error/422', 'post body parameter \'data\' is missing');

        $data = $request->data;

        if(false == isset($data->grant_type))
            return $this->load->controller('http/error/422', 'grant type is missing');

        if(false == in_array($data->grant_type, $valid_grant_types))
            return $this->load->controller('http/error/422', 'Invalid grant type');

        switch($data->grant_type){
            case 'basic':
                if(false == isset($data->phone))
                    return $this->load->controller('http/error/422', 'phone is missing');

                if(false == isset($data->password))
                    return $this->load->controller('http/error/422', 'password is missing');

                $login_service = $this->loginService();

                $grocer_id = $login_service->loginIsValid($data->phone, $data->password);
                if(null == $grocer_id)
                    return $this->load->controller('http/error/401', 'could not find a login with provided credentials');

                $session = $this->sessionService();

                $token = $session->startSession($grocer_id, $this->request->server['REMOTE_ADDR']);
                return $this->load->controller('http/success/200', array(
                    'data' => array(
                        'token' => $token,
                        'token_type' => 'bearer'
                    )
                ));

            break;
        }
    }

      /**
	 * @OA\Post(
	 * 		path="/sdm/authorize",
	 * 		summary="Auth",
	 * 		tags={"Auth (Grocer)"},
	 * 
	 * 		@OA\RequestBody(
	 * 			@OA\JsonContent(
	 * 				@OA\Property(
	 * 					property="data",
	 * 					type="object",
	 * 					@OA\Property(
	 * 						property="attributes",
	 * 						type="object",
	 * 						ref="#/components/schemas/grocer_auth_post_body"
	 * 					)
	 * 				)
	 * 			)
     *        ),
     *     	@OA\Response(
	 * 			response="200",
	 * 			description="Success",
	 * 			@OA\JsonContent(
	 * 				type="object",
	 * 				@OA\Property(
	 * 					property="data",
	 * 					type="object",
	 * 					ref="#/components/schemas/grocer_auth"
	 * 				)
	 * 			)
	 * 		)
     * )
	 */

    private function loginService() : Login {
        $this->load->module('Sdm');

        return $this->module_sdm->service('Login');
    }

    private function sessionService() : Session {
        $this->load->module('Sdm');

        return $this->module_sdm->service('Session');
    }
}
?>