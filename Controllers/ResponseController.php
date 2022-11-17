<?php

namespace SimpleAPI\Controllers;

class ResponseController
{
    public static function LogError(int $status, string $error)
    {
        $response = [
            "status" => $status,
            "error" => $error,
            "data" => "null",
        ];

        return json_encode($response, http_response_code($status));
    }

    public static function LogData(string $message, array|string $data)
    {
        $response = [
            "status" => 200,
            "error" => "null",
            "message" => $message,
            "data" => $data,
        ];

        return json_encode($response, http_response_code(200));
    }
}
