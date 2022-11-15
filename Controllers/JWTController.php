<?php

namespace SimpleAPI\Controllers;
use Firebase\JWT\JWT;

class JWTController
{
    public static function Token($data)
    {
        $time = time();
        $exp = $time + 60 * 60 * 24;

        $token = [
            "iat" => $time,
            "exp" => $exp,
            "data" => $data,
        ];

        $jwt = JWT::encode($token, $_ENV["JWT_TOKEN"], "HS256");

        return [$jwt, $exp];
    }
}

?>
