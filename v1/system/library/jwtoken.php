<?php 

use Firebase\JWT\JWT;

class JWToken {
    private string $password;

    function __construct(string $password) {
        $this->password = $password;
    }

    public function encode(array $payload) : string {
        return JWT::encode($payload, $this->password);
    }

    public function decode(string $jwt) {
        return JWT::decode($jwt, $this->password, array('HS256'));
    }
}

?>