<?php

namespace SimpleAPI\Controllers;

use SimpleAPI\Database\DBController;
use Firebase\JWT\JWT;

class JWTController
{
    public static function CreateToken($data)
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

    public static function CheckToken($token)
    {
        $query = "SELECT token_user,token_exp_user FROM users WHERE token_user = '$token'";
        $token = DBController::query($query);

        if (!empty($token)) {
            $time = time();

            if ($token[0]->token_exp_user > $time) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public static function UpdateToken(array $token, int $id)
    {
        $set = "token_user = '$token[0]', token_exp_user = '$token[1]'";
        $update = "UPDATE users SET $set WHERE id_user = $id";
        DBController::query($update);
    }
}
